<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\EmployerJob;
use App\Models\EmployerJobApplication;
use App\Models\JobApplication;
use App\Models\JobRole;
use App\Models\Resume;
use App\Models\UpskillOpportunity;
use App\Services\GptService;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $jobRoles = JobRole::where('is_active', true)->orderBy('title')->limit(8)->get();
        return view('hirevo.index', compact('jobRoles'));
    }

    public function jobList(Request $request): View
    {
        $query = JobRole::where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $jobRoles = $query->orderBy('title')->get();
        $appliedJobIds = auth()->check()
            ? \App\Models\JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all()
            : [];
        $searchQuery = $request->get('q', '');
        $searchLocation = $request->get('location', '');

        return view('hirevo.job-list', compact('jobRoles', 'appliedJobIds', 'searchQuery', 'searchLocation'));
    }

    public function skillMatch(JobRole $jobRole, ResumeAnalysisService $resumeAnalysis): View
    {
        $jobRole->load('requiredSkills');
        $requiredSkills = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();

        $matchPercentage = 0;
        $matchedSkills = [];
        $missingSkills = $requiredSkills;
        $candidateSkills = [];

        $primaryResume = null;
        $matchingJobGoals = [];
        $upskillOpportunities = [];
        $appliedJobIds = [];
        $userSkillsForUpskill = [];

        if (auth()->check() && auth()->user()->isCandidate()) {
            $appliedJobIds = JobApplication::where('user_id', auth()->id())->pluck('job_role_id')->all();
            $primaryResume = auth()->user()->resumes()->where('is_primary', true)->first()
                ?? auth()->user()->resumes()->orderByDesc('created_at')->first();
            if ($primaryResume) {
                $matchingJobGoals = $resumeAnalysis->getMatchingJobGoalsForResume($primaryResume, 20);
                $upskillOpportunities = UpskillOpportunity::active()->orderBy('sort_order')->get();
                $userSkillsForUpskill = array_map('strtolower', $primaryResume->getExtractedSkillsList());
            }
            $profile = auth()->user()->candidateProfile;
            if ($profile && ! empty($profile->skills)) {
                $candidateSkills = array_map(function ($s) {
                    return strtolower(trim($s));
                }, preg_split('/[\s,;|]+/', $profile->skills, -1, PREG_SPLIT_NO_EMPTY));
                $candidateSkills = array_unique($candidateSkills);

                if (count($requiredSkills) > 0) {
                    $matchedSkills = array_values(array_intersect($requiredSkills, $candidateSkills));
                    $missingSkills = array_values(array_diff($requiredSkills, $candidateSkills));
                    $matchPercentage = (int) round((count($matchedSkills) / count($requiredSkills)) * 100);
                }
            } else {
                $missingSkills = $requiredSkills;
            }
        }

        $hasApplied = auth()->check()
            ? JobApplication::where('user_id', auth()->id())->where('job_role_id', $jobRole->id)->exists()
            : false;

        // Related employer job openings: match by job role title and required skills keywords
        $keywords = array_merge(
            array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/i', ' ', $jobRole->title))),
            $jobRole->requiredSkills->pluck('skill_name')->take(5)->map(fn ($s) => trim($s))->all()
        );
        $keywords = array_values(array_unique(array_filter(array_map('strtolower', $keywords), fn ($kw) => strlen($kw) >= 2)));
        $relatedJobs = collect();
        if (count($keywords) > 0) {
            $query = EmployerJob::where('status', 'active')->with('user');
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', '%' . $kw . '%')
                        ->orWhere('description', 'like', '%' . $kw . '%');
                }
            });
            $relatedJobs = $query->orderByDesc('created_at')->limit(10)->get();
        }
        $appliedEmployerJobIds = auth()->check()
            ? EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all()
            : [];

        return view('hirevo.skill-match', [
            'jobRole' => $jobRole,
            'requiredSkills' => $jobRole->requiredSkills,
            'matchPercentage' => $matchPercentage,
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'candidateSkills' => $candidateSkills,
            'hasProfile' => auth()->check() && auth()->user()->candidateProfile,
            'hasApplied' => $hasApplied,
            'primaryResume' => $primaryResume,
            'matchingJobGoals' => $matchingJobGoals,
            'upskillOpportunities' => $upskillOpportunities,
            'appliedJobIds' => $appliedJobIds,
            'userSkillsForUpskill' => $userSkillsForUpskill,
            'relatedJobs' => $relatedJobs,
            'appliedEmployerJobIds' => $appliedEmployerJobIds,
        ]);
    }

    public function pricing(): View
    {
        return view('hirevo.pricing');
    }

    public function jobOpenings(Request $request): View
    {
        $query = EmployerJob::where('status', 'active')->with(['user.referrerProfile']);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhereHas('user.referrerProfile', function ($uq) use ($q) {
                        $uq->where('company_name', 'like', '%' . $q . '%');
                    });
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->get('location') . '%');
        }

        $validJobTypes = ['full_time', 'part_time', 'contract', 'internship', 'temporary', 'volunteer', 'other'];
        if ($request->filled('job_type') && in_array($request->get('job_type'), $validJobTypes, true)) {
            $query->where('job_type', $request->get('job_type'));
        }

        $validWorkTypes = ['On-site', 'remote', 'hybrid'];
        if ($request->filled('work_location_type') && in_array($request->get('work_location_type'), $validWorkTypes, true)) {
            $query->where('work_location_type', $request->get('work_location_type'));
        }

        $jobs = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $appliedIds = auth()->check()
            ? EmployerJobApplication::where('user_id', auth()->id())->pluck('employer_job_id')->all()
            : [];
        $searchQuery = $request->get('q', '');
        $searchLocation = $request->get('location', '');
        $filterJobType = $request->get('job_type', '');
        $filterWorkType = $request->get('work_location_type', '');

        $locationOptions = EmployerJob::where('status', 'active')
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values()
            ->all();

        return view('hirevo.job-openings', compact(
            'jobs', 'appliedIds', 'searchQuery', 'searchLocation',
            'filterJobType', 'filterWorkType', 'locationOptions'
        ));
    }

    public function showEmployerJobApply(EmployerJob $job): View|RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (auth()->check() && ! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings')->with('info', 'Only candidates can apply.');
        }
        if (auth()->check()) {
            $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
            if ($exists) {
                return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
            }
        } else {
            return redirect()->route('login', ['redirect' => route('job-openings.apply', $job)]);
        }
        $job->load('user.referrerProfile');
        $user = auth()->user();
        $resumes = $user->resumes()->orderByDesc('created_at')->get();
        $profile = $user->candidateProfile;
        return view('hirevo.job-openings-apply', compact('job', 'resumes', 'profile'));
    }

    public function storeEmployerJobApply(Request $request, EmployerJob $job): RedirectResponse
    {
        if ($job->status !== 'active') {
            return redirect()->route('job-openings')->with('info', 'This job is no longer accepting applications.');
        }
        if (! auth()->user()->isCandidate()) {
            return redirect()->route('job-openings');
        }
        $exists = EmployerJobApplication::where('employer_job_id', $job->id)->where('user_id', auth()->id())->exists();
        if ($exists) {
            return redirect()->route('job-openings')->with('info', 'You have already applied for this job.');
        }
        $request->validate([
            'resume_id'       => ['nullable', 'integer', 'exists:resumes,id'],
            'cover_message'  => ['nullable', 'string', 'max:2000'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'headline'        => ['nullable', 'string', 'max:255'],
            'education'       => ['nullable', 'string', 'max:500'],
            'experience_years'=> ['nullable', 'integer', 'min:0', 'max:50'],
            'skills'          => ['nullable', 'string', 'max:2000'],
            'location'        => ['nullable', 'string', 'max:255'],
            'expected_salary' => ['nullable', 'string', 'max:100'],
        ]);
        $user = auth()->user();
        if ($request->resume_id) {
            $resume = Resume::where('user_id', $user->id)->find($request->resume_id);
            if (! $resume) {
                return back()->withErrors(['resume_id' => 'Invalid resume.']);
            }
        }
        if ($request->filled('phone')) {
            $user->update(['phone' => $request->phone]);
        }
        $profile = $user->candidateProfile ?? new CandidateProfile(['user_id' => $user->id]);
        $profile->headline = $request->filled('headline') ? $request->headline : $profile->headline;
        $profile->education = $request->filled('education') ? $request->education : $profile->education;
        $profile->experience_years = $request->filled('experience_years') ? (int) $request->experience_years : $profile->experience_years;
        $profile->skills = $request->filled('skills') ? $request->skills : $profile->skills;
        $profile->location = $request->filled('location') ? $request->location : $profile->location;
        $profile->expected_salary = $request->filled('expected_salary') ? $request->expected_salary : $profile->expected_salary;
        $profile->save();

        $application = EmployerJobApplication::create([
            'employer_job_id' => $job->id,
            'user_id'         => $user->id,
            'resume_id'       => $request->resume_id ?: null,
            'cover_message'   => $request->cover_message ? trim($request->cover_message) : null,
            'status'          => 'applied',
        ]);

        // Store ATS score and job match score for employer view
        $resume = $application->resume;
        $atsScore = $resume ? $resume->ai_score : null;
        $resumeText = null;
        if ($resume && Storage::disk('local')->exists($resume->file_path)) {
            $resumeText = app(ResumeAnalysisService::class)->extractTextFromFile(
                Storage::disk('local')->path($resume->file_path),
                $resume->mime_type ?? 'application/pdf'
            );
        }
        if ($resumeText === null || $resumeText === '') {
            // Build minimal candidate summary from profile for match scoring
            $parts = [];
            if ($profile->headline) {
                $parts[] = $profile->headline;
            }
            if ($profile->education) {
                $parts[] = 'Education: ' . $profile->education;
            }
            if ($profile->experience_years !== null) {
                $parts[] = 'Experience: ' . $profile->experience_years . ' years';
            }
            if ($profile->skills) {
                $parts[] = 'Skills: ' . (is_array($profile->skills) ? implode(', ', $profile->skills) : $profile->skills);
            }
            $resumeText = implode("\n", $parts) ?: 'Candidate profile.';
        }

        // Job match score: try AI first, then always fall back to rule-based so score is set at apply time
        $jobMatchScore = null;
        $jobMatchExplanation = null;
        $resumeAnalysis = app(ResumeAnalysisService::class);
        if ($resumeText !== '') {
            $gpt = app(GptService::class);
            if ($gpt->isAvailable()) {
                $match = $gpt->getResumeJobMatchScore(
                    $resumeText,
                    $job->title,
                    $job->description ?? '',
                    []
                );
                if ($match !== null) {
                    $jobMatchScore = $match['score'];
                    $jobMatchExplanation = $match['explanation'] ?? null;
                }
            }
            if ($jobMatchScore === null) {
                $match = $resumeAnalysis->getEmployerJobMatchRuleBased(
                    $resumeText,
                    $job->title,
                    $job->description ?? ''
                );
                $jobMatchScore = $match['score'];
                $jobMatchExplanation = $match['explanation'] ?? null;
            }
        }

        $application->update([
            'ats_score' => $atsScore,
            'job_match_score' => $jobMatchScore,
            'job_match_explanation' => $jobMatchExplanation,
        ]);

        return redirect()->route('job-openings')->with('success', 'Your application has been submitted.');
    }
}
