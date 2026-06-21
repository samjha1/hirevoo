<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    @include('partials.seo-head')
    @include('partials.meta-pixel')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f2a50">
    <link rel="shortcut icon" href="{{ asset($theme.'/assets/images/favicon.ico') }}">
    <!-- DM Sans: Clean, modern, readable -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset($theme.'/assets/css/bootstrap.min.css') }}">
    <link href="{{ asset($theme.'/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset($theme.'/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/hirevo-theme.css') }}" rel="stylesheet">

    <style>
        /* ─── Tokens ─────────────────────────────────────────── */
        :root {
            --sidebar-w: 256px;
            --topbar-h: 64px;
            /* Main column horizontal inset (topbar + content); keep small so wide screens use width */
            --ec-inline: 0.875rem;

            --ink-900: #0d1117;
            --ink-700: #1f2937;
            --ink-500: #4b5563;
            --ink-300: #9ca3af;
            --ink-100: #f3f4f6;
            --ink-50:  #f9fafb;

            --surface: #ffffff;
            --surface-2: #f5f7fa;
            --border: #e5e8ee;
            --border-soft: #f0f2f5;

            --brand: #0f2a50;
            --brand-mid: #163d73;
            --brand-light: rgba(15,42,80,.06);
            --brand-glow: rgba(15,42,80,.12);

            --accent: #2563eb;
            --accent-soft: #eff4ff;
            --accent-glow: rgba(37,99,235,.15);

            --green: #16a34a;
            --green-soft: #f0fdf4;
            --red: #dc2626;
            --red-soft: #fef2f2;
            --amber: #d97706;
            --amber-soft: #fffbeb;

            --shadow-xs: 0 1px 2px rgba(0,0,0,.04);
            --shadow-sm: 0 1px 4px rgba(0,0,0,.06), 0 2px 8px rgba(0,0,0,.04);
            --shadow-md: 0 4px 16px rgba(0,0,0,.06), 0 1px 4px rgba(0,0,0,.04);
            --shadow-lg: 0 8px 32px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.04);

            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-pill: 999px;

            --font: 'DM Sans', system-ui, sans-serif;
            --font-mono: 'DM Mono', monospace;

            --transition: 0.18s cubic-bezier(.4,0,.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font);
            font-size: 0.9375rem;
            background: var(--surface-2);
            color: var(--ink-700);
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── Layout ─────────────────────────────────────────── */
        .ew { display: flex; min-height: 100vh; }
        .em { flex: 1; min-width: 0; margin-left: var(--sidebar-w); display: flex; flex-direction: column; min-height: 100vh; }

        /* ─── Sidebar ────────────────────────────────────────── */
        .es {
            width: var(--sidebar-w);
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 40;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #0a1f3d 0%, #0f2a50 42%, #0c2347 100%);
            border-right: 1px solid rgba(255,255,255,.06);
            box-shadow: 4px 0 24px rgba(15, 42, 80, 0.12);
        }

        .es::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(160deg, rgba(255,255,255,.06) 0%, transparent 45%),
                radial-gradient(ellipse 120% 80% at 0% 0%, rgba(96,165,250,.12), transparent 55%);
            pointer-events: none;
        }
        .es::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 180px;
            background: radial-gradient(ellipse 100% 100% at 50% 100%, rgba(37,99,235,.14), transparent 70%);
            pointer-events: none;
        }

        /* ── Brand ── */
        .es-brand {
            flex-shrink: 0;
            padding: 1.125rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
        }
        .es-brand a {
            display: block;
            flex: 1;
            min-width: 0;
            text-decoration: none;
        }
        .es-brand img {
            display: block;
            width: 100%;
            max-width: 190px;
            height: auto;
            object-fit: contain;
            object-position: left center;
        }
        .es-brand-close {
            background: none;
            border: none;
            color: rgba(255,255,255,.6);
            cursor: pointer;
            padding: .375rem;
            border-radius: var(--radius-sm);
            display: none;
            line-height: 1;
            transition: color var(--transition), background var(--transition);
        }
        .es-brand-close:hover { color: #fff; background: rgba(255,255,255,.1); }

        /* ── Nav scroll area ── */
        .es-body {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,.18) transparent;
        }
        .es-body::-webkit-scrollbar { width: 5px; }
        .es-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: var(--radius-pill); }

        .es-section-label {
            font-size: .6875rem;
            font-weight: 600;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
            padding: 1rem 1.375rem .375rem;
        }

        .es-nav { list-style: none; margin: 0; padding: 0 .625rem; }
        .es-nav li { margin-bottom: .125rem; }

        .es-link {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .55rem .7rem;
            border-radius: var(--radius);
            color: rgba(255,255,255,.78);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: background var(--transition), color var(--transition), transform var(--transition), box-shadow var(--transition);
            position: relative;
            border: 1px solid transparent;
        }
        .es-link .es-icon-wrap {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            transition: background var(--transition), border-color var(--transition);
        }
        .es-link .es-icon {
            font-size: 1.05rem;
            opacity: .9;
        }
        .es-link:hover {
            color: #fff;
            background: rgba(255,255,255,.07);
            border-color: rgba(255,255,255,.1);
            transform: translateX(2px);
        }
        .es-link:hover .es-icon-wrap {
            background: rgba(255,255,255,.1);
            border-color: rgba(255,255,255,.14);
        }
        .es-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(255,255,255,.14) 0%, rgba(255,255,255,.08) 100%);
            border-color: rgba(255,255,255,.16);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(0,0,0,.12);
        }
        .es-link.active .es-icon-wrap {
            background: rgba(255,255,255,.16);
            border-color: rgba(255,255,255,.2);
        }
        .es-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18%;
            height: 64%;
            width: 3px;
            border-radius: 0 var(--radius-pill) var(--radius-pill) 0;
            background: linear-gradient(180deg, #93c5fd, #60a5fa);
            box-shadow: 0 0 10px rgba(96,165,250,.45);
        }
        .es-link.disabled-link {
            opacity: .38;
            cursor: not-allowed;
            pointer-events: none;
        }

        .es-divider {
            height: 1px;
            background: rgba(255,255,255,.08);
            margin: .625rem .625rem;
        }

        /* ── Sidebar account wallet ── */
        .es-wallet {
            flex-shrink: 0;
            position: relative;
            z-index: 1;
            margin: .5rem .75rem .875rem;
            padding: .9rem;
            border-radius: var(--radius-lg);
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
        }
        .es-wallet__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            margin-bottom: .75rem;
        }
        .es-wallet__title {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: rgba(255,255,255,.45);
        }
        .es-wallet__status {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: .2rem .45rem;
            border-radius: var(--radius-pill);
        }
        .es-wallet__status--active {
            color: #86efac;
            background: rgba(22,163,74,.2);
            border: 1px solid rgba(22,163,74,.35);
        }
        .es-wallet__status--inactive {
            color: #fcd34d;
            background: rgba(245,158,11,.15);
            border: 1px solid rgba(245,158,11,.3);
        }
        .es-wallet__status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }
        .es-wallet__plan {
            display: block;
            font-size: .95rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.25;
            margin-bottom: .25rem;
            letter-spacing: -.02em;
        }
        .es-wallet__plan--muted { color: rgba(255,255,255,.55); font-weight: 600; font-size: .85rem; }
        .es-wallet__meta {
            font-size: .7rem;
            color: rgba(255,255,255,.5);
            margin-bottom: .75rem;
            line-height: 1.4;
        }
        .es-wallet__stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .5rem;
            margin-bottom: .75rem;
        }
        .es-wallet__stat {
            padding: .5rem .55rem;
            border-radius: var(--radius-sm);
            background: rgba(0,0,0,.15);
            border: 1px solid rgba(255,255,255,.08);
        }
        .es-wallet__stat-label {
            display: block;
            font-size: .6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgba(255,255,255,.4);
            margin-bottom: .15rem;
        }
        .es-wallet__stat-value {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            font-variant-numeric: tabular-nums;
            line-height: 1.1;
        }
        .es-wallet__stat-value--low { color: #fca5a5; }
        .es-wallet__stat-value--launch { color: #fcd34d; font-size: .78rem; font-weight: 600; }
        .es-wallet__btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            width: 100%;
            padding: .5rem .75rem;
            border-radius: var(--radius-sm);
            font-size: .78rem;
            font-weight: 600;
            text-decoration: none;
            transition: all var(--transition);
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        .es-wallet__btn:hover {
            background: rgba(255,255,255,.18);
            border-color: rgba(255,255,255,.35);
            color: #fff;
        }
        .es-wallet__btn--urgent {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-color: transparent;
        }
        .es-wallet__btn--urgent:hover {
            filter: brightness(1.06);
        }

        /* ─── Topbar ─────────────────────────────────────────── */
        .et {
            height: var(--topbar-h);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 var(--ec-inline);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 30;
            box-shadow: var(--shadow-xs);
            flex-shrink: 0;
        }

        .et-left { display: flex; align-items: center; gap: .75rem; }

        .et-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: .4rem;
            border-radius: var(--radius-sm);
            color: var(--ink-500);
            transition: all var(--transition);
            line-height: 1;
        }
        .et-toggle:hover { background: var(--ink-100); color: var(--ink-700); }

        .et-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ink-900);
            letter-spacing: -.01em;
        }

        .et-right { display: flex; align-items: center; gap: .625rem; }

        .et-plan-pill,
        .et-credits-pill {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .35rem .8rem .35rem .45rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-pill);
            text-decoration: none;
            font-size: .8125rem;
            color: var(--ink-700);
            transition: all var(--transition);
            box-shadow: var(--shadow-xs);
            max-width: 220px;
        }
        .et-plan-pill:hover,
        .et-credits-pill:hover {
            border-color: #c7d2fe;
            box-shadow: var(--shadow-sm);
            color: var(--ink-900);
        }
        .et-plan-pill__icon,
        .et-credits-pill__icon {
            width: 1.65rem;
            height: 1.65rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: .9rem;
        }
        .et-plan-pill__icon { background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #16a34a; }
        .et-plan-pill__icon--launch { background: linear-gradient(135deg, #fffbeb, #fef3c7); color: #d97706; }
        .et-plan-pill__icon--none { background: var(--amber-soft); color: var(--amber); }
        .et-credits-pill__icon { background: linear-gradient(135deg, #fff7ed, #ffedd5); color: #d97706; }
        .et-plan-pill__text,
        .et-credits-pill__text {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.15;
        }
        .et-plan-pill__label,
        .et-credits-pill__label {
            font-size: .6rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ink-300);
        }
        .et-plan-pill__value,
        .et-credits-pill__value {
            font-size: .8125rem;
            font-weight: 700;
            color: var(--ink-900);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }
        .et-plan-pill__value--muted { color: var(--ink-500); font-weight: 600; }
        .et-credits-pill__value { font-variant-numeric: tabular-nums; }
        @media (max-width: 767.98px) {
            .et-plan-pill__label,
            .et-credits-pill__label { display: none; }
            .et-plan-pill,
            .et-credits-pill { padding: .35rem .65rem .35rem .4rem; max-width: none; }
        }

        /* Avatar dropdown */
        .et-user { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .et-user.et-user-trigger {
            gap: .5rem;
            padding: .3rem .65rem .3rem .4rem;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border);
            background: linear-gradient(180deg, #fff 0%, var(--ink-50) 100%);
            box-shadow: var(--shadow-xs);
            transition: border-color var(--transition), box-shadow var(--transition), transform var(--transition);
        }
        .et-user.et-user-trigger:hover {
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: var(--shadow-sm);
            transform: translateY(-1px);
        }
        .et-user-initials-badge {
            width: 38px;
            height: 38px;
            font-size: .72rem;
        }
        .et-user-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 0;
            line-height: 1.2;
        }
        .et-user-fullname {
            font-size: .875rem;
            font-weight: 600;
            color: var(--ink-900);
            letter-spacing: -.02em;
            max-width: min(11rem, 42vw);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .et-user-sub {
            font-size: .65rem;
            font-weight: 500;
            color: var(--ink-500);
            letter-spacing: .02em;
        }
        .et-user-chevron {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(15, 23, 42, 0.06);
            color: var(--ink-300);
            font-size: .9rem;
            transition: background var(--transition), color var(--transition);
        }
        .et-user-trigger:hover .et-user-chevron {
            background: rgba(37, 99, 235, 0.12);
            color: var(--accent);
        }

        /* ─── Content area ───────────────────────────────────── */
        .ec {
            flex: 1;
            width: 100%;
            max-width: none;
            padding: 1.25rem var(--ec-inline) 1.5rem;
            box-sizing: border-box;
        }
        @media (min-width: 768px) {
            :root { --ec-inline: 1rem; }
        }
        @media (min-width: 1200px) {
            :root { --ec-inline: 1.25rem; }
        }
        @media (min-width: 1600px) {
            :root { --ec-inline: 1.5rem; }
        }

        /* ─── Alerts ─────────────────────────────────────────── */
        .ec-alert {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .875rem 1rem;
            border-radius: var(--radius);
            font-size: .9rem;
            margin-bottom: 1rem;
            border: 1px solid;
            animation: slideIn .2s ease;
        }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
        .ec-alert.success { background: var(--green-soft); border-color: #bbf7d0; color: #15803d; }
        .ec-alert.info { background: var(--accent-soft); border-color: #bfdbfe; color: #1d4ed8; }
        .ec-alert.error { background: var(--red-soft); border-color: #fecaca; color: var(--red); }
        .ec-alert-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            opacity: .6;
            padding: .1rem .2rem;
            border-radius: 4px;
            line-height: 1;
            color: inherit;
            flex-shrink: 0;
        }
        .ec-alert-close:hover { opacity: 1; }

        /* ─── Backdrop (mobile) ──────────────────────────────── */
        .eb {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.45);
            z-index: 39;
            backdrop-filter: blur(2px);
        }
        .eb.show { display: block; }

        /* ─── Reusable card ──────────────────────────────────── */
        .hcard {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        /* ─── Tabs ───────────────────────────────────────────── */
        .h-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .375rem;
            margin-bottom: 1.25rem;
            background: var(--surface);
            padding: .375rem;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-xs);
        }
        .h-tabs .h-tab {
            padding: .5rem .9rem;
            font-size: .875rem;
            color: var(--ink-500);
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: var(--radius-sm);
            background: transparent;
            transition: all var(--transition);
            font-weight: 450;
        }
        .h-tabs .h-tab:hover {
            color: var(--brand);
            background: var(--brand-light);
        }
        .h-tabs .h-tab.active {
            color: var(--brand);
            font-weight: 600;
            background: var(--brand-light);
            border-color: var(--brand-glow);
        }

        /* ─── Job cards ──────────────────────────────────────── */
        .h-job-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.125rem 1.25rem;
            transition: box-shadow var(--transition), border-color var(--transition), transform var(--transition);
        }
        .h-job-card:hover {
            box-shadow: var(--shadow-md);
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        /* ─── Status badges ──────────────────────────────────── */
        .hbadge {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            font-size: .75rem;
            font-weight: 600;
            padding: .25rem .625rem;
            border-radius: var(--radius-pill);
            line-height: 1;
        }
        .hbadge::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
        .hbadge.active { background: var(--green-soft); color: var(--green); }
        .hbadge.active::before { background: var(--green); }
        .hbadge.closed { background: var(--red-soft); color: var(--red); }
        .hbadge.closed::before { background: var(--red); }
        .hbadge.draft { background: var(--amber-soft); color: var(--amber); }
        .hbadge.draft::before { background: var(--amber); }
        .hbadge.pending { background: var(--ink-100); color: var(--ink-500); }
        .hbadge.pending::before { background: var(--ink-300); }

        /* ─── Responsive ─────────────────────────────────────── */
        @media (max-width: 991.98px) {
            .es {
                transform: translateX(-100%);
                transition: transform .28s cubic-bezier(.4,0,.2,1);
                box-shadow: none;
            }
            .es.show {
                transform: translateX(0);
                box-shadow: var(--shadow-lg);
            }
            .em { margin-left: 0; width: 100%; }
            .et-toggle { display: flex; }
            .es-brand-close { display: flex; }
            .ec { padding: 1rem 0.75rem 1.25rem; }
        }

        /* ─── Dropdown adjustments ───────────────────────────── */
        .dropdown-menu { border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-lg); padding: .375rem; font-size: .9rem; }
        .dropdown-item { border-radius: var(--radius-sm); padding: .5rem .75rem; color: var(--ink-700); }
        .dropdown-item:hover { background: var(--ink-50); color: var(--ink-900); }
        .dropdown-item.text-danger:hover { background: var(--red-soft); color: var(--red); }
        .dropdown-divider { margin: .25rem 0; border-color: var(--border-soft); }

        /* Jobs index: status row (All / Active / …) — align with list, beat global link styles */
        .ec .employer-card .employer-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: center;
            padding: .75rem var(--ec-inline) 1rem;
            margin: 0;
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .ec .employer-card .employer-tabs .tab-link {
            display: inline-flex;
            align-items: center;
            padding: .5rem 1rem;
            font-size: .875rem;
            font-weight: 500;
            color: var(--ink-500) !important;
            text-decoration: none !important;
            border-radius: var(--radius-pill);
            border: 1px solid transparent;
            white-space: nowrap;
            transition: color var(--transition), background var(--transition), border-color var(--transition), box-shadow var(--transition);
        }
        .ec .employer-card .employer-tabs .tab-link:hover {
            color: var(--brand) !important;
            background: var(--brand-light);
            border-color: var(--brand-glow);
        }
        .ec .employer-card .employer-tabs .tab-link.active {
            color: #fff !important;
            font-weight: 600;
            background: linear-gradient(135deg, var(--brand-mid) 0%, var(--brand) 100%);
            border-color: rgba(15, 42, 80, 0.35);
            box-shadow: var(--shadow-xs);
        }
        .ec .employer-card .employer-tabs .tab-link.active:hover {
            color: #fff !important;
            filter: brightness(1.05);
        }
        .ec .employer-card .employer-tabs .tab-link:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
        }
        .ec .employer-jobs-list-wrap {
            padding-left: var(--ec-inline);
            padding-right: var(--ec-inline);
        }
    </style>
    @stack('styles')

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-BXYNKF2NHW"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-BXYNKF2NHW');
    </script>
</head>
<body>
    <!-- Mobile backdrop -->
    <div class="eb" id="eb" aria-hidden="true"></div>

    <div class="ew">
        <!-- ════ Sidebar ════════════════════════════════════════ -->
        <aside class="es" id="es" aria-label="Employer navigation">
            <div class="es-brand">
                <a href="{{ route('home') }}" aria-label="Hirevo home">
                    <img src="{{ asset('images/20260419_104558_0000.png') }}" alt="Hirevo" width="150" height="68" decoding="async">
                </a>
                <button class="es-brand-close" id="es-close" aria-label="Close menu">
                    <i class="mdi mdi-close" style="font-size:1.25rem;"></i>
                </button>
            </div>

            <div class="es-body">
                <ul class="es-nav">
                    <li>
                        <a class="es-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}"
                           href="{{ route('employer.dashboard') }}">
                            <span class="es-icon-wrap"><i class="mdi mdi-view-dashboard-outline es-icon"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                @if(auth()->user()->referrerProfile?->is_approved)
                    <p class="es-section-label">Talent</p>
                    <ul class="es-nav">
                        <li>
                            <a class="es-link {{ request()->routeIs('employer.talent-pool.*') ? 'active' : '' }}"
                               href="{{ route('employer.talent-pool.index') }}">
                                <span class="es-icon-wrap"><i class="mdi mdi-account-search-outline es-icon"></i></span>
                                <span>Talent Pool</span>
                            </a>
                        </li>
                        <li>
                            <a class="es-link {{ request()->routeIs('employer.plans.*') ? 'active' : '' }}"
                               href="{{ route('employer.plans.index') }}">
                                <span class="es-icon-wrap"><i class="mdi mdi-tag-multiple-outline es-icon"></i></span>
                                <span>Plans & Pricing</span>
                            </a>
                        </li>
                    </ul>

                    @php
                        $pipelineJob = auth()->user()
                            ->employerJobs()
                            ->orderByDesc('created_at')
                            ->first(['id', 'title', 'status']);
                    @endphp

                    <p class="es-section-label">Jobs</p>
                    <ul class="es-nav">
                        <li>
                            <a class="es-link {{ request()->routeIs('employer.jobs.*') && !request()->routeIs('employer.jobs.create') ? 'active' : '' }}"
                               href="{{ route('employer.jobs.index') }}">
                                <span class="es-icon-wrap"><i class="mdi mdi-briefcase-outline es-icon"></i></span>
                                <span>My Jobs</span>
                            </a>
                        </li>
                        <li>
                            <a class="es-link {{ request()->routeIs('employer.jobs.create') ? 'active' : '' }}"
                               href="{{ route('employer.jobs.create') }}">
                                <span class="es-icon-wrap"><i class="mdi mdi-plus-circle-outline es-icon"></i></span>
                                <span>Post a Job</span>
                            </a>
                        </li>
                        <li>
                            @if($pipelineJob)
                                <a class="es-link {{ request()->routeIs('employer.jobs.pipeline') ? 'active' : '' }}"
                                   href="{{ route('employer.jobs.pipeline', $pipelineJob) }}">
                                    <span class="es-icon-wrap"><i class="mdi mdi-source-pull es-icon"></i></span>
                                    <span>ATS Pipeline</span>
                                </a>
                            @else
                                <span class="es-link disabled-link" title="Post a job first">
                                    <span class="es-icon-wrap"><i class="mdi mdi-source-pull es-icon"></i></span>
                                    <span>ATS Pipeline</span>
                                </span>
                            @endif
                        </li>
                    </ul>
                @endif

                <div class="es-divider"></div>

                <p class="es-section-label">Account</p>
                <ul class="es-nav">
                    <li>
                        <a class="es-link {{ request()->routeIs('employer.profile') ? 'active' : '' }}"
                           href="{{ route('employer.profile') }}">
                            <span class="es-icon-wrap"><i class="mdi mdi-domain es-icon"></i></span>
                            <span>Company Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="es-link" href="{{ route('contact') }}">
                            <span class="es-icon-wrap"><i class="mdi mdi-help-circle-outline es-icon"></i></span>
                            <span>Help & Support</span>
                        </a>
                    </li>
                    <li>
                        <a class="es-link" href="{{ route('contact') }}">
                            <span class="es-icon-wrap"><i class="mdi mdi-phone-outline es-icon"></i></span>
                            <span>Contact Us</span>
                        </a>
                    </li>
                </ul>
            </div>

            @php
                $credits = $employerCredits ?? 0;
                $talentPoolTokens = $employerTalentPoolTokens ?? 0;
                $hasPlan = $employerHasActivePlan ?? false;
                $planName = $employerActivePlanName ?? null;
                $planExpires = $employerPlanExpiresAt ?? null;
                $planIsLaunch = $employerPlanIsLaunch ?? false;
            @endphp
            <div class="es-wallet">
                <div class="es-wallet__head">
                    <span class="es-wallet__title">Your account</span>
                    @if($hasPlan)
                        <span class="es-wallet__status es-wallet__status--active">
                            <span class="es-wallet__status-dot"></span> Active
                        </span>
                    @else
                        <span class="es-wallet__status es-wallet__status--inactive">
                            <span class="es-wallet__status-dot"></span> No plan
                        </span>
                    @endif
                </div>

                @if($hasPlan && $planName)
                    <span class="es-wallet__plan">{{ $planName }}</span>
                    @if($planExpires)
                        <div class="es-wallet__meta">
                            <i class="mdi mdi-calendar-clock"></i>
                            {{ $planIsLaunch ? 'Access until' : 'Renews / expires' }}
                            {{ $planExpires->format('d M Y') }}
                        </div>
                    @endif
                @else
                    <span class="es-wallet__plan es-wallet__plan--muted">No active subscription</span>
                    <div class="es-wallet__meta">Unlock Talent Pool and hiring tools with a plan.</div>
                @endif

                <div class="es-wallet__stats">
                    <div class="es-wallet__stat">
                        <span class="es-wallet__stat-label">Job credits</span>
                        <span class="es-wallet__stat-value {{ $credits < 1 ? 'es-wallet__stat-value--low' : '' }}">{{ $credits }}</span>
                    </div>
                    <div class="es-wallet__stat">
                        <span class="es-wallet__stat-label">Pool tokens</span>
                        <span class="es-wallet__stat-value {{ $talentPoolTokens < 1 ? 'es-wallet__stat-value--low' : '' }}">{{ $talentPoolTokens }}</span>
                    </div>
                </div>

                <a href="{{ route('employer.plans.index') }}" class="es-wallet__btn {{ (!$hasPlan || $credits < 1) ? 'es-wallet__btn--urgent' : '' }}">
                    <i class="mdi mdi-{{ $hasPlan ? 'arrow-up-circle-outline' : 'rocket-launch-outline' }}"></i>
                    {{ $hasPlan ? ($credits < 1 ? 'Get more credits' : 'Manage plan') : 'Choose a plan' }}
                </a>
            </div>
        </aside>

        <!-- ════ Main ════════════════════════════════════════════ -->
        <div class="em">
            <!-- Topbar -->
            <header class="et">
                <div class="et-left">
                    <button class="et-toggle" id="et-toggle" aria-label="Open menu">
                        <i class="mdi mdi-menu" style="font-size:1.375rem;"></i>
                    </button>
                    <span class="et-title">@yield('header_title', 'Dashboard')</span>
                </div>

                <div class="et-right">
                    <a href="{{ route('employer.plans.index') }}" class="et-plan-pill" title="Your subscription plan">
                        @if($hasPlan && $planName)
                            <span class="et-plan-pill__icon {{ $planIsLaunch ? 'et-plan-pill__icon--launch' : '' }}">
                                <i class="mdi mdi-{{ $planIsLaunch ? 'rocket-launch' : 'shield-check' }}"></i>
                            </span>
                            <span class="et-plan-pill__text">
                                <span class="et-plan-pill__label">Active plan</span>
                                <span class="et-plan-pill__value">{{ $planName }}</span>
                            </span>
                        @else
                            <span class="et-plan-pill__icon et-plan-pill__icon--none"><i class="mdi mdi-shield-off-outline"></i></span>
                            <span class="et-plan-pill__text">
                                <span class="et-plan-pill__label">Plan</span>
                                <span class="et-plan-pill__value et-plan-pill__value--muted">No active plan</span>
                            </span>
                        @endif
                    </a>

                    @unless(request()->routeIs('employer.plans.*'))
                        <a href="{{ route('employer.plans.index') }}" class="et-credits-pill" title="Job posting credits">
                            <span class="et-credits-pill__icon"><i class="mdi mdi-lightning-bolt"></i></span>
                            <span class="et-credits-pill__text">
                                <span class="et-credits-pill__label">Credits</span>
                                <span class="et-credits-pill__value">{{ $credits }}</span>
                            </span>
                        </a>
                        @if($hasPlan)
                            <span class="et-credits-pill" title="Talent pool tokens" id="tp-topbar-tokens">
                                <span class="et-credits-pill__icon" style="background:linear-gradient(135deg,#e8f6ef,#d1fae5);color:#15803d;"><i class="mdi mdi-wallet-outline"></i></span>
                                <span class="et-credits-pill__text">
                                    <span class="et-credits-pill__label">Tokens</span>
                                    <span class="et-credits-pill__value" id="tp-topbar-tokens-value">{{ $talentPoolTokens }}</span>
                                </span>
                            </span>
                        @endif
                    @endunless

                    @yield('header_actions')

                    <div class="dropdown">
                        @php
                            $employerTopbarName = trim((string) auth()->user()->name);
                            $employerDisplayName = $employerTopbarName !== '' ? \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($employerTopbarName)) : 'Account';
                            $employerInitials = auth()->user()->initials();
                        @endphp
                        <a href="#" class="et-user et-user-trigger d-flex align-items-center text-decoration-none min-w-0" data-bs-toggle="dropdown" aria-expanded="false" @if($employerTopbarName !== '') title="{{ $employerTopbarName }}" @endif>
                            <span class="hirevo-user-initials-avatar et-user-initials-badge flex-shrink-0" aria-hidden="true">{{ $employerInitials }}</span>
                            <span class="et-user-meta text-start">
                                <span class="et-user-fullname">{{ $employerDisplayName }}</span>
                                <span class="et-user-sub">Employer</span>
                            </span>
                            <span class="et-user-chevron" aria-hidden="true"><i class="mdi mdi-chevron-down"></i></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('employer.profile') }}">
                                    <i class="mdi mdi-domain" style="font-size:1rem; opacity:.7;"></i> Company Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('home') }}">
                                    <i class="mdi mdi-arrow-left" style="font-size:1rem; opacity:.7;"></i> Back to Site
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="mdi mdi-logout" style="font-size:1rem;"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="ec">
                @if(session('success'))
                    <div class="ec-alert success" role="alert">
                        <i class="mdi mdi-check-circle-outline" style="font-size:1.1rem; flex-shrink:0; margin-top:.05rem;"></i>
                        <span>{{ session('success') }}</span>
                        <button class="ec-alert-close" onclick="this.closest('.ec-alert').remove()" aria-label="Dismiss">
                            <i class="mdi mdi-close" style="font-size:1rem;"></i>
                        </button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="ec-alert info" role="alert">
                        <i class="mdi mdi-information-outline" style="font-size:1.1rem; flex-shrink:0; margin-top:.05rem;"></i>
                        <span>{{ session('info') }}</span>
                        <button class="ec-alert-close" onclick="this.closest('.ec-alert').remove()" aria-label="Dismiss">
                            <i class="mdi mdi-close" style="font-size:1rem;"></i>
                        </button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="ec-alert error" role="alert">
                        <i class="mdi mdi-alert-circle-outline" style="font-size:1.1rem; flex-shrink:0; margin-top:.05rem;"></i>
                        <span>{{ session('error') }}</span>
                        <button class="ec-alert-close" onclick="this.closest('.ec-alert').remove()" aria-label="Dismiss">
                            <i class="mdi mdi-close" style="font-size:1rem;"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div><!-- /.em -->
    </div><!-- /.ew -->

    <script src="{{ asset($theme.'/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function () {
            var sidebar  = document.getElementById('es');
            var backdrop = document.getElementById('eb');
            var toggle   = document.getElementById('et-toggle');
            var closeBtn = document.getElementById('es-close');

            function open() {
                sidebar.classList.add('show');
                backdrop.classList.add('show');
                backdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function close() {
                sidebar.classList.remove('show');
                backdrop.classList.remove('show');
                backdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            if (toggle)   toggle.addEventListener('click', open);
            if (closeBtn) closeBtn.addEventListener('click', close);
            if (backdrop) backdrop.addEventListener('click', close);

            // Close on nav click (mobile)
            sidebar && sidebar.querySelectorAll('.es-link').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (window.innerWidth < 992) close();
                });
            });
        })();
    </script>
    @include('partials.flash-auto-dismiss')
    @stack('scripts')
</body>
</html>