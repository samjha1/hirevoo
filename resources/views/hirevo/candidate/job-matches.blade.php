@extends('layouts.candidate')

@section('title', 'Job Matches')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Job Matches</h1>
        <p class="cp-greeting-sub">All roles related to your resume — ranked by fit, including stretch opportunities.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline"><i class="mdi mdi-arrow-left"></i><span>Dashboard</span></a>
@endsection

@section('content')
@php
    $strongMin = (int) config('hirevo_candidate_features.job_match_min_pct', 45);
    $includeMin = (int) config('hirevo_candidate_features.job_match_include_min_pct', 15);
    $strongMatches = [];
    $moderateMatches = [];
    $stretchMatches = [];
    foreach ($jobMatches ?? [] as $job) {
        $tier = $job['match_tier'] ?? ((int) ($job['match'] ?? 0) >= $strongMin ? 'strong' : 'stretch');
        if ($tier === 'strong') {
            $strongMatches[] = $job;
        } elseif ($tier === 'moderate') {
            $moderateMatches[] = $job;
        } else {
            $stretchMatches[] = $job;
        }
    }
    $total = count($jobMatches ?? []);
@endphp
<div class="cf-page">
    @if(empty($resume))
        <div class="cf-alert cf-alert--warn">Upload your resume to see personalized matches. <a href="{{ route('resume.upload') }}" class="fw-600">Upload</a></div>
    @elseif($total === 0)
        <div class="cf-card">
            <p class="mb-2">No resume-related roles found above {{ $includeMin }}% fit yet. Re-upload your resume or add skills on your profile.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('resume.upload') }}" class="cf-btn cf-btn--primary">Update resume</a>
                <a href="{{ route('job-openings') }}" class="cf-btn cf-btn--outline">Browse all openings</a>
            </div>
        </div>
    @else
        <div class="cf-gap-hero cf-job-hero">
            <div class="cf-gap-hero-stats">
                <div class="cf-gap-stat">
                    <strong>{{ $total }}</strong>
                    <span>resume-related roles</span>
                </div>
                <div class="cf-gap-stat">
                    <strong>{{ count($strongMatches) }}</strong>
                    <span>strong matches ({{ $strongMin }}%+)</span>
                </div>
                <div class="cf-gap-stat cf-gap-stat--warn">
                    <strong>{{ count($stretchMatches) + count($moderateMatches) }}</strong>
                    <span>stretch roles to grow into</span>
                </div>
            </div>
            <p class="cf-gap-hero-text mb-0">Ranked by how well your resume fits each role. Lower matches are still worth exploring — close skill gaps to move up the list.</p>
        </div>

        @php
            $sections = [
                ['title' => 'Strong matches', 'subtitle' => $strongMin.'%+ fit — apply or explore first', 'jobs' => $strongMatches, 'badge' => 'cf-badge--match'],
                ['title' => 'Good fit roles', 'subtitle' => 'Solid overlap — a few skills away from shortlist', 'jobs' => $moderateMatches, 'badge' => 'cf-badge--moderate'],
                ['title' => 'Related roles', 'subtitle' => 'Lower match but aligned with your background — upskill to qualify', 'jobs' => $stretchMatches, 'badge' => 'cf-badge--stretch'],
            ];
        @endphp

        @foreach($sections as $section)
            @if(!empty($section['jobs']))
                <div class="cf-job-section">
                    <h2 class="cf-section-title">{{ $section['title'] }}</h2>
                    <p class="cf-meta cf-job-section-sub">{{ $section['subtitle'] }} · {{ count($section['jobs']) }} role(s)</p>
                    @foreach($section['jobs'] as $job)
                        <div class="cf-card cf-job-card">
                            <div class="cf-card-head">
                                <div>
                                    <h3 class="cf-card-title mb-1">{{ $job['title'] }}</h3>
                                    <div class="cf-meta">{{ $job['company'] }} · {{ $job['location'] }}</div>
                                    <div class="cf-meta mt-1">{{ $job['experience'] }} · {{ $job['salary'] }}</div>
                                </div>
                                <div class="cf-job-badges">
                                    <span class="cf-badge {{ $section['badge'] }}">{{ $job['match'] }}%</span>
                                    <span class="cf-meta">{{ $job['match_label'] ?? '' }}</span>
                                </div>
                            </div>
                            @if(!empty($job['missing_skills']))
                                <div class="cf-meta mb-1">Skills to strengthen for this role:</div>
                                <div class="cf-pills mb-2">
                                    @foreach($job['missing_skills'] as $s)
                                        <span class="cf-pill cf-pill--miss">{{ $s }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="cf-job-actions d-flex flex-wrap gap-2">
                                <a href="{{ $job['url'] }}" class="cf-btn cf-btn--primary">
                                    {{ ($job['type'] ?? '') === 'goal' ? 'View skill match' : 'Apply now' }}
                                    <i class="mdi mdi-arrow-right"></i>
                                </a>
                                @if(!empty($job['missing_skills']))
                                    <a href="{{ route('candidate.skill-gaps') }}" class="cf-btn cf-btn--outline">Close skill gaps</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach

        <div class="cf-card">
            <h3 class="cf-card-title">Want more interviews?</h3>
            <p class="cf-meta">Improve missing skills to turn stretch roles into strong matches.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('candidate.skill-gaps') }}" class="cf-btn cf-btn--primary">Skill gap analysis</a>
                <a href="{{ route('candidate.assessments') }}" class="cf-btn cf-btn--outline">Practice assessments</a>
                <a href="{{ route('job-openings') }}" class="cf-btn cf-btn--outline">Browse all jobs</a>
            </div>
        </div>
    @endif
</div>
@endsection
