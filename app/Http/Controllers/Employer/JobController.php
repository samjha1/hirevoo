<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerJob;
use App\Services\GptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved before you can view or post jobs.');
        }

        $query = $user->employerJobs()->withCount('applications')->orderByDesc('created_at');
        if ($request->filled('status') && in_array($request->status, ['active', 'draft', 'closed'], true)) {
            $query->where('status', $request->status);
        }
        $jobs = $query->get();

        $counts = [
            'all'    => $user->employerJobs()->count(),
            'active' => $user->employerJobs()->where('status', 'active')->count(),
            'draft'  => $user->employerJobs()->where('status', 'draft')->count(),
            'closed' => $user->employerJobs()->where('status', 'closed')->count(),
        ];

        return view('hirevo.employer.jobs.index', [
            'jobs'   => $jobs,
            'counts' => $counts,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved before you can post jobs.');
        }

        $companyName = $profile->company_name ?? '';

        return view('hirevo.employer.jobs.create', ['companyName' => $companyName]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('error', 'Your account must be approved before you can post jobs.');
        }
        if ($profile->credits < 1) {
            return redirect()
                ->route('employer.jobs.create')
                ->with('error', 'You need at least 1 credit to post a job. Buy credits to continue.');
        }

        $validated = $request->validate([
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:10000'],
            'location'            => ['nullable', 'string', 'max:255'],
            'status'              => ['nullable', 'in:draft,active,closed'],
            'job_type'            => ['required', 'in:full_time,part_time,contract,internship,temporary,volunteer,other'],
            'is_night_shift'      => ['nullable', 'boolean'],
            'work_location_type'  => ['required', 'in:office,remote,hybrid'],
            'pay_type'            => ['required', 'in:fixed,hourly,negotiable,not_disclosed,other'],
            'perks'               => ['nullable', 'string', 'max:2000'],
            'joining_fee_required'=> ['required', 'in:0,1'],
        ]);

        $profile->decrement('credits');
        $user->employerJobs()->create([
            'company_name'         => $profile->company_name ?? null,
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'location'            => $validated['location'] ?? null,
            'status'              => $validated['status'] ?? 'active',
            'job_type'            => $validated['job_type'],
            'is_night_shift'      => ! empty($request->boolean('is_night_shift')),
            'work_location_type'  => $validated['work_location_type'],
            'pay_type'            => $validated['pay_type'],
            'perks'               => $validated['perks'] ?? null,
            'joining_fee_required'=> (bool) $validated['joining_fee_required'],
        ]);

        return redirect()->route('employer.jobs.index')->with('success', 'Job posted successfully. 1 credit used.');
    }

    public function edit(EmployerJob $job): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage jobs.');
        }

        return view('hirevo.employer.jobs.edit', ['job' => $job]);
    }

    public function update(Request $request, EmployerJob $job): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage jobs.');
        }

        $validated = $request->validate([
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:10000'],
            'location'             => ['nullable', 'string', 'max:255'],
            'status'               => ['required', 'in:draft,active,closed'],
            'job_type'             => ['nullable', 'in:full_time,part_time,contract,internship,temporary,volunteer,other'],
            'is_night_shift'       => ['nullable', 'boolean'],
            'work_location_type'   => ['nullable', 'in:office,remote,hybrid'],
            'pay_type'             => ['nullable', 'in:fixed,hourly,negotiable,not_disclosed,other'],
            'perks'                => ['nullable', 'string', 'max:2000'],
            'joining_fee_required' => ['nullable', 'in:0,1'],
        ]);

        $job->update([
            'title'                => $validated['title'],
            'description'          => $validated['description'] ?? null,
            'location'             => $validated['location'] ?? null,
            'status'               => $validated['status'],
            'job_type'             => $validated['job_type'] ?? null,
            'is_night_shift'       => ! empty($request->boolean('is_night_shift')),
            'work_location_type'   => $validated['work_location_type'] ?? null,
            'pay_type'             => $validated['pay_type'] ?? null,
            'perks'                => $validated['perks'] ?? null,
            'joining_fee_required'  => isset($validated['joining_fee_required']) ? (bool) $validated['joining_fee_required'] : $job->joining_fee_required,
        ]);

        return redirect()->route('employer.jobs.index')->with('success', 'Job updated.');
    }

    public function destroy(EmployerJob $job): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage jobs.');
        }

        $job->delete();
        return redirect()->route('employer.jobs.index')->with('success', 'Job removed.');
    }

    public function duplicate(EmployerJob $job): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('info', 'Your account must be approved to manage jobs.');
        }

        $user->employerJobs()->create([
            'company_name'         => $profile->company_name ?? $job->company_name,
            'title'               => $job->title . ' (Copy)',
            'description'         => $job->description,
            'location'             => $job->location,
            'status'               => 'draft',
            'job_type'             => $job->job_type,
            'is_night_shift'       => $job->is_night_shift,
            'work_location_type'   => $job->work_location_type,
            'pay_type'             => $job->pay_type,
            'perks'                => $job->perks,
            'joining_fee_required' => $job->joining_fee_required,
        ]);

        return redirect()->route('employer.jobs.index')->with('success', 'Job duplicated as draft.');
    }

    public function repost(EmployerJob $job): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer() || $job->user_id !== $user->id) {
            return redirect()->route('employer.dashboard');
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return redirect()->route('employer.dashboard')->with('error', 'Your account must be approved to repost jobs.');
        }
        if ($profile->credits < 1) {
            return redirect()
                ->route('employer.jobs.index')
                ->with('error', 'You need at least 1 credit to repost. Buy credits to continue.');
        }

        $profile->decrement('credits');
        $user->employerJobs()->create([
            'company_name'         => $profile->company_name ?? $job->company_name,
            'title'               => $job->title,
            'description'         => $job->description,
            'location'            => $job->location,
            'status'              => 'active',
            'job_type'             => $job->job_type,
            'is_night_shift'       => $job->is_night_shift,
            'work_location_type'   => $job->work_location_type,
            'pay_type'             => $job->pay_type,
            'perks'                => $job->perks,
            'joining_fee_required' => $job->joining_fee_required,
        ]);

        return redirect()->route('employer.jobs.index')->with('success', 'Job reposted and is now active. 1 credit used.');
    }

    public function generateDescription(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $profile = $user->referrerProfile;
        if (! $profile || ! $profile->is_approved) {
            return response()->json(['error' => 'Your account must be approved to use this feature.'], 403);
        }

        $title = $request->input('title', '');
        $title = is_string($title) ? trim($title) : '';
        if ($title === '') {
            return response()->json(['error' => 'Job title is required'], 422);
        }

        $gpt = new GptService();
        if (! $gpt->isAvailable()) {
            return response()->json(['error' => 'AI service is not configured'], 503);
        }

        $description = $gpt->generateJobDescription($title);
        if ($description === null) {
            $message = $gpt->getLastError() ?: 'Could not generate description. Check OPENAI_API_KEY in .env.';
            return response()->json(['error' => $message], 502);
        }

        return response()->json(['description' => $description]);
    }
}
