{{-- Compact dashboard overview --}}
<div class="cd-grid cd-grid--top">
    <div class="cd-card cd-card--score cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Overall Hiring Score</h2>
            <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-link cd-link--sm">Full report →</a>
        </div>
        <div class="cd-score-body cd-score-body--compact">
            <div class="cd-score-ring-wrap cd-score-ring-wrap--sm">
                <svg class="cd-score-ring" viewBox="0 0 120 120" aria-hidden="true">
                    <circle class="cd-score-ring-track" cx="60" cy="60" r="54"/>
                    <circle class="cd-score-ring-fill" cx="60" cy="60" r="54"
                            stroke-dasharray="{{ $scoreCirc }}"
                            stroke-dashoffset="{{ $scoreOffset }}"/>
                </svg>
                <div class="cd-score-ring-center">
                    <span class="cd-score-num">{{ $hiringScore }}<small>/100</small></span>
                    <span class="cd-score-badge">{{ $hiringScoreLabel }}</span>
                </div>
            </div>
            <p class="cd-score-meta cd-score-meta--compact mb-0">Better than <strong>{{ $scorePercentile }}%</strong> of candidates</p>
        </div>
    </div>

    <div class="cd-card cd-card--trend cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Score Trend</h2>
            <span class="cd-card-meta">6 mo</span>
        </div>
        <div class="cd-trend-chart-wrap cd-trend-chart-wrap--sm">
            <canvas id="cdScoreTrendChartOverview" height="140" aria-label="Score trend"></canvas>
        </div>
    </div>

    <div class="cd-card cd-card--next cd-card--compact">
        <div class="cd-next-icon" aria-hidden="true"><i class="mdi mdi-target"></i></div>
        <p class="cd-next-label">Next Step</p>
        <h3 class="cd-next-title cd-next-title--sm">{{ $nextStep['title'] }}</h3>
        <a href="{{ $nextStep['url'] }}" class="cd-btn cd-btn--primary cd-btn--sm">{{ $nextStep['label'] }}</a>
    </div>
</div>

<div class="cd-grid cd-grid--middle">
    <div class="cd-card cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Score Breakdown</h2>
            <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-link cd-link--sm">See all →</a>
        </div>
        <ul class="cd-breakdown-list cd-breakdown-list--compact">
            @foreach(array_slice($scoreBreakdown, 0, 3) as $item)
                <li class="cd-breakdown-item">
                    <div class="cd-breakdown-body">
                        <div class="cd-breakdown-row">
                            <span>{{ $item['label'] }}</span>
                            <strong>{{ $item['score'] }}/100</strong>
                        </div>
                        <div class="cd-progress"><span style="width: {{ $item['score'] }}%"></span></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="cd-card cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Career Roadmap</h2>
            <a href="{{ route('candidate.dashboard') }}#roadmap" class="cd-link cd-link--sm">Full roadmap →</a>
        </div>
        <ol class="cd-roadmap cd-roadmap--compact">
            @foreach($roadmapSteps as $step)
                <li class="cd-roadmap-step cd-roadmap-step--{{ $step['status'] }}">
                    <span class="cd-roadmap-dot" aria-hidden="true">
                        @if($step['status'] === 'completed')<i class="mdi mdi-check"></i>
                        @elseif($step['status'] === 'in_progress')<i class="mdi mdi-circle-medium"></i>@endif
                    </span>
                    <span class="cd-roadmap-label">{{ $step['label'] }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    <div class="cd-card cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Insights</h2>
            <a href="{{ route('candidate.dashboard') }}#career-report" class="cd-link cd-link--sm">Full report →</a>
        </div>
        <div class="cd-insights-list cd-insights-list--compact">
            @foreach(array_slice($insights ?? [], 0, 3) as $insight)
                <div class="cd-insight-item cd-insight-item--compact">
                    <span class="cd-insight-label">{{ $insight['label'] }}</span>
                    <strong>{{ $insight['value'] }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</div>

@include('hirevo.candidate.partials.dashboard._ai-tools')

<div class="cd-grid cd-grid--bottom">
    <x-candidate-premium-gate feature="Job Matches" compact>
    <div class="cd-card cd-card--compact" id="job-matches">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Job Matches</h2>
            <a href="{{ route('candidate.job-matches') }}" class="cd-link cd-link--sm">View all</a>
        </div>
        <div class="cd-jobs-list">
            @forelse(array_slice($jobMatches, 0, 2) as $job)
                <div class="cd-job-item cd-job-item--compact">
                    <div class="cd-job-main">
                        <strong>{{ $job['title'] }}</strong>
                        <span class="cd-match-pill">{{ $job['match'] }}%</span>
                    </div>
                </div>
            @empty
                <p class="cd-empty-text mb-0">Upload resume for matches.</p>
            @endforelse
        </div>
    </div>
    </x-candidate-premium-gate>

    <div class="cd-card cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Applications</h2>
            <a href="{{ route('candidate.dashboard') }}#applications" class="cd-link cd-link--sm">View all →</a>
        </div>
        <div class="cd-tracker cd-tracker--compact">
            @foreach([
                ['key' => 'applied', 'label' => 'Applied'],
                ['key' => 'shortlisted', 'label' => 'Shortlisted'],
                ['key' => 'interview', 'label' => 'Interview'],
                ['key' => 'offer', 'label' => 'Offer'],
            ] as $t)
                <div class="cd-tracker-item">
                    <span class="cd-tracker-num">{{ $trackerCounts[$t['key']] ?? 0 }}</span>
                    <span class="cd-tracker-label">{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="cd-card cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Quick Stats</h2>
        </div>
        <div class="cd-stats-strip cd-stats-strip--inline">
            <div class="cd-stat-box">
                <span class="cd-stat-num">{{ $profilePct }}%</span>
                <span class="cd-stat-lbl">Profile</span>
            </div>
            <div class="cd-stat-box">
                <span class="cd-stat-num">{{ $hsResume && $hsResume['ai_score'] !== null ? $hsResume['ai_score'] : '—' }}</span>
                <span class="cd-stat-lbl">ATS</span>
            </div>
            <div class="cd-stat-box">
                <span class="cd-stat-num">{{ $dashboardStats['total_apps'] ?? 0 }}</span>
                <span class="cd-stat-lbl">Applied</span>
            </div>
        </div>
    </div>
</div>

<div class="cd-banner cd-banner--compact">
    <div class="cd-banner-content">
        <h3>Improve your Hiring Score to 80+</h3>
        <a href="{{ route('candidate.dashboard') }}#hiring-score" class="cd-btn cd-btn--primary cd-btn--sm">View improvement plan</a>
    </div>
</div>

@if(($applicationStatusCounts['all'] ?? $allApplications->total() ?? 0) > 0)
    <div class="cd-card cd-card--apps cd-card--compact">
        <div class="cd-card-head">
            <h2 class="cd-card-title">Recent Applications</h2>
            <a href="{{ route('candidate.dashboard') }}#applications" class="cd-link cd-link--sm">View all →</a>
        </div>
        <div class="cd-apps-list">
            @foreach($allApplications as $row)
                @if($loop->index >= 3) @break @endif
                @if($row->kind === 'employer')
                    @php $app = $row->application; $job = $app->employerJob; @endphp
                    <div class="cd-app-row">
                        <strong class="small">{{ $job->title }}</strong>
                        <span class="cd-status cd-status--{{ $app->status ?? 'applied' }}">{{ ucfirst($app->status ?? 'applied') }}</span>
                    </div>
                @else
                    @php $app = $row->application; @endphp
                    <div class="cd-app-row">
                        <strong class="small">{{ $app->jobRole->title }}</strong>
                        <span class="cd-status cd-status--applied">Goal</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
