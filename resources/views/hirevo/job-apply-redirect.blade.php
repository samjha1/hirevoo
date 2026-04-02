@extends('layouts.app')

@section('title', 'Redirecting to employer site')

@section('content')
    <section class="section py-5">
        <div class="container" style="max-width: 760px;">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-700 mb-2">Almost done</h1>
                    <p class="text-muted mb-3">
                        Your application has been saved on Hirevo.
                        You will be redirected to the employer’s website to complete your application.
                    </p>

                    <div class="alert alert-light border rounded-3 mb-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="uil uil-external-link-alt fs-18 mt-1"></i>
                            <div class="small">
                                <div class="fw-600 text-dark">{{ $job->title }}</div>
                                <div class="text-muted text-break">{{ $applyLink }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ $applyLink }}" class="btn btn-primary rounded-pill" rel="noreferrer noopener">Continue to company site</a>
                        <a href="{{ route('job-openings') }}" class="btn btn-outline-secondary rounded-pill">Back to jobs</a>
                    </div>

                    <p class="text-muted small mt-3 mb-0">
                        Redirecting in <span id="redirectSeconds">2</span> seconds…
                    </p>

                    <noscript>
                        <p class="text-muted small mt-3 mb-0">
                            JavaScript is disabled. Please click “Continue to company site”.
                        </p>
                    </noscript>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var seconds = 2;
            var el = document.getElementById('redirectSeconds');
            var tick = function () {
                if (el) el.textContent = String(seconds);
                if (seconds <= 0) {
                    window.location.href = @json($applyLink);
                    return;
                }
                seconds -= 1;
                setTimeout(tick, 1000);
            };
            setTimeout(tick, 250);
        })();
    </script>
@endsection

