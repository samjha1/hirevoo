<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Services\EmployerJobImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobImportController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved before you can import jobs.');
        }

        return view('hirevo.employer.jobs.import', [
            'templateHeaders' => EmployerJobImportService::CSV_HEADERS,
        ]);
    }

    public function store(Request $request, EmployerJobImportService $importService): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('error', 'Your account must be approved before you can import jobs.');
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'skip_duplicates' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('csv_file');
        if ($file === null) {
            return back()->with('error', 'Please choose a CSV file to upload.');
        }

        $path = $file->getRealPath();
        if (! is_string($path) || $path === '') {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        try {
            $summary = $importService->importFromCsvFile(
                $path,
                $user,
                $request->boolean('skip_duplicates'),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }

        $failedCount = count($summary['failed']);
        $message = "Import complete: {$summary['imported']} imported, {$summary['skipped']} skipped, {$failedCount} failed.";

        if ($failedCount > 0) {
            $details = collect($summary['failed'])
                ->take(5)
                ->map(fn (array $f) => "Line {$f['line']}: {$f['message']}")
                ->implode(' ');

            return redirect()
                ->route('employer.jobs.import')
                ->with('warning', $message.' '.$details);
        }

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', $message);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $filename = 'employer_jobs_template.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, EmployerJobImportService::CSV_HEADERS);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
