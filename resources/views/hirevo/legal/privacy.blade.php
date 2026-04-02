@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <section class="section py-5">
        <div class="container" style="max-width: 980px;">
            <h1 class="h3 fw-bold mb-3">Privacy Policy</h1>
            <p class="text-muted mb-4">This policy explains how we collect, use, and protect your information.</p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700">1. Introduction</h2>
                    <p class="text-muted">Your privacy is important to us. This policy explains how we collect, use, and protect your information.</p>

                    <h2 class="h5 fw-700 mt-4">2. Information We Collect</h2>
                    <ul class="text-muted ps-3">
                        <li>Name, email, and profile details</li>
                        <li>Resume and uploaded documents</li>
                        <li>Usage and interaction data</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">3. How We Use Information</h2>
                    <ul class="text-muted ps-3">
                        <li>Provide relevant opportunities</li>
                        <li>Improve platform functionality</li>
                        <li>Communicate updates</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">4. Data Sharing</h2>
                    <p class="text-muted mb-2">We may share data with:</p>
                    <ul class="text-muted ps-3">
                        <li>Recruiters or employers (relevant information only)</li>
                        <li>Service providers supporting our platform</li>
                    </ul>
                    <p class="text-muted mb-0">We do not sell personal data.</p>

                    <h2 class="h5 fw-700 mt-4">5. Data Security</h2>
                    <p class="text-muted">We implement reasonable safeguards to protect your data. However, no system is completely secure.</p>

                    <h2 class="h5 fw-700 mt-4">6. Your Rights</h2>
                    <ul class="text-muted ps-3">
                        <li>Access or update your data</li>
                        <li>Request deletion</li>
                        <li>Contact us for concerns</li>
                    </ul>

                    <h2 class="h5 fw-700 mt-4">7. Data Retention</h2>
                    <p class="text-muted">We retain data only as long as necessary to provide services.</p>

                    <h2 class="h5 fw-700 mt-4">8. Updates</h2>
                    <p class="text-muted">This policy may change over time. Continued use implies acceptance.</p>

                    <h2 class="h5 fw-700 mt-4">9. Contact</h2>
                    <p class="text-muted mb-0">For privacy questions, reach out via <a href="{{ route('contact') }}">Contact</a>.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

