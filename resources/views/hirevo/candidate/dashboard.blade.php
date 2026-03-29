@extends('layouts.app')

@section('title', 'My Applications')

@push('styles')
<style>
    /* Candidate dashboard – production styles (uses theme vars) */
    .apps-page {
        background: var(--hirevo-accent, #f3f4f6);
        min-height: 100vh;
        padding-bottom: 3rem;
        color: #0f172a;
    }

    .apps-hero {
        background: #fff;
        border-bottom: 1px solid rgba(0,0,0,.06);
        padding: 1.75rem 0 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .apps-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse 50% 80% at 95% 30%, rgba(11,31,59,.04) 0%, transparent 60%);
        pointer-events: none;
    }
    .apps-hero .container { position: relative; z-index: 1; }

    .breadcrumb-row {
        display: flex;
        align-items: center;
        gap: .35rem;
        font-size: .8125rem;
        color: #64748b;
        margin-bottom: 1rem;
    }
    .breadcrumb-row a {
        color: #64748b;
        text-decoration: none;
        transition: color .2s ease;
    }
    .breadcrumb-row a:hover { color: var(--hirevo-primary, #0B1F3B); }
    .breadcrumb-row .sep { opacity: .5; }
    .breadcrumb-row .current { color: #475569; font-weight: 500; }

    .hero-inner {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .hero-label {
        font-size: .6875rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--hirevo-secondary, #10B981);
        margin-bottom: .35rem;
    }
    .hero-title {
        font-size: 1.65rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 .25rem;
        line-height: 1.2;
    }
    .hero-sub {
        font-size: .875rem;
        color: #64748b;
        margin: 0;
    }
    .hero-action {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: var(--hirevo-primary, #0B1F3B);
        color: #fff;
        font-size: .875rem;
        font-weight: 500;
        padding: .5rem 1.15rem;
        border-radius: 999px;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
        box-shadow: 0 2px 8px rgba(11,31,59,.2);
        white-space: nowrap;
    }
    .hero-action:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(11,31,59,.25); }
    .hero-action:focus-visible { outline: 2px solid var(--hirevo-primary); outline-offset: 2px; }
    .hero-action svg { flex-shrink: 0; }

    .stats-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: .75rem;
        margin-top: 1.5rem;
    }
    .stat-pill {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1rem 1.15rem;
        display: flex;
        align-items: center;
        gap: .75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: box-shadow .2s ease;
    }
    .stat-pill:hover { box-shadow: 0 4px 12px rgba(0,0,0,.06); }
    .stat-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        display: grid; place-items: center;
        flex-shrink: 0;
        font-size: 1rem;
    }
    .stat-icon.purple { background: rgba(11,31,59,.08); color: var(--hirevo-primary); }
    .stat-icon.green  { background: rgba(16,185,129,.15); color: var(--hirevo-secondary); }
    .stat-icon.amber  { background: rgba(245,158,11,.15); color: #d97706; }
    .stat-icon.blue   { background: rgba(59,130,246,.12); color: #2563eb; }
    .stat-num { font-size: 1.25rem; font-weight: 700; line-height: 1; color: #0f172a; }
    .stat-lbl { font-size: .6875rem; color: #64748b; margin-top: .15rem; }

    .apps-body { padding-top: 1.5rem; }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .section-title-group { display: flex; align-items: center; gap: .5rem; }
    .section-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--hirevo-primary);
        flex-shrink: 0;
    }
    .section-dot.green { background: var(--hirevo-secondary); }
    .section-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0; }
    .section-count {
        background: rgba(11,31,59,.1);
        color: var(--hirevo-primary);
        font-size: .6875rem;
        font-weight: 700;
        padding: .15rem .5rem;
        border-radius: 999px;
    }
    .section-count.green-count { background: rgba(16,185,129,.15); color: var(--hirevo-secondary); }
    .section-desc { font-size: .8125rem; color: #64748b; margin: 0; }

    .apps-grid { display: flex; flex-direction: column; gap: .6rem; }

    .app-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 14px;
        padding: 1.15rem 1.35rem;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: box-shadow .2s ease, border-color .2s ease, transform .2s ease;
        position: relative;
        overflow: hidden;
    }
    .app-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: var(--hirevo-primary);
        opacity: 0;
        transition: opacity .2s ease;
        border-radius: 3px 0 0 3px;
    }
    .app-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        border-color: rgba(11,31,59,.12);
        transform: translateY(-2px);
    }
    .app-card:hover::before { opacity: 1; }
    @media (prefers-reduced-motion: reduce) {
        .app-card, .app-card::before { transition: none; }
        .app-card:hover { transform: none; }
    }

    .app-card-main { min-width: 0; }
    .app-card-top {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: .4rem;
        flex-wrap: wrap;
    }
    .company-logo {
        width: 34px; height: 34px;
        border-radius: 8px;
        background: rgba(11,31,59,.08);
        display: grid; place-items: center;
        font-size: .75rem;
        font-weight: 700;
        color: var(--hirevo-primary);
        flex-shrink: 0;
        border: 1px solid rgba(11,31,59,.1);
    }
    .company-name { font-size: .8125rem; font-weight: 500; color: #475569; }

    .app-job-title {
        font-size: .9375rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 .4rem;
        line-height: 1.3;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .app-job-title a { color: inherit; text-decoration: none; }
    .app-job-title a:hover { text-decoration: underline; }

    .app-meta { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem; }
    .meta-tag {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        font-size: .6875rem;
        color: #64748b;
        background: var(--hirevo-accent, #f3f4f6);
        border: 1px solid rgba(0,0,0,.06);
        padding: .2rem .5rem;
        border-radius: 999px;
    }
    .meta-tag svg { opacity: .7; flex-shrink: 0; }

    .app-card-aside {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .5rem;
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .6875rem;
        font-weight: 600;
        padding: .25rem .6rem;
        border-radius: 999px;
    }
    .status-badge .dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .status-badge.applied    { background: #f1f5f9; color: #64748b; }
    .status-badge.applied .dot { background: #94a3b8; }
    .status-badge.shortlisted { background: rgba(16,185,129,.15); color: #059669; }
    .status-badge.shortlisted .dot { background: var(--hirevo-secondary); }
    .status-badge.interviewed { background: rgba(59,130,246,.12); color: #2563eb; }
    .status-badge.interviewed .dot { background: #3b82f6; }
    .status-badge.offered    { background: rgba(11,31,59,.1); color: var(--hirevo-primary); }
    .status-badge.offered .dot { background: var(--hirevo-primary); }
    .status-badge.hired      { background: rgba(16,185,129,.2); color: #047857; }
    .status-badge.hired .dot { background: var(--hirevo-secondary); }
    .status-badge.rejected   { background: rgba(239,68,68,.12); color: #dc2626; }
    .status-badge.rejected .dot { background: #ef4444; }

    .match-ring {
        position: relative;
        width: 40px; height: 40px;
        flex-shrink: 0;
    }
    .match-ring svg { transform: rotate(-90deg); }
    .match-ring .track { fill: none; stroke: var(--hirevo-accent,#f3f4f6); stroke-width: 3; }
    .match-ring .fill { fill: none; stroke: var(--hirevo-primary); stroke-width: 3; stroke-linecap: round; transition: stroke-dashoffset .4s ease; }
    .match-ring .fill.green { stroke: var(--hirevo-secondary); }
    .match-ring .fill.amber { stroke: #f59e0b; }
    .match-ring-num {
        position: absolute;
        inset: 0;
        display: grid; place-items: center;
        font-size: .5625rem;
        font-weight: 700;
        color: #0f172a;
    }
    .match-no { font-size: .75rem; color: #94a3b8; }

    .app-date { font-size: .6875rem; color: #64748b; white-space: nowrap; }

    .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        background: #fff;
        border: 1px dashed rgba(0,0,0,.1);
        border-radius: 14px;
    }
    .empty-icon {
        width: 52px; height: 52px;
        background: rgba(11,31,59,.08);
        border-radius: 14px;
        display: grid; place-items: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }
    .empty-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 .35rem; }
    .empty-sub { font-size: .875rem; color: #64748b; margin: 0 0 1.25rem; }

    .btn-outline-accent {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        background: transparent;
        color: var(--hirevo-primary);
        border: 1.5px solid var(--hirevo-primary);
        font-size: .8125rem;
        font-weight: 500;
        padding: .4rem 1rem;
        border-radius: 999px;
        text-decoration: none;
        transition: background .2s ease, color .2s ease;
    }
    .btn-outline-accent:hover { background: var(--hirevo-primary); color: #fff; }
    .btn-outline-accent:focus-visible { outline: 2px solid var(--hirevo-primary); outline-offset: 2px; }

    .apps-section { margin-bottom: 2rem; }

    .legend-bar {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 14px;
        padding: 1rem 1.35rem;
        display: flex;
        flex-wrap: wrap;
        gap: .75rem 1.25rem;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
    }
    .legend-title { font-size: .6875rem; font-weight: 600; color: #475569; letter-spacing: .04em; text-transform: uppercase; margin-right: .25rem; }
    .legend-item { display: flex; align-items: center; gap: .3rem; font-size: .75rem; color: #64748b; }
    .legend-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

    .app-alert {
        border: none;
        border-radius: 12px;
        padding: .85rem 1rem;
        font-size: .875rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 640px) {
        .hero-title { font-size: 1.35rem; }
        .app-card { grid-template-columns: 1fr; padding: 1rem 1.15rem; }
        .app-card-aside { flex-direction: row; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .apps-hero { padding: 1.25rem 0 1.25rem; }
        .stats-strip { grid-template-columns: 1fr 1fr; }
        .app-job-title { white-space: normal; }
    }
</style>
@endpush

@section('content')

    <div class="apps-page">

        {{-- ── Hero ── --}}
        <div class="apps-hero">
            <div class="container">
                <div class="breadcrumb-row">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="sep">›</span>
                    <span class="current">My Applications</span>
                </div>

                <div class="hero-inner">
                    <div class="hero-title-block">
                        <p class="hero-label">Career tracker</p>
                        <h1 class="hero-title">My Applications</h1>
                        <p class="hero-sub">Track every application — from first click to final offer.</p>
                    </div>
                    <a href="{{ route('job-openings') }}" class="hero-action">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        Browse more jobs
                    </a>
                </div>

                {{-- Stats --}}
                @php
                    $totalApps      = $employerApplications->count() + $jobGoalApplications->count();
                    $activeApps     = $employerApplications->whereIn('status', ['shortlisted','interviewed','offered'])->count();
                    $hiredCount     = $employerApplications->where('status','hired')->count();
                    $avgMatch       = $employerApplications->whereNotNull('job_match_score')->avg('job_match_score');
                @endphp
                <div class="stats-strip">
                    <div class="stat-pill">
                        <div class="stat-icon purple">📋</div>
                        <div>
                            <div class="stat-num">{{ $totalApps }}</div>
                            <div class="stat-lbl">Total applied</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon blue">🔍</div>
                        <div>
                            <div class="stat-num">{{ $activeApps }}</div>
                            <div class="stat-lbl">Active reviews</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon green">🎉</div>
                        <div>
                            <div class="stat-num">{{ $hiredCount }}</div>
                            <div class="stat-lbl">Offers / Hired</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon amber">⚡</div>
                        <div>
                            <div class="stat-num">{{ $avgMatch ? round($avgMatch) . '%' : '—' }}</div>
                            <div class="stat-lbl">Avg match</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Body ── --}}
        <div class="apps-body">
            <div class="container">

                @if(session('success'))
                    <div class="app-alert alert alert-success alert-dismissible fade show mt-3" role="alert">
                        ✓ {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="app-alert alert alert-info alert-dismissible fade show mt-3" role="alert">
                        ℹ {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ── Employer job applications ── --}}
                <div class="apps-section">
                    <div class="section-header">
                        <div class="section-title-group">
                            <div class="section-dot"></div>
                            <h2 class="section-title">Job Applications</h2>
                            @if($employerApplications->isNotEmpty())
                                <span class="section-count">{{ $employerApplications->count() }}</span>
                            @endif
                        </div>
                        <p class="section-desc d-none d-sm-block">Applications to company job openings via Hirevo</p>
                    </div>

                    @if($employerApplications->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon">💼</div>
                            <p class="empty-title">No applications yet</p>
                            <p class="empty-sub">Explore job openings and apply to get started.</p>
                            <a href="{{ route('job-openings') }}" class="btn-outline-accent">Browse job openings</a>
                        </div>
                    @else
                        <div class="apps-grid">
                            @foreach($employerApplications as $app)
                                @php
                                    $job = $app->employerJob;
                                    $companyName = $job->company_name ?? ($job->user?->referrerProfile?->company_name ?? '—');
                                    $initials = collect(explode(' ', $companyName))->take(2)->map(fn($w) => strtoupper($w[0] ?? ''))->implode('');
                                    $statusKey = $app->status ?? 'applied';
                                    $statusLabel = \App\Models\EmployerJobApplication::statusOptions()[$statusKey] ?? ucfirst($statusKey);
                                    $score = $app->job_match_score;
                                    $scoreColor = $score >= 75 ? 'green' : ($score >= 50 ? 'amber' : '');
                                    $circumference = 2 * 3.14159 * 15;
                                    $offset = $score !== null ? $circumference - ($score / 100 * $circumference) : $circumference;
                                @endphp
                                <div class="app-card">
                                    <div class="app-card-main">
                                        <div class="app-card-top">
                                            <div class="company-logo">{{ $initials ?: '?' }}</div>
                                            <span class="company-name">{{ $companyName }}</span>
                                        </div>
                                        <p class="app-job-title"><a href="{{ route('job-openings') }}">{{ $job->title }}</a></p>
                                        <div class="app-meta">
                                            @if($job->formatted_location)
                                                <span class="meta-tag">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                {{ $job->formatted_location }}
                                            </span>
                                            @endif
                                            @if($job->work_location_type)
                                                <span class="meta-tag">{{ ucfirst($job->work_location_type) }}</span>
                                            @endif
                                            @if($job->job_type)
                                                <span class="meta-tag">{{ str_replace('_', ' ', ucfirst($job->job_type)) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="app-card-aside">
                                    <span class="status-badge {{ $statusKey }}">
                                        <span class="dot"></span>
                                        {{ $statusLabel }}
                                    </span>
                                        @if($score !== null)
                                            <div class="text-end">
                                                <div class="match-no mb-1">Profile match score</div>
                                                <div class="match-ring d-inline-block" title="{{ $score }}% match">
                                                    <svg width="40" height="40" viewBox="0 0 40 40">
                                                        <circle class="track" cx="20" cy="20" r="15"/>
                                                        <circle class="fill {{ $scoreColor }}" cx="20" cy="20" r="15"
                                                                stroke-dasharray="{{ $circumference }}"
                                                                stroke-dashoffset="{{ $offset }}"/>
                                                    </svg>
                                                    <div class="match-ring-num">{{ $score }}%</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="match-no">Profile match score — No score</span>
                                        @endif
                                        <span class="app-date">{{ $app->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ── Job goal applications ── --}}
                <div class="apps-section">
                    <div class="section-header">
                        <div class="section-title-group">
                            <div class="section-dot green"></div>
                            <h2 class="section-title">Job Goal Applications</h2>
                            @if($jobGoalApplications->isNotEmpty())
                                <span class="section-count green-count">{{ $jobGoalApplications->count() }}</span>
                            @endif
                        </div>
                        <p class="section-desc d-none d-sm-block">Skill-based role applications via your job goals</p>
                    </div>

                    @if($jobGoalApplications->isEmpty())
                        <div class="empty-state">
                            <div class="empty-icon">🎯</div>
                            <p class="empty-title">No job goal applications yet</p>
                            <p class="empty-sub">Explore skill-based roles aligned with your goals.</p>
                            <a href="{{ route('job-list') }}" class="btn-outline-accent">Explore job goals</a>
                        </div>
                    @else
                        <div class="apps-grid">
                            @foreach($jobGoalApplications as $app)
                                @php
                                    $score = $app->match_score ?? null;
                                    $scoreColor = $score >= 75 ? 'green' : ($score >= 50 ? 'amber' : '');
                                    $circumference = 2 * 3.14159 * 15;
                                    $offset = $score !== null ? $circumference - ($score / 100 * $circumference) : $circumference;
                                    $statusKey = $app->status ?? 'applied';
                                @endphp
                                <div class="app-card">
                                    <div class="app-card-main">
                                        <div class="app-card-top">
                                            <div class="company-logo" style="background:#d6f5ec;color:#059669;border-color:rgba(0,184,122,.15)">🎯</div>
                                            <span class="company-name">Skill-based role</span>
                                        </div>
                                        <p class="app-job-title">
                                            <a href="{{ route('job-goal.show', $app->jobRole) }}" class="text-decoration-none" style="color:inherit">{{ $app->jobRole->title }}</a>
                                        </p>
                                        <div class="app-meta">
                                        <span class="meta-tag">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            Skill match
                                        </span>
                                        </div>
                                    </div>
                                    <div class="app-card-aside">
                                    <span class="status-badge {{ $statusKey }}">
                                        <span class="dot"></span>
                                        {{ ucfirst($statusKey) }}
                                    </span>
                                        @if($score !== null)
                                            <div class="text-end">
                                                <div class="match-no mb-1">Profile match score</div>
                                                <div class="match-ring d-inline-block" title="{{ $score }}% match">
                                                    <svg width="40" height="40" viewBox="0 0 40 40">
                                                        <circle class="track" cx="20" cy="20" r="15"/>
                                                        <circle class="fill {{ $scoreColor }}" cx="20" cy="20" r="15"
                                                                stroke-dasharray="{{ $circumference }}"
                                                                stroke-dashoffset="{{ $offset }}"/>
                                                    </svg>
                                                    <div class="match-ring-num">{{ $score }}%</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="match-no">Profile match score — No score</span>
                                        @endif
                                        <span class="app-date">{{ $app->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ── Legend ── --}}
                <div class="legend-bar">
                    <span class="legend-title">Status key</span>
                    <div class="legend-item"><span class="legend-dot" style="background:#94a3b8"></span> Applied</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--green)"></span> Shortlisted</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--blue)"></span> Interviewed</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--accent)"></span> Offered</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#10b981"></span> Hired</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--red)"></span> Rejected</div>
                </div>

            </div>
        </div>
    </div>

@endsection