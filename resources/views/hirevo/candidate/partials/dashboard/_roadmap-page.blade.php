@php
    $completedSteps = collect($roadmapSteps)->where('status', 'completed')->count();
    $roadmapPct = count($roadmapSteps) > 0 ? (int) round(($completedSteps / count($roadmapSteps)) * 100) : 0;
@endphp

<div class="cd-page-back">
    <a href="{{ route('candidate.dashboard') }}" class="cd-link"><i class="mdi mdi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="cd-section-head">
    <div>
        <p class="cd-section-kicker">Your path forward</p>
        <h2 class="cd-section-title">Career Roadmap</h2>
        <p class="cd-section-sub">Step-by-step plan from assessments to landing your next role.</p>
    </div>
</div>

<div class="cd-card cd-roadmap-progress-card">
    <div class="cd-roadmap-progress-top">
        <div>
            <span class="cd-roadmap-progress-num">{{ $completedSteps }}/{{ count($roadmapSteps) }}</span>
            <span class="cd-roadmap-progress-lbl">steps completed</span>
        </div>
        <span class="cd-match-pill">{{ $roadmapPct }}% done</span>
    </div>
    <div class="cd-progress cd-progress--lg"><span style="width: {{ $roadmapPct }}%"></span></div>
</div>

<div class="cd-roadmap-full">
    @foreach($roadmapSteps as $idx => $step)
        <div class="cd-roadmap-full-step cd-roadmap-full-step--{{ $step['status'] }}">
            <div class="cd-roadmap-full-marker">
                <span class="cd-roadmap-full-num">{{ $idx + 1 }}</span>
                @if($step['status'] === 'completed')
                    <i class="mdi mdi-check cd-roadmap-full-check"></i>
                @endif
            </div>
            <div class="cd-roadmap-full-body">
                <div class="cd-roadmap-full-head">
                    <h3>{{ $step['label'] }}</h3>
                    <span class="cd-roadmap-status">
                        @if($step['status'] === 'completed') Completed
                        @elseif($step['status'] === 'in_progress') In Progress
                        @else Pending
                        @endif
                    </span>
                </div>
                <p class="cd-roadmap-full-desc">{{ $step['description'] ?? '' }}</p>
                @if(($step['status'] ?? '') !== 'completed' && ! empty($step['action_url']))
                    <a href="{{ $step['action_url'] }}" class="cd-btn cd-btn--primary cd-btn--sm">{{ $step['action_label'] ?? 'Get started' }}</a>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="cd-grid cd-grid--middle">
    <div class="cd-card cd-card--next">
        <div class="cd-next-icon" aria-hidden="true"><i class="mdi mdi-target"></i></div>
        <p class="cd-next-label">Recommended Next Step</p>
        <h3 class="cd-next-title">{{ $nextStep['title'] }}</h3>
        <p class="cd-next-desc">{{ $nextStep['description'] }}</p>
        <a href="{{ $nextStep['url'] }}" class="cd-btn cd-btn--primary">{{ $nextStep['label'] }}</a>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Quick Links</h2></div>
        <div class="cd-roadmap-links">
            <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-roadmap-link-item"><i class="mdi mdi-gauge"></i> My Hiring Score</a>
            <a href="{{ route('candidate.dashboard') }}#career-report" class="cd-roadmap-link-item"><i class="mdi mdi-chart-box-outline"></i> Career Report</a>
            <a href="{{ route('job-openings') }}" class="cd-roadmap-link-item"><i class="mdi mdi-briefcase-search"></i> Browse Jobs</a>
            <a href="{{ route('candidate.resume.review') }}" class="cd-roadmap-link-item"><i class="mdi mdi-file-document-outline"></i> Resume Review</a>
        </div>
    </div>
</div>

<div class="cd-banner">
    <div class="cd-banner-content">
        <h3>Stay on track</h3>
        <p>Complete each roadmap step to improve your hiring score and interview readiness.</p>
        <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-btn cd-btn--primary">View hiring score</a>
    </div>
</div>
