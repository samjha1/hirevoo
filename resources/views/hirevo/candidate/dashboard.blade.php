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
        /* main-content already has padding-top for fixed navbar — no extra margin */
        margin-top: 0;
        background: #fff;
        border-bottom: 1px solid rgba(0,0,0,.06);
        padding: 0.85rem 0 1.15rem;
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
        margin-bottom: 0.65rem;
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
    .stat-icon i { font-size: 1.2rem; line-height: 1; }
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

    .apps-section { margin-bottom: 2rem; scroll-margin-top: 88px; }

    .apps-pagination-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding-top: 0.25rem;
    }
    .apps-pagination-meta {
        font-size: 0.8125rem;
        color: #64748b;
    }
    .apps-pagination .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.35rem;
    }
    .apps-pagination .page-link {
        border-radius: 10px !important;
        border: 1px solid rgba(0, 0, 0, 0.08);
        color: var(--hirevo-primary, #0B1F3B);
        font-size: 0.8125rem;
        font-weight: 500;
        padding: 0.4rem 0.75rem;
        min-width: 2.25rem;
        text-align: center;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
    }
    .apps-pagination .page-link:hover {
        background: rgba(11, 31, 59, 0.06);
        border-color: rgba(11, 31, 59, 0.15);
        color: var(--hirevo-primary);
    }
    .apps-pagination .page-item.active .page-link {
        background: var(--hirevo-primary);
        border-color: var(--hirevo-primary);
        color: #fff;
    }
    .apps-pagination .page-item.disabled .page-link {
        opacity: 0.45;
    }
    @media (prefers-reduced-motion: reduce) {
        .apps-pagination .page-link { transition: none; }
    }

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
        .apps-hero { padding: 0.7rem 0 1rem; }
        .stats-strip { grid-template-columns: 1fr 1fr; }
        .app-job-title { white-space: normal; }
    }

    .apps-page { scroll-behavior: smooth; }

    /* Insight strip: skills + referral (dashboard only) — compact */
    .dash-insight-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 0.25rem;
    }
    @media (max-width: 991px) {
        .dash-insight-grid { grid-template-columns: 1fr; }
    }
    .dash-card {
        border-radius: 12px;
        padding: 0.85rem 1rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 14px rgba(11, 31, 59, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }
    .dash-card--skills {
        background: linear-gradient(145deg, #ffffff 0%, #f8fafc 55%, rgba(16, 185, 129, 0.06) 100%);
    }
    .dash-card--skills::after {
        content: '';
        position: absolute;
        top: -35%;
        right: -18%;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(11, 31, 59, 0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    .dash-card--referral {
        background: linear-gradient(135deg, var(--hirevo-primary, #0B1F3B) 0%, #132a45 50%, #0d2844 100%);
        border-color: rgba(255, 255, 255, 0.08);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .dash-card-inner { position: relative; z-index: 1; }
    .dash-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--hirevo-secondary, #10b981);
        background: rgba(16, 185, 129, 0.12);
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        margin-bottom: 0.35rem;
    }
    .dash-card--referral .dash-badge {
        background: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
    }
    .dash-card-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.25rem;
        line-height: 1.3;
    }
    .dash-card--referral .dash-card-title { color: #fff; }
    .dash-card-sub {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0 0 0.65rem;
        line-height: 1.4;
        max-width: 34rem;
    }
    .dash-card--referral .dash-card-sub {
        color: rgba(255, 255, 255, 0.72);
        margin-bottom: 0.75rem;
    }
    .dash-match-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.6875rem;
        font-weight: 700;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid rgba(11, 31, 59, 0.12);
        color: var(--hirevo-primary);
        margin-bottom: 0.55rem;
        box-shadow: 0 1px 3px rgba(11, 31, 59, 0.05);
    }
    .dash-skill-col-lbl {
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #475569;
        margin: 0 0 0.3rem;
    }
    .dash-skill-col-lbl.strengthen { color: #b45309; }
    .dash-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.28rem;
    }
    .dash-chip {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: #fff;
        color: #334155;
    }
    .dash-chip--have {
        background: rgba(16, 185, 129, 0.12);
        border-color: rgba(16, 185, 129, 0.28);
        color: #047857;
    }
    .dash-chip--gap {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.35);
        color: #b45309;
    }
    .dash-chip--suggest {
        background: rgba(99, 102, 241, 0.08);
        border-color: rgba(99, 102, 241, 0.28);
        color: #4338ca;
        border-style: dashed;
    }
    .dash-consult {
        background: #fff;
        border: 1px solid rgba(11, 31, 59, 0.1);
        border-radius: 10px;
        padding: 0.65rem 0.85rem;
        box-shadow: 0 1px 8px rgba(11, 31, 59, 0.05);
        margin-top: 0.65rem;
    }
    .dash-consult .dash-consult-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.25rem;
    }
    .dash-chip-more {
        font-size: 0.6875rem;
        color: #64748b;
        align-self: center;
        margin-left: 0.15rem;
    }
    .dash-skill-block { margin-bottom: 0.6rem; }
    .dash-skill-block:last-of-type { margin-bottom: 0.25rem; }
    .dash-card-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.65rem;
    }
    .dash-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dash-btn-primary {
        background: var(--hirevo-primary);
        color: #fff;
        border: none;
        box-shadow: 0 2px 10px rgba(11, 31, 59, 0.25);
    }
    .dash-btn-primary:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(11, 31, 59, 0.3); }
    .dash-btn-ghost {
        background: transparent;
        color: var(--hirevo-primary);
        border: 1.5px solid rgba(11, 31, 59, 0.2);
    }
    .dash-btn-ghost:hover { background: rgba(11, 31, 59, 0.06); color: var(--hirevo-primary); }
    .dash-card--referral .dash-btn-referral {
        background: #10b981;
        color: #fff;
        border: none;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.4rem 1rem;
        box-shadow: 0 3px 12px rgba(16, 185, 129, 0.3);
        cursor: pointer;
    }
    .dash-card--referral .dash-btn-referral:hover {
        color: #fff;
        background: #059669;
        transform: translateY(-1px);
    }
    .dash-referral-foot {
        font-size: 0.6875rem;
        color: rgba(255, 255, 255, 0.55);
        margin: 0.45rem 0 0;
    }
    .dash-source-hint {
        font-size: 0.625rem;
        color: #94a3b8;
        margin: 0.35rem 0 0;
    }

    /* Simplified upgrade path: full-width clickable rows */
    .dash-action-stack { display: flex; flex-direction: column; gap: 0.4rem; }
    .dash-action-tile {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.85rem;
        border-radius: 10px;
        text-decoration: none;
        color: #0f172a;
        border: 1px solid rgba(11, 31, 59, 0.1);
        background: #fff;
        transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }
    .dash-action-tile:hover {
        color: #0f172a;
        border-color: rgba(16, 185, 129, 0.45);
        box-shadow: 0 4px 14px rgba(11, 31, 59, 0.08);
        transform: translateY(-1px);
    }
    .dash-action-tile--masters {
        border-color: rgba(16, 185, 129, 0.35);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, #fff 55%);
    }
    .dash-action-tile__icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 10px;
        background: rgba(16, 185, 129, 0.18);
        color: #047857;
        display: grid;
        place-items: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .dash-action-tile__text { min-width: 0; flex: 1; font-size: 0.8125rem; line-height: 1.35; }
    .dash-action-tile__text strong { display: block; color: var(--hirevo-primary); margin-bottom: 0.15rem; }
    .dash-action-tile__sub { display: block; font-size: 0.6875rem; font-weight: 500; color: #64748b; margin-top: 0.15rem; }
    .dash-action-tile__chev { font-size: 1.25rem; color: #94a3b8; flex-shrink: 0; }
    .dash-action-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        padding: 0.55rem 0.85rem;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--hirevo-primary);
        background: #fff;
        border: 1px solid rgba(11, 31, 59, 0.12);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        box-sizing: border-box;
    }
    .dash-action-row:hover {
        color: var(--hirevo-primary);
        background: rgba(11, 31, 59, 0.05);
        border-color: rgba(11, 31, 59, 0.18);
    }
    .dash-action-row .mdi-chevron-right { font-size: 1.1rem; opacity: 0.45; flex-shrink: 0; }
    .dash-action-row--cta {
        background: var(--hirevo-primary);
        color: #fff;
        border: none;
        cursor: pointer;
        font-family: inherit;
        text-align: left;
        box-shadow: 0 2px 10px rgba(11, 31, 59, 0.2);
    }
    .dash-action-row--cta:hover {
        color: #fff;
        background: #0a1a35;
        transform: translateY(-1px);
    }
    @media (prefers-reduced-motion: reduce) {
        .dash-action-tile, .dash-action-row, .dash-action-row--cta { transition: none; }
        .dash-action-tile:hover, .dash-action-row--cta:hover { transform: none; }
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