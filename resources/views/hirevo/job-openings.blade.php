@extends('layouts.app')

@section('title', 'Job Openings')

@push('styles')
<style>
    .job-openings-page {
        background: linear-gradient(180deg, #f0f7ff 0%, #f8fafc 18%, #fff 45%);
    }
    .jo-hero {
        position: relative;
        overflow: hidden;
    }
    .jo-hero::after {
        content: '';
        position: absolute;
        top: -40%;
        right: -8%;
        width: min(420px, 55vw);
        height: min(420px, 55vw);
        border-radius: 50%;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .jo-search-shell {
        border-radius: 20px;
        border: 1px solid rgba(11, 31, 59, 0.08);
        background: #fff;
        box-shadow: 0 12px 40px rgba(11, 31, 59, 0.07);
    }
    .jo-search-shell .form-control {
        border: 1px solid rgba(11, 31, 59, 0.1);
        padding: 0.65rem 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .jo-search-shell .form-control:focus {
        border-color: rgba(11, 31, 59, 0.35);
        box-shadow: 0 0 0 3px rgba(11, 31, 59, 0.08);
    }
    .jo-search-btn {
        padding: 0.65rem 1.25rem;
        font-weight: 600;
        border-radius: 14px !important;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .jo-search-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(11, 31, 59, 0.2);
    }
    .jo-filters-card {
        border-radius: 20px;
        border: 1px solid rgba(11, 31, 59, 0.06);
        box-shadow: 0 8px 28px rgba(11, 31, 59, 0.06);
    }
    .jo-filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        border: 1px solid transparent;
    }
    .jo-filter-chip:not(.jo-filter-chip--active) {
        background: rgba(11, 31, 59, 0.06);
        color: #334155;
    }
    .jo-filter-chip:not(.jo-filter-chip--active):hover {
        background: rgba(16, 185, 129, 0.12);
        color: var(--hirevo-primary);
        transform: translateY(-1px);
    }
    .jo-filter-chip--active {
        background: var(--hirevo-primary) !important;
        color: #fff !important;
        box-shadow: 0 4px 14px rgba(11, 31, 59, 0.25);
    }
    .jo-results-bar {
        border-radius: 14px;
        padding: 0.75rem 1.1rem;
        background: rgba(11, 31, 59, 0.03);
        border: 1px solid rgba(11, 31, 59, 0.06);
    }
    .jo-job-card {
        border-radius: 18px;
        border: 1px solid rgba(11, 31, 59, 0.07);
        background: #fff;
        box-shadow: 0 4px 18px rgba(11, 31, 59, 0.04);
        transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.22s ease;
    }
    .jo-job-card:hover {
        border-color: rgba(16, 185, 129, 0.35);
        box-shadow: 0 14px 36px rgba(11, 31, 59, 0.09);
        transform: translateY(-2px);
    }
    .jo-job-card .jo-job-card-inner {
        --jo-avatar-size: 52px;
    }
    .jo-co-avatar {
        width: var(--jo-avatar-size);
        height: var(--jo-avatar-size);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.15rem;
        color: var(--hirevo-primary);
        background: linear-gradient(145deg, rgba(11, 31, 59, 0.08), rgba(16, 185, 129, 0.12));
        flex-shrink: 0;
        line-height: 1;
    }
    @media (min-width: 768px) {
        .jo-job-card .jo-job-card-inner {
            align-items: flex-start;
        }
        .jo-job-card .jo-co-avatar {
            margin-top: 0.125rem;
        }
    }
    .jo-job-card-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        border-top: 1px solid rgba(11, 31, 59, 0.08);
    }
    .jo-job-card-footer .jo-apply-btn {
        min-width: 9.5rem;
    }
    .jo-job-title {
        color: #0f172a;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.05rem;
        line-height: 1.35;
        transition: color 0.2s ease;
    }
    .jo-job-title:hover {
        color: var(--hirevo-secondary);
    }
    .jo-job-desc-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-top: 0.75rem;
    }
    .jo-job-posted {
        font-size: 0.72rem;
    }
    .jo-meta-pill {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: rgba(11, 31, 59, 0.06);
        color: #475569;
    }
    .jo-meta-pill--accent {
        background: rgba(16, 185, 129, 0.14);
        color: #047857;
    }
    .jo-apply-btn {
        font-weight: 600;
        padding: 0.45rem 1.15rem;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .jo-apply-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(11, 31, 59, 0.18);
    }
    @media (prefers-reduced-motion: reduce) {
        .jo-job-card,
        .jo-job-card:hover,
        .jo-filter-chip,
        .jo-search-btn,
        .jo-apply-btn {
            transition: none;
            transform: none;
        }
    }
    @keyframes joFadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .jo-animate-in {
        animation: joFadeUp 0.45s ease backwards;
    }
    @media (prefers-reduced-motion: reduce) {
        .jo-animate-in { animation: none; }
    }
    .jo-animate-in:nth-child(1) { animation-delay: 0.02s; }
    .jo-animate-in:nth-child(2) { animation-delay: 0.05s; }
    .jo-animate-in:nth-child(3) { animation-delay: 0.08s; }
    .jo-animate-in:nth-child(4) { animation-delay: 0.11s; }
    .jo-animate-in:nth-child(5) { animation-delay: 0.14s; }
    .jo-animate-in:nth-child(6) { animation-delay: 0.17s; }
    .jo-animate-in:nth-child(7) { animation-delay: 0.2s; }
    .jo-animate-in:nth-child(8) { animation-delay: 0.23s; }
    .job-openings-page .pagination .page-link {
        border-radius: 10px;
        margin: 0 0.2rem;
        border: 1px solid rgba(11, 31, 59, 0.1);
        color: var(--hirevo-primary);
    }
    .job-openings-page .pagination .page-item.active .page-link {
        background: var(--hirevo-primary);
        border-color: var(--hirevo-primary);
    }
</style>
@endpush

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
        $queryAll = array_filter(request()->query(), fn ($v) => $v !== '' && $v !== null);
        $hasActiveFilters = (isset($searchQuery) && $searchQuery !== '')
            || (isset($searchLocation) && $searchLocation !== '')
            || (isset($filterJobType) && $filterJobType !== '')
            || (isset($filterWorkType) && $filterWorkType !== '');
    @endphp
    <section class="section py-4 py-lg-5 job-openings-page">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb"></nav>

            <div class="row align-items-center jo-hero mb-4 pb-lg-1">
                <div class="col-lg-7 mb-4 mb-lg-0 position-relative" style="z-index: 1;">
                    <p class="text-uppercase small fw-bold text-primary mb-2" style="letter-spacing: 0.1em;">Live openings</p>
                    <h1 class="display-6 fw-bold text-dark mb-2">Find a role that fits you</h1>
                    <p class="text-muted mb-0 lead fs-6">Search by title or company, narrow by location and work style — then apply in a few clicks.</p>
                </div>
                <div class="col-lg-5 text-center text-lg-end d-none d-md-block position-relative" style="z-index: 1;">
                    <img src="{{ $siteImg('Image 2.PNG') }}" alt="" class="img-fluid hirevo-site-illustration jo-hero-art" style="max-height: 160px;" loading="lazy" width="400" height="160">
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('job-openings') }}" method="GET" class="mb-4">
                <div class="jo-search-shell p-3 p-md-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="job-openings-q" class="form-label small fw-600 text-dark mb-1">What are you looking for?</label>
                            <div class="position-relative">
                                <i class="uil uil-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                                <input type="search" name="q" id="job-openings-q" class="form-control rounded-3 ps-5" placeholder="Role, keyword, or company…" value="{{ old('q', $searchQuery ?? '') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="job-openings-location" class="form-label small fw-600 text-dark mb-1">Location</label>
                            <div class="position-relative">
                                <i class="uil uil-map-marker position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                                <input type="text" name="location" id="job-openings-location" class="form-control rounded-3 ps-5" placeholder="City, remote, region…" value="{{ old('location', $searchLocation ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 jo-search-btn rounded-3"><i class="uil uil-search me-1"></i> Search roles</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="job_type" value="{{ $filterJobType ?? '' }}">
                <input type="hidden" name="work_location_type" value="{{ $filterWorkType ?? '' }}">
            </form>

            <div class="row">
                {{-- Mobile: jobs first; desktop: filters left --}}
                <div class="col-lg-3 mb-4 mb-lg-0 order-2 order-lg-1">
                    <div class="card jo-filters-card border-0 sticky-top" style="top: 92px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <h2 class="h6 fw-bold mb-0 text-dark">Refine</h2>
                                @if($hasActiveFilters)
                                    <a href="{{ route('job-openings') }}" class="small text-primary text-decoration-none fw-600">Clear all</a>
                                @endif
                            </div>

                            <form action="{{ route('job-openings') }}" method="GET" id="filters-form">
                                @if($searchQuery ?? '')
                                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                                @endif
                                @if($searchLocation ?? '')
                                    <input type="hidden" name="location" value="{{ $searchLocation }}">
                                @endif

                                <div class="mb-4">
                                    <label class="form-label small fw-600 text-muted mb-2 d-block">Job type</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('job-openings', array_diff_key($queryAll, ['job_type' => 1])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === '' ? 'jo-filter-chip--active' : '' }}">All</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'internship'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'internship' ? 'jo-filter-chip--active' : '' }}">Internship</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'full_time'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'full_time' ? 'jo-filter-chip--active' : '' }}">Full-time</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'part_time'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'part_time' ? 'jo-filter-chip--active' : '' }}">Part-time</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'contract'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'contract' ? 'jo-filter-chip--active' : '' }}">Contract</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'temporary'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'temporary' ? 'jo-filter-chip--active' : '' }}">Temporary</a>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small fw-600 text-muted mb-2 d-block">Where you work</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('job-openings', array_diff_key($queryAll, ['work_location_type' => 1])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === '' ? 'jo-filter-chip--active' : '' }}">All</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'remote'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'remote' ? 'jo-filter-chip--active' : '' }}">Remote</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'office'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'office' ? 'jo-filter-chip--active' : '' }}">On-site</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'hybrid'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'hybrid' ? 'jo-filter-chip--active' : '' }}">Hybrid</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 order-1 order-lg-2">
                    <div class="jo-results-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <p class="mb-0 small">
                            @if($hasActiveFilters)
                                <span class="fw-700 text-dark">{{ $jobs->total() }}</span>
                                <span class="text-muted">{{ Str::plural('opening', $jobs->total()) }} match</span>
                                @if(isset($searchQuery) && $searchQuery !== '')
                                    <span class="text-muted"> · “{{ Str::limit(e($searchQuery), 42) }}”</span>
                                @endif
                            @else
                                <span class="fw-700 text-dark">{{ $jobs->total() }}</span>
                                <span class="text-muted">{{ Str::plural('role', $jobs->total()) }} available now</span>
                            @endif
                        </p>
                        <span class="small text-muted d-none d-sm-inline"><i class="uil uil-sort-amount-down me-1"></i>Newest first</span>
                    </div>

                    <div class="jo-job-list">
                        @forelse($jobs as $job)
                            @php
                                $companyName = $job->user->referrerProfile?->company_name ?? $job->company_name ?? 'Company';
                                $initialRaw = trim($companyName);
                                $initial = $initialRaw !== '' ? strtoupper(mb_substr($initialRaw, 0, 1)) : '?';
                                $jobTypeLabels = [
                                    'full_time' => 'Full-time',
                                    'part_time' => 'Part-time',
                                    'contract' => 'Contract',
                                    'internship' => 'Internship',
                                    'temporary' => 'Temporary',
                                    'volunteer' => 'Volunteer',
                                    'other' => 'Other',
                                ];
                                $jobTypeLabel = $job->job_type ? ($jobTypeLabels[$job->job_type] ?? $job->job_type) : null;
                                $workTypeLabels = ['office' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid'];
                                $workTypeLabel = $job->work_location_type ? ($workTypeLabels[$job->work_location_type] ?? $job->work_location_type) : null;
                            @endphp
                            <div class="card border-0 jo-job-card mb-3 jo-animate-in">
                                <div class="card-body p-3 p-md-4">
                                    <div class="row jo-job-card-inner g-3 align-items-start">
                                        <div class="col-auto">
                                            <div class="jo-co-avatar" aria-hidden="true">{{ $initial }}</div>
                                        </div>
                                        <div class="col min-w-0">
                                            <div class="row g-2 align-items-start">
                                                <div class="col-12 min-w-0">
                                                    <h2 class="h5 mb-1">
                                                        <a href="{{ route('job-openings.apply', $job) }}" class="jo-job-title d-block">{{ $job->title }}</a>
                                                    </h2>
                                                    <p class="text-muted small mb-0 fw-500">{{ $companyName }}</p>
                                                </div>
                                                <div class="col-12 mt-1 mt-md-2">
                                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                                        @if($job->formatted_location)
                                                            <span class="jo-meta-pill"><i class="uil uil-map-marker text-muted me-1"></i>{{ $job->formatted_location }}</span>
                                                        @endif
                                                        @if($jobTypeLabel)
                                                            <span class="jo-meta-pill">{{ $jobTypeLabel }}</span>
                                                        @endif
                                                        @if($workTypeLabel)
                                                            <span class="jo-meta-pill jo-meta-pill--accent">{{ $workTypeLabel }}</span>
                                                        @endif
                                                        @if($job->formatted_salary_summary)
                                                            <span class="jo-meta-pill"><i class="uil uil-money-stack text-muted me-1"></i>{{ $job->formatted_salary_summary }}</span>
                                                        @endif
                                                        @if($job->experience_years !== null && $job->experience_years !== '')
                                                            <span class="jo-meta-pill">{{ (int) $job->experience_years === 0 ? 'Fresher friendly' : ((int) $job->experience_years . '+ yrs exp.') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <p class="text-muted mb-0 small lh-base jo-job-desc-clamp">{{ Str::limit(strip_tags($job->description), 180) ?: '—' }}</p>
                                                    @if($job->created_at)
                                                        <p class="text-muted mb-0 mt-2 jo-job-posted"><i class="uil uil-clock me-1"></i>Posted {{ $job->created_at->diffForHumans() }}</p>
                                                    @endif
                                                </div>
                                                <div class="col-12 jo-job-card-footer pt-3 mt-1">
                                                    @if(in_array($job->id, $appliedIds ?? []))
                                                        <span class="badge bg-success rounded-pill px-3 py-2">Applied</span>
                                                    @else
                                                        <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm rounded-pill jo-apply-btn">
                                                            {{ $job->apply_link ? 'Apply on company site' : 'Apply now' }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card border-0 jo-filters-card text-center py-5 px-3">
                                <div class="card-body py-5">
                                    <div class="rounded-4 bg-light d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 76px; height: 76px;">
                                        <i class="uil uil-briefcase-alt text-primary" style="font-size: 2rem;"></i>
                                    </div>
                                    <h2 class="h5 fw-bold text-dark mb-2">No roles here yet</h2>
                                    @if($hasActiveFilters)
                                        <p class="text-muted mb-4 mx-auto" style="max-width: 420px;">Nothing matches those filters. Try broader keywords or reset filters to see every opening.</p>
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            <a href="{{ route('job-openings') }}" class="btn btn-primary rounded-pill px-4">View all jobs</a>
                                            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">Home</a>
                                        </div>
                                    @else
                                        <p class="text-muted mb-4">New roles are added often — check back soon or explore goals from the home page.</p>
                                        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4">Back to home</a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($jobs->hasPages())
                        <div class="d-flex justify-content-center mt-4 pt-2">
                            {{ $jobs->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
