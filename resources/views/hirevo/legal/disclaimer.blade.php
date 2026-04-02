@extends('layouts.app')

@section('title', 'Disclaimers')

@section('content')
    <section class="section py-5">
        <div class="container" style="max-width: 980px;">
            <h1 class="h3 fw-bold mb-3">Disclaimers</h1>
            <p class="text-muted mb-4">Important information about using Hirevoo.</p>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-700">General Disclaimer</h2>
                    <p class="text-muted">Hirevoo provides career-related tools and opportunities but does not guarantee job placement, interview selection, or hiring outcomes.</p>

                    <h2 class="h5 fw-700 mt-4">Accuracy Disclaimer</h2>
                    <p class="text-muted">While we aim to provide accurate information, we do not guarantee completeness or correctness. Users should verify opportunities independently.</p>

                    <h2 class="h5 fw-700 mt-4">Third-Party Disclaimer</h2>
                    <p class="text-muted">Hirevoo is not responsible for external websites or recruiters, actions of third-party employers, or third-party interactions.</p>

                    <h2 class="h5 fw-700 mt-4">Use at Your Own Risk</h2>
                    <p class="text-muted mb-0">Users are responsible for decisions made using the platform.</p>
                </div>
            </div>
        </div>
    </section>
@endsection

