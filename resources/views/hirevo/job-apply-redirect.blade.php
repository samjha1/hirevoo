@extends('layouts.app')

@section('title', 'Redirecting to employer site')

@section('content')
    @php
        $jobsIndexUrl = route('job-openings');
    @endphp
    <section class="section py-5">
        <div class="container" style="max-width: 760px;">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-700 mb-2">Almost done</h1>
                    <p class="text-muted mb-3">
                        Your application has been saved on Hirevo.
                        The employer’s apply page will open in a <strong>new tab</strong> so you can finish there. This tab will stay on Hirevo.
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
                        <a href="{{ $applyLink }}" class="btn btn-primary rounded-pill" target="_blank" rel="noreferrer noopener">Open employer site in new tab</a>
                        <a href="{{ $jobsIndexUrl }}" class="btn btn-outline-secondary rounded-pill">Back to jobs</a>
                    </div>

                    <p class="text-muted small mt-3 mb-0" id="redirectStatus">
                        Opening employer site in a new tab in <span id="redirectSeconds">2</span>s…
                    </p>
                    <p class="text-muted small mt-2 mb-0 d-none" id="redirectBlocked" role="status"></p>

                    <noscript>
                        <p class="text-muted small mt-3 mb-0">
                            JavaScript is disabled. Use “Open employer site in new tab” above.
                        </p>
                    </noscript>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function () {
            var applyLink = @json($applyLink);
            var seconds = 2;
            var el = document.getElementById('redirectSeconds');
            var statusEl = document.getElementById('redirectStatus');
            var blockedEl = document.getElementById('redirectBlocked');

            var tick = function () {
                if (el) {
                    el.textContent = String(seconds);
                }
                if (seconds <= 0) {
                    var w = window.open(applyLink, '_blank', 'noopener,noreferrer');
                    if (w) {
                        if (statusEl) {
                            statusEl.textContent = 'Employer site opened in a new tab. Finish your application there — this Hirevo tab stays open.';
                        }
                    } else {
                        if (statusEl) {
                            statusEl.classList.add('d-none');
                        }
                        if (blockedEl) {
                            blockedEl.textContent = 'Your browser blocked the automatic new tab. Click “Open employer site in new tab” above.';
                            blockedEl.classList.remove('d-none');
                        }
                    }
                    return;
                }
                seconds -= 1;
                setTimeout(tick, 1000);
            };
            setTimeout(tick, 250);
        })();
    </script>
@endsection
