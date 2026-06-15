@extends('layouts.candidate')

@section('title', 'Skill Gap Analysis')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Skill Gap Analysis</h1>
        <p class="cp-greeting-sub">Skills missing from your resume vs what employers need — close gaps to get more interviews.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline"><i class="mdi mdi-arrow-left"></i><span>Dashboard</span></a>
@endsection

@section('content')
@php
    $sg = $skillGaps ?? [];
    $matchPct = $sg['match_pct'] ?? null;
    $resumeSkills = $sg['resume_skills'] ?? [];
    $gaps = $sg['gaps'] ?? [];
    $gapDetails = $sg['gap_details'] ?? [];
    $matched = $sg['matched'] ?? [];
    $gapCount = count($gaps);
    $resumeSkillCount = count($resumeSkills);
@endphp
<div class="cf-page">
    @if(empty($resume))
        <div class="cf-alert cf-alert--warn">
            <i class="mdi mdi-file-upload-outline"></i>
            Upload your resume so we can find skills you're missing and help you land more interviews.
            <a href="{{ route('resume.upload') }}" class="fw-600">Upload resume</a>
        </div>
    @else
        @if(!empty($sg['summary']))
            <div class="cf-gap-hero">
                <div class="cf-gap-hero-stats">
                    <div class="cf-gap-stat">
                        <strong>{{ $resumeSkillCount }}</strong>
                        <span>on your resume</span>
                    </div>
                    <div class="cf-gap-stat cf-gap-stat--warn">
                        <strong>{{ $gapCount }}</strong>
                        <span>missing for interviews</span>
                    </div>
                    @if($matchPct !== null)
                        <div class="cf-gap-stat">
                            <strong>{{ $matchPct }}%</strong>
                            <span>best role match</span>
                        </div>
                    @endif
                </div>
                <p class="cf-gap-hero-text mb-0">{{ $sg['summary'] }}</p>
            </div>
        @endif

        @if($gapCount > 0)
            <div class="cf-missing-block">
                <div class="cf-missing-head">
                    <h2 class="cf-section-title mb-0">
                        <i class="mdi mdi-alert-circle-outline text-warning"></i>
                        Missing skills from your resume
                    </h2>
                    <span class="cf-meta">Prioritized by how often employers ask for them</span>
                </div>

                @foreach($gapDetails as $gap)
                    <div class="cf-missing-card">
                        <div class="cf-missing-card-top">
                            <div>
                                <h3 class="cf-missing-skill">{{ $gap['skill'] }}</h3>
                                <p class="cf-meta mb-0">{{ $gap['impact'] }}</p>
                            </div>
                            <span class="cf-badge cf-badge--gap">
                                @if(($gap['roles_count'] ?? 1) > 1)
                                    {{ $gap['roles_count'] }} roles
                                @else
                                    Priority {{ $gap['priority'] }}
                                @endif
                            </span>
                        </div>

                        @if(!empty($gap['roles_sample']))
                            <div class="cf-missing-roles">
                                <i class="mdi mdi-briefcase-outline"></i>
                                Needed for:
                                @foreach(array_slice($gap['roles_sample'], 0, 2) as $roleName)
                                    <span class="cf-pill cf-pill--miss">{{ $roleName }}</span>
                                @endforeach
                                @if(($gap['roles_count'] ?? 0) > 2)
                                    <span class="cf-meta">+{{ $gap['roles_count'] - 2 }} more</span>
                                @endif
                            </div>
                        @endif

                        <p class="cf-missing-tip">
                            <i class="mdi mdi-lightbulb-on-outline"></i>
                            {{ $gap['interview_tip'] ?? ('Practice '.$gap['skill'].' and add it to your resume projects section.') }}
                        </p>

                        <div class="cf-grid-2 cf-missing-levels">
                            <div>
                                <span class="cf-meta">On resume today</span>
                                <div class="cf-progress"><span style="width: {{ $gap['current_pct'] }}%"></span></div>
                            </div>
                            <div>
                                <span class="cf-meta">Target to get shortlisted</span>
                                <div class="cf-progress"><span style="width: {{ $gap['target_pct'] }}%"></span></div>
                            </div>
                        </div>

                        <div class="cf-missing-actions">
                            <a href="{{ route('candidate.assessments') }}" class="cf-btn cf-btn--primary">
                                <i class="mdi mdi-clipboard-check-outline"></i>
                                Test {{ $gap['skill'] }}
                            </a>
                            <a href="{{ route('resume.upload') }}" class="cf-btn cf-btn--outline">
                                <i class="mdi mdi-file-document-edit-outline"></i>
                                Update resume
                            </a>
                        </div>
                    </div>
                @endforeach

                @if($gapDetails === [] && $gaps !== [])
                    <div class="cf-card">
                        <h3 class="cf-card-title">Add these to your resume</h3>
                        <div class="cf-pills">
                            @foreach($gaps as $skill)
                                <span class="cf-pill cf-pill--miss">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="cf-card cf-alert cf-alert--success">
                <i class="mdi mdi-check-circle-outline"></i>
                No major skill gaps detected for your matching roles. Focus on mock interviews and applying to open jobs.
            </div>
        @endif

        @if(!empty($sg['role_title']))
            <div class="cf-card">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h2 class="cf-card-title">
                            @if(($sg['source'] ?? '') === 'resume_market_scan')
                                Top matching role
                            @else
                                Target role
                            @endif
                            : {{ $sg['role_title'] }}
                        </h2>
                        <p class="cf-meta mb-0">
                            @if(!empty($sg['roles_scanned']) && ($sg['roles_scanned'] ?? 0) > 1)
                                Compared against {{ $sg['roles_scanned'] }} roles that fit your resume
                            @else
                                Compared against required skills for this role
                            @endif
                        </p>
                    </div>
                    @if($matchPct !== null)
                        <span class="cf-badge cf-badge--match">{{ $matchPct }}% match</span>
                    @endif
                </div>
                @if($matchPct !== null)
                    <div class="cf-progress mt-2"><span style="width: {{ $matchPct }}%"></span></div>
                @endif
                @if(!empty($sg['role_url']))
                    <a href="{{ $sg['role_url'] }}" class="cf-btn cf-btn--outline mt-2">View full role match →</a>
                @endif
            </div>
        @elseif($resumeSkillCount === 0)
            <div class="cf-card">
                <p class="mb-2">We couldn't read skills from your resume yet. Re-upload a clearer PDF or add skills on your profile.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('resume.upload') }}" class="cf-btn cf-btn--primary">Re-upload resume</a>
                    <a href="{{ route('profile') }}" class="cf-btn cf-btn--outline">Edit profile skills</a>
                </div>
            </div>
        @endif

        @if($resumeSkills !== [])
            <div class="cf-card">
                <h3 class="cf-card-title">Skills already on your resume</h3>
                <p class="cf-meta">These are detected from your resume{{ !empty($matched) ? ' and match employer requirements' : '' }}.</p>
                <div class="cf-pills">
                    @foreach($resumeSkills as $skill)
                        @php $isMatched = in_array(mb_strtolower($skill), array_map('mb_strtolower', $matched), true); @endphp
                        <span class="cf-pill {{ $isMatched ? 'cf-pill--ok' : '' }}">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($matched))
            <div class="cf-card">
                <h3 class="cf-card-title">Skills employers already see on your resume</h3>
                <div class="cf-pills">
                    @foreach($matched as $skill)
                        <span class="cf-pill cf-pill--ok">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!empty($jobMatches))
            <div class="cf-card">
                <h3 class="cf-card-title">Jobs waiting once you close gaps</h3>
                <p class="cf-meta mb-2">Roles ranked by resume fit — improving missing skills raises these match scores.</p>
                @foreach(array_slice($jobMatches, 0, 4) as $job)
                    <div class="cf-job-row">
                        <div>
                            <strong>{{ $job['title'] }}</strong>
                            <div class="cf-meta">{{ $job['company'] }} · {{ $job['match'] }}% match</div>
                            @if(!empty($job['missing_skills']))
                                <div class="cf-pills mt-1">
                                    @foreach($job['missing_skills'] as $s)
                                        <span class="cf-pill cf-pill--miss">{{ is_string($s) ? ucwords($s) : $s }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <a href="{{ $job['url'] }}" class="cf-btn cf-btn--outline">View</a>
                    </div>
                @endforeach
                <a href="{{ route('candidate.job-matches') }}" class="cf-btn cf-btn--outline mt-2">All job matches →</a>
            </div>
        @endif

        <div class="cf-card">
            <h3 class="cf-card-title">Get more interviews — next steps</h3>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('candidate.assessments') }}" class="cf-btn cf-btn--primary">Practice skill tests</a>
                <a href="{{ route('candidate.mock-interviews') }}" class="cf-btn cf-btn--outline">Mock interviews</a>
                <a href="{{ route('resume.upload') }}" class="cf-btn cf-btn--outline">Update resume</a>
                <a href="{{ route('candidate.job-matches') }}" class="cf-btn cf-btn--outline">View job matches</a>
            </div>
        </div>
    @endif
</div>
@endsection
