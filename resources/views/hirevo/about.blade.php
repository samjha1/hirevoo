@extends('layouts.app')

@section('title', 'About')

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
    @endphp
    <section class="section py-5">
        <div class="container" style="max-width: 920px;">
            <div class="text-center mb-4">
                <h1 class="h2 fw-bold mb-2">We didn’t start Hirevoo to build another job platform.</h1>
                <p class="text-muted mb-0">Hirevoo is built to make careers less confusing and opportunities more accessible.</p>
            </div>

            <div class="rounded-4 overflow-hidden shadow-sm mb-4 mb-lg-5">
                <img src="{{ $siteImg('headway-5QgIuuBxKwM-unsplash.jpg') }}" alt="People collaborating at work" class="w-100 hirevo-site-photo" style="max-height: 260px; object-fit: cover; object-position: center;" loading="lazy" width="1200" height="260">
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">Why we started</h2>
                    <p class="text-muted mb-3">
                        We started Hirevoo after noticing something very simple.
                        A lot of people are trying hard — learning, applying, and putting in the effort — but still things don’t work out the way they should.
                    </p>
                    <p class="text-muted mb-0">
                        Not because they lack talent. But because they lack clarity, direction, and access to the right opportunities.
                        That gap is bigger than it looks. And that’s where Hirevoo comes in.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">What we believe</h2>
                    <ul class="text-muted mb-3 ps-3">
                        <li>Careers should not depend on guesswork.</li>
                        <li>Opportunities should not be hard to find.</li>
                        <li>Talent should not go unnoticed.</li>
                    </ul>
                    <p class="text-muted mb-0">
                        Today, the system is noisy. People apply to hundreds of jobs without knowing what’s missing.
                        Companies struggle to find the right candidates despite so many applicants.
                        Most people are just trying to figure it out on their own.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">What we’re building</h2>
                    <p class="text-muted mb-0">
                        Hirevoo is not just a job platform. We’re building something more practical —
                        a place where people understand their profile, know what they need to improve, and find opportunities that actually match them.
                        Instead of pushing people to apply more, we want to help them apply better.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">Who it’s for</h2>
                    <ul class="text-muted mb-0 ps-3">
                        <li>Students trying to figure out where to start</li>
                        <li>Freshers who feel lost in the job search process</li>
                        <li>Job seekers who want better opportunities</li>
                        <li>Businesses that want relevant, ready talent</li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted mb-3">Because one right opportunity can change everything — and we believe more people deserve access to that.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => route('resume.upload')]) }}" class="btn btn-primary rounded-pill px-4">Start with your resume</a>
                    <a href="{{ route('job-openings') }}" class="btn btn-outline-primary rounded-pill px-4">Browse jobs</a>
                </div>
            </div>
        </div>
    </section>
@endsection
