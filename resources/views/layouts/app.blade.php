<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Home') | Hirevo - AI Career Intelligence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hirevo - AI Career Intelligence + Referral Network + Skill Monetization">
    <meta content="Hirevo" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset($theme.'/assets/images/favicon.ico') }}">

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
        <!-- START TOP-BAR -->
        <div class="top-bar">
            <div class="container-fluid custom-container">
                <div class="row g-0 align-items-center">
                    <div class="col-md-7">
                        <ul class="list-inline mb-0 text-center text-md-start">
                            <li class="list-inline-item">
                                <p class="fs-13 mb-0"> <i class="mdi mdi-map-marker"></i> Your Location: <a href="javascript:void(0)" class="text-dark">India</a></p>
                            </li>
                            <li class="list-inline-item">
                                <ul class="topbar-social-menu list-inline mb-0">
                                    <li class="list-inline-item"><a href="javascript:void(0)" class="social-link"><i class="uil uil-whatsapp"></i></a></li>
                                    <li class="list-inline-item"><a href="javascript:void(0)" class="social-link"><i class="uil uil-facebook-messenger-alt"></i></a></li>
                                    <li class="list-inline-item"><a href="javascript:void(0)" class="social-link"><i class="uil uil-instagram"></i></a></li>
                                    <li class="list-inline-item"><a href="javascript:void(0)" class="social-link"><i class="uil uil-envelope"></i></a></li>
                                    <li class="list-inline-item"><a href="javascript:void(0)" class="social-link"><i class="uil uil-twitter-alt"></i></a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-5">
                        <ul class="list-inline mb-0 text-center text-md-end">
                            @guest
                            <li class="list-inline-item py-2 me-2 align-middle">
                                <a href="{{ route('login') }}" class="text-dark fw-medium fs-13"><i class="uil uil-sign-in-alt"></i> Sign In</a>
                            </li>
                            <li class="list-inline-item py-2 me-2 align-middle">
                                <a href="{{ route('register') }}" class="text-dark fw-medium fs-13"><i class="uil uil-lock"></i> Sign Up</a>
                            </li>
                            @endguest
                            @auth
                            <li class="list-inline-item py-2 me-2 align-middle">
                                <a href="{{ route('profile') }}" class="text-dark fw-medium fs-13"><i class="uil uil-user"></i> Profile</a>
                            </li>
                            <li class="list-inline-item align-middle">
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf<button type="submit" class="btn btn-link text-dark fw-medium fs-13 p-0 border-0"><i class="uil uil-sign-out-alt"></i> Sign Out</button></form>
                            </li>
                            @endauth
                            <li class="list-inline-item align-middle">
                                <div class="dropdown d-inline-block language-switch">
                                    <button type="button" class="btn" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img id="header-lang-img" src="{{ asset($theme.'/assets/images/flags/us.jpg') }}" alt="Header Language" height="16">
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="eng"><img src="{{ asset($theme.'/assets/images/flags/us.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">English</span></a>
                                        <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="sp"><img src="{{ asset($theme.'/assets/images/flags/spain.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Spanish</span></a>
                                        <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="gr"><img src="{{ asset($theme.'/assets/images/flags/germany.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">German</span></a>
                                        <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="it"><img src="{{ asset($theme.'/assets/images/flags/italy.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Italian</span></a>
                                        <a href="javascript:void(0);" class="dropdown-item notify-item language" data-lang="ru"><img src="{{ asset($theme.'/assets/images/flags/russia.jpg') }}" alt="user-image" class="me-1" height="12"> <span class="align-middle">Russian</span></a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TOP-BAR -->

        <!-- Navbar Start -->
        <nav class="navbar navbar-expand-lg fixed-top sticky" id="navbar">
            <div class="container-fluid custom-container">
                <a class="navbar-brand text-dark fw-bold me-auto" href="{{ route('home') }}">
                    <img src="{{ asset($theme.'/assets/images/logo-dark.png') }}" height="22" alt="" class="logo-dark">
                    <img src="{{ asset($theme.'/assets/images/logo-light.png') }}" height="22" alt="" class="logo-light">
                </a>
                <div>
                    <button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-label="Toggle navigation">
                        <i class="mdi mdi-menu"></i>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mx-auto navbar-center">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('job-list') }}">Job Goals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('pricing') }}">Pricing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('about') }}">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('contact') }}">Contact</a>
                        </li>
                    </ul>
                    <ul class="header-menu list-inline d-flex align-items-center mb-0">
                        @guest
                        <li class="list-inline-item align-items-center d-flex me-2">
                            <span class="text-muted fs-13 me-2 d-none d-lg-inline">Student</span>
                            <a href="{{ route('login') }}" class="nav-link text-primary fw-medium p-0 me-2">Sign in</a>
                            <a href="{{ route('register', ['role' => 'candidate']) }}" class="btn btn-primary btn-sm rounded-pill px-3">Sign up</a>
                        </li>
                        <li class="list-inline-item border-start border-2 border-secondary ms-2 ps-3 me-2" style="height: 24px;"></li>
                        <li class="list-inline-item align-items-center d-flex">
                            <span class="text-muted fs-13 me-2 d-none d-lg-inline">Employer</span>
                            <a href="{{ route('login') }}" class="nav-link text-primary fw-medium p-0 me-2">Sign in</a>
                            <a href="{{ route('register', ['role' => 'referrer']) }}" class="btn btn-primary btn-sm rounded-pill px-3">Sign up</a>
                        </li>
                        @else
                        <li class="list-inline-item dropdown me-4">
                            <a href="javascript:void(0)" class="header-item noti-icon position-relative" id="notification" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-bell fs-22"></i>
                                <div class="count position-absolute">0</div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end p-0" aria-labelledby="notification">
                                <div class="notification-header border-bottom bg-light">
                                    <h6 class="mb-1">Notification</h6>
                                    <p class="text-muted fs-13 mb-0">You have 0 unread notifications</p>
                                </div>
                                <div class="notification-footer border-top text-center">
                                    <a class="primary-link fs-13" href="javascript:void(0)">View More..</a>
                                </div>
                            </div>
                        </li>
                        <li class="list-inline-item dropdown">
                            <a href="javascript:void(0)" class="header-item" id="userdropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ asset($theme.'/assets/images/profile.jpg') }}" alt="mdo" width="35" height="35" class="rounded-circle me-1">
                                <span class="d-none d-md-inline-block fw-medium">Hi, {{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown">
                                <li><a class="dropdown-item" href="{{ route('profile') }}">My Profile</a></li>
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
                                <script>document.write(new Date().getFullYear())</script> &copy; Hirevo - AI Career Intelligence. Template by <a href="https://themeforest.net/search/themesdesign" target="_blank" class="text-reset text-decoration-underline">Themesdesign</a>
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
