@extends('layouts.employer')

@section('title', $isApproved ? 'Dashboard' : 'Pending Approval')
@section('header_title', $isApproved ? 'Dashboard' : 'Pending Approval')

@section('header_actions')
    @if($isApproved)
        <a href="{{ route('employer.jobs.create') }}" class="ed-btn ed-btn--primary">
            <i class="mdi mdi-plus"></i> Post a job
        </a>
    @endif
@endsection

@push('styles')
<style>
.ed-dashboard { max-width: 1200px; margin: 0 auto; }
.ed-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .5rem 1rem;
    border-radius: 10px;
    font-size: .875rem;
    font-weight: 600;
    text-decoration: none;
    border: none;
    transition: transform .15s, box-shadow .18s;
}
.ed-btn--primary {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff !important;
    box-shadow: 0 3px 12px rgba(22,163,74,.28);
}
.ed-btn--primary:hover { transform: translateY(-1px); box-shadow: 0 5px 16px rgba(22,163,74,.35); color: #fff !important; }
.ed-btn--outline {
    background: #fff;
    color: var(--brand) !important;
    border: 1.5px solid var(--border);
}
.ed-btn--outline:hover { background: var(--brand-light); border-color: #c7d2e0; }

/* Welcome hero */
.ed-hero {
    position: relative;
    overflow: hidden;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.25rem;
    border-radius: var(--radius-xl);
    background: linear-gradient(135deg, #0f2a50 0%, #163d73 55%, #1a4a8a 100%);
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 10px 32px rgba(15,42,80,.18);
    color: #fff;
}
.ed-hero__glow {
    position: absolute;
    top: -40px; right: -20px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(96,165,250,.22), transparent 70%);
    pointer-events: none;
}
.ed-hero__inner { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.ed-hero__eyebrow {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #93c5fd;
    margin-bottom: .35rem;
}
.ed-hero__title {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 .25rem;
    letter-spacing: -.02em;
}
.ed-hero__sub { font-size: .875rem; color: rgba(255,255,255,.6); margin: 0; }
.ed-hero__chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }
.ed-hero__chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-size: .72rem;
    font-weight: 600;
    padding: .3rem .65rem;
    border-radius: 100px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.14);
    color: rgba(255,255,255,.85);
}
.ed-hero__chip i { font-size: .85rem; }
.ed-hero__chip--active { background: rgba(22,163,74,.2); border-color: rgba(22,163,74,.35); color: #86efac; }
.ed-hero__actions { display: flex; gap: .5rem; flex-wrap: wrap; }
.ed-hero__actions .ed-btn--outline {
    background: rgba(255,255,255,.1);
    border-color: rgba(255,255,255,.25);
    color: #fff !important;
}
.ed-hero__actions .ed-btn--outline:hover { background: rgba(255,255,255,.18); }

/* Stat cards */
.ed-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: .875rem; margin-bottom: 1.25rem; }
.ed-stat {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.15rem;
    box-shadow: var(--shadow-xs);
    transition: border-color .2s, box-shadow .2s, transform .2s;
    position: relative;
    overflow: hidden;
}
.ed-stat::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    opacity: .9;
}
.ed-stat--jobs::after { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.ed-stat--active::after { background: linear-gradient(90deg, #16a34a, #4ade80); }
.ed-stat--apps::after { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.ed-stat--hires::after { background: linear-gradient(90deg, #d97706, #fbbf24); }
.ed-stat:hover { border-color: #c7d2e0; box-shadow: var(--shadow-sm); transform: translateY(-2px); }
.ed-stat__top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .65rem; }
.ed-stat__icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
}
.ed-stat--jobs .ed-stat__icon { background: #eff6ff; color: #2563eb; }
.ed-stat--active .ed-stat__icon { background: #ecfdf5; color: #16a34a; }
.ed-stat--apps .ed-stat__icon { background: #f5f3ff; color: #7c3aed; }
.ed-stat--hires .ed-stat__icon { background: #fffbeb; color: #d97706; }
.ed-stat__label { font-size: .75rem; font-weight: 600; color: var(--ink-500); margin: 0; }
.ed-stat__value {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--ink-900);
    letter-spacing: -.03em;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.ed-stat__hint { font-size: .7rem; color: var(--ink-300); margin-top: .35rem; }

/* Panels grid */
.ed-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
.ed-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xs);
    overflow: hidden;
}
.ed-panel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: 1rem 1.15rem;
    border-bottom: 1px solid var(--border-soft);
    background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
}
.ed-panel__title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--ink-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.ed-panel__title i { color: var(--accent); font-size: 1.1rem; }
.ed-panel__body { padding: 1rem 1.15rem 1.15rem; }

/* Funnel */
.ed-funnel { display: flex; flex-direction: column; gap: .65rem; }
.ed-funnel__item { display: grid; grid-template-columns: 100px 1fr 36px; align-items: center; gap: .65rem; }
.ed-funnel__label { font-size: .78rem; font-weight: 500; color: var(--ink-600, #4b5563); }
.ed-funnel__bar-wrap {
    height: 8px;
    background: var(--ink-100);
    border-radius: 100px;
    overflow: hidden;
}
.ed-funnel__bar {
    height: 100%;
    border-radius: 100px;
    background: linear-gradient(90deg, var(--brand-mid), var(--accent));
    transition: width .5s ease;
    min-width: 4px;
}
.ed-funnel__count { font-size: .85rem; font-weight: 700; color: var(--ink-900); text-align: right; font-variant-numeric: tabular-nums; }

/* Performance metrics */
.ed-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
.ed-metric {
    padding: .85rem .9rem;
    border-radius: var(--radius);
    background: var(--ink-50);
    border: 1px solid var(--border-soft);
}
.ed-metric__label { font-size: .68rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--ink-400); margin-bottom: .25rem; }
.ed-metric__value { font-size: 1.25rem; font-weight: 800; color: var(--ink-900); letter-spacing: -.02em; }
.ed-metric__value span { font-size: .85rem; font-weight: 600; color: var(--ink-400); }
.ed-metric--wide { grid-column: 1 / -1; }

/* Top jobs */
.ed-jobs-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--ink-500);
}
.ed-jobs-empty i { font-size: 2.5rem; color: var(--ink-300); margin-bottom: .75rem; display: block; }
.ed-job-row {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .85rem 0;
    border-bottom: 1px solid var(--border-soft);
    text-decoration: none;
    color: inherit;
    transition: background .15s;
}
.ed-job-row:last-child { border-bottom: none; padding-bottom: 0; }
.ed-job-row:first-child { padding-top: 0; }
.ed-job-row:hover .ed-job-row__title { color: var(--accent); }
.ed-job-row__rank {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: var(--brand-light);
    color: var(--brand);
    font-size: .75rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.ed-job-row__body { flex: 1; min-width: 0; }
.ed-job-row__title {
    font-size: .9rem;
    font-weight: 600;
    color: var(--ink-900);
    margin: 0 0 .15rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color .15s;
}
.ed-job-row__meta { font-size: .72rem; color: var(--ink-400); }
.ed-job-row__count {
    text-align: right;
    flex-shrink: 0;
}
.ed-job-row__count strong {
    display: block;
    font-size: 1rem;
    font-weight: 800;
    color: var(--ink-900);
    line-height: 1.1;
}
.ed-job-row__count span { font-size: .65rem; color: var(--ink-400); text-transform: uppercase; letter-spacing: .04em; }

/* Pending states */
.ed-pending-stack { display: flex; flex-direction: column; gap: 1rem; max-width: 720px; }
.ed-pending-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.35rem 1.5rem;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.ed-pending-card::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
}
.ed-pending-card--danger::before { background: linear-gradient(180deg, #ef4444, #dc2626); }
.ed-pending-card--warn::before { background: linear-gradient(180deg, #f59e0b, #d97706); }
.ed-pending-card__head { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
.ed-pending-card__icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.ed-pending-card--danger .ed-pending-card__icon { background: #fef2f2; color: #dc2626; }
.ed-pending-card--warn .ed-pending-card__icon { background: #fffbeb; color: #d97706; }
.ed-pending-card__badge {
    display: inline-block;
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    padding: .2rem .55rem;
    border-radius: 100px;
    margin-top: .35rem;
}
.ed-pending-card--danger .ed-pending-card__badge { background: #fef2f2; color: #dc2626; }
.ed-pending-card--warn .ed-pending-card__badge { background: #fffbeb; color: #b45309; }

@media (max-width: 991.98px) {
    .ed-stats { grid-template-columns: repeat(2, 1fr); }
    .ed-grid { grid-template-columns: 1fr; }
}
@media (max-width: 575.98px) {
    .ed-stats { grid-template-columns: 1fr; }
    .ed-hero__inner { flex-direction: column; align-items: flex-start; }
    .ed-funnel__item { grid-template-columns: 80px 1fr 30px; }
    .ed-metrics { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="ed-dashboard">

@if(!$isApproved)
    <div class="ed-pending-stack">
        <div class="ed-pending-card ed-pending-card--danger">
            <div class="ed-pending-card__head">
                <div class="ed-pending-card__icon"><i class="mdi mdi-email-alert-outline"></i></div>
                <div>
                    <h5 class="fw-700 mb-1">Email verification required</h5>
                    <p class="text-muted small mb-0">{{ $profile->company_name ?? 'Company' }}</p>
                    <span class="ed-pending-card__badge">Action required</span>
                </div>
            </div>
            <p class="text-muted mb-3">
                We sent a One-Time Password (OTP) to <strong>{{ auth()->user()->email }}</strong>.
                Verify your email to activate your account and start posting jobs.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('verify-email') }}" class="ed-btn ed-btn--primary" style="background:linear-gradient(135deg,#dc2626,#b91c1c);box-shadow:0 3px 12px rgba(220,38,38,.25);">
                    <i class="mdi mdi-check-circle"></i> Verify email
                </a>
                <a href="{{ route('employer.profile') }}" class="ed-btn ed-btn--outline">Edit profile</a>
            </div>
        </div>

        <div class="ed-pending-card ed-pending-card--warn">
            <div class="ed-pending-card__head">
                <div class="ed-pending-card__icon"><i class="mdi mdi-clock-outline"></i></div>
                <div>
                    <h5 class="fw-700 mb-1">Account under review</h5>
                    <p class="text-muted small mb-0">{{ $profile->company_name ?? 'Company' }}</p>
                    <span class="ed-pending-card__badge">Pending approval</span>
                </div>
            </div>
            <p class="text-muted mb-0">
                After email verification, our team will review your company details. You can post jobs and manage applications once approved.
                Questions? <a href="{{ route('contact') }}">Contact us</a>.
            </p>
        </div>
    </div>

@else
    @php
        $companyName = $profile->company_name ?? 'there';
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $hasPlan = $employerHasActivePlan ?? false;
        $planName = $employerActivePlanName ?? null;
        $credits = $employerCredits ?? 0;
        $funnelMax = max(1, $applicationCounts['total'], $applicationCounts['applied'], $applicationCounts['shortlisted'], $applicationCounts['interviewed'], $applicationCounts['offered'], $applicationCounts['hired'], $applicationCounts['rejected']);
        $funnelSteps = [
            ['label' => 'Applied', 'count' => $applicationCounts['applied'], 'color' => '#2563eb'],
            ['label' => 'Shortlisted', 'count' => $applicationCounts['shortlisted'], 'color' => '#7c3aed'],
            ['label' => 'Interviewed', 'count' => $applicationCounts['interviewed'], 'color' => '#0891b2'],
            ['label' => 'Offered', 'count' => $applicationCounts['offered'], 'color' => '#d97706'],
            ['label' => 'Hired', 'count' => $applicationCounts['hired'], 'color' => '#16a34a'],
            ['label' => 'Rejected', 'count' => $applicationCounts['rejected'], 'color' => '#94a3b8'],
        ];
    @endphp

    <div class="ed-hero">
        <div class="ed-hero__glow"></div>
        <div class="ed-hero__inner">
            <div>
                <div class="ed-hero__eyebrow">{{ $greeting }}</div>
                <h1 class="ed-hero__title">{{ $companyName }}</h1>
                <p class="ed-hero__sub">Track hiring performance, manage applications, and grow your team.</p>
                <div class="ed-hero__chips">
                    <span class="ed-hero__chip"><i class="mdi mdi-briefcase-outline"></i> {{ $counts['active'] }} active jobs</span>
                    <span class="ed-hero__chip"><i class="mdi mdi-lightning-bolt"></i> {{ $credits }} credits</span>
                    @if($hasPlan && $planName)
                        <span class="ed-hero__chip ed-hero__chip--active"><i class="mdi mdi-shield-check"></i> {{ $planName }}</span>
                    @else
                        <span class="ed-hero__chip"><i class="mdi mdi-shield-off-outline"></i> No active plan</span>
                    @endif
                </div>
            </div>
            <div class="ed-hero__actions">
                <a href="{{ route('employer.jobs.create') }}" class="ed-btn ed-btn--primary"><i class="mdi mdi-plus"></i> Post job</a>
                <a href="{{ route('employer.talent-pool.index') }}" class="ed-btn ed-btn--outline"><i class="mdi mdi-account-search"></i> Talent Pool</a>
            </div>
        </div>
    </div>

    <div class="ed-stats">
        <div class="ed-stat ed-stat--jobs">
            <div class="ed-stat__top">
                <p class="ed-stat__label">Total jobs</p>
                <div class="ed-stat__icon"><i class="mdi mdi-briefcase-outline"></i></div>
            </div>
            <div class="ed-stat__value">{{ $counts['all'] }}</div>
            <div class="ed-stat__hint">{{ $counts['draft'] }} draft · {{ $counts['closed'] }} closed</div>
        </div>
        <div class="ed-stat ed-stat--active">
            <div class="ed-stat__top">
                <p class="ed-stat__label">Active jobs</p>
                <div class="ed-stat__icon"><i class="mdi mdi-check-circle-outline"></i></div>
            </div>
            <div class="ed-stat__value">{{ $counts['active'] }}</div>
            <div class="ed-stat__hint">Live on job board</div>
        </div>
        <div class="ed-stat ed-stat--apps">
            <div class="ed-stat__top">
                <p class="ed-stat__label">Applications</p>
                <div class="ed-stat__icon"><i class="mdi mdi-account-multiple-outline"></i></div>
            </div>
            <div class="ed-stat__value">{{ $applicationCounts['total'] }}</div>
            <div class="ed-stat__hint">Across all postings</div>
        </div>
        <div class="ed-stat ed-stat--hires">
            <div class="ed-stat__top">
                <p class="ed-stat__label">Total hires</p>
                <div class="ed-stat__icon"><i class="mdi mdi-trophy-outline"></i></div>
            </div>
            <div class="ed-stat__value">{{ $applicationCounts['hired'] }}</div>
            <div class="ed-stat__hint">{{ $report['hire_rate'] }}% hire rate</div>
        </div>
    </div>

    <div class="ed-grid">
        <div class="ed-panel">
            <div class="ed-panel__head">
                <h2 class="ed-panel__title"><i class="mdi mdi-filter-variant"></i> Application funnel</h2>
                <a href="{{ route('employer.jobs.index') }}" class="ed-btn ed-btn--outline" style="padding:.35rem .7rem;font-size:.78rem;">View jobs</a>
            </div>
            <div class="ed-panel__body">
                @if($applicationCounts['total'] < 1)
                    <p class="text-muted small mb-0">No applications yet. Post a job to start building your funnel.</p>
                @else
                    <div class="ed-funnel">
                        @foreach($funnelSteps as $step)
                            @php $pct = min(100, round(($step['count'] / $funnelMax) * 100)); @endphp
                            <div class="ed-funnel__item">
                                <span class="ed-funnel__label">{{ $step['label'] }}</span>
                                <div class="ed-funnel__bar-wrap">
                                    <div class="ed-funnel__bar" style="width:{{ $pct }}%;background:linear-gradient(90deg,{{ $step['color'] }},{{ $step['color'] }}99);"></div>
                                </div>
                                <span class="ed-funnel__count">{{ $step['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="ed-panel">
            <div class="ed-panel__head">
                <h2 class="ed-panel__title"><i class="mdi mdi-chart-line"></i> Performance</h2>
            </div>
            <div class="ed-panel__body">
                <div class="ed-metrics">
                    <div class="ed-metric">
                        <div class="ed-metric__label">Shortlist rate</div>
                        <div class="ed-metric__value">{{ $report['shortlist_rate'] }}<span>%</span></div>
                    </div>
                    <div class="ed-metric">
                        <div class="ed-metric__label">Hire rate</div>
                        <div class="ed-metric__value">{{ $report['hire_rate'] }}<span>%</span></div>
                    </div>
                    <div class="ed-metric">
                        <div class="ed-metric__label">Avg match</div>
                        <div class="ed-metric__value">{{ $report['avg_match_score'] ?? '—' }}</div>
                    </div>
                    <div class="ed-metric">
                        <div class="ed-metric__label">Avg ATS</div>
                        <div class="ed-metric__value">{{ $report['avg_ats_score'] ?? '—' }}</div>
                    </div>
                    @if(!$hasPlan)
                        <div class="ed-metric ed-metric--wide" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border-color:#fde68a;">
                            <div class="ed-metric__label" style="color:#b45309;">Upgrade hiring</div>
                            <div style="font-size:.8rem;color:#92400e;margin-top:.25rem;">Unlock Talent Pool and advanced tools with a plan.</div>
                            <a href="{{ route('employer.plans.index') }}" class="ed-btn ed-btn--primary mt-2" style="display:inline-flex;padding:.4rem .85rem;font-size:.78rem;background:linear-gradient(135deg,#f59e0b,#d97706);">
                                View plans
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="ed-panel">
        <div class="ed-panel__head">
            <h2 class="ed-panel__title"><i class="mdi mdi-fire"></i> Top jobs by applications</h2>
            <a href="{{ route('employer.jobs.index') }}" class="ed-btn ed-btn--outline" style="padding:.35rem .7rem;font-size:.78rem;">All jobs</a>
        </div>
        <div class="ed-panel__body">
            @if($topJobs->isEmpty())
                <div class="ed-jobs-empty">
                    <i class="mdi mdi-briefcase-plus-outline"></i>
                    <p class="mb-2 fw-600">No jobs posted yet</p>
                    <p class="small text-muted mb-3">Create your first listing to start receiving applications.</p>
                    <a href="{{ route('employer.jobs.create') }}" class="ed-btn ed-btn--primary"><i class="mdi mdi-plus"></i> Post your first job</a>
                </div>
            @else
                @foreach($topJobs as $i => $job)
                    <a href="{{ route('employer.jobs.edit', $job) }}" class="ed-job-row">
                        <span class="ed-job-row__rank">{{ $i + 1 }}</span>
                        <div class="ed-job-row__body">
                            <p class="ed-job-row__title">{{ $job->title }}</p>
                            <span class="ed-job-row__meta">
                                <span class="hbadge {{ $job->status === 'active' ? 'active' : ($job->status === 'closed' ? 'closed' : 'draft') }}">{{ ucfirst($job->status) }}</span>
                            </span>
                        </div>
                        <div class="ed-job-row__count">
                            <strong>{{ $job->displayApplicationsCount() }}</strong>
                            <span>apps</span>
                        </div>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
@endif

</div>
@endsection
