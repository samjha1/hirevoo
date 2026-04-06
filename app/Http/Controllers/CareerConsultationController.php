<?php

namespace App\Http\Controllers;

use App\Models\CareerConsultationRequest;
use App\Models\JobRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CareerConsultationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'job_role_id' => ['required', 'integer', 'exists:job_roles,id'],
            'source' => ['required', 'string', 'in:dashboard,job_goal'],
            'match_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'gap_skills' => ['nullable', 'array', 'max:50'],
            'gap_skills.*' => ['string', 'max:160'],
            'suggested_gap_skills' => ['nullable', 'array', 'max:50'],
            'suggested_gap_skills.*' => ['string', 'max:160'],
            'matched_skills' => ['nullable', 'array', 'max:50'],
            'matched_skills.*' => ['string', 'max:160'],
        ]);

        $user = auth()->user();
        if (! $user || ! $user->isCandidate()) {
            return redirect()->route('login', ['redirect' => url()->previous()])
                ->with('info', 'Sign in as a candidate to request a consultation.');
        }

        $jobRoleId = $request->input('job_role_id');
        if ($jobRoleId !== null) {
            JobRole::query()->whereKey($jobRoleId)->firstOrFail();
        }

        $resumeId = $user->resumes()->where('is_primary', true)->value('id');
        if ($resumeId === null) {
            $resumeId = $user->resumes()->orderByDesc('created_at')->value('id');
        }

        CareerConsultationRequest::create([
            'user_id' => $user->id,
            'job_role_id' => $jobRoleId,
            'resume_id' => $resumeId,
            'source' => $request->input('source'),
            'match_percentage' => $request->input('match_percentage'),
            'gap_skills' => array_values(array_filter($request->input('gap_skills', []), fn ($s) => is_string($s) && trim($s) !== '')),
            'suggested_gap_skills' => array_values(array_filter($request->input('suggested_gap_skills', []), fn ($s) => is_string($s) && trim($s) !== '')),
            'matched_skills' => array_values(array_filter($request->input('matched_skills', []), fn ($s) => is_string($s) && trim($s) !== '')),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thanks — we’ve saved your consultation request. Our team will use your skill gaps and goals to follow up.');
    }
}
