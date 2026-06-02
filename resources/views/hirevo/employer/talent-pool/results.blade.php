@extends('layouts.employer')

@section('title', 'Talent Pool — Results')

@section('header_title', 'Search Results')

@section('header_actions')
    <a href="{{ route('employer.talent-pool.index', request()->only(['q', 'skills', 'location', 'education', 'experience_min', 'experience_max'])) }}" class="btn btn-outline-secondary btn-sm">
        <i class="mdi mdi-pencil-outline me-1"></i> Modify search
    </a>
@endsection

@push('styles')
<style>
    .tp-page { --tp-green: #22a06b; --tp-green-soft: #e8f6ef; --tp-border: #e8ecf1; }
    .tp-results-summary {
        background: var(--surface);
        border: 1px solid var(--tp-border);
        border-radius: var(--radius);
        padding: .75rem 1rem;
        margin-bottom: 1rem;
        display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
    }
    .tp-results-summary .tp-query { font-weight: 600; color: var(--ink-900); }
    .tp-results-count {
        font-size: .875rem; font-weight: 600; color: var(--tp-green);
        margin-left: auto; white-space: nowrap;
    }
    .tp-chip {
        display: inline-flex; align-items: center;
        font-size: .75rem; padding: .2rem .55rem;
        border-radius: var(--radius-pill);
        background: var(--ink-50); color: var(--ink-500); border: 1px solid var(--tp-border);
    }
    .tp-layout { display: grid; grid-template-columns: 280px 1fr; gap: 1rem; align-items: start; }
    @media (max-width: 991.98px) { .tp-layout { grid-template-columns: 1fr; } .tp-filters-wrap { order: 2; } }
    .tp-filters-wrap { position: sticky; top: calc(var(--topbar-h) + .75rem); }
    .tp-filters-card {
        background: var(--surface); border: 1px solid var(--tp-border);
        border-radius: var(--radius); padding: 1rem; box-shadow: var(--shadow-xs);
    }
    .tp-filters-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem; padding-bottom: .75rem; border-bottom: 1px solid var(--tp-border);
    }
    .tp-clear-filters { color: var(--accent); text-decoration: none; }
    .tp-filter-block { margin-bottom: .75rem; padding-bottom: .75rem; border-bottom: 1px solid var(--border-soft); }
    .tp-filter-block:last-of-type { border-bottom: none; }
    .tp-filter-toggle {
        width: 100%; display: flex; align-items: center; justify-content: space-between;
        background: none; border: none; padding: 0 0 .5rem; font-weight: 600; font-size: .875rem;
    }
    .tp-facet-list { display: flex; flex-direction: column; gap: .25rem; }
    .tp-facet-option, .tp-loc-option {
        display: flex; align-items: flex-start; gap: .5rem; font-size: .8125rem;
        cursor: pointer; margin: 0; padding: .35rem .25rem; border-radius: var(--radius-sm);
    }
    .tp-facet-option:hover, .tp-loc-option:hover { background: var(--ink-50); }
    .tp-facet-count { color: var(--ink-300); margin-left: auto; white-space: nowrap; font-size: .75rem; }
    .tp-loc-dropdown { position: relative; }
    .tp-loc-trigger { cursor: pointer; background: var(--surface); }
    .tp-loc-panel {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 30;
        background: var(--surface); border: 1px solid var(--tp-border);
        border-radius: var(--radius-sm); box-shadow: var(--shadow-lg);
        max-height: 280px; display: flex; flex-direction: column;
    }
    .tp-loc-panel[hidden] { display: none !important; }
    .tp-loc-search { margin: .5rem; width: calc(100% - 1rem); }
    .tp-loc-list { overflow-y: auto; padding: 0 .25rem .5rem; flex: 1; }
    .tp-loc-label { flex: 1; line-height: 1.3; }
    .tp-apply-btn { font-weight: 600; border-radius: var(--radius-sm); }
    .tp-results-area { min-height: 200px; }
    .tp-results-list { display: flex; flex-direction: column; gap: .75rem; }
    .tp-candidate-row { border: 1px solid var(--tp-border) !important; transition: box-shadow .2s ease; overflow: hidden; }
    .tp-candidate-row:hover { box-shadow: var(--shadow-sm); }
    .tp-avatar { object-fit: cover; flex-shrink: 0; }
    .tp-avatar-fallback {
        width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
        background: var(--accent-soft); color: var(--brand); font-weight: 700; font-size: .8rem;
    }
    .tp-name-link { color: var(--ink-900) !important; font-size: 1rem; }
    .tp-badge-verified { background: var(--green-soft); color: var(--green); }
    .tp-badge-talent-pool { background: var(--accent-soft); color: var(--accent); }
    .tp-matching {
        background: var(--tp-green-soft); border-top: 1px solid #d4eddf; border-bottom: 1px solid #d4eddf;
        padding: .5rem 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: .35rem;
    }
    .tp-matching-label { font-size: .75rem; font-weight: 600; color: var(--tp-green); }
    .tp-match-pill {
        font-size: .75rem; padding: .2rem .5rem; border-radius: var(--radius-pill);
        background: #fff; color: var(--tp-green); border: 1px solid #b8e6ce;
    }
    .tp-detail-line { font-size: .8125rem; color: var(--ink-500); margin-bottom: .25rem; }
    .tp-detail-key { color: var(--ink-300); display: inline-block; min-width: 7rem; }
    .tp-toolbar { display: flex; align-items: center; justify-content: space-between; padding: .5rem 0 .75rem; }
    .tp-page-nav { display: flex; align-items: center; gap: .25rem; }
    .tp-page-btn {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border: 1px solid var(--tp-border); border-radius: var(--radius-sm);
        background: var(--surface); color: var(--ink-700); text-decoration: none;
    }
    .tp-page-label { font-size: .8125rem; color: var(--ink-500); padding: 0 .5rem; }
    .btn-icon { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .tp-candidate-row.tp-row-openable { cursor: pointer; }
    .tp-candidate-row.tp-row-openable.tp-row-active {
        border-color: var(--tp-green) !important;
        box-shadow: 0 0 0 1px var(--tp-green), var(--shadow-sm);
    }
    .tp-drawer-backdrop {
        position: fixed; inset: 0; background: rgba(15, 23, 42, .45); z-index: 1050;
        opacity: 0; visibility: hidden; transition: opacity .3s ease;
        backdrop-filter: blur(2px);
    }
    .tp-drawer-backdrop.show { opacity: 1; visibility: visible; }
    .tp-drawer {
        position: fixed; top: 0; right: 0; width: min(520px, 100vw);
        height: 100vh; height: 100dvh;
        max-height: 100vh; max-height: 100dvh;
        background: #fff; z-index: 1060;
        box-shadow: -8px 0 40px rgba(15, 23, 42, .12);
        transform: translateX(100%);
        transition: transform .35s cubic-bezier(.32, .72, 0, 1);
        display: flex; flex-direction: column;
        overflow: hidden;
    }
    .tp-drawer.show { transform: translateX(0); }
    .tp-dr {
        display: flex; flex-direction: column;
        flex: 1 1 auto;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }
    #tp-drawer-body {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    #tp-drawer-body[hidden] { display: none !important; }
    #tp-drawer-loading[hidden] { display: none !important; }
    .tp-dr-top {
        padding: 1.25rem 1.25rem .75rem;
        border-bottom: 1px solid var(--tp-border);
        flex-shrink: 0;
        position: relative;
    }
    .tp-dr-close {
        position: absolute; top: 1rem; right: 1rem;
        width: 36px; height: 36px; border: none; background: var(--ink-50);
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        color: var(--ink-500); cursor: pointer; transition: background .2s;
    }
    .tp-dr-close:hover { background: var(--ink-100); color: var(--ink-900); }
    .tp-dr-hero { display: flex; gap: 1rem; padding-right: 2.5rem; }
    .tp-dr-avatar {
        width: 64px; height: 64px; border-radius: 50%; object-fit: cover; flex-shrink: 0;
        border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .tp-dr-avatar-fallback {
        width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, #e8f6ef, #ebf2ff);
        color: var(--tp-green); font-weight: 700; font-size: 1.1rem;
        display: flex; align-items: center; justify-content: center;
    }
    .tp-dr-name { font-size: 1.25rem; font-weight: 700; color: var(--ink-900); margin: 0 0 .5rem; line-height: 1.25; }
    .tp-dr-meta { display: flex; flex-wrap: wrap; gap: .75rem 1rem; font-size: .8125rem; color: var(--ink-500); }
    .tp-dr-meta span { display: inline-flex; align-items: center; gap: .25rem; }
    .tp-dr-meta i { font-size: 1rem; opacity: .7; }
    .tp-dr-tabs {
        display: flex; gap: 0; padding: 0 1.25rem; border-bottom: 1px solid var(--tp-border);
        flex-shrink: 0;
    }
    .tp-dr-tab {
        background: none; border: none; padding: .75rem 1rem; font-size: .875rem; font-weight: 600;
        color: var(--ink-400); cursor: pointer; position: relative; margin-bottom: -1px;
    }
    .tp-dr-tab.active { color: var(--tp-green); }
    .tp-dr-tab.active::after {
        content: ''; position: absolute; left: 1rem; right: 1rem; bottom: 0;
        height: 2px; background: var(--tp-green); border-radius: 2px 2px 0 0;
    }
    .tp-dr-middle {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
    }
    .tp-dr-scroll {
        flex: 1 1 auto;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 1.25rem;
        padding-bottom: 2rem;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }
    .tp-dr-scroll::-webkit-scrollbar { width: 6px; }
    .tp-dr-scroll::-webkit-scrollbar-thumb {
        background: var(--ink-200);
        border-radius: 3px;
    }
    .tp-dr-scroll::-webkit-scrollbar-thumb:hover { background: var(--ink-300); }
    .tp-dr-section { margin-bottom: 1.5rem; }
    .tp-dr-section-title {
        font-size: .6875rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: var(--ink-400); margin-bottom: .75rem;
    }
    .tp-dr-timeline { position: relative; padding-left: 1.25rem; }
    .tp-dr-timeline::before {
        content: ''; position: absolute; left: 5px; top: 8px; bottom: 8px;
        width: 2px; background: var(--tp-border); border-radius: 1px;
    }
    .tp-dr-exp { position: relative; padding-bottom: 1.25rem; }
    .tp-dr-exp:last-child { padding-bottom: 0; }
    .tp-dr-exp::before {
        content: ''; position: absolute; left: -1.25rem; top: .45rem;
        width: 10px; height: 10px; border-radius: 50%; background: #fff;
        border: 2px solid var(--tp-green); margin-left: -1px;
    }
    .tp-dr-exp-title { font-size: .9375rem; font-weight: 600; color: var(--ink-900); margin-bottom: .15rem; line-height: 1.4; }
    .tp-dr-exp-company { font-size: .8125rem; color: var(--ink-500); margin-bottom: .2rem; }
    .tp-dr-exp-period { font-size: .75rem; color: var(--ink-300); }
    .tp-dr-highlight { background: #fef9c3; padding: 0 .15em; border-radius: 2px; }
    .tp-dr-pills { display: flex; flex-wrap: wrap; gap: .4rem; }
    .tp-dr-pill {
        font-size: .75rem; padding: .35rem .65rem; border-radius: 100px;
        background: var(--ink-50); color: var(--ink-600); border: 1px solid var(--tp-border);
    }
    .tp-dr-summary { font-size: .875rem; color: var(--ink-600); line-height: 1.6; }
    .tp-dr-edu { font-size: .875rem; color: var(--ink-600); margin-bottom: .5rem; }
    .tp-dr-cv-empty {
        text-align: center; padding: 3rem 1rem; color: var(--ink-400);
    }
    .tp-dr-cv-empty i { font-size: 2.5rem; opacity: .4; display: block; margin-bottom: .75rem; }
    .tp-dr-footer {
        flex-shrink: 0; padding: 1rem 1.25rem 1.25rem;
        border-top: 1px solid var(--tp-border);
        background: linear-gradient(180deg, rgba(255,255,255,.92) 0%, #fff 24%);
        box-shadow: 0 -4px 20px rgba(15, 23, 42, .06);
    }
    .tp-dr-phone-btn {
        display: flex; align-items: center; justify-content: center; gap: .5rem;
        width: 100%; padding: .875rem 1rem; border: none; border-radius: 10px;
        background: linear-gradient(180deg, #1a7f4b 0%, #156b3f 100%);
        color: #fff; font-size: 1rem; font-weight: 600; text-decoration: none;
        box-shadow: 0 4px 14px rgba(34, 160, 107, .35);
        transition: transform .2s, box-shadow .2s;
    }
    .tp-dr-phone-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(34, 160, 107, .4); }
    .tp-dr-phone-btn.is-locked { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); box-shadow: 0 4px 14px rgba(15, 23, 42, .2); }
    .tp-dr-footer-note {
        display: flex; align-items: center; justify-content: center; gap: 1rem;
        margin-top: .65rem; font-size: .75rem; color: var(--ink-400);
    }
    .tp-dr-actions { display: flex; gap: .5rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--tp-border); }
    .tp-dr-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 1rem; color: var(--ink-400); }
    .tp-dr-loading .spinner-border { width: 2rem; height: 2rem; color: var(--tp-green); }
    #tp-loading {
        position: absolute; inset: 0; background: rgba(255,255,255,.65); z-index: 5;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: opacity .2s ease;
    }
    #tp-loading.show { opacity: 1; visibility: visible; }
    .tp-results-wrap { position: relative; }
</style>
@endpush

@section('content')
@php
    $queryLabel = trim((string) ($filters['q'] ?? ''));
    if ($queryLabel === '' && !empty($filters['skills'])) {
        $queryLabel = trim((string) $filters['skills']);
    }
    if ($queryLabel === '') {
        $queryLabel = 'All candidates';
    }
@endphp
<div class="tp-page">
    <form id="tp-search-form" method="GET" action="{{ route('employer.talent-pool.results') }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
        <input type="hidden" name="skills" value="{{ $filters['skills'] ?? '' }}">

        @if(empty($canAccessTalentPool))
            <div class="alert alert-warning mb-3">
                <i class="mdi mdi-lock-outline me-1"></i>
                Resume database access requires an active plan. Use <strong>Phone number</strong> on any card to view plans, or
                <a href="{{ route('employer.plans.index') }}" class="alert-link fw-600">subscribe here</a>.
            </div>
        @elseif(!empty($requiresSearch))
            <div class="alert alert-info mb-3">
                Enter at least {{ config('hirevo_plans.min_search_length', 2) }} characters, skills, location, or other filters to search. We only load matching candidates — not the entire database.
            </div>
        @endif

        <div class="tp-results-summary">
            <a href="{{ route('employer.talent-pool.index', request()->only(['q', 'skills', 'location', 'education', 'experience_min', 'experience_max'])) }}" class="btn btn-sm btn-light border me-1" title="Back to search">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <span class="tp-query">{{ \Illuminate\Support\Str::limit($queryLabel, 60) }}</span>
            @if(!empty($filters['skills']) && ($filters['q'] ?? '') !== ($filters['skills'] ?? ''))
                <span class="tp-chip">{{ $filters['skills'] }}</span>
            @endif
            @if(!empty($filters['location']))
                <span class="tp-chip">{{ $filters['location'] }}</span>
            @endif
            @if(!empty($filters['education']))
                <span class="tp-chip">{{ $filters['education'] }}</span>
            @endif
            @if(empty($requiresSearch))
                <span class="tp-results-count" id="tp-total-count">
                    {{ number_format($totalCount ?? 0) }} {{ ($totalCount ?? 0) === 1 ? 'candidate' : 'candidates' }}
                </span>
            @endif
        </div>

        <div class="tp-layout">
            <aside class="tp-filters-wrap">
                <div class="tp-filters-card" id="tp-filters-container">
                    @include('hirevo.employer.talent-pool._filters', [
                        'filters' => $filters,
                        'selectedLocations' => $selectedLocations,
                        'facets' => $facets,
                        'activeFilterCount' => $activeFilterCount,
                        'educationOptions' => $educationOptions,
                    ])
                </div>
            </aside>

            <div class="tp-results-wrap">
                <div id="tp-loading" aria-hidden="true">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                </div>
                <div id="tp-results" class="tp-results-area">
                    @include('hirevo.employer.talent-pool._results', [
                        'candidates' => $candidates,
                        'paginator' => $paginator,
                        'perPage' => $perPage,
                        'totalCount' => $totalCount ?? 0,
                        'matchingSkills' => $matchingSkills,
                        'requiresSearch' => $requiresSearch ?? false,
                        'canAccessTalentPool' => $canAccessTalentPool,
                    ])
                </div>
            </div>
        </div>
    </form>
</div>

<div class="tp-drawer-backdrop" id="tp-drawer-backdrop" aria-hidden="true"></div>
<aside class="tp-drawer" id="tp-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Candidate profile">
    <div class="tp-dr" id="tp-drawer-root">
        <div class="tp-dr-loading" id="tp-drawer-loading">
            <div class="spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>
            <span class="small">Loading profile…</span>
        </div>
        <div id="tp-drawer-body" hidden></div>
    </div>
</aside>

@endsection

@php
    $tpHighlightTerms = array_values(array_unique(array_filter(array_map(
        'trim',
        array_merge(
            preg_split('/[\s,;]+/', (string) ($filters['q'] ?? '')) ?: [],
            preg_split('/[\s,;]+/', (string) ($filters['skills'] ?? '')) ?: []
        )
    ), fn ($t) => strlen($t) >= 2)));
@endphp
@push('scripts')
@include('hirevo.employer.talent-pool._results-scripts', ['tpHighlightTerms' => $tpHighlightTerms])
@endpush
