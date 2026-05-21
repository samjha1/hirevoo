@extends('layouts.app')

@section('title', 'FAQ')

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
    @endphp
    <section class="section py-5">
        <div class="container" style="max-width: 980px;">
            <div class="mb-4">
                <h1 class="h3 fw-bold mb-2">Frequently Asked Questions</h1>
                <p class="text-muted mb-0">Still have questions? We're always open to helping you.</p>
            </div>

            <div class="rounded-4 overflow-hidden shadow-sm mb-4">
                <img src="{{ $siteImg('krakenimages-376KN_ISplE-unsplash.jpg') }}" alt="Team collaboration and learning" class="w-100 hirevo-site-photo" style="max-height: 200px; object-fit: cover; object-position: center 30%;" loading="lazy" width="1200" height="200">
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700 mb-3">General</h2>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">What is Hirevoo?</h3>
                        <p class="text-muted mb-0">Hirevoo helps you understand your profile, improve where it matters, and connect with opportunities that actually match you.</p>
                    </div>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">How is Hirevoo different from other job platforms?</h3>
                        <p class="text-muted mb-0">Most platforms focus on showing more jobs. Hirevoo focuses on helping you understand what's missing in your profile and how to improve before applying.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="h6 fw-600 mb-1">Is Hirevoo only for freshers?</h3>
                        <p class="text-muted mb-0">No. While we're especially helpful for students and freshers, anyone looking for better direction and relevant opportunities can use Hirevoo.</p>
                    </div>

                    <h2 class="h5 fw-700 mb-3">For candidates</h2>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">How do I get started?</h3>
                        <p class="text-muted mb-0">Start by uploading your resume, exploring job roles, and browsing opportunities that match your profile.</p>
                    </div>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">Does Hirevoo guarantee a job?</h3>
                        <p class="text-muted mb-0">No platform can guarantee a job. We help you improve your chances by giving clarity, direction, and access to relevant opportunities.</p>
                    </div>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">How does resume analysis help me?</h3>
                        <p class="text-muted mb-0">It helps you understand whether your resume is strong enough, what skills you might be missing, and what to improve before applying.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="h6 fw-600 mb-1">Are the job opportunities verified?</h3>
                        <p class="text-muted mb-0">We aim to share relevant and genuine opportunities. However, we recommend users verify details before applying.</p>
                    </div>

                    <h2 class="h5 fw-700 mb-3">Referrals &amp; community</h2>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">How does the referral system work?</h3>
                        <p class="text-muted mb-0">If you work in a company and know open roles, you can refer candidates through Hirevoo. Rewards (if applicable) depend on successful hiring and verification.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="h6 fw-600 mb-1">Will I receive spam?</h3>
                        <p class="text-muted mb-0">No. We focus on sharing only relevant and useful updates.</p>
                    </div>

                    <h2 class="h5 fw-700 mb-3">Businesses</h2>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">How can companies use Hirevoo?</h3>
                        <p class="text-muted mb-0">Businesses can connect with relevant candidates and simplify hiring decisions.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="h6 fw-600 mb-1">Is Hirevoo a recruitment agency?</h3>
                        <p class="text-muted mb-0">No. Hirevoo is a platform that helps connect candidates and opportunities, not a traditional recruitment agency.</p>
                    </div>

                    <h2 class="h5 fw-700 mb-3">Privacy &amp; safety</h2>
                    <div class="mb-3">
                        <h3 class="h6 fw-600 mb-1">Is my data safe?</h3>
                        <p class="text-muted mb-0">We take reasonable measures to protect your data and use it only to improve your experience and provide relevant opportunities.</p>
                    </div>
                    <div class="mb-0">
                        <h3 class="h6 fw-600 mb-1">Can I delete my data?</h3>
                        <p class="text-muted mb-0">Yes. You can request deletion by contacting support.</p>
                    </div>

                    <hr class="my-4">
                    <p class="text-muted mb-0">
                        Still have questions? <a href="{{ route('help') }}">Visit Help Center</a> or <a href="{{ route('contact') }}">contact support</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
