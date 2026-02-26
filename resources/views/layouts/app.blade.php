<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="{{ rtrim(config('app.asset_url') ?? config('app.url'), '/') }}/">
    <title>@yield('title', 'Home') | Hirevo - AI Career Intelligence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hirevo - AI Career Intelligence + Referral Network + Skill Monetization">
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
                    <img src="{{ asset('images/hirevo-logo.png') }}" alt="Hirevo" class="hirevo-logo logo-dark">
                    <img src="{{ asset('images/hirevo-logo.png') }}" alt="Hirevo" class="hirevo-logo logo-light">
                </a>
                <button class="navbar-toggler hirevo-nav-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-label="Toggle navigation">
                    <i class="mdi mdi-menu fs-28"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mx-auto navbar-center hirevo-nav-links">
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
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" href="{{ route('admin.employers.index') }}">Employers</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        @else
                        @endif
                        @endauth
                        @guest
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('job-list') }}">Job Goals</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('job-openings') }}">Jobs</a></li>
                            <li class="nav-item"><a class="nav-link nav-link-resume {{ request()->routeIs('resume.*') ? 'active' : '' }}" href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => url('/resume/upload')]) }}">Resume Score</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('pricing') }}">Pricing</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                        @endguest
                        @auth
                        @if(!auth()->user()->isReferrer() && !auth()->user()->isAdmin())
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('job-list') }}">Job Goals</a></li>
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
                            <a href="javascript:void(0)" class="nav-link hirevo-nav-icon position-relative" id="notification" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-bell fs-20"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">0</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-0" aria-labelledby="notification">
                                <div class="px-3 py-3 border-bottom bg-light">
                                    <h6 class="mb-0">Notifications</h6>
                                    <p class="text-muted small mb-0">You have 0 unread</p>
                                </div>
                                <div class="p-2 text-center">
                                    <a class="dropdown-item small text-primary" href="javascript:void(0)">View all</a>
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
                                    <li><a class="dropdown-item" href="{{ route('admin.employers.index') }}">Manage Employers</a></li>
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
            <div class="page-content">
                @yield('content')
            </div>

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
                                        <input type="text" class="form-control" id="subscribe" placeholder="Enter your email">
                                        <button class="btn btn-primary" type="button" id="subscribebtn">Subscribe</button>
                                    </div>
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

            <!-- START FOOTER -->
            <section class="bg-footer">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="footer-item mt-4 mt-lg-0 me-lg-5">
                                <h4 class="text-white mb-4">Hirevo</h4>
                                <p class="text-white-50">AI Career Intelligence + Referral Network + Skill Monetization Engine. Find your dream role with skill-gap analysis and verified referrals.</p>
                                <p class="text-white mt-3">Follow Us on:</p>
                                <ul class="footer-social-menu list-inline mb-0">
                                    <li class="list-inline-item"><a href="#"><i class="uil uil-facebook-f"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="uil uil-linkedin-alt"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="uil uil-google"></i></a></li>
                                    <li class="list-inline-item"><a href="#"><i class="uil uil-twitter"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="footer-item mt-4 mt-lg-0">
                                <p class="fs-16 text-white mb-4">Company</p>
                                <ul class="list-unstyled footer-list mb-0">
                                    <li><a href="{{ route('about') }}"><i class="mdi mdi-chevron-right"></i> About Us</a></li>
                                    <li><a href="{{ route('contact') }}"><i class="mdi mdi-chevron-right"></i> Contact Us</a></li>
                                    <li><a href="{{ route('pricing') }}"><i class="mdi mdi-chevron-right"></i> Pricing</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="footer-item mt-4 mt-lg-0">
                                <p class="fs-16 text-white mb-4">For Jobs</p>
                                <ul class="list-unstyled footer-list mb-0">
                                    <li><a href="{{ route('job-list') }}"><i class="mdi mdi-chevron-right"></i> Job Goals</a></li>
                                    <li><a href="{{ route('job-openings') }}"><i class="mdi mdi-chevron-right"></i> Job Openings</a></li>
                                    <li><a href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => url('/resume/upload')]) }}"><i class="mdi mdi-chevron-right"></i> Resume Score</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="footer-item mt-4 mt-lg-0">
                                <p class="text-white fs-16 mb-4">Account</p>
                                <ul class="list-unstyled footer-list mb-0">
                                    @auth
                                    <li><a href="{{ route('profile') }}"><i class="mdi mdi-chevron-right"></i> My Profile</a></li>
                                    @else
                                    <li><a href="{{ route('login') }}"><i class="mdi mdi-chevron-right"></i> Sign In</a></li>
                                    <li><a href="{{ route('register') }}"><i class="mdi mdi-chevron-right"></i> Sign Up</a></li>
                                    @endauth
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="footer-item mt-4 mt-lg-0">
                                <p class="fs-16 text-white mb-4">Support</p>
                                <ul class="list-unstyled footer-list mb-0">
                                    <li><a href="{{ route('contact') }}"><i class="mdi mdi-chevron-right"></i> Help Center</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- END FOOTER -->

            <div class="footer-alt">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <p class="text-white-50 text-center mb-0">
                                <script>document.write(new Date().getFullYear())</script> &copy; Hirevo - AI Career Intelligence.<a href="" target="_blank" class="text-reset text-decoration-underline"></a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

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
    @stack('scripts')
</body>
</html>
