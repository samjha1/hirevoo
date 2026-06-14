<div class="cd-page-back">
    <a href="{{ route('candidate.dashboard') }}" class="cd-link"><i class="mdi mdi-arrow-left"></i> Back to Dashboard</a>
</div>

<div class="cd-section-head">
    <div>
        <p class="cd-section-kicker">Track your progress</p>
        <h2 class="cd-section-title">Applications Tracker</h2>
        <p class="cd-section-sub">All job openings and job goals you have applied to — with live status updates.</p>
    </div>
    <a href="{{ route('job-openings') }}" class="cd-btn cd-btn--primary cd-btn--sm">Browse jobs</a>
</div>

<div class="cd-tracker cd-apps-tracker">
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

@php
    $statusFilter = $applicationStatusFilter ?? 'all';
    $statusCounts = $applicationStatusCounts ?? ['all' => 0];
    $statusTabs = [
        'all' => 'All',
        'applied' => 'Applied',
        'shortlisted' => 'Shortlisted',
        'interviewed' => 'Interview',
        'offered' => 'Offered',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
    ];
@endphp

<div class="cd-apps-filters" role="tablist" aria-label="Filter by status">
    @foreach($statusTabs as $key => $label)
        <a href="{{ $key === 'all' ? route('candidate.dashboard').'#applications' : route('candidate.dashboard', ['status' => $key]).'#applications' }}"
           class="cd-apps-filter {{ $statusFilter === $key ? 'is-active' : '' }}"
           role="tab"
           aria-selected="{{ $statusFilter === $key ? 'true' : 'false' }}">
            {{ $label }}
            <span class="cd-apps-filter-count">{{ $statusCounts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>

@if($allApplications->total() === 0)
    <div class="cd-card cd-apps-empty">
        <div class="cd-apps-empty-icon" aria-hidden="true"><i class="mdi mdi-briefcase-off-outline"></i></div>
        <h3>No applications{{ $statusFilter !== 'all' ? ' in this stage' : '' }}</h3>
        <p>
            @if($statusFilter !== 'all')
                Try another filter or browse new openings.
            @else
                Browse live openings or explore skill-based job goals to get started.
            @endif
        </p>
        <div class="cd-apps-empty-actions">
            <a href="{{ route('job-openings') }}" class="cd-btn cd-btn--primary cd-btn--sm">Browse job openings</a>
            <a href="{{ route('job-list') }}" class="cd-btn cd-btn--sm crr-btn-outline">Explore job goals</a>
        </div>
    </div>
@else
    <div class="cd-apps-grid">
        @foreach($allApplications as $row)
            @if($row->kind === 'employer')
                @php
                    $app = $row->application;
                    $job = $app->employerJob;
                    $companyName = $job->company_name ?? ($job->user?->referrerProfile?->company_name ?? '—');
                    $initials = collect(explode(' ', $companyName))->take(2)->map(fn ($w) => strtoupper($w[0] ?? ''))->implode('');
                    $statusKey = $app->status ?? 'applied';
                    $statusLabel = \App\Models\EmployerJobApplication::statusOptions()[$statusKey] ?? ucfirst($statusKey);
                    $score = $app->job_match_score;
                    $scoreColor = $score >= 75 ? 'high' : ($score >= 50 ? 'mid' : 'low');
                @endphp
                <article class="cd-app-card">
                    <div class="cd-app-card-top">
                        <div class="cd-app-logo">{{ $initials ?: '?' }}</div>
                        <div class="cd-app-card-head">
                            <span class="cd-app-type">Live opening</span>
                            <h3 class="cd-app-title">{{ $job->title }}</h3>
                            <span class="cd-app-company">{{ $companyName }}</span>
                        </div>
                        <span class="cd-status cd-status--{{ $statusKey }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="cd-app-card-meta">
                        @if($job->formatted_location)
                            <span><i class="mdi mdi-map-marker-outline"></i> {{ $job->formatted_location }}</span>
                        @endif
                        @if($job->work_location_type)
                            <span><i class="mdi mdi-home-outline"></i> {{ ucfirst($job->work_location_type) }}</span>
                        @endif
                        @if($job->job_type)
                            <span><i class="mdi mdi-briefcase-outline"></i> {{ str_replace('_', ' ', ucfirst($job->job_type)) }}</span>
                        @endif
                    </div>
                    <div class="cd-app-card-foot">
                        <span class="cd-app-date"><i class="mdi mdi-calendar-outline"></i> Applied {{ $app->created_at->format('d M Y') }}</span>
                        @if($score !== null)
                            <span class="cd-app-match cd-app-match--{{ $scoreColor }}">{{ $score }}% match</span>
                        @endif
                        <a href="{{ route('job-openings') }}" class="cd-link cd-link--sm">View opening</a>
                    </div>
                </article>
            @else
                @php
                    $app = $row->application;
                    $score = $app->match_score ?? null;
                    $scoreColor = $score >= 75 ? 'high' : ($score >= 50 ? 'mid' : 'low');
                    $statusKey = $app->status ?? 'applied';
                @endphp
                <article class="cd-app-card cd-app-card--goal">
                    <div class="cd-app-card-top">
                        <div class="cd-app-logo cd-app-logo--goal">🎯</div>
                        <div class="cd-app-card-head">
                            <span class="cd-app-type">Job goal</span>
                            <h3 class="cd-app-title">
                                <a href="{{ route('job-goal.show', $app->jobRole) }}">{{ $app->jobRole->title }}</a>
                            </h3>
                            <span class="cd-app-company">Skill-based application</span>
                        </div>
                        <span class="cd-status cd-status--{{ $statusKey }}">{{ ucfirst($statusKey) }}</span>
                    </div>
                    <div class="cd-app-card-meta">
                        <span><i class="mdi mdi-chart-box-outline"></i> Skill match application</span>
                    </div>
                    <div class="cd-app-card-foot">
                        <span class="cd-app-date"><i class="mdi mdi-calendar-outline"></i> Applied {{ $app->created_at->format('d M Y') }}</span>
                        @if($score !== null)
                            <span class="cd-app-match cd-app-match--{{ $scoreColor }}">{{ $score }}% match</span>
                        @endif
                        <a href="{{ route('job-goal.show', $app->jobRole) }}" class="cd-link cd-link--sm">View goal</a>
                    </div>
                </article>
            @endif
        @endforeach
    </div>

    <div class="cd-apps-pagination-wrap">
        @if($allApplications->total() > 0)
            <p class="cd-apps-pagination-meta">
                Showing {{ $allApplications->firstItem() }}–{{ $allApplications->lastItem() }} of {{ $allApplications->total() }}
            </p>
        @endif
        @if($allApplications->hasPages())
            <div class="cd-apps-pagination">{{ $allApplications->onEachSide(1)->links() }}</div>
        @endif
    </div>
@endif

<div class="cd-apps-legend">
    <span class="cd-apps-legend-title">Status key</span>
    <div class="cd-apps-legend-items">
        @foreach([
            ['applied', 'Applied', '#94a3b8'],
            ['shortlisted', 'Shortlisted', '#10b981'],
            ['interviewed', 'Interviewed', '#3b82f6'],
            ['offered', 'Offered', '#8b5cf6'],
            ['hired', 'Hired', '#047857'],
            ['rejected', 'Rejected', '#ef4444'],
        ] as [$key, $label, $color])
            <span class="cd-apps-legend-item"><i style="background:{{ $color }}"></i>{{ $label }}</span>
        @endforeach
    </div>
</div>
