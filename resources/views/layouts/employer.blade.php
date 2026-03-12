<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    <title>@yield('title', 'Dashboard') | Hirevo — Own Your Next Career Move</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset($theme.'/assets/images/favicon.ico') }}">
    <!-- SN Pro by Tobias Whetton / Supernotes (Fontsource) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/400.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/500.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/600.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/700.css">
    <link rel="stylesheet" href="{{ asset($theme.'/assets/css/bootstrap.min.css') }}">
    <link href="{{ asset($theme.'/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset($theme.'/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/hirevo-theme.css') }}" rel="stylesheet">
    <style>
        body.employer-body { background: #f5f6f8; margin: 0; }
        .employer-wrapper { min-height: 100vh; }
        .employer-sidebar { background: var(--hirevo-primary, #0B1F3B); width: 260px; flex-shrink: 0; position: fixed; left: 0; top: 0; bottom: 0; z-index: 40; overflow-y: auto; }
        .employer-sidebar .nav-link { color: rgba(255,255,255,0.88); padding: 0.65rem 1.25rem; border-radius: 0.5rem; margin: 0 0.5rem; font-size: 0.9375rem; display: flex; align-items: center; }
        .employer-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .employer-sidebar .nav-link.active { color: #fff; background: var(--hirevo-secondary, #10B981); font-weight: 500; }
        .employer-sidebar .nav-link i { margin-right: 0.75rem; font-size: 1.25rem; opacity: 0.95; }
        .employer-sidebar .sidebar-brand { padding: 1.25rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.12); }
        .employer-sidebar .sidebar-brand a { color: #fff; font-weight: 700; text-decoration: none; font-size: 1.125rem; display: flex; align-items: center; }
        .employer-sidebar .sidebar-brand a img { margin-right: 0.5rem; }
        .employer-main { flex: 1; min-width: 0; margin-left: 260px; display: flex; flex-direction: column; background: #f5f6f8; min-height: 100vh; }
        .employer-topbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0.875rem 1.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.04); position: sticky; top: 0; z-index: 30; flex-shrink: 0; }
        .employer-content { padding: 1.5rem; flex: 1; }
        .employer-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .employer-job-card { transition: box-shadow 0.2s, border-color 0.2s; }
        .employer-job-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-color: #d1d5db; }
        .employer-tabs { display: flex; flex-wrap: wrap; gap: 0; border-bottom: 1px solid #e5e7eb; margin-bottom: 1.25rem; background: #fff; padding: 0 0.25rem; border-radius: 8px 8px 0 0; }
        .employer-tabs .tab-link { padding: 0.6rem 1rem; font-size: 0.875rem; color: #6b7280; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px; border-radius: 4px 4px 0 0; }
        .employer-tabs .tab-link:hover { color: var(--hirevo-primary); }
        .employer-tabs .tab-link.active { color: var(--hirevo-primary); font-weight: 600; border-bottom-color: var(--hirevo-primary); }
        .job-card-status { font-size: 0.8125rem; font-weight: 500; }
        .job-card-meta { font-size: 0.8125rem; color: #6b7280; }
        .credits-sidebar-box { margin: 0.75rem 0.75rem 1rem; padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.875rem; }
        .credits-sidebar-box.out { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.35); color: #fecaca; }
        .credits-sidebar-box.ok { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: rgba(255,255,255,0.95); }
        .credits-sidebar-box .btn { font-size: 0.8125rem; padding: 0.4rem 0.75rem; }
        .credits-sidebar-box.out .btn { background: #dc2626; border-color: #dc2626; color: #fff; }
        .credits-sidebar-box.out .btn:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }
        .credits-sidebar-box.ok .btn { border-color: rgba(255,255,255,0.5); color: #fff; }
        .credits-sidebar-box.ok .btn:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.6); color: #fff; }
        @media (max-width: 991.98px) {
            .employer-sidebar { width: 280px; position: fixed; left: 0; top: 0; bottom: 0; z-index: 1050; transform: translateX(-100%); transition: transform 0.25s ease; box-shadow: 4px 0 12px rgba(0,0,0,0.15); }
            .employer-sidebar.show { transform: translateX(0); }
            .employer-main { margin-left: 0; width: 100%; }
            .employer-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 1040; }
            .employer-backdrop.show { display: block; }
            .employer-content { padding: 1rem; }
        }
        @media (min-width: 992px) {
            .employer-sidebar-toggle { display: none !important; }
        }
        .post-job-page .form-select.status-select { max-width: 220px; font-size: 0.875rem; }
    </style>
    @stack('styles')
</head>
<body class="employer-body bg-light">
    <div class="employer-backdrop" id="employerBackdrop" aria-hidden="true"></div>
    <div class="d-flex employer-wrapper">
        <aside class="employer-sidebar" id="employerSidebar" aria-label="Employer menu">
            <div class="sidebar-brand d-flex align-items-center justify-content-between">
                <a href="{{ route('home') }}" class="d-flex align-items-center">
                    <picture>
                        <source srcset="{{ asset('images/12575%20%20-2.svg') }}" type="image/svg+xml">
                        <img src="{{ asset('images/hirevo-logo.png') }}" alt="Hirevo" class="hirevo-logo">
                    </picture>
                </a>
                <button type="button" class="btn btn-link text-white p-0 d-lg-none ms-2" id="employerSidebarClose" aria-label="Close menu"><i class="mdi mdi-close mdi-24px"></i></button>
            </div>
            <nav class="nav flex-column py-2">
                <a class="nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}" href="{{ route('employer.dashboard') }}">
                    <i class="mdi mdi-view-dashboard-outline"></i> Dashboard
                </a>
                @if(auth()->user()->referrerProfile?->is_approved)
                <a class="nav-link {{ request()->routeIs('employer.jobs.*') && !request()->routeIs('employer.jobs.create') ? 'active' : '' }}" href="{{ route('employer.jobs.index') }}">
                    <i class="mdi mdi-briefcase-outline"></i> Jobs
                </a>
                <a class="nav-link {{ request()->routeIs('employer.jobs.create') ? 'active' : '' }}" href="{{ route('employer.jobs.create') }}">
                    <i class="mdi mdi-plus-circle-outline"></i> Post Job
                </a>
                @endif
                <a class="nav-link {{ request()->routeIs('employer.profile') ? 'active' : '' }}" href="{{ route('employer.profile') }}">
                    <i class="mdi mdi-domain"></i> Company Profile
                </a>
                <hr class="my-2 mx-3 border-secondary opacity-25">
                <a class="nav-link" href="{{ route('contact') }}"><i class="mdi mdi-help-circle-outline"></i> Help & Support</a>
                <a class="nav-link" href="{{ route('contact') }}"><i class="mdi mdi-phone-outline"></i> Contact</a>
            </nav>
            <div class="credits-sidebar-box {{ ($employerCredits ?? 0) < 1 ? 'out' : 'ok' }}">
                @if(($employerCredits ?? 0) < 1)
                    <p class="mb-2 small fw-medium mb-2"><i class="mdi mdi-alert-circle-outline me-1"></i>You've run out of credits.</p>
                    <a href="{{ route('employer.credits.index') }}" class="btn btn-sm w-100"><i class="mdi mdi-coin me-1"></i>Buy credits</a>
                @else
                    <p class="mb-1 small opacity-75">Available credits</p>
                    <p class="mb-2 fw-600 fs-5 mb-2">{{ $employerCredits ?? 0 }}</p>
                    <a href="{{ route('employer.credits.index') }}" class="btn btn-sm w-100"><i class="mdi mdi-coin me-1"></i>Buy credits</a>
                @endif
            </div>
        </aside>
        <div class="employer-main">
            <header class="employer-topbar d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-link text-dark p-0 employer-sidebar-toggle" id="employerSidebarToggle" aria-label="Open menu"><i class="mdi mdi-menu mdi-28px"></i></button>
                    <h1 class="h5 mb-0 fw-600 text-dark">@yield('header_title', 'Dashboard')</h1>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3 flex-wrap">
                    <a href="{{ route('employer.credits.index') }}" class="d-flex align-items-center text-dark text-decoration-none border rounded-pill px-2 px-lg-3 py-2 bg-light">
                        <i class="mdi mdi-coin-outline me-1 me-lg-2 text-warning"></i>
                        <span class="fw-medium"><span class="d-none d-sm-inline">Available Credits: </span><strong>{{ $employerCredits ?? 0 }}</strong></span>
                    </a>
                    @yield('header_actions')
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            @if(!empty($employerProfilePhotoUrl))
                                <img src="{{ $employerProfilePhotoUrl }}" alt="" width="36" height="36" class="rounded-circle me-2 object-fit-cover" style="object-fit: cover;">
                            @else
                                <img src="{{ asset($theme.'/assets/images/profile.jpg') }}" alt="" width="36" height="36" class="rounded-circle me-2">
                            @endif
                            <span class="d-none d-sm-inline fw-medium">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('employer.profile') }}">Company Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('home') }}">Back to site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item text-danger">Logout</button></form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <main class="employer-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script src="{{ asset($theme.'/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function() {
            var sidebar = document.getElementById('employerSidebar');
            var backdrop = document.getElementById('employerBackdrop');
            var toggle = document.getElementById('employerSidebarToggle');
            var closeBtn = document.getElementById('employerSidebarClose');
            function openMenu() {
                if (sidebar) sidebar.classList.add('show');
                if (backdrop) { backdrop.classList.add('show'); backdrop.setAttribute('aria-hidden', 'false'); }
                document.body.style.overflow = 'hidden';
            }
            function closeMenu() {
                if (sidebar) sidebar.classList.remove('show');
                if (backdrop) { backdrop.classList.remove('show'); backdrop.setAttribute('aria-hidden', 'true'); }
                document.body.style.overflow = '';
            }
            if (toggle) toggle.addEventListener('click', openMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMenu);
            if (backdrop) backdrop.addEventListener('click', closeMenu);
            document.querySelectorAll('.employer-sidebar .nav-link').forEach(function(el) {
                el.addEventListener('click', function() { if (window.innerWidth < 992) closeMenu(); });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
