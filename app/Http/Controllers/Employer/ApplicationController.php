<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\InterviewSchedule;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * ATS / Pipeline tracking board per job.
     * Shows applications grouped by their current stage (status) and allows moving them.
     */
    public function pipeline(Request $request, EmployerJob $job): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }

        $focus = $request->get('focus'); // optional: 'shortlisted' or any other status key
        $validStatuses = array_keys(EmployerJobApplication::statusOptions());
        if ($focus && ! in_array($focus, $validStatuses, true)) {
            $focus = null;
        }

        // Dropdown should show only "posted" jobs (active), not drafts.
        $jobsForSelect = $user->employerJobs()
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'status']);

        // Safety: if current $job is not active for some reason, keep it selectable in the dropdown.
        if ($job->status !== 'active' && $job->exists) {
            $jobsForSelect = $jobsForSelect->prepend($job);
        }

        // Load applications once, then group in the view.
        $applications = $job->applications()
            ->with(['user.candidateProfile', 'resume'])
            ->get();

        return view('hirevo.employer.applications.pipeline', [
            'job' => $job,
            'jobsForSelect' => $jobsForSelect,
            'applications' => $applications,
            'focus' => $focus,
        ]);
    }

    /**
     * Application detail view (candidate info + resume viewer + match scores).
     */
    public function show(Request $request, EmployerJobApplication $application): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to view applications.');
        }

        $application->load(['employerJob', 'user.candidateProfile', 'resume', 'interviews']);

        return view('hirevo.employer.applications.show', [
            'application' => $application,
        ]);
    }

    /**
     * Schedule an interview for a candidate application.
     * - Auto-generates meeting URL for Video interviews (placeholder links).
     * - Moves application stage to "interviewed".
     */
    public function storeInterview(Request $request, EmployerJobApplication $application): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage applications.');
        }

        $validated = $request->validate([
            'interview_type'    => ['required', 'in:phone,video,in_person'],
            'interviewer_name'  => ['nullable', 'string', 'max:255'],
            'scheduled_at'      => ['required', 'date'],
            'duration_minutes'  => ['nullable', 'integer', 'min:15', 'max:240'],
            'meeting_provider'  => ['nullable', 'in:zoom,google_meet,teams'],
            'meeting_url'       => ['nullable', 'url', 'max:2000'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ]);

        $interviewType = $validated['interview_type'];
        $provider = $validated['meeting_provider'] ?? 'google_meet';

        $meetingUrl = $validated['meeting_url'] ?? null;
        if ($meetingUrl === '') {
            $meetingUrl = null;
        }

        if ($interviewType === 'video' && ! $meetingUrl) {
            $token = substr(str_replace('-', '', (string) Str::uuid()), 0, 12);
            if ($provider === 'zoom') {
                $meetingUrl = 'https://zoom.us/j/' . random_int(1000000000, 9999999999);
            } elseif ($provider === 'teams') {
                $meetingUrl = 'https://teams.microsoft.com/l/meetup-join/' . $token;
            } else {
                // Google Meet join link format: https://meet.google.com/xxx-yyyy-zzz (3-4-3).
                // We generate an alphanumeric token then split into the expected groups.
                $meetToken = Str::random(10); // xxx-yyyy-zzz => 3 + 4 + 3
                $a = substr($meetToken, 0, 3);
                $b = substr($meetToken, 3, 4);
                $c = substr($meetToken, 7, 3);
                $meetingUrl = 'https://meet.google.com/' . $a . '-' . $b . '-' . $c;
            }
        }

        $interview = InterviewSchedule::create([
            'employer_job_application_id' => $application->id,
            'interview_type'               => $interviewType,
            'interviewer_name'            => $validated['interviewer_name'] ?? null,
            'scheduled_at'                => $validated['scheduled_at'],
            'duration_minutes'           => (int) ($validated['duration_minutes'] ?? 30),
            'meeting_url'                => $meetingUrl,
            'status'                      => 'scheduled',
            'notes'                       => $validated['notes'] ?? null,
        ]);

        // Move candidate to Interviewed stage when scheduling.
        $application->update(['status' => EmployerJobApplication::STATUS_INTERVIEWED]);

        // Best-effort notifications (won't break scheduling if mail isn't configured).
        try {
            $candidateEmail = $application->user?->email;
            $companyEmail = $application->employerJob?->user?->email;
            $candidateName = $application->user?->name ?: 'Candidate';
            $jobTitle = $application->employerJob?->title ?: 'Job';
            $when = $interview->scheduled_at instanceof \Carbon\CarbonInterface
                ? $interview->scheduled_at->format('d M Y, g:i A')
                : (string) $interview->scheduled_at;
            $duration = (int) ($interview->duration_minutes ?? 30);
            $typeLabel = $interviewType === 'in_person' ? 'In-Person' : ucfirst($interviewType);
            $meetLine = $meetingUrl ? "Meeting link: {$meetingUrl}\n" : '';
            $interviewerLine = ! empty($validated['interviewer_name'])
                ? "Interviewer: {$validated['interviewer_name']}\n"
                : '';

            if ($candidateEmail) {
                Mail::raw(
                    "Hi {$candidateName},\n\nYour interview has been scheduled.\n\n"
                    . "Job: {$jobTitle}\n"
                    . "Type: {$typeLabel}\n"
                    . "When: {$when}\n"
                    . "Duration: {$duration} mins\n"
                    . $interviewerLine
                    . $meetLine,
                    function ($m) use ($candidateEmail) {
                        $m->to($candidateEmail)->subject('Interview Scheduled');
                    }
                );
            }

            if ($companyEmail) {
                Mail::raw(
                    "Interview scheduled.\n\n"
                    . "Candidate: {$candidateName}\n"
                    . "Job: {$jobTitle}\n"
                    . "Type: {$typeLabel}\n"
                    . "When: {$when}\n"
                    . "Duration: {$duration} mins\n"
                    . $interviewerLine
                    . $meetLine,
                    function ($m) use ($companyEmail) {
                        $m->to($companyEmail)->subject('Interview Scheduled');
                    }
                );
            }
        } catch (\Throwable $e) {
            // Ignore notification failures for now.
        }

        return redirect()->back()->with('success', 'Interview scheduled successfully.');
    }

    /**
     * Cancel an interview schedule.
     */
    public function cancelInterview(Request $request, InterviewSchedule $interview): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            abort(403);
        }

        $application = $interview->application()->with('employerJob')->first();
        if (! $application || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }

        $interview->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Interview cancelled.');
    }

    /**
     * Download an iCalendar (.ics) invite for a scheduled interview.
     */
    public function calendarInvite(Request $request, InterviewSchedule $interview): Response
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            abort(403);
        }

        $interview->load(['application.employerJob', 'application.user']);
        $application = $interview->application;
        if (! $application || ! $application->employerJob || $application->employerJob->user_id !== $user->id) {
            abort(403);
        }

        $jobTitle = $application->employerJob->title ?? 'Interview';
        $candidateName = $application->user->name ?? 'Candidate';
        $meetingUrl = $interview->meeting_url;

        $startUtc = $interview->scheduled_at->copy()->setTimezone('UTC');
        $endUtc = $interview->scheduled_at->copy()->addMinutes((int) $interview->duration_minutes)->setTimezone('UTC');

        $icsEscape = static function (?string $value): string {
            $value = $value ?? '';
            // Escape special chars for iCalendar fields
            return str_replace(
                ["\\", "\r\n", "\n", "\r", ',', ';'],
                ["\\\\", "\\n", "\\n", "\\n", '\\,', '\\;'],
                $value
            );
        };

        $uid = ($interview->id ?: (string) Str::uuid()) . '@hirevo';
        $dtStamp = now()->utc()->format('Ymd\\THis\\Z');
        $dtStart = $startUtc->format('Ymd\\THis\\Z');
        $dtEnd = $endUtc->format('Ymd\\THis\\Z');

        $summary = $icsEscape("Interview - {$jobTitle} ({$candidateName})");
        $descriptionParts = [
            "Candidate: {$candidateName}",
            "Job: {$jobTitle}",
            $meetingUrl ? "Meeting link: {$meetingUrl}" : null,
            $interview->notes ? "Notes: {$interview->notes}" : null,
        ];
        $description = $icsEscape(implode("\\n", array_filter($descriptionParts)));

        $location = $interview->interview_type === 'in_person'
            ? $icsEscape('On-site / In-Person')
            : $icsEscape($interview->meeting_url ? 'Online' : 'TBD');

        $body = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//Hirevo//Interview Scheduling//EN\r\n"
            . "CALSCALE:GREGORIAN\r\n"
            . "METHOD:PUBLISH\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:{$uid}\r\n"
            . "DTSTAMP:{$dtStamp}\r\n"
            . "DTSTART:{$dtStart}\r\n"
            . "DTEND:{$dtEnd}\r\n"
            . "SUMMARY:{$summary}\r\n"
            . "DESCRIPTION:{$description}\r\n"
            . "LOCATION:{$location}\r\n"
            . "URL:{$icsEscape($meetingUrl)}\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        $filename = 'interview-' . $interview->id . '.ics';
        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

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

        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, [10, 25, 50], true)) {
            $perPage = 10;
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

        // Apply filters/sorting on full query, then paginate the filtered result.
        $applications = $query->paginate($perPage)->withQueryString();

        return view('hirevo.employer.applications.index', [
            'job'          => $job,
            'applications' => $applications,
            'filters'     => [
                'exp_min'  => $request->get('exp_min'),
                'exp_max'  => $request->get('exp_max'),
                'ats_min'  => $request->get('ats_min'),
                'match_min'=> $request->get('match_min'),
                'sort'     => $sort,
                'per_page' => $perPage,
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
        $requiredSkills = is_array($job->required_skills)
            ? $job->required_skills
            : (is_string($job->required_skills) ? preg_split('/[\r\n,;|]+/', $job->required_skills) : []);
        $requiredSkills = array_values(array_filter(array_map('trim', $requiredSkills ?? []), fn ($s) => $s !== ''));
        if ($gpt->isAvailable()) {
            $match = $gpt->getResumeJobMatchScore(
                $resumeText,
                $job->title,
                $job->description ?? '',
                $requiredSkills
            );
        }
        if ($match === null) {
            $match = app(ResumeAnalysisService::class)->getEmployerJobMatchRuleBased(
                $resumeText,
                $job->title,
                $job->description ?? '',
                $requiredSkills
            );
        }

        $application->update([
            'job_match_score' => $match['score'],
            'job_match_explanation' => $match['explanation'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Match score calculated: ' . $match['score'] . '%');
    }
}
