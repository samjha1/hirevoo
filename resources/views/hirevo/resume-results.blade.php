@extends('layouts.app')

@section('title', 'Resume Analysis — ATS Score & Job Matches')

@push('styles')
<style>
/* ─── CSS Variables ─────────────────────────────────────────── */
:root {
    --rr-primary: #0b1f3b;
    --rr-accent: #10b981;
    --rr-accent-2: #6366f1;
    --rr-warn: #f59e0b;
    --rr-danger: #ef4444;
    --rr-surface: #ffffff;
    --rr-bg: #f1f5f9;
    --rr-border: rgba(15,23,42,0.08);
    --rr-text: #0f172a;
    --rr-muted: #64748b;
    --rr-card-shadow: 0 4px 24px rgba(15,23,42,0.07);
    --rr-card-hover-shadow: 0 16px 48px rgba(15,23,42,0.13);
    --rr-radius: 1.25rem;
    --rr-radius-sm: 0.85rem;
    --navbar-h: 72px;
}

/* ─── Page Base ─────────────────────────────────────────────── */
.rr-page {
    background: linear-gradient(160deg, #eef2ff 0%, #f8fafc 18%, #ffffff 55%);
    min-height: 100vh;
}

/* ─── Hero ──────────────────────────────────────────────────── */
.rr-hero-v2 {
    border-radius: var(--rr-radius);
    background: linear-gradient(135deg, #0b1f3b 0%, #1e3a5f 55%, #0f5242 100%);
    color: #fff;
    padding: 1.75rem 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(11,31,59,0.28);
}
.rr-hero-v2::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,0.25) 0%, transparent 70%);
    pointer-events: none;
}
.rr-hero-v2::after {
    content: '';
    position: absolute;
    bottom: -40px; left: -30px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99,102,241,0.18) 0%, transparent 70%);
    pointer-events: none;
}
.rr-hero-v2 .rr-hero-inner { position: relative; z-index: 1; }
.rr-hero-kicker {
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #34d399;
    margin-bottom: 0.3rem;
}
.rr-hero-title {
    font-size: 1.35rem;
    font-weight: 800;
    line-height: 1.25;
    color: #fff;
}
@media (min-width: 768px) { .rr-hero-title { font-size: 1.65rem; } }
.rr-hero-sub {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.65);
    line-height: 1.5;
    margin-top: 0.4rem;
}
.rr-hero-file-bar {
    margin-top: 1rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(255,255,255,0.12);
    display: flex;
    align-items: center;
    gap: 0.55rem;
    flex-wrap: wrap;
}
.rr-hero-file-icon {
    width: 32px; height: 32px;
    border-radius: 0.5rem;
    background: rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.rr-hero-filename {
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
}
.rr-hero-filedate {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
}

/* ─── ATS Score Card ────────────────────────────────────────── */
.rr-ats-card {
    border-radius: var(--rr-radius);
    background: var(--rr-surface);
    border: 1px solid var(--rr-border);
    box-shadow: var(--rr-card-shadow);
    overflow: hidden;
}
.rr-ats-card-header {
    padding: 1rem 1.25rem 0.75rem;
    border-bottom: 1px solid rgba(15,23,42,0.05);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.rr-ats-card-body { padding: 1.25rem; }

/* SVG Score Ring */
.rr-score-svg-wrap {
    position: relative;
    width: 110px; height: 110px;
    flex-shrink: 0;
}
.rr-score-svg { transform: rotate(-90deg); display: block; }
.rr-score-svg .bg-circle { fill: none; stroke: #e2e8f0; stroke-width: 8; }
.rr-score-svg .fg-circle {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 283;
    stroke-dashoffset: 283;
    transition: stroke-dashoffset 1.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.rr-score-svg .fg-circle.score-high { stroke: url(#rr-grad-high); }
.rr-score-svg .fg-circle.score-mid  { stroke: url(#rr-grad-mid);  }
.rr-score-svg .fg-circle.score-low  { stroke: url(#rr-grad-low);  }
.rr-score-inner {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}
.rr-score-number {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
}
.rr-score-number.score-high { color: #047857; }
.rr-score-number.score-mid  { color: #b45309; }
.rr-score-number.score-low  { color: #b91c1c; }
.rr-score-pct { font-size: 0.72rem; font-weight: 700; opacity: 0.7; }
.rr-score-label {
    font-size: 0.6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 0.15rem;
}
.rr-band-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.rr-band-pill.high { background: rgba(16,185,129,0.12); color: #047857; border: 1.5px solid rgba(16,185,129,0.3); }
.rr-band-pill.mid  { background: rgba(245,158,11,0.12);  color: #b45309; border: 1.5px solid rgba(245,158,11,0.3); }
.rr-band-pill.low  { background: rgba(239,68,68,0.1);    color: #b91c1c; border: 1.5px solid rgba(239,68,68,0.25); }
.rr-progress-bar-wrap {
    height: 6px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    margin: 0.6rem 0;
}
.rr-progress-fill {
    height: 100%;
    border-radius: 999px;
    width: 0;
    transition: width 1.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.rr-progress-fill.high { background: linear-gradient(90deg, #10b981, #34d399); }
.rr-progress-fill.mid  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.rr-progress-fill.low  { background: linear-gradient(90deg, #ef4444, #f87171); }

/* Skill chips */
.rr-skill-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.22rem 0.6rem;
    border-radius: 999px;
    font-size: 0.68rem;
    font-weight: 600;
    background: rgba(99,102,241,0.1);
    color: #4338ca;
    border: 1px solid rgba(99,102,241,0.2);
    transition: background 0.15s ease, transform 0.15s ease;
    cursor: default;
}
.rr-skill-chip:hover {
    background: rgba(99,102,241,0.18);
    transform: translateY(-1px);
}
.rr-skill-chip--more {
    background: #f1f5f9;
    color: var(--rr-muted);
    border-color: #e2e8f0;
}

/* Summary card */
.rr-summary-card {
    border-radius: var(--rr-radius);
    background: linear-gradient(125deg, #f0f9ff 0%, #fafffe 60%, #fff 100%);
    border: 1px solid rgba(16,185,129,0.18);
    box-shadow: 0 4px 18px rgba(16,185,129,0.07);
    padding: 1.1rem 1.25rem;
}

/* ─── Tab Toolbar ───────────────────────────────────────────── */
.rr-tab-toolbar {
    border-radius: var(--rr-radius);
    background: var(--rr-surface);
    border: 1px solid var(--rr-border);
    box-shadow: var(--rr-card-shadow);
    padding: 1rem 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}
.rr-tabs {
    display: flex;
    gap: 0.35rem;
    background: #f1f5f9;
    border-radius: 0.75rem;
    padding: 0.25rem;
}
.rr-tab {
    padding: 0.35rem 0.85rem;
    border-radius: 0.55rem;
    font-size: 0.78rem;
    font-weight: 600;
    border: none;
    background: transparent;
    color: var(--rr-muted);
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.rr-tab.active {
    background: var(--rr-surface);
    color: var(--rr-text);
    box-shadow: 0 2px 8px rgba(15,23,42,0.1);
}
.rr-tab .rr-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px; height: 18px;
    border-radius: 999px;
    font-size: 0.62rem;
    font-weight: 700;
    margin-left: 0.3rem;
    background: rgba(15,23,42,0.08);
    color: var(--rr-muted);
    padding: 0 4px;
}
.rr-tab.active .rr-tab-count {
    background: var(--rr-accent);
    color: #fff;
}
.rr-tabs--links .rr-tab {
    display: inline-flex;
    align-items: center;
    color: inherit;
}
.rr-tabs--links .rr-tab:not(.active):hover {
    color: var(--rr-text);
    background: rgba(255, 255, 255, 0.55);
}

/* ─── Pagination (Bootstrap) ───────────────────────────────── */
.rr-pagination-wrap .pagination {
    gap: 0.35rem;
    flex-wrap: wrap;
    margin-bottom: 0;
}
.rr-pagination-wrap .page-link {
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.4rem 0.75rem;
    border: 1px solid var(--rr-border);
    color: var(--rr-text);
}
.rr-pagination-wrap .page-item.active .page-link {
    background: var(--rr-accent);
    border-color: var(--rr-accent);
    color: #fff;
}
.rr-pagination-wrap .page-item.disabled .page-link {
    opacity: 0.45;
}

/* ─── Job Cards ─────────────────────────────────────────────── */
.rr-browse-grid {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}
.rr-job-card {
    border-radius: var(--rr-radius);
    background: var(--rr-surface);
    border: 1px solid var(--rr-border);
    box-shadow: var(--rr-card-shadow);
    padding: 1.25rem 1.35rem;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.2s ease;
    animation: rrFadeUp 0.45s ease backwards;
    position: relative;
    overflow: hidden;
}
.rr-job-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--rr-radius) var(--rr-radius) 0 0;
    opacity: 0;
    transition: opacity 0.2s ease;
}
.rr-job-card:hover { transform: translateY(-3px); box-shadow: var(--rr-card-hover-shadow); border-color: rgba(16,185,129,0.3); }
.rr-job-card:hover::before { opacity: 1; }
.rr-job-card.kind-employer::before { background: linear-gradient(90deg, #10b981, #34d399); }
.rr-job-card.kind-goal::before    { background: linear-gradient(90deg, #6366f1, #818cf8); }
.rr-job-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.85rem;
}
.rr-job-card-badges {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
}
.rr-rank-badge {
    font-size: 0.6rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.18rem 0.52rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #0b1f3b, #1e3a5f);
    color: #fff;
}
.rr-kind-badge {
    font-size: 0.62rem;
    font-weight: 700;
    padding: 0.18rem 0.52rem;
    border-radius: 999px;
}
.rr-kind-badge.live {
    background: rgba(16,185,129,0.12);
    color: #047857;
    border: 1px solid rgba(16,185,129,0.25);
}
.rr-kind-badge.goal {
    background: rgba(99,102,241,0.1);
    color: #4338ca;
    border: 1px solid rgba(99,102,241,0.22);
}
.rr-job-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--rr-text);
    text-decoration: none;
    line-height: 1.3;
    display: block;
    transition: color 0.15s;
}
.rr-job-title:hover { color: var(--rr-accent); }
.rr-job-meta {
    font-size: 0.8rem;
    color: var(--rr-muted);
    margin-top: 0.25rem;
    font-weight: 500;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.rr-job-meta-dot {
    width: 3px; height: 3px;
    border-radius: 50%;
    background: #cbd5e1;
    display: inline-block;
}

/* Match ring */
.rr-match-ring {
    width: 58px; height: 58px;
    min-width: 58px;
    border-radius: 50%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-weight: 800;
    font-size: 0.92rem;
    line-height: 1;
    letter-spacing: -0.02em;
    border: 2.5px solid transparent;
    flex-shrink: 0;
}
.rr-match-ring small { font-size: 0.58rem; font-weight: 700; opacity: 0.8; }
.rr-match-ring.high { background: rgba(16,185,129,0.1);  color: #047857; border-color: rgba(16,185,129,0.35); }
.rr-match-ring.mid  { background: rgba(245,158,11,0.1);  color: #b45309; border-color: rgba(245,158,11,0.35); }
.rr-match-ring.low  { background: rgba(100,116,139,0.08); color: #475569; border-color: rgba(100,116,139,0.22); }

/* Card footer */
.rr-card-footer {
    margin-top: 1rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(15,23,42,0.06);
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

/* Empty state */
.rr-empty {
    text-align: center;
    padding: 3rem 1.5rem;
    border-radius: var(--rr-radius);
    background: var(--rr-surface);
    border: 1.5px dashed #e2e8f0;
}
.rr-empty-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: #f1f5f9;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    margin: 0 auto 1rem;
}

/* ─── Animations ─────────────────────────────────────────────── */
@keyframes rrFadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes rrReveal {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Utility ────────────────────────────────────────────────── */
.rr-section-label {
    font-size: 0.6rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--rr-muted);
}
.fw-600 { font-weight: 600 !important; }

@media (prefers-reduced-motion: reduce) {
    .rr-job-card, .rr-job-card:hover { transition: none; transform: none; animation: none; }
    .rr-score-svg .fg-circle { transition: none; }
    .rr-progress-fill { transition: none; }
}
</style>
@endpush

@section('content')

{{-- Hidden SVG defs for gradient rings --}}
<svg width="0" height="0" style="position:absolute;overflow:hidden">
    <defs>
        <linearGradient id="rr-grad-high" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"   stop-color="#10b981"/>
            <stop offset="100%" stop-color="#34d399"/>
        </linearGradient>
        <linearGradient id="rr-grad-mid" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"   stop-color="#f59e0b"/>
            <stop offset="100%" stop-color="#fbbf24"/>
        </linearGradient>
        <linearGradient id="rr-grad-low" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"   stop-color="#ef4444"/>
            <stop offset="100%" stop-color="#f87171"/>
        </linearGradient>
    </defs>
</svg>

<section class="section pb-5 pt-4 rr-page">
    <div class="container">

        {{-- Breadcrumb --}}
        <nav class="mb-3" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('resume.upload') }}" class="text-decoration-none">Resume</a></li>
                <li class="breadcrumb-item active" aria-current="page">Results</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 border-0 rounded-3 shadow-sm" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $score      = $resume->ai_score ?? 0;
            $bandKey    = $score >= 70 ? 'high' : ($score >= 50 ? 'mid' : 'low');
            $bandLabel  = $score >= 70 ? 'Strong' : ($score >= 50 ? 'Fair' : 'Needs Work');
            $skills     = is_array($resume->extracted_skills) ? $resume->extracted_skills : [];
            $circumference = 283; // 2π × r (r=45)
            $offset     = $circumference - ($score / 100) * $circumference;

            $matchKind = $resumeMatchKind ?? 'all';
            $matchTotals = $resumeMatchTotals ?? ['all' => 0, 'employer' => 0, 'goal' => 0];
            $employerCount = (int) ($matchTotals['employer'] ?? 0);
            $goalCount = (int) ($matchTotals['goal'] ?? 0);
            $allMatchCount = (int) ($matchTotals['all'] ?? 0);
        @endphp

        {{-- ═══ HERO ═══ --}}
        <div class="rr-hero-v2 mb-4">
            <div class="rr-hero-inner">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <p class="rr-hero-kicker mb-1"><i class="uil uil-check-circle me-1"></i>Analysis complete</p>
                        <h1 class="rr-hero-title mb-0">Your Resume Insights</h1>
                        <p class="rr-hero-sub mb-0">ATS score, skill gaps, and {{ $allMatchCount }} matched roles ranked by fit — best first.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center flex-shrink-0">
                        <a href="{{ route('resume.upload') }}" class="btn btn-sm rounded-pill px-3"
                           style="background:rgba(255,255,255,0.15);color:#fff;border:1.5px solid rgba(255,255,255,0.3);font-size:0.78rem;font-weight:600;backdrop-filter:blur(4px);">
                            <i class="uil uil-upload me-1"></i>New Upload
                        </a>
                        <a href="{{ route('profile') }}" class="btn btn-sm rounded-pill px-3"
                           style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.75);border:1.5px solid rgba(255,255,255,0.15);font-size:0.78rem;font-weight:600;">
                            Profile
                        </a>
                    </div>
                </div>
                <div class="rr-hero-file-bar">
                    <div class="rr-hero-file-icon"><i class="uil uil-file-alt" style="color:#34d399"></i></div>
                    <div>
                        <div class="rr-hero-filename">{{ $resume->file_name ?? 'Resume' }}</div>
                        @if($resume->created_at)
                            <div class="rr-hero-filedate">Uploaded {{ $resume->created_at->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ MAIN (full width) ═══ --}}
        <div class="row g-4 align-items-start">
            <div class="col-12" id="rr-jobs-main-col">

                {{-- ATS Score Card --}}
                <div class="rr-ats-card mb-3" id="rr-ats-snapshot">
                    <div class="rr-ats-card-header">
                        <i class="uil uil-analytics text-primary fs-5"></i>
                        <h2 class="h6 fw-bold mb-0">ATS Score</h2>
                    </div>
                    <div class="rr-ats-card-body">
                        <div class="row g-3 align-items-center">
                            {{-- SVG Ring --}}
                            <div class="col-auto">
                                <div class="rr-score-svg-wrap">
                                    <svg class="rr-score-svg" width="110" height="110" viewBox="0 0 110 110">
                                        <circle class="bg-circle" cx="55" cy="55" r="45"/>
                                        <circle class="fg-circle {{ $bandKey }}"
                                                cx="55" cy="55" r="45"
                                                id="rr-score-arc"
                                                data-offset="{{ $offset }}"
                                                style="stroke-dashoffset: {{ $circumference }}"/>
                                    </svg>
                                    <div class="rr-score-inner">
                                        <span class="rr-score-number {{ $bandKey }}" id="rr-score-num" data-target="{{ $score }}">0</span>
                                        <span class="rr-score-pct">%</span>
                                        <span class="rr-score-label {{ $bandKey === 'high' ? 'text-success' : ($bandKey === 'mid' ? 'text-warning' : 'text-danger') }}">ATS</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Score Details --}}
                            <div class="col min-w-0">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="rr-band-pill {{ $bandKey }}">
                                        @if($bandKey === 'high')
                                            <i class="uil uil-check-circle"></i>
                                        @elseif($bandKey === 'mid')
                                            <i class="uil uil-exclamation-circle"></i>
                                        @else
                                            <i class="uil uil-times-circle"></i>
                                        @endif
                                        {{ $bandLabel }}
                                    </span>
                                    <span class="text-muted" style="font-size:0.72rem;">out of 100</span>
                                </div>
                                <div class="rr-progress-bar-wrap">
                                    <div class="rr-progress-fill {{ $bandKey }}" id="rr-progress-fill" data-width="{{ min(100, $score) }}"></div>
                                </div>
                                @if($resume->ai_score_explanation)
                                    <p class="text-muted mb-0" style="font-size:0.79rem;line-height:1.55;">{{ Str::limit($resume->ai_score_explanation, 300) }}</p>
                                @endif
                            </div>
                        </div>

                        @if(count($skills) > 0)
                            <div class="pt-3 mt-3 border-top">
                                <p class="rr-section-label mb-2">Detected Skills</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach(array_slice($skills, 0, 18) as $sk)
                                        <span class="rr-skill-chip">{{ is_string($sk) ? $sk : '' }}</span>
                                    @endforeach
                                    @if(count($skills) > 18)
                                        <span class="rr-skill-chip rr-skill-chip--more">+{{ count($skills) - 18 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- AI Summary --}}
                @if($resume->ai_summary)
                    <div class="rr-summary-card mb-3">
                        <p class="rr-section-label mb-2"><i class="uil uil-lightbulb-alt me-1"></i>Resume Highlights</p>
                        <p class="text-muted mb-0" style="font-size:0.82rem;line-height:1.6;">{{ Str::limit(strip_tags($resume->ai_summary), 600) }}</p>
                    </div>
                @endif

                {{-- Tab Toolbar --}}
                <div class="rr-tab-toolbar mb-3" id="rr-browse-jobs">
                    <div>
                        <h2 class="h6 fw-bold text-dark mb-0">
                            <i class="uil uil-briefcase-alt text-primary me-1"></i>Recommended for You
                        </h2>
                        @if($allMatchCount > 0)
                            <p class="text-muted small mb-0 mt-1">
                                <strong class="text-dark">{{ $allMatchCount }}</strong> roles ranked by match ·
                                <a href="{{ route('job-openings') }}" class="text-primary text-decoration-none fw-600">Browse all openings</a>
                            </p>
                        @endif
                        @if(isset($matchesPaginator) && $matchesPaginator->total() > 0)
                            <p class="text-muted small mb-0 mt-1" style="font-size:0.78rem;">
                                Showing <strong class="text-dark">{{ $matchesPaginator->firstItem() }}–{{ $matchesPaginator->lastItem() }}</strong> of {{ $matchesPaginator->total() }}
                                @if($matchKind === 'employer')
                                    <span class="text-muted">· Live jobs only</span>
                                @elseif($matchKind === 'goal')
                                    <span class="text-muted">· Job goals only</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="rr-tabs rr-tabs--links" role="tablist">
                            <a href="{{ route('resume.results', ['resume' => $resume, 'kind' => 'all']) }}"
                               class="rr-tab text-decoration-none {{ $matchKind === 'all' ? 'active' : '' }}"
                               role="tab" aria-selected="{{ $matchKind === 'all' ? 'true' : 'false' }}">
                                All <span class="rr-tab-count">{{ $allMatchCount }}</span>
                            </a>
                            <a href="{{ route('resume.results', ['resume' => $resume, 'kind' => 'employer']) }}"
                               class="rr-tab text-decoration-none {{ $matchKind === 'employer' ? 'active' : '' }}"
                               role="tab" aria-selected="{{ $matchKind === 'employer' ? 'true' : 'false' }}">
                                Live Jobs <span class="rr-tab-count">{{ $employerCount }}</span>
                            </a>
                            <a href="{{ route('resume.results', ['resume' => $resume, 'kind' => 'goal']) }}"
                               class="rr-tab text-decoration-none {{ $matchKind === 'goal' ? 'active' : '' }}"
                               role="tab" aria-selected="{{ $matchKind === 'goal' ? 'true' : 'false' }}">
                                Goals <span class="rr-tab-count">{{ $goalCount }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Job Cards --}}
                <div class="rr-browse-grid" id="rr-cards-grid">
                    @forelse($matchesPaginator as $idx => $entry)
                        @php
                            $rank     = (int) (($matchesPaginator->firstItem() ?? 1) + $idx);
                            $delay    = min($idx * 0.045, 0.7);
                            $matchPct = $entry['match'];
                            $ringCls  = $matchPct >= 55 ? 'high' : ($matchPct >= 28 ? 'mid' : 'low');
                        @endphp

                        @if($entry['kind'] === 'employer')
                            @php
                                $item        = $entry['payload'];
                                $job         = $item['job'];
                                $companyName = $job->user->referrerProfile?->company_name ?? $job->company_name ?? 'Company';
                            @endphp
                            <article class="rr-job-card kind-employer" data-kind="employer" style="animation-delay:{{ $delay }}s">
                                <div class="rr-job-card-head">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="rr-job-card-badges">
                                            @if($rank <= 3)
                                                <span class="rr-rank-badge">#{{ $rank }} Match</span>
                                            @endif
                                            <span class="rr-kind-badge live">Live Opening</span>
                                        </div>
                                        <a href="{{ route('job-openings.apply', $job) }}" class="rr-job-title">{{ $job->title }}</a>
                                        <div class="rr-job-meta">
                                            <span><i class="uil uil-building me-1"></i>{{ $companyName }}</span>
                                            @if($job->formatted_location)
                                                <span class="rr-job-meta-dot"></span>
                                                <span><i class="uil uil-map-marker me-1"></i>{{ $job->formatted_location }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="rr-match-ring {{ $ringCls }}">{{ $matchPct }}<small>%</small></div>
                                </div>
                                {{-- Match bar --}}
                                <div class="rr-progress-bar-wrap mt-3 mb-0" style="height:5px;">
                                    <div class="rr-progress-fill {{ $ringCls }}" style="width:{{ min(100, $matchPct) }}%;transition:none;"></div>
                                </div>
                                <div class="rr-card-footer">
                                    @if(in_array($job->id, $appliedEmployerJobIds ?? []))
                                        <span class="badge bg-success px-3 py-2 rounded-pill">
                                            <i class="uil uil-check me-1"></i>Applied
                                        </span>
                                    @else
                                        <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                            {{ $job->apply_link ? 'Apply on Site' : 'Apply Now' }}
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill js-rr-referral"
                                            data-resume-id="{{ $resume->id }}"
                                            data-employer-job-id="{{ $job->id }}">
                                        <i class="uil uil-users-alt me-1"></i>Get referral
                                    </button>
                                </div>
                            </article>

                        @else
                            @php
                                $item   = $entry['payload'];
                                $role   = $item['job_role'];
                                $match  = $entry['match'];
                                $ringCls = $match >= 65 ? 'high' : ($match >= 35 ? 'mid' : 'low');
                            @endphp
                            <article class="rr-job-card kind-goal" data-kind="goal" style="animation-delay:{{ $delay }}s">
                                <div class="rr-job-card-head">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="rr-job-card-badges">
                                            @if($rank <= 3)
                                                <span class="rr-rank-badge">#{{ $rank }} Match</span>
                                            @endif
                                            <span class="rr-kind-badge goal">Job Goal</span>
                                        </div>
                                        <a href="{{ route('job-goal.show', $role) }}" class="rr-job-title">{{ $role->title }}</a>
                                        @if($role->description)
                                            <p class="rr-job-meta mb-0">{{ Str::limit(strip_tags($role->description), 80) }}</p>
                                        @endif
                                    </div>
                                    <div class="rr-match-ring {{ $ringCls }}">{{ $match }}<small>%</small></div>
                                </div>
                                <div class="rr-progress-bar-wrap mt-3 mb-0" style="height:5px;">
                                    <div class="rr-progress-fill {{ $ringCls }}" style="width:{{ min(100, $match) }}%;transition:none;"></div>
                                </div>
                                <div class="rr-card-footer">
                                    <a href="{{ route('job-goal.show', $role) }}" class="btn btn-outline-primary btn-sm rounded-pill">View Role</a>
                                    @if(in_array($role->id, $appliedJobIds ?? []))
                                        <span class="badge bg-success px-3 py-2 rounded-pill align-self-center">
                                            <i class="uil uil-check me-1"></i>Applied
                                        </span>
                                    @else
                                        <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm rounded-pill">Apply</a>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill js-rr-referral"
                                            data-resume-id="{{ $resume->id }}"
                                            data-job-role-id="{{ $role->id }}">
                                        <i class="uil uil-users-alt me-1"></i>Get referral
                                    </button>
                                    <form action="{{ route('resume.lead') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="resume_id" value="{{ $resume->id }}">
                                        <input type="hidden" name="job_role_id" value="{{ $role->id }}">
                                        <button type="submit" class="btn btn-sm rounded-pill px-3"
                                                style="background:rgba(16,185,129,0.1);color:#047857;border:1px solid rgba(16,185,129,0.25);font-size:0.75rem;font-weight:600;">
                                            <i class="uil uil-graduation-cap me-1"></i>Get Help to Learn
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @endif

                    @empty
                        <div class="rr-empty">
                            <div class="rr-empty-icon">
                                <i class="uil uil-search text-muted"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">No matches yet</h5>
                            <p class="text-muted small mb-3">Browse openings or goals — your next upload may unlock more recommendations.</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('job-openings') }}" class="btn btn-primary btn-sm rounded-pill px-4">Explore Openings</a>
                                <a href="{{ route('job-list') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">Job Goals</a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if(isset($matchesPaginator) && $matchesPaginator->hasPages())
                    <nav class="rr-pagination-wrap d-flex justify-content-center mt-4 mb-2" aria-label="Recommended roles pages">
                        {{ $matchesPaginator->fragment('rr-browse-jobs')->links() }}
                    </nav>
                @endif

            </div>

        </div>{{-- /row --}}
    </div>{{-- /.container --}}
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ── Animate ATS score ring ──────────────────────────── */
    var arc      = document.getElementById('rr-score-arc');
    var numEl    = document.getElementById('rr-score-num');
    var fillEl   = document.getElementById('rr-progress-fill');
    var target   = numEl ? parseInt(numEl.getAttribute('data-target'), 10) : 0;
    var offset   = arc   ? parseFloat(arc.getAttribute('data-offset'))     : 283;
    var barWidth = fillEl ? parseFloat(fillEl.getAttribute('data-width'))  : 0;

    function animateScoreRing() {
        if (!arc || !numEl) return;
        var start     = null;
        var duration  = 1400;
        var startDash = 283;

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var ease     = 1 - Math.pow(1 - progress, 3); // ease-out cubic

            arc.style.strokeDashoffset = startDash - (startDash - offset) * ease;
            numEl.textContent           = Math.round(target * ease);
            if (fillEl) fillEl.style.width = (barWidth * ease) + '%';

            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    /* Trigger on scroll into view */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { animateScoreRing(); io.disconnect(); }
            });
        }, { threshold: 0.3 });
        var snap = document.getElementById('rr-ats-snapshot');
        if (snap) io.observe(snap);
    } else {
        animateScoreRing();
    }

    /* Referral: save lead via fetch, then go to plan (pricing) without reloading this page first */
    var refUrl = @json(route('resume.referral'));
    var planUrl = @json(route('pricing'));
    document.addEventListener('click', function (ev) {
        var btn = ev.target.closest('.js-rr-referral');
        if (!btn) return;
        ev.preventDefault();
        if (btn.disabled) return;
        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        var token = tokenMeta ? tokenMeta.getAttribute('content') : '';
        var fd = new FormData();
        fd.append('_token', token);
        fd.append('resume_id', btn.getAttribute('data-resume-id'));
        var ej = btn.getAttribute('data-employer-job-id');
        var jr = btn.getAttribute('data-job-role-id');
        if (ej) fd.append('employer_job_id', ej);
        if (jr) fd.append('job_role_id', jr);
        btn.disabled = true;
        fetch(refUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token
            },
            body: fd,
            credentials: 'same-origin'
        }).then(function (res) {
            if (!res.ok) throw new Error('referral failed');
            return res.json();
        }).then(function (data) {
            if (data && data.redirect) {
                window.location.assign(data.redirect);
            } else {
                window.location.assign(planUrl);
            }
        }).catch(function () {
            btn.disabled = false;
        });
    });
})();
</script>
@endpush
