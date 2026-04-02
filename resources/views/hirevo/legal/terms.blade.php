@extends('layouts.app')

@section('title', 'Terms & Conditions')

@section('content')
    <section class="section py-5">
        <div class="container" style="max-width: 980px;">
            <h1 class="h3 fw-bold mb-3">Terms &amp; Conditions</h1>
            <p class="text-muted mb-4">By using Hirevoo, you agree to the terms below.</p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700">1. Introduction</h2>
                    <p class="text-muted">Welcome to Hirevoo. By accessing or using our platform, you agree to comply with and be bound by these Terms of Use. If you do not agree, please do not use the platform.</p>

                    <h2 class="h5 fw-700 mt-4">2. Eligibility</h2>
                    <p class="text-muted">You must be at least 18 years old or have parental/guardian consent to use Hirevoo.</p>

                    <h2 class="h5 fw-700 mt-4">3. Use of Services</h2>
                    <p class="text-muted mb-2">You agree to:</p>
                    <ul class="text-muted ps-3">
                        <li>Provide accurate and complete information</li>
                        <li>Use the platform only for lawful purposes</li>
                        <li>Not misuse, disrupt, or attempt unauthorized access</li>
                    </ul>
                    <p class="text-muted mb-2">You must not:</p>
                    <ul class="text-muted ps-3">
                        <li>Submit false or misleading information</li>
                        <li>Impersonate another individual or entity</li>
                        <li>Use the platform for fraudulent activities</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">4. User Content</h2>
                    <ul class="text-muted ps-3">
                        <li>You retain ownership of your content</li>
                        <li>You grant Hirevoo permission to use it to provide services</li>
                        <li>You confirm the content is accurate and lawful</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">5. Job Listings &amp; Referrals</h2>
                    <ul class="text-muted ps-3">
                        <li>Hirevoo does not guarantee job placement</li>
                        <li>Hirevoo does not control hiring decisions</li>
                        <li>Hirevoo acts only as a platform connecting users and opportunities</li>
                    </ul>
                    <p class="text-muted mb-2">Referral rewards (if applicable):</p>
                    <ul class="text-muted ps-3">
                        <li>Are subject to verification</li>
                        <li>May be modified or discontinued</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">6. Limitation of Liability</h2>
                    <p class="text-muted mb-2">Hirevoo is not liable for:</p>
                    <ul class="text-muted ps-3">
                        <li>Job outcomes or hiring decisions</li>
                        <li>Losses from third-party interactions</li>
                        <li>Platform downtime or technical issues</li>
                    </ul>
                    <p class="text-muted mb-0">Use of the platform is at your own risk.</p>

                    <h2 class="h5 fw-700 mt-4">7. Termination</h2>
                    <p class="text-muted mb-2">We reserve the right to:</p>
                    <ul class="text-muted ps-3">
                        <li>Suspend or terminate accounts</li>
                        <li>Remove content violating terms</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">8. Changes to Terms</h2>
                    <p class="text-muted">We may update these terms periodically. Continued use means acceptance of updates.</p>

                    <h2 class="h5 fw-700 mt-4">9. Contact</h2>
                    <p class="text-muted mb-0">For questions, contact us via the <a href="{{ route('contact') }}">support page</a>.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

