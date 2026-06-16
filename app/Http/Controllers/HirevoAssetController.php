<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HirevoAssetController extends Controller
{
    public function candidateCss(): Response|BinaryFileResponse
    {
        $path = $this->resolveCandidateCssPath();

        if ($path === null) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function candidateCssContents(): ?string
    {
        $path = $this->resolveCandidateCssPath();

        if ($path === null) {
            return null;
        }

        $contents = file_get_contents($path);

        return is_string($contents) && $contents !== '' ? $contents : null;
    }

    public function candidateCssVersion(): string
    {
        $path = $this->resolveCandidateCssPath();

        return $path !== null ? (string) filemtime($path) : '1';
    }

    private function resolveCandidateCssPath(): ?string
    {
        foreach ([
            resource_path('css/hirevo-candidate.css'),
            public_path('css/hirevo-candidate.css'),
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
