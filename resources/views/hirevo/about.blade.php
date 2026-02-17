@extends('layouts.app')

@section('title', 'About')

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">About Us</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="javascript:void(0)">Company</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end page title -->

    <!-- START SHAPE -->
    <div class="position-relative" style="z-index: 1">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 1440 250">
                <path fill="" fill-opacity="1" d="M0,192L120,202.7C240,213,480,235,720,234.7C960,235,1200,213,1320,202.7L1440,192L1440,320L1320,320C1200,320,960,320,720,320C480,320,240,320,120,320L0,320Z"></path>
            </svg>
        </div>
    </div>
    <!-- END SHAPE -->

    <!-- START ABOUT -->
    <section class="section overflow-hidden">
        <div class="container">
            <div class="row align-items-center g-0">
                <div class="col-lg-6">
                    <div class="section-title me-lg-5">
                        <h6 class="sub-title">About Us</h6>
                        <h2 class="title mb-4">Why choose <span class="text-primary fw-bold">Hirevo</span>?</h2>
                        <p class="text-muted">Hirevo is an AI-powered Career Intelligence Platform combining skill-gap analysis, premium referral access, and EdTech lead monetization in one place. We help candidates find their dream role and bridge skill gaps with data-driven insights.</p>
                        <div class="row mt-4 pt-2">
                            <div class="col-md-6">
                                <ul class="list-unstyled about-list text-muted mb-0 mb-md-3">
                                    <li><i class="uil uil-check text-primary me-2"></i> Skill gap analysis</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> Job goal matching</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> Referral marketplace</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> Learning roadmap</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled about-list text-muted">
                                    <li><i class="uil uil-check text-primary me-2"></i> AI resume scoring</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> EdTech lead bidding</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> Verified referrers</li>
                                    <li><i class="uil uil-check text-primary me-2"></i> Premium plans</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('contact') }}" class="btn btn-primary btn-hover">Contact Us <i class="uil uil-angle-right-b align-middle"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-img mt-4 mt-lg-0 text-center">
                        <img src="{{ asset($theme.'/assets/images/about/img-01.jpg') }}" alt="" class="img-fluid rounded" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22 viewBox=%220 0 400 300%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22400%22 height=%22300%22/%3E%3Ctext fill=%22%23999%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EAbout Hirevo%3C/text%3E%3C/svg%3E'">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END ABOUT -->

    <!-- COUNTER START -->
    <section class="section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="counter-box mt-3">
                        <div class="counters text-center">
                            <h5 class="counter mb-0">3</h5>
                            <h6 class="fs-16 mt-3">User Types</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="counter-box mt-3">
                        <div class="counters text-center">
                            <h5 class="counter mb-0">2</h5>
                            <h6 class="fs-16 mt-3">Revenue Models</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="counter-box mt-3">
                        <div class="counters text-center">
                            <h5 class="counter mb-0">AI</h5>
                            <h6 class="fs-16 mt-3">Career Engine</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="counter-box mt-3">
                        <div class="counters text-center">
                            <h5 class="counter mb-0">24/7</h5>
                            <h6 class="fs-16 mt-3">Support</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- COUNTER END -->

    <!-- Key Features -->
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="section-title text-center mb-5">
                        <h3 class="title mb-4">What we offer</h3>
                        <p class="para-desc text-muted mx-auto">Skill-gap analysis, referral network, and EdTech lead marketplace in one platform.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="about-feature p-3 d-flex align-items-center rounded-3">
                        <div class="featrue-icon flex-shrink-0">
                            <i class="uim uim-object-ungroup"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fs-18 mb-0">Skill gap analysis</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="about-feature p-3 d-flex align-items-center rounded-3">
                        <div class="featrue-icon flex-shrink-0">
                            <i class="uim uil-user-check"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fs-18 mb-0">Referral marketplace</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="about-feature p-3 d-flex align-items-center rounded-3">
                        <div class="featrue-icon flex-shrink-0">
                            <i class="uim uil-book-open"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="fs-18 mb-0">EdTech lead bidding</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
