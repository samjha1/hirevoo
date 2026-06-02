@extends('layouts.app')

@section('title', 'My Applications')

@push('styles')
@php
    $candidateDashCss = public_path('css/hirevo-candidate-dashboard.css');
    $candidateDashCssVer = is_file($candidateDashCss) ? (string) filemtime($candidateDashCss) : '1';
@endphp
<link href="{{ asset('css/hirevo-candidate-dashboard.css') }}?v={{ $candidateDashCssVer }}" rel="stylesheet">
{{-- Fallback layout if the external CSS file is missing on production --}}
<style>
.apps-page{background:var(--hirevo-accent,#f3f4f6);min-height:100vh;padding-bottom:3rem;color:#0f172a}
.apps-hero{background:#fff;border-bottom:1px solid rgba(0,0,0,.06);padding:.85rem 0 1.15rem}
.hero-inner{display:flex;align-items:flex-end;justify-content:space-between;gap:1rem;flex-wrap:wrap}
.stats-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;margin-top:1.5rem}
.stat-pill{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:12px;padding:1rem 1.15rem;display:flex;align-items:center;gap:.75rem;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.stat-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;font-size:1rem}
.stat-icon.purple{background:rgba(11,31,59,.08);color:var(--hirevo-primary,#0B1F3B)}
.stat-icon.blue{background:rgba(59,130,246,.12);color:#2563eb}
.stat-icon.green{background:rgba(16,185,129,.15);color:var(--hirevo-secondary,#10B981)}
.stat-icon.amber{background:rgba(245,158,11,.15);color:#d97706}
.stat-num{font-size:1.25rem;font-weight:700;line-height:1;color:#0f172a}
.stat-lbl{font-size:.6875rem;color:#64748b;margin-top:.15rem}
.dash-insight-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.25rem}
@media (max-width:991px){.dash-insight-grid{grid-template-columns:1fr}}
.dash-card{border-radius:12px;padding:.85rem 1rem;position:relative;overflow:hidden;box-shadow:0 2px 14px rgba(11,31,59,.06);border:1px solid rgba(0,0,0,.06)}
.apps-grid{display:flex;flex-direction:column;gap:.6rem}
.app-card{background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:14px;padding:1.15rem 1.35rem;display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:center}
@media (max-width:640px){.stats-strip{grid-template-columns:1fr 1fr}.app-card{grid-template-columns:1fr}}
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

                @include('hirevo.partials.sponsored-ad', ['ad' => $sponsoredAd ?? null, 'variant' => $sponsoredAdVariant ?? 'dashboard'])

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

                {{-- Stats (all-time totals, not current page) --}}
                @php
                    $totalApps = $dashboardStats['total_apps'];
                    $activeApps = $dashboardStats['active_reviews'];
                    $hiredCount = $dashboardStats['hired_count'];
                    $avgMatch = $dashboardStats['avg_match'];
                @endphp
                <div class="stats-strip">
                    <div class="stat-pill">
                        <div class="stat-icon purple"><i class="mdi mdi-clipboard-text-outline"></i></div>
                        <div>
                            <div class="stat-num">{{ $totalApps }}</div>
                            <div class="stat-lbl">Total applied</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon blue"><i class="mdi mdi-eye-outline"></i></div>
                        <div>
                            <div class="stat-num">{{ $activeApps }}</div>
                            <div class="stat-lbl">In progress</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon green"><i class="mdi mdi-trophy-outline"></i></div>
                        <div>
                            <div class="stat-num">{{ $hiredCount }}</div>
                            <div class="stat-lbl">Hired</div>
                        </div>
                    </div>
                    <div class="stat-pill">
                        <div class="stat-icon amber"><i class="mdi mdi-chart-donut"></i></div>
                        <div>
                            <div class="stat-num">{{ $avgMatch ? round($avgMatch) . '%' : '—' }}</div>
                            <div class="stat-lbl">Avg job match</div>
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

                @php
                    $consultGapPayload = $consultGapPayload ?? ['display_gaps' => [], 'suggested_only' => [], 'actual_gaps' => []];
                    $dashGapsDisplay = ! empty($consultGapPayload['display_gaps']) ? $consultGapPayload['display_gaps'] : ($dashboardSkillGaps ?? []);
                    $dashboardRecommendMasters = $dashboardRecommendMasters ?? false;
                    $dashboardMastersField = $dashboardMastersField ?? 'your field';
                @endphp

                <div class="dash-insight-grid mt-3">
                    <div class="dash-card dash-card--skills">
                        <div class="dash-card-inner">
                            @if(!$primaryResume)
                                <span class="dash-badge">Upgrade path</span>
                                <h3 class="dash-card-title">Level up your career</h3>
                                <p class="dash-card-sub mb-2">Upload your resume so we can tailor upgrade steps — skills, programs, and next moves.</p>

                                @if($dashboardRecommendMasters)
                                    <a href="{{ route('pricing') }}" class="dash-action-tile dash-action-tile--masters mb-2">
                                        <span class="dash-action-tile__icon" aria-hidden="true"><i class="mdi mdi-school-outline"></i></span>
                                        <span class="dash-action-tile__text">
                                            <strong>Recommended: M.E. / M.Tech in {{ $dashboardMastersField }}</strong>
                                            <span class="dash-action-tile__sub">You have B.E. / B.Tech — a masters in your field is a strong upgrade path. View programs &amp; pricing.</span>
                                        </span>
                                        <i class="mdi mdi-chevron-right dash-action-tile__chev" aria-hidden="true"></i>
                                    </a>
                                @endif

                                <div class="dash-action-stack">
                                    <a href="{{ route('resume.upload') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-file-upload-outline me-1"></i> Upload resume</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('job-list') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-bullseye-arrow me-1"></i> Explore job goals</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @elseif($skillFocusRole && $dashboardSkillMatchPct !== null)
                                <span class="dash-badge">Upgrade path</span>
                                <h3 class="dash-card-title">{{ $skillFocusRole->title }}</h3>
                                <p class="dash-card-sub mb-2">
                                    <strong>{{ $dashboardSkillMatchPct }}%</strong> of this role’s skills show on your resume — open the full match to close gaps and move up.
                                </p>

                                @if($dashboardRecommendMasters)
                                    <a href="{{ route('pricing') }}" class="dash-action-tile dash-action-tile--masters mb-2">
                                        <span class="dash-action-tile__icon" aria-hidden="true"><i class="mdi mdi-school-outline"></i></span>
                                        <span class="dash-action-tile__text">
                                            <strong>Recommended: M.E. / M.Tech in {{ $dashboardMastersField }}</strong>
                                            <span class="dash-action-tile__sub">Upgrade from B.E. / B.Tech with a masters aligned to your field — see programs.</span>
                                        </span>
                                        <i class="mdi mdi-chevron-right dash-action-tile__chev" aria-hidden="true"></i>
                                    </a>
                                @endif

                                <div class="dash-action-stack">
                                    <a href="{{ route('job-goal.show', $skillFocusRole) }}" class="dash-action-row">
                                        <span><i class="mdi mdi-chart-box-outline me-1"></i> Full skill match &amp; gaps</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('resume.upload') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-file-document-edit-outline me-1"></i> Update resume</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('job-list') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-view-list-outline me-1"></i> Browse more job goals</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>

                                @if(count($dashGapsDisplay) > 0)
                                    <form action="{{ route('career-consultation.store') }}" method="POST" class="dash-action-stack mt-2 mb-0">
                                        @csrf
                                        <input type="hidden" name="job_role_id" value="{{ $skillFocusRole->id }}">
                                        <input type="hidden" name="source" value="dashboard">
                                        <input type="hidden" name="match_percentage" value="{{ $dashboardSkillMatchPct }}">
                                        @foreach($dashGapsDisplay as $g)
                                            <input type="hidden" name="gap_skills[]" value="{{ $g }}">
                                        @endforeach
                                        @foreach($consultGapPayload['suggested_only'] ?? [] as $g)
                                            <input type="hidden" name="suggested_gap_skills[]" value="{{ $g }}">
                                        @endforeach
                                        @foreach($dashboardSkillMatched ?? [] as $m)
                                            <input type="hidden" name="matched_skills[]" value="{{ $m }}">
                                        @endforeach
                                        <button type="submit" class="dash-action-row dash-action-row--cta">
                                            <span><i class="mdi mdi-account-voice me-1"></i> Request career consultation</span>
                                            <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @endif

                                @if($skillFocusSource === 'applied_goal')
                                    <p class="dash-source-hint mb-0">Based on your latest job goal application.</p>
                                @elseif($skillFocusSource === 'resume_top')
                                    <p class="dash-source-hint mb-0">Based on your best-fit role from your resume.</p>
                                @endif
                                @if(($dashboardSkillMatchLayer ?? null) === 'ai')
                                    <p class="dash-source-hint mb-0">Match uses AI on your resume text (synonyms included).</p>
                                @endif
                            @else
                                <span class="dash-badge">Upgrade path</span>
                                <h3 class="dash-card-title">Pick a goal to upgrade toward</h3>
                                <p class="dash-card-sub mb-2">Choose a job goal and we’ll show how your resume lines up — plus masters and upskill options where they fit.</p>

                                @if($dashboardRecommendMasters)
                                    <a href="{{ route('pricing') }}" class="dash-action-tile dash-action-tile--masters mb-2">
                                        <span class="dash-action-tile__icon" aria-hidden="true"><i class="mdi mdi-school-outline"></i></span>
                                        <span class="dash-action-tile__text">
                                            <strong>Recommended: M.E. / M.Tech in {{ $dashboardMastersField }}</strong>
                                            <span class="dash-action-tile__sub">With B.E. / B.Tech, a masters in your field is a clear upgrade — explore programs.</span>
                                        </span>
                                        <i class="mdi mdi-chevron-right dash-action-tile__chev" aria-hidden="true"></i>
                                    </a>
                                @endif

                                <div class="dash-action-stack">
                                    <a href="{{ route('job-list') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-bullseye-arrow me-1"></i> Choose a job goal</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('resume.upload') }}" class="dash-action-row">
                                        <span><i class="mdi mdi-refresh me-1"></i> Refresh resume</span>
                                        <i class="mdi mdi-chevron-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="dash-card dash-card--referral">
                        <div class="dash-card-inner">
                            <span class="dash-badge">Refer & earn</span>
                            <h3 class="dash-card-title">💸 Earn up to ₹5,000 per successful referral</h3>
                            <p class="dash-card-sub mb-2">
                                <strong class="text-white">Refer talent in your company & start earning.</strong>
                                Know open roles? Refer candidates and earn rewards. Tell us your company and how many people you can refer  we’ll connect you with matching candidates.
                            </p>
                            <button
                                type="button"
                                class="dash-btn dash-btn-referral"
                                data-bs-toggle="modal"
                                data-bs-target="#referralSignupModal">
                                🚀 Start referring & earn
                            </button>
                            <p class="dash-referral-foot mb-0">No cost · Flexible · Quick payouts</p>
                        </div>
                    </div>
                </div>

                {{-- ── All applications (employer + job goals), newest first ── --}}
                <div class="apps-section" id="applications">
                    <div class="section-header">
                        <div class="section-title-group">
                            <div class="section-dot"></div>
                            <h2 class="section-title">Applications</h2>
                            @if($allApplications->total() > 0)
                                <span class="section-count">{{ $allApplications->total() }}</span>
                            @endif
                        </div>
                        <p class="section-desc d-none d-sm-block">Job openings and job goals — newest first</p>
                    </div>

                    @if($allApplications->total() === 0)
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <p class="empty-title">No applications yet</p>
                            <p class="empty-sub">Browse live openings or explore skill-based job goals.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('job-openings') }}" class="btn-outline-accent">Browse job openings</a>
                                <a href="{{ route('job-list') }}" class="btn-outline-accent">Explore job goals</a>
                            </div>
                        </div>
                    @else
                        <div class="apps-grid">
                            @foreach($allApplications as $row)
                                @if($row->kind === 'employer')
                                    @php
                                        $app = $row->application;
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
                                            <p class="app-job-title mb-1"><a href="{{ route('job-openings') }}">{{ $job->title }}</a></p>
                                            <span class="meta-tag mb-2 d-inline-flex" style="font-size:0.65rem;">Live opening</span>
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
                                @else
                                    @php
                                        $app = $row->application;
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
                                                <span class="company-name">Job goal</span>
                                            </div>
                                            <p class="app-job-title mb-1">
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
                                @endif
                            @endforeach
                        </div>
                        <div class="apps-pagination-wrap">
                            @if($allApplications->total() > 0)
                                <p class="apps-pagination-meta mb-0">
                                    Showing {{ $allApplications->firstItem() }}–{{ $allApplications->lastItem() }} of {{ $allApplications->total() }}
                                </p>
                            @endif
                            @if($allApplications->hasPages())
                                <div class="apps-pagination">
                                    {{ $allApplications->onEachSide(1)->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ── Legend ── --}}
                <div class="legend-bar">
                    <span class="legend-title">Status key</span>
                    <div class="legend-item"><span class="legend-dot" style="background:#94a3b8"></span> Applied</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#10b981"></span> Shortlisted</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#3b82f6"></span> Interviewed</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#0B1F3B"></span> Offered</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#047857"></span> Hired</div>
                    <div class="legend-item"><span class="legend-dot" style="background:#ef4444"></span> Rejected</div>
                </div>

            </div>
        </div>
    </div>

@endsection