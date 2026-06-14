<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    @include('partials.seo-head')
    @include('partials.meta-pixel')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="shortcut icon" href="{{ asset($theme.'/assets/images/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset($theme.'/assets/css/bootstrap.min.css') }}">
    <link href="{{ asset($theme.'/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/hirevo-theme.css') }}" rel="stylesheet">
    @php
        $candidateDashCss = public_path('css/hirevo-candidate-dashboard.css');
        $candidateDashCssVer = is_file($candidateDashCss) ? (string) filemtime($candidateDashCss) : '1';
    @endphp
    <link href="{{ asset('css/hirevo-candidate-dashboard.css') }}?v={{ $candidateDashCssVer }}" rel="stylesheet">
    @stack('styles')
    <title>@yield('title', 'Dashboard') — Hirevoo</title>
</head>
<body class="cp-body @yield('body_class')">
    <div class="cp-backdrop" id="cp-backdrop" aria-hidden="true"></div>

    <div class="cp-wrap">
        <aside class="cp-sidebar" id="cp-sidebar" aria-label="Candidate navigation">
            <div class="cp-sidebar-brand">
                <a href="{{ route('home') }}" aria-label="Hirevoo home">
                    <img src="{{ asset('images/20260419_104558_0000.png') }}" alt="Hirevoo" width="160" height="40" decoding="async">
                    <span class="cp-sidebar-tagline">Know. Improve. Get Hired.</span>
                </a>
                <button type="button" class="cp-sidebar-close" id="cp-sidebar-close" aria-label="Close menu">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <nav class="cp-sidebar-nav">
                @php
                    $navItems = [
                        ['route' => 'candidate.dashboard', 'icon' => 'mdi-view-dashboard-outline', 'label' => 'Dashboard', 'active' => false],
                        ['href' => route('candidate.dashboard').'#hiring-score', 'icon' => 'mdi-gauge', 'label' => 'My Hiring Score', 'active' => false],
                        ['href' => route('candidate.dashboard').'#career-report', 'icon' => 'mdi-chart-box-outline', 'label' => 'Career Report', 'active' => false],
                        ['href' => route('candidate.dashboard').'#roadmap', 'icon' => 'mdi-map-marker-path', 'label' => 'Roadmap', 'active' => false],
                        ['route' => 'job-list', 'icon' => 'mdi-clipboard-check-outline', 'label' => 'Assessments', 'active' => request()->routeIs('job-list')],
                        ['route' => 'candidate.resume.review', 'icon' => 'mdi-file-document-edit-outline', 'label' => 'Resume Review', 'active' => request()->routeIs('candidate.resume.review', 'resume.results')],
                        ['route' => 'help', 'icon' => 'mdi-microphone-outline', 'label' => 'Mock Interviews', 'active' => request()->routeIs('help')],
                        ['href' => route('candidate.dashboard').'#applications', 'icon' => 'mdi-briefcase-check-outline', 'label' => 'Applications Tracker', 'active' => false],
                        ['href' => route('candidate.dashboard').'#skill-gaps', 'icon' => 'mdi-chart-timeline-variant', 'label' => 'Skill Gap Analysis', 'active' => false],
                        ['route' => 'pricing', 'icon' => 'mdi-school-outline', 'label' => 'Learning Hub', 'active' => request()->routeIs('pricing')],
                        ['href' => route('candidate.dashboard').'#job-matches', 'icon' => 'mdi-briefcase-search-outline', 'label' => 'Job Matches', 'active' => false],
                        ['route' => 'help', 'icon' => 'mdi-currency-inr', 'label' => 'Salary Insights', 'active' => false],
                        ['route' => 'profile', 'icon' => 'mdi-account-circle-outline', 'label' => 'Profile & Resume', 'active' => request()->routeIs('profile')],
                        ['route' => 'profile', 'icon' => 'mdi-cog-outline', 'label' => 'Settings', 'active' => false],
                    ];
                @endphp
                <ul class="cp-nav-list">
                    @foreach($navItems as $item)
                        <li>
                            <a class="cp-nav-link {{ ($item['active'] ?? false) ? 'is-active' : '' }}"
                               href="{{ isset($item['route']) ? route($item['route']) : ($item['href'] ?? '#') }}">
                                <i class="mdi {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="cp-sidebar-upgrade">
                <div class="cp-sidebar-upgrade-icon" aria-hidden="true">👑</div>
                <p class="cp-sidebar-upgrade-title">Upgrade Your Career</p>
                <p class="cp-sidebar-upgrade-sub">Unlock premium tools to boost your hiring score.</p>
                <a href="{{ route('pricing') }}" class="cp-sidebar-upgrade-btn">Explore Premium</a>
            </div>
        </aside>

        <div class="cp-main">
            <header class="cp-topbar">
                <button type="button" class="cp-topbar-toggle" id="cp-topbar-toggle" aria-label="Open menu">
                    <i class="mdi mdi-menu"></i>
                </button>
                <div class="cp-topbar-greeting">
                    @yield('header_greeting')
                </div>
                <div class="cp-topbar-actions">
                    @yield('header_actions')
                </div>
            </header>

            <main class="cp-content">
                @if(session('success'))
                    <div class="cp-alert cp-alert--success" role="alert">
                        <i class="mdi mdi-check-circle-outline"></i>
                        <span>{{ session('success') }}</span>
                        <button type="button" class="cp-alert-close" onclick="this.closest('.cp-alert').remove()" aria-label="Dismiss"><i class="mdi mdi-close"></i></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="cp-alert cp-alert--info" role="alert">
                        <i class="mdi mdi-information-outline"></i>
                        <span>{{ session('info') }}</span>
                        <button type="button" class="cp-alert-close" onclick="this.closest('.cp-alert').remove()" aria-label="Dismiss"><i class="mdi mdi-close"></i></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Referral modal --}}
    <div class="modal fade" id="referralSignupModal" tabindex="-1" aria-labelledby="referralSignupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="referralSignupModalLabel">Refer in your company & earn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('referral-signup.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Share your details. We'll contact you to help you refer candidates and start earning.</p>
                        <div class="mb-3">
                            <label for="referral_company_name" class="form-label">Company name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="referral_company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Your company name" required>
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="referral_name" class="form-label">Your name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="referral_name" name="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Full name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="referral_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="referral_email" name="email" value="{{ old('email', auth()->user()->email) }}" placeholder="you@company.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="referral_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="referral_phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="Phone number">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="referral_max_candidates" class="form-label">How many candidates can you refer? <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('max_candidates') is-invalid @enderror" id="referral_max_candidates" name="max_candidates" value="{{ old('max_candidates', 1) }}" min="1" max="100" required>
                            @error('max_candidates')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-0">
                            <label for="referral_message" class="form-label">Message (optional)</label>
                            <textarea class="form-control" id="referral_message" name="message" rows="2" placeholder="Any additional details">{{ old('message') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset($theme.'/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        (function () {
            var sidebar  = document.getElementById('cp-sidebar');
            var backdrop = document.getElementById('cp-backdrop');
            var toggle   = document.getElementById('cp-topbar-toggle');
            var closeBtn = document.getElementById('cp-sidebar-close');

            function openSidebar() {
                sidebar.classList.add('is-open');
                backdrop.classList.add('is-visible');
                backdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
            function closeSidebar() {
                sidebar.classList.remove('is-open');
                backdrop.classList.remove('is-visible');
                backdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            if (toggle)   toggle.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            sidebar && sidebar.querySelectorAll('.cp-nav-link').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (window.innerWidth < 992) closeSidebar();
                });
            });
        })();
    </script>
    @stack('scripts')
    @include('partials.flash-auto-dismiss')
</body>
</html>
