<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(EmployerJob $job): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }

        $applications = $job->applications()
            ->with(['user.candidateProfile', 'resume'])
            ->orderByDesc('created_at')
            ->get();

        return view('hirevo.employer.applications.index', [
            'job'          => $job,
            'applications' => $applications,
        ]);
    }

    public function updateStatus(Request $request, EmployerJobApplication $application): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage applications.');
        }
        $valid = $request->validate(['status' => 'required|in:' . implode(',', array_keys(EmployerJobApplication::statusOptions()))]);
        $application->update(['status' => $valid['status']]);
        return redirect()->back()->with('success', 'Application status updated.');
    }

    public function viewResume(EmployerJobApplication $application): BinaryFileResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }
        if (! $application->resume_id || ! $application->resume) {
            return redirect()->back()->with('error', 'No resume attached.');
        }
        $path = $application->resume->file_path;
        if (! Storage::disk('local')->exists($path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }
        $mime = $application->resume->mime_type ?? 'application/pdf';
        $filename = $application->resume->file_name ?? 'resume.pdf';
        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function downloadResume(EmployerJobApplication $application): BinaryFileResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }
        if (! $application->resume_id || ! $application->resume) {
            return redirect()->back()->with('error', 'No resume attached.');
        }
        $path = $application->resume->file_path;
        if (! Storage::disk('local')->exists($path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }
        return response()->download(Storage::disk('local')->path($path), $application->resume->file_name ?? 'resume.pdf', [
            'Content-Type' => $application->resume->mime_type ?? 'application/pdf',
        ]);
    }
}
