<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    <title>@yield('title', 'Home') | Hirevo — Own Your Next Career Move</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hirevo — Own Your Next Career Move. AI Career Intelligence, skill-gap analysis, referral marketplace & job goals.">
    <meta property="og:title" content="@yield('og_title', 'Hirevo — Own Your Next Career Move')">
    <meta property="og:description" content="@yield('og_description', 'AI Career Intelligence, skill-gap analysis, referral marketplace & job goals.')">
    <meta property="og:type" content="website">
    <meta content="Hirevo" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset($theme.'/assets/images/favicon.ico') }}">

    <!-- SN Pro by Tobias Whetton / Supernotes (Fontsource) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/400.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/500.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/600.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/sn-pro@5.2.6/700.css">

    <link rel="stylesheet" href="{{ asset($theme.'/assets/libs/choices.js/public/assets/styles/choices.min.css') }}">
    <link rel="stylesheet" href="{{ asset($theme.'/assets/libs/swiper/swiper-bundle.min.css') }}">
    <link href="{{ asset($theme.'/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet">
    <link href="{{ asset($theme.'/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset($theme.'/assets/css/app.min.css') }}" id="app-style" rel="stylesheet">
    <link href="{{ asset('css/hirevo-theme.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="preloader">
        <div id="status">
            <ul>
                <li></li><li></li><li></li><li></li><li></li><li></li>
            </ul>
        </div>
    </div>

    <div>
        <!-- Navbar Start -->
        <nav class="navbar navbar-expand-lg fixed-top sticky hirevo-navbar" id="navbar">
            <div class="container-fluid custom-container">
                <a class="navbar-brand hirevo-nav-brand d-flex align-items-center" href="{{ route('home') }}">
                    <picture>
                        <source srcset="{{ asset('images/12575-2.svg') }}" type="image/svg+xml">
                        <img src="{{ asset('images/hirevo-logo.png') }}" alt="Hirevo" class="hirevo-logo" width="192" height="58">
                    </picture>
                </a>
                <button class="navbar-toggler hirevo-nav-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-label="Toggle navigation">
                    <i class="mdi mdi-menu fs-28"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto hirevo-nav-links">
                        @auth
                        @if(auth()->user()->isReferrer())
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employer.dashboard') ? 'active' : '' }}" href="{{ route('employer.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}" href="{{ route('employer.jobs.index') }}">My Jobs</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employer.jobs.create') ? 'active' : '' }}" href="{{ route('employer.jobs.create') }}">Post Job</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employer.profile') ? 'active' : '' }}" href="{{ route('employer.profile') }}">Company Profile</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">More</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('pricing') }}">Pricing</a></li>
                                    <li><a class="dropdown-item" href="{{ route('about') }}">About</a></li>
                                    <li><a class="dropdown-item" href="{{ route('contact') }}">Contact</a></li>
                                </ul>
                            </li>
                        @elseif(auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        @else
                        @endif
                        @endauth
                        @guest
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('job-openings') }}">Jobs</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-resume {{ request()->routeIs('resume.*') ? 'active' : '' }}" href="{{ route('resume.upload') }}">Resume Score</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        @endguest
                        @auth
                        @if(!auth()->user()->isReferrer() && !auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('job-openings') }}">Jobs</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-resume {{ request()->routeIs('resume.*') ? 'active' : '' }}" href="{{ route('resume.upload') }}">Resume Score</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        @endif
                        @endauth
                    </ul>
                    <ul class="navbar-nav align-items-center hirevo-nav-actions">
                        @guest
                        <li class="nav-item">
                            <a href="{{ route('login') }}" class="nav-link hirevo-nav-login">Log in</a>
                        </li>
                            <li class="nav-item ms-2">
                                <a href="{{ route('register', ['role' => 'candidate']) }}" class="btn hirevo-btn-signup">Sign up</a>
                            </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link hirevo-nav-employers dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">For Employers</a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2">
                                <li><a class="dropdown-item rounded-2 mx-1" href="{{ route('login', ['role' => 'referrer']) }}">Log in as employer</a></li>
                                <li><a class="dropdown-item rounded-2 mx-1" href="{{ route('register', ['role' => 'referrer']) }}">Sign up as employer</a></li>
                            </ul>
                        </li>

                        @else
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link hirevo-nav-icon position-relative" id="notification" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <i class="mdi mdi-bell fs-20"></i>
                                @if(($navUnreadCount ?? 0) > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">{{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}</span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-0 hirevo-notify-dropdown" style="min-width: 320px; max-width: 380px;">
                                <div class="px-3 py-3 border-bottom bg-light d-flex align-items-center justify-content-between gap-2">
                                    <div>
                                        <h6 class="mb-0">Notifications</h6>
                                        <p class="text-muted small mb-0">
                                            @if(auth()->user()->isCandidate())
                                                @if(($navUnreadCount ?? 0) > 0)
                                                    {{ $navUnreadCount }} unread
                                                @else
                                                    You’re all caught up
                                                @endif
                                            @else
                                                Account updates appear here when relevant
                                            @endif
                                        </p>
                                    </div>
                                    @if(auth()->user()->isCandidate() && ($navUnreadCount ?? 0) > 0)
                                        <form action="{{ route('notifications.read-all') }}" method="post" class="mb-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 small">Mark all read</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="hirevo-notify-list" style="max-height: 320px; overflow-y: auto;">
                                    @if(auth()->user()->isCandidate())
                                        @forelse($navNotifications ?? [] as $note)
                                            @php
                                                $payload = is_array($note->data) ? $note->data : [];
                                                $title = $payload['title'] ?? 'Update';
                                                $body = $payload['body'] ?? '';
                                            @endphp
                                            <form action="{{ route('notifications.read', $note->id) }}" method="post" class="mb-0">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-start border-0 border-bottom rounded-0 py-3 px-3 w-100 {{ $note->read_at ? 'bg-transparent opacity-75' : 'bg-light bg-opacity-50' }}">
                                                    <div class="fw-600 small text-dark">{{ $title }}</div>
                                                    <div class="text-muted small mt-1" style="line-height: 1.35;">{{ \Illuminate\Support\Str::limit($body, 140) }}</div>
                                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $note->created_at->diffForHumans() }}</div>
                                                </button>
                                            </form>
                                        @empty
                                            <div class="text-muted small text-center py-4 px-3">No notifications yet. When an employer updates your application stage, you’ll see it here.</div>
                                        @endforelse
                                    @else
                                        <div class="text-muted small text-center py-4 px-3">Candidate application alerts appear when you apply to jobs on Hirevo.</div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        <li class="nav-item dropdown ms-2">
                            <a href="javascript:void(0)" class="nav-link hirevo-nav-user d-flex align-items-center" id="userdropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ asset($theme.'/assets/images/profile.jpg') }}" alt="" width="32" height="32" class="rounded-circle me-2 object-fit-cover">
                                <span class="d-none d-md-inline-block fw-medium text-dark">{{ auth()->user()->name }}</span>
                                <i class="uil uil-angle-down ms-1 d-none d-md-inline-block small"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" aria-labelledby="userdropdown">
                                @if(auth()->user()->isReferrer())
                                    <li><a class="dropdown-item" href="{{ route('employer.dashboard') }}">Employer Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('employer.profile') }}">Company Profile</a></li>
                                @elseif(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('profile') }}">My Profile</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('profile') }}">My Profile</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                            </ul>
                        </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        <!-- Navbar End -->

        @guest
        <!-- START SIGN-UP MODAL -->
        <div class="modal fade" id="signupModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-5">
                        <div class="position-absolute end-0 top-0 p-3">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="auth-content">
                            <div class="w-100">
                                <div class="text-center mb-4">
                                    <h5>Sign Up</h5>
                                    <p class="text-muted">Sign Up and get access to all the features of Hirevo</p>
                                </div>
                                <form action="{{ route('register') }}" method="GET" class="auth-form">
                                    <div class="text-center">
                                        <a href="{{ route('register') }}" class="btn btn-primary w-100">Go to Sign Up</a>
                                    </div>
                                </form>
                                <div class="mt-3 text-center">
                                    <p class="mb-0">Already a member ? <a href="{{ route('login') }}" class="form-text text-primary text-decoration-underline"> Sign-in </a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END SIGN-UP MODAL -->
        @endguest

        <div class="main-content">
            @if(session('referral_success'))
                <div class="container mt-3">
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        {{ session('referral_success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            <div class="page-content">
                @yield('content')
            </div>

            <!-- START REFERRAL (Refer in your company & earn) -->
            <section class="py-5" style="background: linear-gradient(135deg, var(--hirevo-primary, #0B1F3B) 0%, #162d4d 100%);">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-lg-7">
                            <div class="text-center text-lg-start">
                                <h4 class="text-white mb-2">Refer in your company & earn</h4>
                                <p class="text-white-50 mb-0">If you can refer candidates in your company, sign up here. Share your company name and how many people you can refer — we’ll get in touch so you can start earning.</p>
                            </div>
                        </div>
                        <div class="col-lg-5 mt-4 mt-lg-0 text-center text-lg-end">
                            <button type="button" class="btn btn-success btn-lg rounded-pill px-4 hirevo-cta-btn" data-bs-toggle="modal" data-bs-target="#referralSignupModal">
                                <i class="uil uil-user-plus me-1"></i> I can refer in my company
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END REFERRAL -->

            <!-- START SUBSCRIBE -->
            <section class="bg-subscribe">
                <div class="container">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-lg-6">
                            <div class="text-center text-lg-start">
                                <h4 class="text-white">Get New Jobs Notification!</h4>
                                <p class="text-white-50 mb-0">Subscribe & get all related jobs notification.</p>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mt-4 mt-lg-0">
                                <form class="subscribe-form" action="#">
                                    <div class="input-group justify-content-center justify-content-lg-end">
                                        <label for="subscribe" class="visually-hidden">Your email address</label>
                                        <input type="email" class="form-control" id="subscribe" name="subscribe" placeholder="Your email address" aria-label="Your email address" required>
                                        <button class="btn btn-primary hirevo-cta-btn" type="button" id="subscribebtn">Subscribe</button>
                                    </div>
                                    <div id="subscribe-message" class="hirevo-subscribe-message mt-2 text-center text-lg-end" aria-live="polite"></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="email-img d-none d-lg-block">
                    <img src="{{ asset($theme.'/assets/images/subscribe.png') }}" alt="" class="img-fluid">
                </div>
            </section>
            <!-- END SUBSCRIBE -->

            <!-- Referral Signup Modal -->
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
                                <p class="text-muted small mb-3">Share your details. We’ll contact you to help you refer candidates and start earning.</p>
                                <div class="mb-3">
                                    <label for="referral_company_name" class="form-label">Company name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="referral_company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Your company name" required>
                                    @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="referral_name" class="form-label">Your name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="referral_name" name="name" value="{{ old('name') }}" placeholder="Full name" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="referral_email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="referral_email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="referral_phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="referral_phone" name="phone" value="{{ old('phone') }}" placeholder="Phone number">
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
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- START FOOTER -->
            <footer class="hirevo-footer">
                <div class="hirevo-footer__main">
                    <div class="container">
                        <div class="row align-items-start">
                            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                                <div class="hirevo-footer__brand">
                                    <a href="{{ route('home') }}" class="hirevo-footer__logo d-inline-block">
                                        <picture>
                                            <source srcset="{{ asset('images/12575-2.svg')}}" type="image/svg+xml">
{{--                                            <source srcset="{{ asset('images/hirevo-logo.png')}}" type="image/svg+xml">--}}
                                        </picture>
                                    </a>
                                    <p class="hirevo-footer__tagline">Careers don’t grow with random applications. They grow with clarity, preparation, and the right opportunities.</p>
                                    <p class="hirevo-footer__follow">Follow us</p>
                                    <ul class="hirevo-footer__social">
                                        <li><a href="#" aria-label="Facebook"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a></li>
                                        <li><a href="#" aria-label="LinkedIn"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a></li>
                                        <li><a href="#" aria-label="Twitter"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="hirevo-footer__col">
                                    <h5 class="hirevo-footer__heading">Company</h5>
                                    <ul class="hirevo-footer__links">
                                        <li><a href="{{ route('about') }}">About Us</a></li>
                                        <li><a href="{{ route('contact') }}">Contact Us</a></li>
                                        <li><a href="{{ route('faq') }}">FAQ</a></li>
                                        <li><a href="{{ route('pricing') }}">Pricing</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="hirevo-footer__col">
                                    <h5 class="hirevo-footer__heading">For Jobs</h5>
                                    <ul class="hirevo-footer__links">
                                        <li><a href="{{ route('job-list') }}">Job Goals</a></li>
                                        <li><a href="{{ route('job-openings') }}">Job Openings</a></li>
                                        <li><a href="{{ route('resume.upload') }}">Resume Score</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="hirevo-footer__col">
                                    <h5 class="hirevo-footer__heading">Account</h5>
                                    <ul class="hirevo-footer__links">
                                        @auth
                                        <li><a href="{{ route('profile') }}">My Profile</a></li>
                                        @else
                                        <li><a href="{{ route('login') }}">Sign In</a></li>
                                        <li><a href="{{ route('register') }}">Sign Up</a></li>
                                        @endauth
                                    </ul>
                                </div>
                            </div>
                            <div class="col-lg-2 col-6">
                                <div class="hirevo-footer__col">
                                    <h5 class="hirevo-footer__heading">Support</h5>
                                    <ul class="hirevo-footer__links">
                                        <li><a href="{{ route('contact') }}">Contact Support</a></li>
                                        <li><a href="{{ route('help') }}">Help Center</a></li>
                                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                                        <li><a href="{{ route('terms') }}">Terms</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hirevo-footer__bottom">
                    <div class="container">
                        <p class="hirevo-footer__copyright">
                            <script>document.write(new Date().getFullYear())</script> &copy; Hirevo. Own Your Next Career Move. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
            <!-- END FOOTER -->

            <!-- Style switcher -->
            <div id="style-switcher" onclick="toggleSwitcher()" style="left: -165px;">
                <div>
                    <h6>Select your color</h6>
                    <ul class="pattern list-unstyled mb-0">
                        <li><a class="color-list color1" href="javascript: void(0);" onclick="setColorGreen()"></a></li>
                        <li><a class="color-list color2" href="javascript: void(0);" onclick="setColor('blue')"></a></li>
                        <li><a class="color-list color3" href="javascript: void(0);" onclick="setColor('green')"></a></li>
                    </ul>
                    <div class="mt-3">
                        <h6>Light/dark Layout</h6>
                        <div class="text-center mt-3">
                            <a href="javascript: void(0);" id="mode" class="mode-btn text-white rounded-3">
                                <i class="uil uil-brightness mode-dark mx-auto"></i>
                                <i class="uil uil-moon mode-light"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="bottom d-none d-md-block">
                    <a href="javascript: void(0);" class="settings rounded-end"><i class="mdi mdi-cog mdi-spin"></i></a>
                </div>
            </div>

            <button onclick="topFunction()" id="back-to-top"><i class="mdi mdi-arrow-up"></i></button>
        </div>
    </div>

    <script src="{{ asset($theme.'/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($theme.'/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset($theme.'/assets/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset($theme.'/assets/js/pages/switcher.init.js') }}"></script>
    <script src="{{ asset($theme.'/assets/js/app.js') }}"></script>
    <script>
    (function(){
        var preloader = document.getElementById('preloader');
        function hidePreloader() { if (preloader) { preloader.style.opacity = '0'; preloader.style.visibility = 'hidden'; preloader.style.transition = 'opacity 0.3s, visibility 0.3s'; } }
        if (document.readyState === 'complete') hidePreloader();
        else { document.addEventListener('DOMContentLoaded', hidePreloader); window.addEventListener('load', hidePreloader); setTimeout(hidePreloader, 1500); }
    })();
    </script>
    <script>
    (function(){
        document.addEventListener('DOMContentLoaded', function(){
            var btn = document.getElementById('subscribebtn');
            var input = document.getElementById('subscribe');
            var msg = document.getElementById('subscribe-message');
            if (!btn || !input || !msg) return;
            btn.addEventListener('click', function(){
                msg.classList.remove('text-success', 'text-danger');
                var val = (input.value || '').trim();
                var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!val) { msg.textContent = 'Please enter your email.'; msg.classList.add('text-danger'); return; }
                if (!emailRe.test(val)) { msg.textContent = 'Please enter a valid email address.'; msg.classList.add('text-danger'); return; }
                msg.textContent = 'Thanks for subscribing! We\'ll be in touch.';
                msg.classList.add('text-success');
                input.value = '';
            });
        });
    })();
    </script>
    @stack('scripts')
    @if($errors->has('company_name') || $errors->has('max_candidates'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modal = document.getElementById('referralSignupModal');
                if (modal && typeof bootstrap !== 'undefined') {
                    new bootstrap.Modal(modal).show();
                }
            });
        </script>
    @endif
</body>
</html>
