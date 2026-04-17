@extends('layouts.app')

@section('body_class', 'page-job-openings')

@section('title', 'Job Openings')

@push('styles')
<style>
    /* Solid white nav on this page (theme glass bar picks up dark hero behind it) */
    body.page-job-openings .hirevo-navbar.navbar,
    body.page-job-openings #navbar.hirevo-navbar {
        background: #ffffff !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
    }
    body.page-job-openings .hirevo-navbar.navbar.nav-scrolled,
    body.page-job-openings #navbar.hirevo-navbar.nav-scrolled {
        background: #ffffff !important;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08) !important;
    }
    /* ── Page canvas ───────────────────────────────────────────── */
    .job-openings-page {
        --jo-ink: #0a0f1a;
        --jo-muted: #64748b;
        --jo-line: rgba(15, 23, 42, 0.07);
        --jo-mint: #10b981;
        --jo-cyan: #06b6d4;
        --jo-violet: #6366f1;
        --jo-glow: rgba(16, 185, 129, 0.42);
        --jo-surface: #f8fafc;
        --jo-card: #ffffff;
        min-height: 100vh;
        background:
            radial-gradient(ellipse 100% 80% at 50% -30%, rgba(99, 102, 241, 0.09), transparent 55%),
            radial-gradient(ellipse 70% 50% at 100% 20%, rgba(16, 185, 129, 0.07), transparent 45%),
            radial-gradient(ellipse 60% 40% at -5% 60%, rgba(6, 182, 212, 0.06), transparent 45%),
            linear-gradient(180deg, #eef2f7 0%, var(--jo-surface) 32%, #f1f5f9 100%);
        position: relative;
    }
    .job-openings-page::before {
        content: '';
        position: fixed;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='88' height='88' viewBox='0 0 88 88'%3E%3Cpath fill='%2394a3b8' fill-opacity='0.04' d='M0 0h44v44H0V0zm44 44h44v44H44V44z'/%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
    }
    .job-openings-page > .section { position: relative; z-index: 1; }

    /* ── Hero — compact, high-contrast strip ─────────────────────── */
    .jo-hero-band {
        position: relative;
        z-index: 2;
        isolation: isolate;
        overflow: hidden;
        border-radius: 0 0 clamp(14px, 2.5vw, 22px) clamp(14px, 2.5vw, 22px);
        /* Solid base so text never sits on the page grid if overlays fail */
        background-color: #0a1628;
        background-image:
            radial-gradient(circle 50% at 92% 0%, rgba(99, 102, 241, 0.5) 0%, transparent 45%),
            radial-gradient(ellipse 55% 100% at -10% 100%, rgba(16, 185, 129, 0.28) 0%, transparent 45%),
            linear-gradient(118deg, #0a1628 0%, #0f2137 42%, #0d3d38 100%);
        color: #f1f5f9;
        padding: 1.15rem 0 1.35rem;
        margin-bottom: 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        box-shadow: 0 12px 40px rgba(10, 22, 40, 0.35);
    }
    @media (min-width: 992px) {
        .jo-hero-band {
            padding: 1.35rem 0 1.5rem;
            margin-bottom: 0;
        }
    }
    .jo-hero-band::after {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='72' height='72' viewBox='0 0 72 72' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.03'%3E%3Cpath d='M0 0h36v36H0V0zm36 36h36v36H36V36z'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .jo-hero-inner { position: relative; z-index: 2; }
    .jo-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.28rem 0.75rem 0.28rem 0.4rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.18);
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #e2e8f0;
        margin-bottom: 0.55rem;
        backdrop-filter: blur(8px);
    }
    .jo-hero-kicker-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.65rem;
        height: 1.65rem;
        padding: 0 0.35rem;
        border-radius: 999px;
        background: linear-gradient(135deg, var(--jo-mint), var(--jo-cyan));
        color: #042f2e;
        font-size: 0.65rem;
        font-weight: 900;
        letter-spacing: 0;
        box-shadow: 0 0 24px var(--jo-glow);
    }
    .jo-hero-kicker-badge.is-live::before {
        content: '';
        width: 6px;
        height: 6px;
        margin-right: 5px;
        border-radius: 50%;
        background: #042f2e;
        animation: joLivePulse 2s ease-in-out infinite;
    }
    @keyframes joLivePulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.65; transform: scale(0.85); }
    }
    @media (prefers-reduced-motion: reduce) {
        .jo-hero-kicker-badge.is-live::before { animation: none; }
    }
    .jo-hero-title {
        font-size: clamp(1.2rem, 2vw + 0.65rem, 1.55rem);
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.03em;
        margin-bottom: 0.4rem;
        max-width: 22ch;
        color: #f8fafc;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
    }
    .jo-hero-title span {
        display: inline;
        color: #5eead4;
        background: none;
        -webkit-text-fill-color: unset;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    @supports (background-clip: text) {
        .jo-hero-title span {
            background: linear-gradient(105deg, #ecfdf5 0%, #5eead4 40%, #7dd3fc 90%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
        }
    }
    .jo-hero-lead {
        color: #cbd5e1;
        font-size: 0.8125rem;
        max-width: 36rem;
        line-height: 1.45;
        margin-bottom: 0;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    .jo-hero-stat {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-top: 0.6rem;
        padding: 0.35rem 0.85rem 0.35rem 0.55rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.14);
        font-size: 0.75rem;
        color: #e2e8f0;
        font-weight: 600;
        backdrop-filter: blur(8px);
    }
    .jo-hero-stat strong {
        font-size: 1.05rem;
        font-weight: 900;
        letter-spacing: -0.03em;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    .jo-hero-visual {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
        padding: 0.45rem;
    }
    .jo-hero-visual::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 18px;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.2), rgba(148, 163, 184, 0.05));
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.24),
            0 16px 36px rgba(2, 8, 23, 0.34);
        backdrop-filter: blur(8px);
    }
    @media (min-width: 992px) {
        .jo-hero-visual {
            justify-content: flex-end;
            min-height: 0;
            max-width: 360px;
            margin-left: auto;
        }
    }
    .jo-hero-orbit {
        position: absolute;
        width: min(280px, 72vw);
        height: min(280px, 72vw);
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: radial-gradient(circle at 30% 25%, rgba(255,255,255,0.14), transparent 52%);
        animation: joOrbit 22s linear infinite;
    }
    @keyframes joOrbit { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) {
        .jo-hero-orbit { animation: none; }
    }
    .jo-hero-visual img {
        position: relative;
        z-index: 1;
        width: clamp(190px, 34vw, 280px);
        max-height: 170px;
        object-fit: contain;
        filter: drop-shadow(0 10px 22px rgba(2, 8, 23, 0.42));
        transform: translateY(0);
        opacity: 0.98;
    }
    @media (min-width: 992px) {
        .jo-hero-visual img {
            width: clamp(220px, 24vw, 310px);
            max-height: 185px;
        }
    }

    /* ── Floating stack (search + panels) ─────────────────────── */
    .jo-float-search { position: relative; z-index: 3; }

    .jo-flash {
        border-radius: 18px;
        border: none !important;
        padding: 0.9rem 1.15rem !important;
        margin-top: 1rem !important;
        box-shadow: 0 12px 40px rgba(11, 31, 59, 0.1);
    }
    .jo-flash.alert-success {
        background: linear-gradient(135deg, rgba(236, 253, 245, 0.98), rgba(240, 253, 250, 0.95)) !important;
        border-left: 4px solid var(--jo-mint) !important;
        color: #065f46;
    }
    .jo-flash.alert-info {
        background: rgba(240, 249, 255, 0.97) !important;
        border-left: 4px solid #0ea5e9 !important;
        color: #0c4a6e;
    }

    .jo-personalize-banner {
        border-radius: 20px !important;
        border: 1px solid rgba(16, 185, 129, 0.28) !important;
        background:
            linear-gradient(125deg, rgba(255, 255, 255, 0.95) 0%, rgba(236, 253, 245, 0.88) 45%, rgba(224, 242, 254, 0.65) 100%) !important;
        box-shadow: 0 16px 48px rgba(15, 118, 110, 0.12);
        margin-top: 1rem !important;
        animation: joFadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) backwards;
    }
    .jo-personalize-banner strong { color: var(--jo-ink); }

    /* Resume match — bento strip */
    .jo-match-bento {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        align-items: center;
        margin-top: 1.15rem;
        padding: 1.15rem 1.25rem;
        border-radius: 24px;
        background: var(--jo-card);
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.9) inset,
            0 24px 56px -28px rgba(11, 31, 59, 0.18);
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .jo-match-bento {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
            padding: 1.25rem 1.5rem;
        }
    }
    .jo-match-bento::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(180deg, var(--jo-violet), var(--jo-mint), var(--jo-cyan));
        border-radius: 24px 0 0 24px;
    }
    .jo-match-bento.is-busy {
        opacity: 0.88;
        pointer-events: none;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15), 0 24px 56px -28px rgba(11, 31, 59, 0.18);
    }
    .jo-match-bento__icon {
        width: 3.1rem;
        height: 3.1rem;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(145deg, rgba(99, 102, 241, 0.18), rgba(16, 185, 129, 0.12));
        border: 1px solid rgba(99, 102, 241, 0.2);
        color: #4338ca;
        font-size: 1.35rem;
    }
    .jo-match-bento__title {
        font-size: 0.9375rem;
        font-weight: 800;
        color: var(--jo-ink);
        letter-spacing: -0.02em;
        margin-bottom: 0.2rem;
    }
    .jo-match-bento__hint {
        font-size: 0.8125rem;
        color: var(--jo-muted);
        line-height: 1.5;
        margin-bottom: 0;
        max-width: 36rem;
    }
    .jo-match-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        padding-left: 0.25rem;
    }
    @media (min-width: 768px) {
        .jo-match-actions { justify-content: flex-end; padding-left: 0; }
    }
    .jo-file-trigger {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        cursor: pointer;
        margin: 0;
    }
    .jo-file-trigger__input {
        position: absolute;
        width: 0.01px;
        height: 0.01px;
        opacity: 0;
        overflow: hidden;
        z-index: -1;
    }
    .jo-file-trigger__btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 700;
        color: var(--jo-ink);
        background: #f1f5f9;
        border: 1px solid rgba(15, 23, 42, 0.08);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
    }
    .jo-file-trigger:hover .jo-file-trigger__btn {
        background: #e2e8f0;
        border-color: rgba(99, 102, 241, 0.25);
    }
    .jo-file-trigger__input:focus-visible + .jo-file-trigger__btn {
        outline: 2px solid var(--jo-violet);
        outline-offset: 2px;
    }
    .jo-file-trigger__name {
        font-size: 0.75rem;
        color: var(--jo-muted);
        max-width: 160px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .jo-match-submit {
        border: none;
        padding: 0.58rem 1.25rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        color: #fff;
        background: linear-gradient(135deg, #4f46e5, #0d9488);
        box-shadow: 0 8px 24px rgba(79, 70, 229, 0.35);
        transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .jo-match-submit:hover {
        filter: brightness(1.06);
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(79, 70, 229, 0.38);
    }
    .jo-match-login {
        display: inline-flex;
        align-items: center;
        padding: 0.58rem 1.35rem;
        border-radius: 999px;
        font-size: 0.8125rem;
        font-weight: 800;
        border: none;
        background: linear-gradient(135deg, #4f46e5, #2563eb);
        color: #fff !important;
        text-decoration: none !important;
        box-shadow: 0 8px 28px rgba(37, 99, 235, 0.35);
        transition: transform 0.15s ease, box-shadow 0.2s ease;
    }
    .jo-match-login:hover {
        transform: translateY(-2px);
        color: #fff !important;
    }

    /* Search command bar */
    .jo-search-shell {
        margin-top: 1.25rem;
        padding: 1.35rem 1.35rem 1.45rem;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow:
            0 0 0 1px rgba(15, 23, 42, 0.04),
            0 28px 64px -32px rgba(11, 31, 59, 0.35);
    }
    @media (min-width: 768px) {
        .jo-search-shell { padding: 1.5rem 1.65rem 1.65rem; }
    }
    .jo-search-shell__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.1rem;
    }
    .jo-search-shell__head h2 {
        font-size: 0.6875rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: upperimage.pngcase;
        color: var(--jo-muted);
        margin: 0;
    }
    .jo-search-shell__head p {
        margin: 0.25rem 0 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--jo-ink);
        letter-spacing: -0.02em;
    }
    .jo-search-glass .form-control,
    .jo-search-shell .form-control {
        border: 1px solid rgba(11, 31, 59, 0.1);
        border-radius: 16px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        font-size: 0.9375rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: rgba(255, 255, 255, 0.92);
    }
    .jo-search-shell .form-control:focus {
        border-color: rgba(99, 102, 241, 0.45);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }
    .jo-search-shell .form-label {
        color: var(--jo-ink);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.68rem;
    }
    .jo-country-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem 0.75rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }
    .jo-country-row__label {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--jo-muted);
        width: 100%;
        margin-bottom: 0.1rem;
    }
    @media (min-width: 576px) {
        .jo-country-row__label { width: auto; margin-bottom: 0; margin-right: 0.25rem; }
    }
    .jo-country-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.85rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 750;
        text-decoration: none !important;
        color: var(--jo-ink) !important;
        background: #f1f5f9;
        border: 1px solid rgba(15, 23, 42, 0.08);
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
    }
    .jo-country-chip:hover {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.25);
        transform: translateY(-1px);
    }
    .jo-country-chip--active {
        background: linear-gradient(135deg, #312e81, #0f766e) !important;
        color: #fff !important;
        border-color: transparent !important;
        box-shadow: 0 6px 18px rgba(49, 46, 129, 0.28);
    }
    .jo-country-chip--ghost {
        background: transparent;
        border-style: dashed;
        font-weight: 700;
        color: var(--jo-muted) !important;
    }
    .jo-country-chip--ghost:hover {
        background: rgba(15, 23, 42, 0.04);
        color: var(--jo-ink) !important;
    }
    .jo-country-chip--ghost.jo-country-chip--active {
        background: rgba(15, 118, 110, 0.14) !important;
        color: #0f766e !important;
        border: 1px solid rgba(15, 118, 110, 0.35) !important;
        box-shadow: none !important;
    }
    .jo-hero-country-note {
        display: inline-block;
        margin-top: 0.35rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #a7f3d0;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
    }
    .jo-search-btn {
        padding: 0.8rem 1.15rem;
        font-weight: 800;
        border-radius: 16px !important;
        border: none;
        background: linear-gradient(135deg, #1e1b4b 0%, #0f766e 100%);
        letter-spacing: 0.02em;
        transition: transform 0.18s ease, box-shadow 0.22s ease, filter 0.2s ease;
        box-shadow: 0 10px 28px rgba(15, 118, 110, 0.28);
    }
    .jo-search-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
        box-shadow: 0 16px 36px rgba(15, 118, 110, 0.36);
    }

    .jo-layout-row { padding-top: 0.35rem; }

    .jo-filters-card {
        border-radius: 24px;
        border: 1px solid var(--jo-line) !important;
        background: var(--jo-card);
        box-shadow: 0 16px 48px -24px rgba(11, 31, 59, 0.15);
        overflow: hidden;
    }
    .jo-filters-card::before {
        content: '';
        display: block;
        height: 4px;
        background: linear-gradient(90deg, var(--jo-violet), var(--jo-mint), var(--jo-cyan));
    }
    .jo-filters-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .jo-filters-head h2 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 850;
        color: var(--jo-ink);
        letter-spacing: -0.02em;
        margin: 0;
    }
    .jo-filters-head h2 i {
        width: 2rem;
        height: 2rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, rgba(99, 102, 241, 0.12), rgba(16, 185, 129, 0.08));
        color: #4f46e5;
        font-size: 1rem;
    }
    .jo-filter-reset {
        font-size: 0.75rem;
        font-weight: 800;
        text-decoration: none;
        color: #0d9488 !important;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.12);
        transition: background 0.2s ease;
    }
    .jo-filter-reset:hover { background: rgba(16, 185, 129, 0.2); }

    .jo-filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.95rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 650;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
        border: 1px solid transparent;
    }
    .jo-filter-chip:not(.jo-filter-chip--active) {
        background: #f1f5f9;
        color: #475569;
    }
    .jo-filter-chip:not(.jo-filter-chip--active):hover {
        background: rgba(99, 102, 241, 0.1);
        color: #3730a3;
        transform: translateY(-1px);
    }
    .jo-filter-chip--active {
        background: linear-gradient(135deg, #312e81, #0f766e) !important;
        color: #fff !important;
        box-shadow: 0 8px 22px rgba(49, 46, 129, 0.35);
        border-color: transparent !important;
    }

    .jo-results-bar {
        border-radius: 18px;
        padding: 0.9rem 1.2rem;
        background: var(--jo-card);
        border: 1px solid var(--jo-line);
        box-shadow: 0 4px 24px rgba(11, 31, 59, 0.05);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .jo-results-count {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.65rem 0.25rem 0.35rem;
        border-radius: 999px;
        background: #f1f5f9;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--jo-ink);
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .jo-results-count i { color: var(--jo-mint); font-size: 1rem; }

    .jo-sort-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--jo-muted);
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: rgba(99, 102, 241, 0.06);
        border: 1px solid rgba(99, 102, 241, 0.12);
    }
    .jo-sort-chip i { color: var(--jo-violet); }

    .jo-job-list { min-height: 140px; }

    .jo-job-card {
        border-radius: 22px !important;
        border: 1px solid var(--jo-line) !important;
        background: var(--jo-card);
        box-shadow: 0 4px 20px rgba(11, 31, 59, 0.045);
        transition: border-color 0.28s ease, box-shadow 0.35s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        position: relative;
    }
    .jo-job-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 22px;
        padding: 1px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.4), rgba(16, 185, 129, 0.35));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    .jo-job-card:hover {
        border-color: rgba(16, 185, 129, 0.22) !important;
        box-shadow: 0 20px 48px -20px rgba(11, 31, 59, 0.18);
        transform: translateY(-4px);
    }
    .jo-job-card:hover::before { opacity: 1; }

    .jo-job-card:has(.jo-fit-pill) {
        border-color: rgba(16, 185, 129, 0.18) !important;
        box-shadow: 0 8px 32px -16px rgba(16, 185, 129, 0.25);
    }

    .jo-co-avatar {
        border-radius: 18px !important;
        background: linear-gradient(145deg, #e0e7ff 0%, #d1fae5 100%) !important;
        border: 1px solid rgba(79, 70, 229, 0.12) !important;
        font-weight: 900 !important;
        color: #3730a3 !important;
    }
    .jo-job-title {
        font-size: 1.08rem !important;
        font-weight: 850 !important;
        letter-spacing: -0.025em !important;
    }
    .jo-meta-pill {
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    .jo-meta-pill--accent {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.14), rgba(56, 189, 248, 0.1)) !important;
        border-color: rgba(16, 185, 129, 0.2) !important;
    }

    .jo-fit-pill {
        font-size: 0.7rem;
        font-weight: 900;
        padding: 0.28rem 0.72rem;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(99, 102, 241, 0.12));
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.3);
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .jo-apply-btn {
        border-radius: 999px !important;
        font-weight: 800 !important;
        letter-spacing: 0.02em;
        background: linear-gradient(135deg, #4f46e5, #0d9488) !important;
        border: none !important;
    }

    .jo-load-more-btn {
        font-weight: 800;
        padding: 0.85rem 2.25rem;
        border-radius: 999px;
        border: 2px solid rgba(15, 23, 42, 0.08);
        background: var(--jo-card);
        color: var(--jo-ink);
        transition: border-color 0.2s ease, box-shadow 0.25s ease, transform 0.18s ease;
    }
    .jo-load-more-btn:hover:not(:disabled) {
        border-color: rgba(99, 102, 241, 0.35);
        box-shadow: 0 12px 36px rgba(99, 102, 241, 0.15);
        transform: translateY(-2px);
    }

    .jo-load-spinner {
        width: 1.15rem;
        height: 1.15rem;
        border: 2px solid rgba(11, 31, 59, 0.1);
        border-top-color: var(--jo-violet);
        border-radius: 50%;
        animation: joSpin 0.65s linear infinite;
        display: none;
        vertical-align: middle;
        margin-right: 0.35rem;
    }
    .jo-load-more-btn.is-loading .jo-load-spinner { display: inline-block; }
    @keyframes joSpin { to { transform: rotate(360deg); } }

    .jo-empty-state {
        border-radius: 28px;
        border: 2px dashed rgba(15, 23, 42, 0.1) !important;
        background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248, 250, 252, 0.98)) !important;
        box-shadow: 0 20px 56px -32px rgba(11, 31, 59, 0.12);
    }
    .jo-empty-state .jo-empty-icon {
        width: 88px;
        height: 88px;
        border-radius: 24px;
        background: linear-gradient(145deg, #e0e7ff, #d1fae5);
        border: 1px solid rgba(99, 102, 241, 0.15);
    }

    @keyframes joFadeUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .jo-animate-in { animation: joFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) backwards; }
    .jo-card-enter .jo-job-card { animation: joFadeUp 0.48s cubic-bezier(0.22, 1, 0.36, 1) backwards; }

    @media (prefers-reduced-motion: reduce) {
        .jo-load-spinner { animation: none; border-top-color: var(--jo-violet); }
        .jo-job-card, .jo-job-card:hover, .jo-filter-chip, .jo-search-btn, .jo-apply-btn, .jo-load-more-btn,
        .jo-match-submit, .jo-match-login, .jo-animate-in, .jo-card-enter .jo-job-card {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }
        .jo-job-card::before { display: none; }
    }
</style>
@endpush

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
        $queryAll = array_filter(request()->query(), fn ($v) => $v !== '' && $v !== null);
        $countryFilter = $countryFilter ?? '';
        $countryLabels = $countryLabels ?? config('hirevo.job_openings_country_labels', []);
        $hasActiveFilters = (isset($searchQuery) && $searchQuery !== '')
            || (isset($searchLocation) && $searchLocation !== '')
            || (isset($filterJobType) && $filterJobType !== '')
            || (isset($filterWorkType) && $filterWorkType !== '')
            || ($countryFilter !== '');
    @endphp
    <section class="section py-0 job-openings-page">
        <div class="jo-hero-band">
            <div class="container jo-hero-inner">
                <div class="row align-items-center g-3 g-lg-4">
                    <div class="col-lg-7">
                        <div class="jo-hero-kicker">
                            <span class="jo-hero-kicker-badge is-live">Live</span>
                            Open roles
                        </div>
                        <h1 class="jo-hero-title">Job openings that <span>fit</span> you</h1>
                        <p class="jo-hero-lead">Search and filter below  upload your resume to reorder this list for your profile.</p>
                        @if($jobs->total() > 0)
                            <p class="jo-hero-stat mb-0">
                                <strong id="jo-hero-count">{{ $jobs->total() }}</strong>
                                <span>{{ Str::plural('opening', $jobs->total()) }} here</span>
                            </p>
                        @endif
                    </div>
                    <div class="col-lg-5 d-none d-md-flex justify-content-lg-end">
                        <div class="jo-hero-visual">
                            <div class="jo-hero-orbit d-none" aria-hidden="true"></div>
                            <img src="{{ $siteImg('Image2.jpeg') }}" alt="Job search dashboard preview" loading="lazy" width="310" height="185">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container jo-float-search pb-4 pb-lg-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show jo-flash" role="alert">
                    <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show jo-flash" role="alert">
                    <i class="uil uil-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!empty($jobsPersonalized) && $personalizeResumeId)
                <div class="alert jo-personalize-banner d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 px-3 px-md-4" role="status">
                    <span class="small mb-0 pe-md-2">
                        <i class="uil uil-sparkles text-success me-1"></i><strong>Smart matches, ranked by AI.</strong>
                        @if(!empty($jobMatchAiRanked))
                            <span class="text-muted">Your best matches ranked first, with tags highlighting skill alignment.</span>
                        @else
                            <span class="text-muted">Ordered for your CV. Turn on AI in your stack for deeper ranking.</span>
                        @endif
                    </span>
                    <span class="d-flex flex-wrap gap-2">
                        <a href="{{ route('resume.results', $personalizeResumeId) }}" class="btn btn-sm btn-dark rounded-pill px-3 fw-700">Full resume report</a>
                        <a href="{{ route('job-openings', array_merge(request()->except('page'), ['clear_personalization' => 1])) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-700">Newest first</a>
                    </span>
                </div>
            @endif

            <div class="jo-match-bento" id="jo-resume-match-bar">
                <div class="d-flex align-items-start gap-3 min-w-0">
                    <span class="jo-match-bento__icon flex-shrink-0" aria-hidden="true"><i class="uil uil-brain"></i></span>
                    <div class="min-w-0">
                        <p class="jo-match-bento__title mb-0">Match this board to your resume</p>
                        <p class="jo-match-bento__hint mb-0">Same pipeline as Resume Score: upload a PDF, we analyze it, then reorder these listings for you  without leaving the page.</p>
                    </div>
                </div>
                <div class="jo-match-actions">
                @auth
                    @if(auth()->user()->isCandidate())
                        <form action="{{ route('resume.upload.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2 jo-resume-upload-form" id="jo-resume-upload-form">
                            @csrf
                            <input type="hidden" name="return_to" value="job-openings">
                            <input type="hidden" name="jo_q" id="jo-hidden-q" value="{{ $searchQuery ?? '' }}">
                            <input type="hidden" name="jo_location" id="jo-hidden-location" value="{{ $searchLocation ?? '' }}">
                            <input type="hidden" name="jo_job_type" id="jo-hidden-job-type" value="{{ $filterJobType ?? '' }}">
                            <input type="hidden" name="jo_work_type" id="jo-hidden-work-type" value="{{ $filterWorkType ?? '' }}">
                            <input type="hidden" name="jo_country" id="jo-hidden-country" value="{{ $countryFilter }}">
                            <label class="jo-file-trigger mb-0">
                                <input type="file" name="resume" accept="application/pdf,.pdf" required class="jo-file-trigger__input" id="jo-resume-file-input" aria-label="Choose resume PDF">
                                <span class="jo-file-trigger__btn"><i class="uil uil-import"></i> Choose PDF</span>
                                <span class="jo-file-trigger__name" id="jo-file-name">No file yet</span>
                            </label>
                            <button type="submit" class="jo-match-submit">Analyze &amp; reorder</button>
                        </form>
                    @else
                        <span class="small text-muted mb-0 fw-600">Resume matching is available for candidate accounts.</span>
                    @endif
                @else
                    <a href="{{ route('login', ['redirect' => url()->full()]) }}" class="jo-match-login"><i class="uil uil-sign-in-alt me-1"></i>Sign in to upload</a>
                @endauth
                </div>
            </div>

            <form action="{{ route('job-openings') }}" method="GET" class="jo-search-shell jo-search-glass mt-3">
                <div class="jo-search-shell__head">
                    <div>
                        <h2>Search</h2>
                        <p>Zero in on role, stack, or place</p>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="job-openings-q" class="form-label mb-1">Keywords</label>
                        <div class="position-relative">
                            <i class="uil uil-search position-absolute text-muted" style="left: 1.1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                            <input type="search" name="q" id="job-openings-q" class="form-control" placeholder="Title, stack, company…" value="{{ old('q', $searchQuery ?? '') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="job-openings-location" class="form-label mb-1">Location</label>
                        <div class="position-relative">
                            <i class="uil uil-map-marker position-absolute text-muted" style="left: 1.1rem; top: 50%; transform: translateY(-50%); pointer-events: none;"></i>
                            <input type="text" name="location" id="job-openings-location" class="form-control" placeholder="City, remote, region…" value="{{ old('location', $searchLocation ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100 jo-search-btn"><i class="uil uil-search me-1"></i>Search</button>
                    </div>
                </div>
                <input type="hidden" name="job_type" value="{{ $filterJobType ?? '' }}">
                <input type="hidden" name="work_location_type" value="{{ $filterWorkType ?? '' }}">
                @if($countryFilter !== '')
                    <input type="hidden" name="country" value="{{ $countryFilter }}">
                @endif
                <div class="jo-country-row w-100">
                    <span class="jo-country-row__label"><i class="uil uil-globe me-1"></i>Country</span>
                    @foreach(['ca', 'us', 'gb', 'ae'] as $countryCode)
                        @php
                            $meta = $countryLabels[$countryCode] ?? ['label' => strtoupper($countryCode), 'emoji' => ''];
                            $countryQuery = array_diff_key($queryAll, array_flip(['country', 'page']));
                            $countryQuery['country'] = $countryCode;
                        @endphp
                        <a href="{{ route('job-openings', $countryQuery) }}"
                           class="jo-country-chip {{ $countryFilter === $countryCode ? 'jo-country-chip--active' : '' }}">
                            <span aria-hidden="true">{{ $meta['emoji'] ?? '' }}</span>
                            {{ $meta['label'] ?? $countryCode }}
                        </a>
                    @endforeach
                    <a href="{{ route('job-openings', array_diff_key($queryAll, array_flip(['country', 'page']))) }}"
                       class="jo-country-chip jo-country-chip--ghost {{ $countryFilter === '' ? 'jo-country-chip--active' : '' }}">All countries</a>
                </div>
            </form>

            <div class="row jo-layout-row">
                <div class="col-lg-3 mb-4 mb-lg-0 order-2 order-lg-1">
                    <div class="card jo-filters-card border-0 sticky-top" style="top: 92px;">
                        <div class="card-body p-4">
                            <div class="jo-filters-head">
                                <h2><i class="uil uil-slider-h"></i> Refine</h2>
                                @if($hasActiveFilters)
                                    <a href="{{ route('job-openings') }}" class="jo-filter-reset">Reset</a>
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
                    <div class="jo-results-bar mb-3">
                        <p class="mb-0 small d-flex flex-wrap align-items-center gap-2" style="color: var(--jo-muted);">
                            @if($jobs->total() > 0)
                                <span class="jo-results-count"><i class="uil uil-layers-alt"></i> List</span>
                                <span id="jo-showing-line">
                                    <strong class="text-dark" id="jo-range-from">{{ $jobs->firstItem() }}</strong>–<strong class="text-dark" id="jo-range-to">{{ $jobs->lastItem() }}</strong>
                                    of <strong class="text-dark" id="jo-range-total">{{ $jobs->total() }}</strong>
                                </span>
                                @if($hasActiveFilters)
                                    <span class="d-none d-sm-inline text-muted">· filtered</span>
                                @endif
                            @else
                                <span class="fw-800 text-dark">No roles in this view</span>
                            @endif
                        </p>
                        <span class="small jo-sort-chip d-none d-sm-inline" id="jo-sort-label">
                            @if(!empty($jobsPersonalized))
                                <i class="uil uil-chart-line"></i>Resume-ranked
                            @else
                                <i class="uil uil-sort-amount-down"></i>Newest first
                            @endif
                        </span>
                    </div>

                    <div class="jo-job-list" id="jo-job-list">
                        @forelse($jobs as $job)
                            <div class="jo-animate-in" style="animation-delay: {{ min(0.03 * $loop->iteration, 0.24) }}s;">
                                @include('hirevo.partials.employer-job-card', ['job' => $job, 'appliedIds' => $appliedIds ?? [], 'jobMatchScores' => $jobMatchScores ?? []])
                            </div>
                        @empty
                            <div class="card border-0 jo-filters-card jo-empty-state text-center py-5 px-3">
                                <div class="card-body py-5">
                                    <div class="jo-empty-icon d-inline-flex align-items-center justify-content-center mb-4">
                                        <i class="uil uil-rocket" style="font-size: 2rem; color: #4f46e5;"></i>
                                    </div>
                                    <h2 class="h5 fw-bold mb-2" style="color: var(--jo-ink); letter-spacing:-0.02em;">Nothing in frame</h2>
                                    @if($hasActiveFilters)
                                        <p class="text-muted mb-4 mx-auto" style="max-width: 420px;">Try widening keywords or reset filters — great roles might be one click away.</p>
                                        <div class="d-flex flex-wrap justify-content-center gap-2">
                                            <a href="{{ route('job-openings') }}" class="btn btn-primary rounded-pill px-4 jo-apply-btn">View all openings</a>
                                            <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-700">Home</a>
                                        </div>
                                    @else
                                        <p class="text-muted mb-4">Fresh posts land often — bookmark this page or upload a resume for alerts elsewhere in Hirevo.</p>
                                        <a href="{{ route('home') }}" class="btn btn-primary rounded-pill px-4 jo-apply-btn">Explore Hirevo</a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($jobs->hasPages())
                        <div class="text-center mt-4 pt-1" id="jo-load-wrap">
                            <button type="button" class="jo-load-more-btn" id="jo-load-more" data-next-url="{{ $jobs->nextPageUrl() }}">
                                <span class="jo-load-spinner" aria-hidden="true"></span>
                                <span class="jo-load-label">Load more roles</span>
                            </button>
                            <p class="small text-muted mt-2 mb-0 d-none" id="jo-end-msg">That’s everything for now.</p>
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
    var uploadForm = document.getElementById('jo-resume-upload-form');
    var matchBar = document.getElementById('jo-resume-match-bar');
    var fileInput = document.getElementById('jo-resume-file-input');
    var fileNameEl = document.getElementById('jo-file-name');

    if (fileInput && fileNameEl) {
        fileInput.addEventListener('change', function () {
            var f = fileInput.files && fileInput.files[0];
            fileNameEl.textContent = f ? f.name : 'No file yet';
        });
    }

    if (uploadForm && matchBar) {
        uploadForm.addEventListener('submit', function () {
            var q = document.getElementById('job-openings-q');
            var loc = document.getElementById('job-openings-location');
            var hq = document.getElementById('jo-hidden-q');
            var hl = document.getElementById('jo-hidden-location');
            if (q && hq) hq.value = q.value || '';
            if (loc && hl) hl.value = loc.value || '';
            matchBar.classList.add('is-busy');
        });
    }
    @if(session('success') && !empty($jobsPersonalized))
    document.addEventListener('DOMContentLoaded', function () {
        var list = document.getElementById('jo-job-list');
        if (list) {
            setTimeout(function () {
                list.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 200);
        }
    });
    @endif
}());
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
        if (label) label.textContent = loading ? 'Loading…' : 'Load more roles';
image.png    }

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
