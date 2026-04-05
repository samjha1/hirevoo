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
        $gpt = app(GptService::class);

        return view('hirevo.employer.jobs.create', [
            'companyName' => $companyName,
            'aiDescriptionAvailable' => $gpt->isAvailable(),
        ]);
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

        $salaryMinFloor = (int) config('hirevo.employer_salary_min_floor_inr', 150_000);
        $floorLabel = number_format($salaryMinFloor);

        $validated = $request->validate([
            'job_department'      => ['required', 'string', 'max:100'],
            'required_skills'     => ['nullable', 'string', 'max:2000'],
            'title'               => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:10000'],
            'apply_link'          => ['nullable', 'url', 'max:2048'],
            'location_area'       => ['nullable', 'string', 'max:120'],
            'location_city'       => ['nullable', 'string', 'max:120'],
            'location_state'      => ['nullable', 'string', 'max:120'],
            'location_country'    => ['nullable', 'string', 'max:120'],
            'location_pincode'    => ['nullable', 'string', 'max:20'],
            'status'              => ['nullable', 'in:draft,active,closed'],
            'job_type'            => ['required', 'in:full_time,part_time,contract,internship,temporary,volunteer,other'],
            'is_night_shift'      => ['nullable', 'boolean'],
            'work_location_type'  => ['required', 'in:office,remote,hybrid'],
            'pay_type'            => ['required', 'in:fixed,hourly,negotiable,not_disclosed,other'],
            'salary_min'          => [
                'nullable',
                'integer',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $salaryMinFloor, $floorLabel): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $payType = (string) $request->input('pay_type', '');
                    if (! in_array($payType, ['fixed', 'negotiable'], true)) {
                        return;
                    }
                    if ((int) $value > 0 && (int) $value < $salaryMinFloor) {
                        $fail("Minimum salary must be at least ₹{$floorLabel} per annum (LPA) for fixed or negotiable pay.");
                    }
                },
            ],
            'salary_max'          => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'experience_years'    => ['nullable', 'integer', 'min:0', 'max:60'],
            'perks'               => ['nullable', 'string', 'max:2000'],
            'joining_fee_required'=> ['required', 'in:0,1'],
        ]);

        $location = [
            'area' => $validated['location_area'] ?? null,
            'city' => $validated['location_city'] ?? null,
            'state' => $validated['location_state'] ?? null,
            'country' => $validated['location_country'] ?? null,
            'pincode' => $validated['location_pincode'] ?? null,
        ];
        $hasLocationValue = collect($location)->contains(fn ($value) => ! is_null($value) && $value !== '');
        $salaryAmount = null;
        if (isset($validated['salary_min']) || isset($validated['salary_max'])) {
            $min = $validated['salary_min'] ?? null;
            $max = $validated['salary_max'] ?? null;
            if ($min !== null && $max !== null) {
                $salaryAmount = $min . '-' . $max;
            } elseif ($min !== null) {
                $salaryAmount = (string) $min;
            } elseif ($max !== null) {
                $salaryAmount = (string) $max;
            }
        }
        $requiredSkills = $this->normalizeSkillsInput($validated['required_skills'] ?? null);
        $requiredSkillsJson = ! empty($requiredSkills) ? json_encode($requiredSkills, JSON_UNESCAPED_UNICODE) : null;

        $profile->decrement('credits');
        $user->employerJobs()->create([
            'company_name'         => $profile->company_name ?? null,
            'job_department'       => $validated['job_department'],
            'required_skills'      => $requiredSkillsJson,
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'apply_link'          => isset($validated['apply_link']) && $validated['apply_link'] !== '' ? $validated['apply_link'] : null,
            'location'            => $hasLocationValue ? json_encode($location, JSON_UNESCAPED_UNICODE) : null,
            'status'              => $validated['status'] ?? 'active',
            'job_type'            => $validated['job_type'],
            'is_night_shift'      => ! empty($request->boolean('is_night_shift')),
            'work_location_type'  => $validated['work_location_type'],
            'pay_type'            => $validated['pay_type'],
            'salary_min'          => $validated['salary_min'] ?? null,
            'salary_max'          => $validated['salary_max'] ?? null,
            'salary_amount'       => $salaryAmount,
            'experience_years'    => $validated['experience_years'] ?? null,
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

        $gpt = app(GptService::class);

        return view('hirevo.employer.jobs.edit', [
            'job' => $job,
            'aiDescriptionAvailable' => $gpt->isAvailable(),
        ]);
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

        $salaryMinFloor = (int) config('hirevo.employer_salary_min_floor_inr', 150_000);
        $floorLabel = number_format($salaryMinFloor);

        $validated = $request->validate([
            'job_department'        => ['nullable', 'string', 'max:100'],
            'required_skills'       => ['nullable', 'string', 'max:2000'],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string', 'max:10000'],
            'apply_link'           => ['nullable', 'url', 'max:2048'],
            'location_area'        => ['nullable', 'string', 'max:120'],
            'location_city'        => ['nullable', 'string', 'max:120'],
            'location_state'       => ['nullable', 'string', 'max:120'],
            'location_country'     => ['nullable', 'string', 'max:120'],
            'location_pincode'     => ['nullable', 'string', 'max:20'],
            'status'               => ['required', 'in:draft,active,closed'],
            'job_type'             => ['nullable', 'in:full_time,part_time,contract,internship,temporary,volunteer,other'],
            'is_night_shift'       => ['nullable', 'boolean'],
            'work_location_type'   => ['nullable', 'in:office,remote,hybrid'],
            'pay_type'             => ['nullable', 'in:fixed,hourly,negotiable,not_disclosed,other'],
            'salary_min'           => [
                'nullable',
                'integer',
                'min:0',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $salaryMinFloor, $floorLabel): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $payType = (string) $request->input('pay_type', '');
                    if (! in_array($payType, ['fixed', 'negotiable'], true)) {
                        return;
                    }
                    if ((int) $value > 0 && (int) $value < $salaryMinFloor) {
                        $fail("Minimum salary must be at least ₹{$floorLabel} per annum (LPA) for fixed or negotiable pay.");
                    }
                },
            ],
            'salary_max'           => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'experience_years'     => ['nullable', 'integer', 'min:0', 'max:60'],
            'perks'                => ['nullable', 'string', 'max:2000'],
            'joining_fee_required' => ['nullable', 'in:0,1'],
        ]);

        $location = [
            'area' => $validated['location_area'] ?? null,
            'city' => $validated['location_city'] ?? null,
            'state' => $validated['location_state'] ?? null,
            'country' => $validated['location_country'] ?? null,
            'pincode' => $validated['location_pincode'] ?? null,
        ];
        $hasLocationValue = collect($location)->contains(fn ($value) => ! is_null($value) && $value !== '');
        $salaryAmount = null;
        if (isset($validated['salary_min']) || isset($validated['salary_max'])) {
            $min = $validated['salary_min'] ?? null;
            $max = $validated['salary_max'] ?? null;
            if ($min !== null && $max !== null) {
                $salaryAmount = $min . '-' . $max;
            } elseif ($min !== null) {
                $salaryAmount = (string) $min;
            } elseif ($max !== null) {
                $salaryAmount = (string) $max;
            }
        }
        $requiredSkills = $this->normalizeSkillsInput($validated['required_skills'] ?? null);
        $requiredSkillsJson = ! empty($requiredSkills) ? json_encode($requiredSkills, JSON_UNESCAPED_UNICODE) : null;

        $job->update([
            'job_department'       => $validated['job_department'] ?? $job->job_department,
            'required_skills'      => $requiredSkillsJson,
            'title'                => $validated['title'],
            'description'          => $validated['description'] ?? null,
            'apply_link'           => array_key_exists('apply_link', $validated) && $validated['apply_link'] !== '' ? $validated['apply_link'] : null,
            'location'             => $hasLocationValue ? json_encode($location, JSON_UNESCAPED_UNICODE) : null,
            'status'               => $validated['status'],
            'job_type'             => $validated['job_type'] ?? null,
            'is_night_shift'       => ! empty($request->boolean('is_night_shift')),
            'work_location_type'   => $validated['work_location_type'] ?? null,
            'pay_type'             => $validated['pay_type'] ?? null,
            'salary_min'           => $validated['salary_min'] ?? null,
            'salary_max'           => $validated['salary_max'] ?? null,
            'salary_amount'        => $salaryAmount,
            'experience_years'     => $validated['experience_years'] ?? null,
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
            'job_department'       => $job->job_department,
            'required_skills'      => $job->required_skills,
            'title'               => $job->title . ' (Copy)',
            'description'         => $job->description,
            'location'             => $job->location,
            'status'               => 'draft',
            'job_type'             => $job->job_type,
            'is_night_shift'       => $job->is_night_shift,
            'work_location_type'   => $job->work_location_type,
            'pay_type'             => $job->pay_type,
            'salary_min'           => $job->salary_min,
            'salary_max'           => $job->salary_max,
            'salary_amount'       => $job->salary_amount,
            'experience_years'     => $job->experience_years,
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
            'job_department'       => $job->job_department,
            'required_skills'      => $job->required_skills,
            'title'               => $job->title,
            'description'         => $job->description,
            'location'            => $job->location,
            'status'              => 'active',
            'job_type'             => $job->job_type,
            'is_night_shift'       => $job->is_night_shift,
            'work_location_type'   => $job->work_location_type,
            'pay_type'             => $job->pay_type,
            'salary_min'           => $job->salary_min,
            'salary_max'           => $job->salary_max,
            'salary_amount'       => $job->salary_amount,
            'experience_years'     => $job->experience_years,
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

        $gpt = app(GptService::class);
        if (! $gpt->isAvailable()) {
            return response()->json([
                'error' => 'AI description is not set up on this site. Write the job description in the box below.',
            ], 503);
        }

        $description = $gpt->generateJobDescription($title);
        if ($description === null) {
            return response()->json([
                'error' => $this->friendlyEmployerAiMessage($gpt->getLastError()),
            ], 502);
        }

        return response()->json(['description' => $description]);
    }

    /**
     * Avoid exposing .env / operator jargon in employer-facing JSON.
     */
    private function friendlyEmployerAiMessage(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return 'Could not generate a description. Write it below or try again later.';
        }
        $lower = mb_strtolower($raw);
        if (str_contains($lower, 'no ai api keys') || str_contains($lower, 'openai_api_key') || str_contains($lower, 'openrouter')) {
            return 'AI could not run (configuration or rate limits). Write the job description below, or try again later.';
        }
        if (str_contains($lower, '429') || str_contains($lower, 'rate limit') || str_contains($lower, 'busy')) {
            return 'AI is busy right now. Write the description below or try again in a minute.';
        }

        return 'Could not generate a description. Write it below or try again later.';
    }

    private function normalizeSkillsInput(?string $skills): ?array
    {
        if (! is_string($skills) || trim($skills) === '') {
            return null;
        }

        $items = preg_split('/[\r\n,;|]+/', $skills) ?: [];
        $normalized = [];
        $seen = [];
        foreach ($items as $item) {
            $skill = trim((string) $item);
            if ($skill === '') {
                continue;
            }
            $key = mb_strtolower($skill);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $normalized[] = $skill;
        }

        return count($normalized) > 0 ? array_slice($normalized, 0, 50) : null;
    }
}
