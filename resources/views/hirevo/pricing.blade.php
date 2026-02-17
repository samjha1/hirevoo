@extends('layouts.app')

@section('title', 'Pricing')

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">Pricing</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="javascript:void(0)">Company</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Pricing</li>
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

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-5">
                        <h6 class="sub-title">Pricing</h6>
                        <h2 class="fw-bold mb-3">Hirevo Plans</h2>
                        <p class="text-muted">Premium subscription for candidates. Referral requests & AI resume tools.</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="mb-2">Premium Candidate</h5>
                                <h2 class="mb-0">₹999<span class="fs-16 text-muted fw-normal">/month</span></h2>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Resume optimization</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> AI resume score</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Referral readiness score</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> 3 referral requests/month</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Access to verified referrers</li>
                            </ul>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="mb-2">Referrer (Employee)</h5>
                                <h2 class="mb-0">Free</h2>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Company email verification</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Receive referral requests</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Success reward ₹2,000–₹5,000 per hire</li>
                            </ul>
                            <a href="{{ route('register') }}?role=referrer" class="btn btn-soft-primary w-100">Join as Referrer</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h5 class="mb-2">EdTech Partner</h5>
                                <h2 class="mb-0">Bidding</h2>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> View skill-gap leads</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Bid on leads</li>
                                <li class="mb-2"><i class="uil uil-check text-success me-2"></i> Unlock candidate contact on win</li>
                            </ul>
                            <a href="{{ route('register') }}?role=edtech" class="btn btn-soft-primary w-100">Register as EdTech</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
