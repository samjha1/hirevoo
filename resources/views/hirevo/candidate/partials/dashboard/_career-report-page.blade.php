<div class="cd-page-back">
    <a href="{{ route('candidate.dashboard') }}" class="cd-link"><i class="mdi mdi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="cd-section-head">
    <div>
        <p class="cd-section-kicker">Performance analytics</p>
        <h2 class="cd-section-title">Career Report</h2>
        <p class="cd-section-sub">Insights, profile strength, applications, and skill performance from your saved data.</p>
    </div>
</div>

<div class="cd-grid cd-grid--top">
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Profile Strength</h2></div>
        <div class="cd-report-profile">
            <div class="cd-report-profile-ring" style="background: conic-gradient(#6366f1 {{ $profilePct }}%, #e2e8f0 0);">
                <span class="cd-report-profile-pct">{{ $profilePct }}%</span>
                <span class="cd-report-profile-lbl">Complete</span>
            </div>
            <div>
                <p class="mb-1"><strong>{{ $profileCompletion['filled'] ?? 0 }}</strong> of <strong>{{ $profileCompletion['total'] ?? 0 }}</strong> fields filled</p>
                <a href="{{ route('profile') }}" class="cd-btn cd-btn--primary cd-btn--sm">Update profile</a>
            </div>
        </div>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Hiring Score Snapshot</h2></div>
        <p class="cd-report-big-num">{{ $hiringScore }}<span>/100</span></p>
        <p class="text-muted small mb-2">{{ $hiringScoreLabel }} · Top {{ $scorePercentile }}% of candidates</p>
        <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-link">Full hiring score →</a>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Application Pipeline</h2></div>
        <div class="cd-tracker">
            @foreach([
                ['key' => 'applied', 'label' => 'Applied', 'icon' => 'mdi-send-outline', 'color' => '#6366f1'],
                ['key' => 'shortlisted', 'label' => 'Shortlisted', 'icon' => 'mdi-star-outline', 'color' => '#10b981'],
                ['key' => 'interview', 'label' => 'Interview', 'icon' => 'mdi-account-voice', 'color' => '#f59e0b'],
                ['key' => 'offer', 'label' => 'Offer', 'icon' => 'mdi-trophy-outline', 'color' => '#8b5cf6'],
            ] as $t)
                <div class="cd-tracker-item">
                    <span class="cd-tracker-icon" style="--t-color: {{ $t['color'] }}"><i class="mdi {{ $t['icon'] }}"></i></span>
                    <span class="cd-tracker-num">{{ $trackerCounts[$t['key']] ?? 0 }}</span>
                    <span class="cd-tracker-label">{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="cd-card">
    <div class="cd-card-head"><h2 class="cd-card-title">Your Insights</h2></div>
    <div class="cd-insights-list cd-insights-list--full">
        @foreach($insights ?? [] as $insight)
            <div class="cd-insight-item">
                <div class="cd-insight-text">
                    <span class="cd-insight-label">{{ $insight['label'] }}</span>
                    <strong>{{ $insight['value'] }}</strong>
                </div>
                <svg class="cd-sparkline cd-sparkline--lg" viewBox="0 0 120 36" preserveAspectRatio="none" aria-hidden="true">
                    @php
                        $pts = is_array($insight['trend'] ?? null) ? $insight['trend'] : [0,0,0,0,0,0];
                        $max = max($pts) ?: 1;
                        $ptCount = count($pts);
                        $coords = collect($pts)->map(fn ($v, $i) => round($i * (120 / max($ptCount - 1, 1)), 1).','.round(34 - (($v / $max) * 30), 1))->implode(' ');
                    @endphp
                    <polyline fill="none" stroke="{{ $insight['color'] ?? '#6366f1' }}" stroke-width="2.5" points="{{ $coords }}"/>
                </svg>
            </div>
        @endforeach
    </div>
</div>

<div class="cd-grid cd-grid--middle">
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Score Breakdown</h2></div>
        <ul class="cd-breakdown-list">
            @foreach($scoreBreakdown as $item)
                <li class="cd-breakdown-item">
                    <span class="cd-breakdown-icon"><i class="mdi {{ $item['icon'] }}"></i></span>
                    <div class="cd-breakdown-body">
                        <div class="cd-breakdown-row"><span>{{ $item['label'] }}</span><strong>{{ $item['score'] }}/100</strong></div>
                        <div class="cd-progress"><span style="width: {{ $item['score'] }}%"></span></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Skills Overview</h2></div>
        @if(count($dashboardSkillMatched) > 0)
            <p class="cd-detail-label">Strong skills</p>
            <div class="cd-skill-chips mb-3">
                @foreach($dashboardSkillMatched as $sk)<span class="cd-skill-chip cd-skill-chip--match">{{ $sk }}</span>@endforeach
            </div>
        @endif
        @if(count($dashboardSkillGaps) > 0)
            <p class="cd-detail-label">Areas to improve</p>
            <div class="cd-skill-chips">
                @foreach($dashboardSkillGaps as $sk)<span class="cd-skill-chip cd-skill-chip--gap">{{ $sk }}</span>@endforeach
            </div>
        @else
            <p class="cd-empty-text mb-0">Apply to a job goal to see skill gap analysis.</p>
        @endif
    </div>
</div>

@if($skillFocusRole)
    <div class="cd-card cd-card--highlight">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Focus Role: {{ $skillFocusRole->title }}</h2>
            @if($dashboardSkillMatchPct !== null)<span class="cd-match-pill">{{ $dashboardSkillMatchPct }}% match</span>@endif
        </div>
        <p class="text-muted small mb-3">Your current career focus based on applications and resume analysis.</p>
        <a href="{{ route('job-goal.show', $skillFocusRole) }}" class="cd-btn cd-btn--primary cd-btn--sm">Open role report</a>
    </div>
@endif

<div class="cd-grid cd-grid--bottom">
    <div class="cd-card">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Job Matches</h2>
            <a href="{{ route('job-openings') }}" class="cd-link">View all</a>
        </div>
        <div class="cd-jobs-list">
            @foreach($jobMatches as $job)
                <div class="cd-job-item">
                    <div class="cd-job-main">
                        <strong>{{ $job['title'] }}</strong>
                        <span class="cd-job-company">{{ $job['company'] }}</span>
                    </div>
                    <span class="cd-match-pill">{{ $job['match'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>
    <div class="cd-card">
        <div class="cd-card-head"><h2 class="cd-card-title">Activity Summary</h2></div>
        <ul class="cd-report-stats-list">
            <li><span>Total applications</span><strong>{{ $dashboardStats['total_apps'] ?? 0 }}</strong></li>
            <li><span>Active reviews</span><strong>{{ $dashboardStats['active_reviews'] ?? 0 }}</strong></li>
            <li><span>Hired</span><strong>{{ $dashboardStats['hired_count'] ?? 0 }}</strong></li>
            <li><span>Avg job match</span><strong>{{ $dashboardStats['avg_match'] ? round($dashboardStats['avg_match']).'%' : '—' }}</strong></li>
            <li><span>Resume ATS</span><strong>{{ $hsResume && $hsResume['ai_score'] !== null ? $hsResume['ai_score'].'/100' : '—' }}</strong></li>
        </ul>
    </div>
</div>

@if(($applicationStatusCounts['all'] ?? 0) > 0)
    <div class="cd-card">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Recent Applications</h2>
            <a href="{{ route('candidate.dashboard') }}#applications" class="cd-link">View all →</a>
        </div>
        <p class="cd-card-meta mb-3">{{ $applicationStatusCounts['all'] }} total applications</p>
        <a href="{{ route('candidate.dashboard') }}#applications" class="cd-btn cd-btn--primary cd-btn--sm">Open applications tracker</a>
    </div>
@endif
