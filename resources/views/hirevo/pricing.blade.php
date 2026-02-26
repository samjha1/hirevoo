@extends('layouts.app')

@section('title', 'Pricing')

@section('content')
    <section class="section py-4">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Pricing</li>
                </ol>
            </nav>

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
