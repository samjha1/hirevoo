@extends('layouts.app')

@section('title', 'Help Center')

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
    @endphp
    <section class="section py-5">
        <div class="container" style="max-width: 1100px;">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold mb-2">How can we help you?</h1>
                <p class="text-muted mb-0">Find answers, get support, and move forward with clarity.</p>
            </div>

            <div class="text-center mb-4">
                <img src="{{ $siteImg('Image 2.PNG') }}" alt="Guides for jobs, your profile, and account settings" class="img-fluid hirevo-site-illustration mx-auto d-none d-md-inline-block" style="max-height: 200px;" loading="lazy" width="520" height="200">
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <label for="helpSearch" class="form-label fw-600">Search</label>
                    <input id="helpSearch" type="search" class="form-control form-control-lg" placeholder="Search for help (e.g. resume, jobs, account)">
                    <p class="text-muted small mt-2 mb-0">Tip: If you’re unsure where to start, begin with your resume.</p>
                </div>
            </div>

            <div class="row g-3 g-lg-4 mb-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Getting Started</div>
                            <div class="text-muted small">Understand how Hirevoo works and where to begin.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Resume &amp; Profile</div>
                            <div class="text-muted small">Improve your resume and understand your profile better.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Jobs &amp; Opportunities</div>
                            <div class="text-muted small">Find and apply to relevant job opportunities.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Referrals &amp; Community</div>
                            <div class="text-muted small">Learn how referrals and updates work.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Account &amp; Access</div>
                            <div class="text-muted small">Sign in, manage your account, and settings.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="fw-700 mb-1">Privacy &amp; Safety</div>
                            <div class="text-muted small">Your data, security, and platform usage.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">Common questions</h2>
                    <div class="mb-3">
                        <div class="fw-600">How do I start using Hirevoo?</div>
                        <div class="text-muted">Start by uploading your resume or exploring job roles. This helps you understand where you stand and what to improve.</div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-600">Why am I not getting job responses?</div>
                        <div class="text-muted">It usually comes down to profile clarity, resume quality, or role mismatch. Hirevoo helps you identify and fix these gaps.</div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-600">How do I improve my chances of getting hired?</div>
                        <div class="text-muted">Focus on improving your resume, understanding required skills, and applying to roles that match your profile.</div>
                    </div>
                    <div class="mb-3">
                        <div class="fw-600">Are the opportunities real and verified?</div>
                        <div class="text-muted">We aim to share relevant and genuine opportunities. Still, we recommend verifying details before applying.</div>
                    </div>
                    <div class="mb-0">
                        <div class="fw-600">How does the referral system work?</div>
                        <div class="text-muted">If someone refers you through Hirevoo, it increases your chances of visibility. Referral rewards depend on successful hiring.</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-2">Still need help?</h2>
                    <p class="text-muted mb-3">If you couldn’t find what you’re looking for, feel free to reach out. Response time: usually within 24–48 hours.</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary rounded-pill px-4">Contact Support</a>
                    <div class="text-muted small mt-3">We’re building Hirevoo step by step. Every question helps us improve.</div>
                </div>
            </div>
        </div>
    </section>
@endsection

