<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoredFile
{
    public static function uploadsDisk(): string
    {
        $bucket = (string) config('filesystems.disks.s3.bucket');
        $key = (string) config('filesystems.disks.s3.key');
        $secret = (string) config('filesystems.disks.s3.secret');

        if ($bucket !== '' && $key !== '' && $secret !== '') {
            return 's3';
        }

        return (string) config('filesystems.default', 'local');
    }

    public static function imageMaxKb(): int
    {
        return (int) config('hirevo.image_upload_max_kb', 10240);
    }

    public static function imageMaxBytes(): int
    {
        return self::imageMaxKb() * 1024;
    }

    /**
     * Store an uploaded file on S3 (when configured) and return the value for the database.
     * S3: full AWS URL. Local: relative path.
     */
    public static function storeUploadedFile(UploadedFile $file, string $directory): string|false
    {
        $disk = self::uploadsDisk();

        try {
            $path = $file->store($directory, ['disk' => $disk]);
        } catch (\Throwable $e) {
            Log::error('StoredFile upload failed', [
                'disk' => $disk,
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        if ($path === false) {
            return false;
        }

        return self::databaseValueFromStoragePath($path, $disk);
    }

    /**
     * Convert a storage path/key to the value saved in the database.
     */
    public static function databaseValueFromStoragePath(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? self::uploadsDisk();

        if ($disk === 's3') {
            return Storage::disk('s3')->url($path);
        }

        return $path;
    }

    /**
     * Permanent public-style URL for a stored key or legacy full S3 URL (not for browser display on private buckets).
     */
    public static function permanentUrl(string $stored): string
    {
        $key = self::resolveStorageKey($stored);
        if ($key !== null && self::uploadsDisk() === 's3') {
            return Storage::disk('s3')->url($key);
        }

        return $stored;
    }

    /**
     * Normalize a DB value (S3 key, full S3 URL, or legacy local path) to an S3 object key.
     */
    public static function resolveStorageKey(?string $stored): ?string
    {
        if (! filled($stored)) {
            return null;
        }

        if (self::isAbsoluteUrl($stored)) {
            return self::objectKeyFromUrl($stored);
        }

        if (str_starts_with($stored, 'uploads/')) {
            return null;
        }

        return ltrim(str_replace('\\', '/', $stored), '/');
    }

    public static function isAbsoluteUrl(string $stored): bool
    {
        return str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://');
    }

    /**
     * Resolve a stored value (full URL or legacy relative path) to a browser-accessible URL.
     * S3 objects use presigned URLs when the bucket is private.
     */
    public static function url(?string $stored): ?string
    {
        if (! filled($stored)) {
            return null;
        }

        if (str_starts_with($stored, 'uploads/')) {
            return asset($stored);
        }

        if (self::uploadsDisk() === 's3') {
            $key = self::resolveStorageKey($stored);
            if ($key !== null) {
                if (self::shouldUseSignedUrls()) {
                    return self::signedUrlFromKey($key) ?? Storage::disk('s3')->url($key);
                }

                return Storage::disk('s3')->url($key);
            }
        }

        if (str_starts_with($stored, 'profile-photos/')) {
            return Storage::disk('public')->url($stored);
        }

        return null;
    }

    public static function shouldUseSignedUrls(): bool
    {
        if (self::uploadsDisk() !== 's3') {
            return false;
        }

        return filter_var(config('filesystems.disks.s3.use_signed_urls', true), FILTER_VALIDATE_BOOL);
    }

    public static function isS3Stored(string $stored): bool
    {
        if (self::uploadsDisk() !== 's3') {
            return false;
        }

        if (str_starts_with($stored, 'uploads/')) {
            return false;
        }

        $key = self::resolveStorageKey($stored);

        return $key !== null && Storage::disk('s3')->exists($key);
    }

    public static function signedUrl(string $stored, ?int $minutes = null): ?string
    {
        $key = self::resolveStorageKey($stored);
        if ($key === null) {
            return null;
        }

        return self::signedUrlFromKey($key, $minutes);
    }

    public static function signedUrlFromKey(string $key, ?int $minutes = null): ?string
    {
        $minutes = $minutes ?? max(5, (int) config('filesystems.disks.s3.signed_url_minutes', 1440));

        try {
            return Storage::disk('s3')->temporaryUrl($key, now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            Log::warning('StoredFile signed URL failed', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Stream an image for &lt;img src&gt; via the app (works with private S3 buckets).
     *
     * @return StreamedResponse|BinaryFileResponse
     */
    public static function imageResponse(string $stored, string $fallbackMime = 'image/jpeg'): StreamedResponse|BinaryFileResponse
    {
        if (str_starts_with($stored, 'uploads/')) {
            $path = public_path($stored);
            if (! is_readable($path)) {
                abort(404);
            }

            return response()->file($path, [
                'Content-Type' => self::mimeFromPath($path) ?? $fallbackMime,
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        $key = self::resolveStorageKey($stored);
        if ($key === null || ! Storage::disk('s3')->exists($key)) {
            abort(404);
        }

        $mime = $fallbackMime;
        try {
            $mime = Storage::disk('s3')->mimeType($key) ?: $mime;
        } catch (\Throwable) {
            $mime = self::mimeFromPath($key) ?? $mime;
        }

        return response()->stream(function () use ($key): void {
            echo Storage::disk('s3')->get($key);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    protected static function mimeFromPath(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        return $map[$ext] ?? null;
    }

    /**
     * Redirect to a browser-accessible URL for an S3 object (presigned when private).
     */
    public static function browserRedirect(string $stored): RedirectResponse
    {
        if (self::shouldUseSignedUrls() && self::isS3Stored($stored)) {
            $signed = self::signedUrl($stored);
            if ($signed !== null) {
                return redirect()->away($signed);
            }
        }

        return redirect()->away($stored);
    }

    /**
     * Legacy files saved before AWS (Hostinger / storage/app), e.g. resumes/abc.pdf.
     */
    public static function isLegacyLocalPath(string $stored): bool
    {
        if (self::isAbsoluteUrl($stored)) {
            return false;
        }

        return str_starts_with($stored, 'resumes/')
            || str_starts_with($stored, 'uploads/')
            || (! str_contains($stored, '://') && ! str_starts_with($stored, 'employer-profiles/')
                && ! str_starts_with($stored, 'profile-photos/'));
    }

    public static function isStoredOnS3(?string $stored): bool
    {
        if (! filled($stored)) {
            return false;
        }

        if (self::isAbsoluteUrl($stored)) {
            $bucket = (string) config('filesystems.disks.s3.bucket');

            return $bucket !== '' && str_contains($stored, $bucket);
        }

        $key = self::resolveStorageKey($stored);

        return $key !== null && Storage::disk('s3')->exists($key);
    }

    public static function exists(?string $stored): bool
    {
        if (! filled($stored)) {
            return false;
        }

        if (str_starts_with($stored, 'uploads/')) {
            return is_file(public_path($stored));
        }

        if (str_starts_with($stored, 'profile-photos/')) {
            return Storage::disk('public')->exists($stored);
        }

        if (self::isStoredOnS3($stored)) {
            $key = self::resolveStorageKey($stored);

            return $key !== null && Storage::disk('s3')->exists($key);
        }

        // Legacy Hostinger / server disk (storage/app/resumes/…)
        return Storage::disk('local')->exists($stored);
    }

    public static function delete(?string $stored): void
    {
        if (! filled($stored)) {
            return;
        }

        if (self::isStoredOnS3($stored)) {
            $key = self::resolveStorageKey($stored);
            if ($key !== null && Storage::disk('s3')->exists($key)) {
                Storage::disk('s3')->delete($key);
            }

            return;
        }

        if (str_starts_with($stored, 'uploads/')) {
            $full = public_path($stored);
            if (is_file($full)) {
                @unlink($full);
            }

            return;
        }

        if (str_starts_with($stored, 'profile-photos/')) {
            Storage::disk('public')->delete($stored);

            return;
        }

        Storage::disk('local')->delete($stored);
    }

    /**
     * Return a local filesystem path suitable for reading (e.g. PDF parsing). Downloads remote files to a temp file.
     */
    public static function localPathForReading(?string $stored): ?string
    {
        if (! filled($stored)) {
            return null;
        }

        if (self::isStoredOnS3($stored)) {
            $key = self::resolveStorageKey($stored);
            if ($key !== null) {
                try {
                    $contents = Storage::disk('s3')->get($key);
                } catch (\Throwable) {
                    return null;
                }

                if ($contents !== null && $contents !== '') {
                    $tmp = tempnam(sys_get_temp_dir(), 'hirevo_');
                    if ($tmp !== false) {
                        file_put_contents($tmp, $contents);

                        return $tmp;
                    }
                }
            }
        }

        if (str_starts_with($stored, 'uploads/')) {
            $path = public_path($stored);

            return is_readable($path) ? $path : null;
        }

        if (str_starts_with($stored, 'profile-photos/')) {
            $path = Storage::disk('public')->path($stored);

            return is_readable($path) ? $path : null;
        }

        $path = storage_path('app/' . ltrim($stored, '/'));

        return is_readable($path) ? $path : null;
    }

    public static function objectKeyFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        if (! isset($parsed['path'])) {
            return null;
        }

        $path = ltrim($parsed['path'], '/');
        if ($path === '') {
            return null;
        }

        $bucket = (string) config('filesystems.disks.s3.bucket');
        if ($bucket !== '' && str_starts_with($path, $bucket . '/')) {
            return substr($path, strlen($bucket) + 1) ?: null;
        }

        return $path;
    }

    /**
     * @return BinaryFileResponse|RedirectResponse
     */
    public static function inlineResponse(string $stored, string $mime, string $filename): BinaryFileResponse|RedirectResponse
    {
        if (self::isStoredOnS3($stored)) {
            return self::browserRedirect($stored);
        }

        $path = self::localPathForReading($stored);
        if ($path === null) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
        ]);
    }

    /**
     * @return BinaryFileResponse|RedirectResponse|StreamedResponse
     */
    public static function downloadResponse(string $stored, string $filename, string $mime): BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        if (self::isStoredOnS3($stored)) {
            return self::browserRedirect($stored);
        }

        $path = self::localPathForReading($stored);
        if ($path === null) {
            abort(404);
        }

        return response()->download($path, $filename, [
            'Content-Type' => $mime,
        ]);
    }
}
