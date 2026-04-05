@extends('layouts.app')

@section('title', 'Job Openings')

@push('styles')
<style>
    .job-openings-page {
        --jo-ink: #0c1222;
        --jo-muted: #64748b;
        --jo-line: rgba(15, 23, 42, 0.08);
        --jo-glow: rgba(16, 185, 129, 0.45);
        background: #f4f7fb;
        min-height: 100vh;
    }
    .jo-hero-band {
        position: relative;
        overflow: hidden;
        border-radius: 0 0 28px 28px;
        background:
            radial-gradient(ellipse 80% 120% at 100% -20%, rgba(56, 189, 248, 0.35) 0%, transparent 55%),
            radial-gradient(ellipse 60% 100% at -10% 50%, rgba(16, 185, 129, 0.28) 0%, transparent 50%),
            linear-gradient(135deg, #0b1f3b 0%, #0f2847 38%, #134e4a 100%);
        color: #fff;
        padding: 2.25rem 0 3.5rem;
        margin-bottom: -2.25rem;
    }
    @media (min-width: 992px) {
        .jo-hero-band { padding: 2.75rem 0 4rem; margin-bottom: -2.75rem; }
    }
    .jo-hero-band::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.9;
        pointer-events: none;
    }
    .jo-hero-inner { position: relative; z-index: 1; }
    .jo-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 0.65rem;
    }
    .jo-eyebrow span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 12px var(--jo-glow);
        animation: joPulse 2s ease-in-out infinite;
    }
    @keyframes joPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.7; transform: scale(0.92); }
    }
    @media (prefers-reduced-motion: reduce) {
        .jo-eyebrow span { animation: none; }
    }
    .jo-hero-title {
        font-size: clamp(1.65rem, 4vw, 2.35rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.02em;
        margin-bottom: 0.5rem;
    }
    .jo-hero-lead {
        color: rgba(255, 255, 255, 0.78);
        font-size: 0.95rem;
        max-width: 34rem;
        margin-bottom: 0;
        line-height: 1.55;
    }
    .jo-hero-stat {
        display: inline-flex;
        align-items: baseline;
        gap: 0.35rem;
        margin-top: 1.1rem;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.14);
        font-size: 0.8125rem;
    }
    .jo-hero-stat strong { font-size: 1.05rem; font-weight: 800; }
    .jo-float-search {
        position: relative;
        z-index: 2;
        margin-top: 0;
    }
    .jo-search-glass {
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.65);
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow:
            0 4px 6px -1px rgba(15, 23, 42, 0.06),
            0 24px 48px -12px rgba(11, 31, 59, 0.18);
    }
    .jo-search-glass .form-control {
        border: 1px solid rgba(11, 31, 59, 0.1);
        border-radius: 14px;
        padding: 0.7rem 1rem 0.7rem 2.65rem;
        font-size: 0.9375rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .jo-search-glass .form-control:focus {
        border-color: rgba(16, 185, 129, 0.55);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .jo-search-glass .form-label {
        color: var(--jo-ink);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .jo-search-btn {
        padding: 0.72rem 1.2rem;
        font-weight: 700;
        border-radius: 14px !important;
        border: none;
        background: linear-gradient(135deg, #0b1f3b 0%, #0f766e 100%);
        transition: transform 0.18s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .jo-search-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(11, 31, 59, 0.28);
        filter: brightness(1.05);
    }
    .jo-layout-row { padding-top: 0.5rem; }
    .jo-filters-card {
        border-radius: 20px;
        border: 1px solid var(--jo-line);
        background: #fff;
        box-shadow: 0 8px 30px rgba(11, 31, 59, 0.06);
    }
    .jo-filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        border: 1px solid transparent;
    }
    .jo-filter-chip:not(.jo-filter-chip--active) {
        background: #f1f5f9;
        color: #475569;
    }
    .jo-filter-chip:not(.jo-filter-chip--active):hover {
        background: rgba(16, 185, 129, 0.12);
        color: var(--hirevo-primary, #0b1f3b);
        transform: translateY(-1px);
    }
    .jo-filter-chip--active {
        background: linear-gradient(135deg, #0b1f3b, #0d9488) !important;
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(11, 31, 59, 0.22);
    }
    .jo-results-bar {
        border-radius: 16px;
        padding: 0.85rem 1.15rem;
        background: #fff;
        border: 1px solid var(--jo-line);
        box-shadow: 0 2px 12px rgba(11, 31, 59, 0.04);
    }
    .jo-job-list { min-height: 120px; }
    .jo-job-card {
        border-radius: 20px;
        border: 1px solid var(--jo-line);
        background: #fff;
        box-shadow: 0 2px 12px rgba(11, 31, 59, 0.04);
        transition: border-color 0.25s ease, box-shadow 0.28s ease, transform 0.25s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .jo-job-card:hover {
        border-color: rgba(16, 185, 129, 0.35);
        box-shadow: 0 16px 40px rgba(11, 31, 59, 0.1);
        transform: translateY(-3px);
    }
    .jo-job-card-inner { --jo-avatar-size: 54px; }
    .jo-co-avatar {
        width: var(--jo-avatar-size);
        height: var(--jo-avatar-size);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.2rem;
        color: #0b1f3b;
        background: linear-gradient(145deg, #e0f2fe 0%, #d1fae5 100%);
        flex-shrink: 0;
        line-height: 1;
        border: 1px solid rgba(11, 31, 59, 0.06);
    }
    @media (min-width: 768px) {
        .jo-job-card .jo-job-card-inner { align-items: flex-start; }
        .jo-job-card .jo-co-avatar { margin-top: 0.125rem; }
    }
    .jo-job-card-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        border-top: 1px solid var(--jo-line);
    }
    .jo-job-card-footer .jo-apply-btn { min-width: 9.5rem; }
    .jo-job-title {
        color: var(--jo-ink);
        text-decoration: none;
        font-weight: 800;
        font-size: 1.06rem;
        line-height: 1.3;
        letter-spacing: -0.02em;
        transition: color 0.2s ease;
    }
    .jo-job-title:hover { color: #0d9488; }
    .jo-job-desc-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-top: 0.65rem;
    }
    .jo-job-posted { font-size: 0.72rem; }
    .jo-meta-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.3rem 0.68rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
    }
    .jo-meta-pill--accent {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(56, 189, 248, 0.12));
        color: #047857;
    }
    .jo-apply-btn {
        font-weight: 700;
        padding: 0.48rem 1.2rem;
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .jo-apply-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(11, 31, 59, 0.2);
    }
    .jo-load-more-btn {
        font-weight: 700;
        padding: 0.75rem 2rem;
        border-radius: 999px;
        border: 2px solid rgba(11, 31, 59, 0.12);
        background: #fff;
        color: var(--jo-ink);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.18s ease;
    }
    .jo-load-more-btn:hover:not(:disabled) {
        border-color: rgba(16, 185, 129, 0.45);
        box-shadow: 0 8px 28px rgba(11, 31, 59, 0.1);
        transform: translateY(-2px);
    }
    .jo-load-more-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    .jo-load-spinner {
        width: 1.15rem;
        height: 1.15rem;
        border: 2px solid rgba(11, 31, 59, 0.12);
        border-top-color: #0d9488;
        border-radius: 50%;
        animation: joSpin 0.65s linear infinite;
        display: none;
        vertical-align: middle;
        margin-right: 0.35rem;
    }
    .jo-load-more-btn.is-loading .jo-load-spinner { display: inline-block; }
    @keyframes joSpin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) {
        .jo-load-spinner { animation: none; border-top-color: #0d9488; }
    }
    @keyframes joFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .jo-animate-in { animation: joFadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .jo-card-enter .jo-job-card { animation: joFadeUp 0.45s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    @media (prefers-reduced-motion: reduce) {
        .jo-job-card, .jo-job-card:hover, .jo-filter-chip, .jo-search-btn, .jo-apply-btn, .jo-load-more-btn,
        .jo-animate-in, .jo-card-enter .jo-job-card {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
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
    <section class="section py-0 job-openings-page">
        <div class="jo-hero-band">
            <div class="container jo-hero-inner">
                <div class="row align-items-center gy-4">
                    <div class="col-lg-7">
                        <p class="jo-eyebrow mb-0"><span></span> Live on Hirevo</p>
                        <h1 class="jo-hero-title">Find work that actually fits</h1>
                        <p class="jo-hero-lead">Real roles from real companies — search fast, filter by how you want to work, apply without the clutter.</p>
                        @if($jobs->total() > 0)
                            <p class="jo-hero-stat mb-0">
                                <strong id="jo-hero-count">{{ $jobs->total() }}</strong>
                                <span>{{ Str::plural('opening', $jobs->total()) }} right now</span>
                            </p>
                        @endif
                    </div>
                    <div class="col-lg-5 text-center text-lg-end d-none d-md-block">
                        <img src="{{ $siteImg('Image 2.PNG') }}" alt="" class="img-fluid" style="max-height: 140px; opacity: 0.95;" loading="lazy" width="400" height="160">
                    </div>
                </div>
            </div>
        </div>

        <div class="container jo-float-search pb-4 pb-lg-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mt-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3 border-0 shadow-sm mt-3" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('job-openings') }}" method="GET" class="jo-search-glass p-3 p-md-4 mt-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="job-openings-q" class="form-label mb-1">Keywords</label>
                        <div class="position-relative">
                            <i class="uil uil-search position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                            <input type="search" name="q" id="job-openings-q" class="form-control" placeholder="Role, stack, or company…" value="{{ old('q', $searchQuery ?? '') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="job-openings-location" class="form-label mb-1">Location</label>
                        <div class="position-relative">
                            <i class="uil uil-map-marker position-absolute text-muted" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                            <input type="text" name="location" id="job-openings-location" class="form-control" placeholder="City, remote, region…" value="{{ old('location', $searchLocation ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 jo-search-btn"><i class="uil uil-search me-1"></i> Search</button>
                    </div>
                </div>
                <input type="hidden" name="job_type" value="{{ $filterJobType ?? '' }}">
                <input type="hidden" name="work_location_type" value="{{ $filterWorkType ?? '' }}">
            </form>

            <div class="row jo-layout-row">
                <div class="col-lg-3 mb-4 mb-lg-0 order-2 order-lg-1">
                    <div class="card jo-filters-card border-0 sticky-top" style="top: 92px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <h2 class="h6 fw-bold mb-0" style="color: var(--jo-ink);">Filters</h2>
                                @if($hasActiveFilters)
                                    <a href="{{ route('job-openings') }}" class="small text-decoration-none fw-700" style="color: #0d9488;">Reset</a>
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
                                    <label class="form-label small fw-bold text-muted mb-2 d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.06em;">Job type</label>
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
                                    <label class="form-label small fw-bold text-muted mb-2 d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.06em;">Workplace</label>
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
                        <p class="mb-0 small" style="color: var(--jo-muted);">
                            @if($jobs->total() > 0)
                                <span id="jo-showing-line">
                                    Showing <strong class="text-dark" id="jo-range-from">{{ $jobs->firstItem() }}</strong>–<strong class="text-dark" id="jo-range-to">{{ $jobs->lastItem() }}</strong>
                                    of <strong class="text-dark" id="jo-range-total">{{ $jobs->total() }}</strong>
                                </span>
                                @if($hasActiveFilters)
                                    <span class="d-none d-sm-inline"> · filtered</span>
                                @endif
                            @else
                                <span class="fw-600 text-dark">No listings</span>
                            @endif
                        </p>
                        <span class="small d-none d-sm-inline" style="color: var(--jo-muted);"><i class="uil uil-sort-amount-down me-1"></i>Newest first</span>
                    </div>

                    <div class="jo-job-list" id="jo-job-list">
                        @forelse($jobs as $job)
                            <div class="jo-animate-in" style="animation-delay: {{ min(0.03 * $loop->iteration, 0.24) }}s;">
                                @include('hirevo.partials.employer-job-card', ['job' => $job, 'appliedIds' => $appliedIds ?? []])
                            </div>
                        @empty
                            <div class="card border-0 jo-filters-card text-center py-5 px-3">
                                <div class="card-body py-5">
                                    <div class="rounded-4 d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px; background: linear-gradient(145deg, #e0f2fe, #d1fae5);">
                                        <i class="uil uil-briefcase-alt" style="font-size: 2rem; color: #0b1f3b;"></i>
                                    </div>
                                    <h2 class="h5 fw-bold mb-2" style="color: var(--jo-ink);">Nothing here yet</h2>
                                    @if($hasActiveFilters)
                                        <p class="text-muted mb-4 mx-auto" style="max-width: 420px;">Loosen your filters or reset to see every role we have.</p>
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            <a href="{{ route('job-openings') }}" class="btn btn-primary rounded-pill px-4 jo-apply-btn">View all</a>
                                            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4">Home</a>
                                        </div>
                                    @else
                                        <p class="text-muted mb-4">New roles drop regularly — check back soon.</p>
                                        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 jo-apply-btn">Back home</a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($jobs->hasPages())
                        <div class="text-center mt-4 pt-1" id="jo-load-wrap">
                            <button type="button" class="jo-load-more-btn" id="jo-load-more" data-next-url="{{ $jobs->nextPageUrl() }}">
                                <span class="jo-load-spinner" aria-hidden="true"></span>
                                <span class="jo-load-label">Load more openings</span>
                            </button>
                            <p class="small text-muted mt-2 mb-0 d-none" id="jo-end-msg">You’re all caught up.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('jo-load-more');
    var list = document.getElementById('jo-job-list');
    if (!btn || !list || !btn.getAttribute('data-next-url')) return;

    var fromEl = document.getElementById('jo-range-from');
    var toEl = document.getElementById('jo-range-to');
    var totalEl = document.getElementById('jo-range-total');
    var endMsg = document.getElementById('jo-end-msg');
    var wrap = document.getElementById('jo-load-wrap');

    function setLoading(loading) {
        btn.disabled = loading;
        btn.classList.toggle('is-loading', loading);
        var label = btn.querySelector('.jo-load-label');
        if (label) label.textContent = loading ? 'Loading…' : 'Load more openings';
    }

    btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-next-url');
        if (!url) return;
        setLoading(true);
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) throw new Error('Network error');
                return r.json();
            })
            .then(function (data) {
                if (!data.html) return;
                var temp = document.createElement('div');
                temp.innerHTML = data.html;
                temp.querySelectorAll('.jo-job-card-wrap').forEach(function (node) {
                    var enter = document.createElement('div');
                    enter.className = 'jo-card-enter';
                    enter.appendChild(node);
                    list.appendChild(enter);
                });
                var shown = list.querySelectorAll('.jo-job-card-wrap').length;
                var tot = data.total != null ? data.total : shown;
                if (fromEl) fromEl.textContent = shown > 0 ? '1' : '0';
                if (toEl) toEl.textContent = String(Math.min(shown, tot));
                if (totalEl && data.total != null) totalEl.textContent = String(data.total);
                var heroCount = document.getElementById('jo-hero-count');
                if (heroCount && data.total != null) heroCount.textContent = String(data.total);

                if (data.has_more && data.next_page_url) {
                    btn.setAttribute('data-next-url', data.next_page_url);
                } else {
                    btn.setAttribute('data-next-url', '');
                    btn.classList.add('d-none');
                    if (endMsg) endMsg.classList.remove('d-none');
                    if (wrap && !data.has_more) {
                        /* keep wrap for end message */
                    }
                }
            })
            .catch(function () {
                window.location.href = url;
            })
            .finally(function () {
                setLoading(false);
            });
    });
})();
</script>
@endpush
