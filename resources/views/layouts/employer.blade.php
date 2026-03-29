<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    <title>@yield('title', 'Dashboard') | Hirevo — Own Your Next Career Move</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            background: var(--brand);
            border-right: 1px solid rgba(255,255,255,.07);
        }

        /* subtle gradient shine */
        .es::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg, rgba(255,255,255,.04) 0%, transparent 55%);
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
            filter: brightness(0) invert(1);
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
            gap: .75rem;
            padding: .625rem .875rem;
            border-radius: var(--radius);
            color: rgba(255,255,255,.75);
            text-decoration: none;
            font-size: .9rem;
            font-weight: 450;
            transition: background var(--transition), color var(--transition), transform var(--transition);
            position: relative;
            border: 1px solid transparent;
        }
        .es-link .es-icon {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            opacity: .85;
        }
        .es-link:hover {
            color: #fff;
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.1);
            transform: translateX(2px);
        }
        .es-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
            border-color: rgba(255,255,255,.14);
            font-weight: 600;
        }
        .es-link.active::before {
            content: '';
            position: absolute;
            left: -1px;
            top: 20%;
            height: 60%;
            width: 3px;
            border-radius: 0 var(--radius-pill) var(--radius-pill) 0;
            background: rgba(255,255,255,.7);
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

        /* ── Credits box ── */
        .es-credits {
            flex-shrink: 0;
            margin: .625rem .75rem .875rem;
            padding: .875rem 1rem;
            border-radius: var(--radius);
            font-size: .85rem;
        }
        .es-credits.ok {
            background: rgba(22,163,74,.18);
            border: 1px solid rgba(22,163,74,.28);
        }
        .es-credits.out {
            background: rgba(220,38,38,.18);
            border: 1px solid rgba(220,38,38,.3);
        }
        .es-credits-label {
            font-size: .75rem;
            color: rgba(255,255,255,.55);
            margin-bottom: .2rem;
        }
        .es-credits-amount {
            font-size: 1.375rem;
            font-weight: 700;
            color: #fff;
            font-variant-numeric: tabular-nums;
            letter-spacing: -.01em;
            line-height: 1;
            margin-bottom: .625rem;
        }
        .es-credits.out .es-credits-amount { color: #fca5a5; }
        .es-credits-warn {
            font-size: .8125rem;
            color: #fca5a5;
            margin-bottom: .625rem;
            display: flex;
            align-items: center;
            gap: .3rem;
        }
        .es-credits-btn {
            display: block;
            width: 100%;
            padding: .45rem .75rem;
            border-radius: var(--radius-sm);
            font-size: .8125rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all var(--transition);
            border: 1.5px solid;
        }
        .es-credits.ok .es-credits-btn {
            color: rgba(255,255,255,.9);
            border-color: rgba(255,255,255,.3);
            background: transparent;
        }
        .es-credits.ok .es-credits-btn:hover {
            background: rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.5);
            color: #fff;
        }
        .es-credits.out .es-credits-btn {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }
        .es-credits.out .es-credits-btn:hover { background: #b91c1c; border-color: #b91c1c; }

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

        .et-credits-pill {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .375rem .875rem;
            background: var(--ink-50);
            border: 1px solid var(--border);
            border-radius: var(--radius-pill);
            text-decoration: none;
            font-size: .875rem;
            color: var(--ink-700);
            transition: all var(--transition);
        }
        .et-credits-pill:hover {
            background: var(--accent-soft);
            border-color: #bfdbfe;
            color: var(--ink-900);
        }
        .et-credits-pill .coin-icon { color: #d97706; font-size: 1rem; }
        .et-credits-pill strong { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--ink-900); }

        /* Avatar dropdown */
        .et-user { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .et-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-pill);
            object-fit: cover;
            border: 2px solid var(--border);
        }
        .et-user-name {
            font-size: .875rem;
            font-weight: 500;
            color: var(--ink-700);
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
            .et-user-name { display: none; }
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
</head>
<body>
    <!-- Mobile backdrop -->
    <div class="eb" id="eb" aria-hidden="true"></div>

    <div class="ew">
        <!-- ════ Sidebar ════════════════════════════════════════ -->
        <aside class="es" id="es" aria-label="Employer navigation">
            <div class="es-brand">
                <a href="{{ route('home') }}" aria-label="Hirevo home">
                    <img src="{{ asset('images/sam.png') }}" alt="Hirevo" width="150" height="68" decoding="async">
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
                            <i class="mdi mdi-view-dashboard-outline es-icon"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>

                @if(auth()->user()->referrerProfile?->is_approved)
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
                                <i class="mdi mdi-briefcase-outline es-icon"></i>
                                <span>My Jobs</span>
                            </a>
                        </li>
                        <li>
                            <a class="es-link {{ request()->routeIs('employer.jobs.create') ? 'active' : '' }}"
                               href="{{ route('employer.jobs.create') }}">
                                <i class="mdi mdi-plus-circle-outline es-icon"></i>
                                <span>Post a Job</span>
                            </a>
                        </li>
                        <li>
                            @if($pipelineJob)
                                <a class="es-link {{ request()->routeIs('employer.jobs.pipeline') ? 'active' : '' }}"
                                   href="{{ route('employer.jobs.pipeline', $pipelineJob) }}">
                                    <i class="mdi mdi-source-pull es-icon"></i>
                                    <span>ATS Pipeline</span>
                                </a>
                            @else
                                <span class="es-link disabled-link" title="Post a job first">
                                    <i class="mdi mdi-source-pull es-icon"></i>
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
                            <i class="mdi mdi-domain es-icon"></i>
                            <span>Company Profile</span>
                        </a>
                    </li>
                    <li>
                        <a class="es-link" href="{{ route('contact') }}">
                            <i class="mdi mdi-help-circle-outline es-icon"></i>
                            <span>Help & Support</span>
                        </a>
                    </li>
                    <li>
                        <a class="es-link" href="{{ route('contact') }}">
                            <i class="mdi mdi-phone-outline es-icon"></i>
                            <span>Contact Us</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Credits box -->
            @php $credits = $employerCredits ?? 0; @endphp
            <div class="es-credits {{ $credits < 1 ? 'out' : 'ok' }}">
                @if($credits < 1)
                    <div class="es-credits-warn">
                        <i class="mdi mdi-alert-circle-outline"></i>
                        No credits remaining
                    </div>
                @else
                    <div class="es-credits-label">Available credits</div>
                    <div class="es-credits-amount">{{ $credits }}</div>
                @endif
                <a href="{{ route('employer.credits.index') }}" class="es-credits-btn">
                    <i class="mdi mdi-coin me-1"></i>
                    {{ $credits < 1 ? 'Buy Credits' : 'Top Up' }}
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
                    <a href="{{ route('employer.credits.index') }}" class="et-credits-pill">
                        <i class="mdi mdi-coin coin-icon"></i>
                        <span><span class="d-none d-sm-inline" style="color:var(--ink-500); font-weight:400;">Credits: </span><strong>{{ $credits }}</strong></span>
                    </a>

                    @yield('header_actions')

                    <div class="dropdown">
                        <a href="#" class="et-user d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(!empty($employerProfilePhotoUrl))
                                <img src="{{ $employerProfilePhotoUrl }}" alt="{{ auth()->user()->name }}" class="et-avatar">
                            @else
                                <img src="{{ asset($theme.'/assets/images/profile.jpg') }}" alt="{{ auth()->user()->name }}" class="et-avatar">
                            @endif
                            <span class="et-user-name ms-2">{{ auth()->user()->name }}</span>
                            <i class="mdi mdi-chevron-down ms-1" style="font-size:.9rem; color:var(--ink-300);"></i>
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
    @stack('scripts')
</body>
</html>