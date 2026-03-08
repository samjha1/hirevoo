<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request, EmployerJob $job): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }

        $query = $job->applications()->with(['user.candidateProfile', 'resume']);

        // Filters
        if ($request->filled('exp_min') && is_numeric($request->exp_min)) {
            $query->whereHas('user.candidateProfile', function ($q) use ($request) {
                $q->where('experience_years', '>=', (int) $request->exp_min);
            });
        }
        if ($request->filled('exp_max') && is_numeric($request->exp_max)) {
            $query->whereHas('user.candidateProfile', function ($q) use ($request) {
                $q->where('experience_years', '<=', (int) $request->exp_max);
            });
        }
        // Only apply min score filters when value is > 0 (0 or empty = show all, including NULL scores)
        if ($request->filled('ats_min') && is_numeric($request->ats_min) && (int) $request->ats_min > 0) {
            $query->where('ats_score', '>=', (int) $request->ats_min);
        }
        if ($request->filled('match_min') && is_numeric($request->match_min) && (int) $request->match_min > 0) {
            $query->where('job_match_score', '>=', (int) $request->match_min);
        }

        // Sort: default = highest match, then ATS, then experience, then date
        $sort = $request->get('sort', 'match');
        if ($sort === 'ats') {
            $query->orderByRaw('ats_score IS NULL')->orderByDesc('ats_score')->orderByDesc('created_at');
        } elseif ($sort === 'experience') {
            $query->leftJoin('candidate_profiles', function ($j) {
                $j->on('employer_job_applications.user_id', '=', 'candidate_profiles.user_id');
            })->orderByRaw('candidate_profiles.experience_years IS NULL')->orderByDesc('candidate_profiles.experience_years')->orderByDesc('employer_job_applications.created_at')->select('employer_job_applications.*');
        } elseif ($sort === 'date') {
            $query->orderByDesc('created_at');
        } else {
            // match (default): highest job match, then ATS, then date
            $query->orderByRaw('job_match_score IS NULL')->orderByDesc('job_match_score')->orderByRaw('ats_score IS NULL')->orderByDesc('ats_score')->orderByDesc('created_at');
        }

        $applications = $query->get();

        return view('hirevo.employer.applications.index', [
            'job'          => $job,
            'applications' => $applications,
            'filters'     => [
                'exp_min'  => $request->get('exp_min'),
                'exp_max'  => $request->get('exp_max'),
                'ats_min'  => $request->get('ats_min'),
                'match_min'=> $request->get('match_min'),
                'sort'     => $sort,
            ],
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

    /**
     * Calculate and store job match score for an application (e.g. for older applications that don't have it).
     */
    public function calculateMatch(EmployerJobApplication $application): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage applications.');
        }

        $job = $application->employerJob;
        $resume = $application->resume;
        $candidateProfile = $application->user->candidateProfile ?? null;

        $resumeText = null;
        if ($resume && Storage::disk('local')->exists($resume->file_path)) {
            $resumeText = app(ResumeAnalysisService::class)->extractTextFromFile(
                Storage::disk('local')->path($resume->file_path),
                $resume->mime_type ?? 'application/pdf'
            );
        }
        if (($resumeText === null || $resumeText === '') && $candidateProfile) {
            $parts = [];
            if ($candidateProfile->headline) {
                $parts[] = $candidateProfile->headline;
            }
            if ($candidateProfile->education) {
                $parts[] = 'Education: ' . $candidateProfile->education;
            }
            if ($candidateProfile->experience_years !== null) {
                $parts[] = 'Experience: ' . $candidateProfile->experience_years . ' years';
            }
            if ($candidateProfile->skills) {
                $parts[] = 'Skills: ' . (is_array($candidateProfile->skills) ? implode(', ', $candidateProfile->skills) : $candidateProfile->skills);
            }
            $resumeText = implode("\n", $parts) ?: 'Candidate profile.';
        }
        if ($resumeText === null || $resumeText === '') {
            return redirect()->back()->with('error', 'No resume or profile text available to calculate match score.');
        }

        $match = null;
        $gpt = app(GptService::class);
        if ($gpt->isAvailable()) {
            $match = $gpt->getResumeJobMatchScore(
                $resumeText,
                $job->title,
                $job->description ?? '',
                []
            );
        }
        if ($match === null) {
            $match = app(ResumeAnalysisService::class)->getEmployerJobMatchRuleBased(
                $resumeText,
                $job->title,
                $job->description ?? ''
            );
        }

        $application->update([
            'job_match_score' => $match['score'],
            'job_match_explanation' => $match['explanation'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Match score calculated: ' . $match['score'] . '%');
    }
}
