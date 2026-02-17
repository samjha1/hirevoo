<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\JobRole;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $jobRoles = JobRole::where('is_active', true)->orderBy('title')->limit(8)->get();
        return view('hirevo.index', compact('jobRoles'));
    }

    public function jobList(): View
    {
        $jobRoles = JobRole::where('is_active', true)->orderBy('title')->get();
        return view('hirevo.job-list', compact('jobRoles'));
    }

    public function skillMatch(JobRole $jobRole): View
    {
        $jobRole->load('requiredSkills');
        $requiredSkills = $jobRole->requiredSkills->pluck('skill_name')->map(fn ($s) => strtolower(trim($s)))->unique()->values()->all();

        $matchPercentage = 0;
        $matchedSkills = [];
        $missingSkills = $requiredSkills;
        $candidateSkills = [];

        if (auth()->check() && auth()->user()->isCandidate()) {
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

        return view('hirevo.skill-match', [
            'jobRole' => $jobRole,
            'requiredSkills' => $jobRole->requiredSkills,
            'matchPercentage' => $matchPercentage,
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'candidateSkills' => $candidateSkills,
            'hasProfile' => auth()->check() && auth()->user()->candidateProfile,
        ]);
    }

    public function pricing(): View
    {
        return view('hirevo.pricing');
    }
}
