@extends('layouts.app')

@section('title', 'Apply - ' . $jobRole->title)

@section('content')
    <section class="section pt-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                        <div>
                            <h4 class="mb-1">Apply for {{ $jobRole->title }}</h4>
                            <p class="text-muted small mb-0">Submit your application. We'll review and get back to you.</p>
                            <nav aria-label="breadcrumb" class="mt-1">
                                <ol class="breadcrumb mb-0 small text-muted">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-list') }}" class="text-decoration-none">Job Goals</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-goal.show', $jobRole) }}" class="text-decoration-none">{{ $jobRole->title }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Apply</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h4 class="mb-2">{{ $jobRole->title }}</h4>
                            @if($jobRole->description)
                                <p class="text-muted mb-0">{{ $jobRole->description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-lg-5">
                            <h5 class="mb-4">Your application</h5>
                            <form action="{{ route('job-goal.apply.store', $jobRole) }}" method="POST">
                                @csrf
                                @if($errors->any())
                                    <div class="alert alert-danger mb-4">
                                        <ul class="mb-0 list-unstyled">
                                            @foreach($errors->all() as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                @if($resumes->count() > 0)
                                    <div class="mb-4">
                                        <label for="resume_id" class="form-label fw-medium">Attach a resume (optional)</label>
                                        <select name="resume_id" id="resume_id" class="form-select">
                                            <option value="">No resume</option>
                                            @foreach($resumes as $r)
                                                <option value="{{ $r->id }}" {{ (old('resume_id', $primaryResumeId ?? '') == $r->id) ? 'selected' : '' }}>
                                                    {{ $r->file_name ?? 'Resume #' . $r->id }} {{ $r->ai_score ? '(' . $r->ai_score . '% ATS)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="small text-muted mt-1 mb-0">We'll use this resume when reviewing your application. Match score (for employer) is calculated on submit.</p>
                                    </div>
                                @endif
                                <div class="mb-4">
                                    <label for="cover_message" class="form-label fw-medium">Cover message (optional)</label>
                                    <textarea name="cover_message" id="cover_message" class="form-control" rows="4" placeholder="Why are you a good fit for this role?">{{ old('cover_message') }}</textarea>
                                    <p class="small text-muted mt-1 mb-0">Max 2000 characters.</p>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="uil uil-message me-1"></i> Submit application</button>
                                    <a href="{{ route('job-goal.show', $jobRole) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($resumes->count() > 0)
                    <div id="match-score-card" class="card border-0 shadow-sm rounded-4 mt-4 border-primary" style="{{ ($matchResult ?? null) ? '' : 'display:none;' }}">
                        <div class="card-body p-4">
                            <h6 class="mb-2"><i class="uil uil-analysis me-1"></i> Match score for selected resume</h6>
                            <div class="d-flex align-items-center mt-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 56px; height: 56px;">
                                    <span class="fw-bold text-primary" id="match-score-value">{{ $matchResult['score'] ?? '–' }}%</span>
                                </div>
                                <div id="match-score-explanation" class="small text-muted">{{ $matchResult['explanation'] ?? 'Select a resume above to see your match score.' }}</div>
                            </div>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var sel = document.getElementById('resume_id');
                        var card = document.getElementById('match-score-card');
                        var scoreEl = document.getElementById('match-score-value');
                        var explEl = document.getElementById('match-score-explanation');
                        var url = '{{ route("job-goal.match-score", $jobRole) }}';
                        if (!sel || !card) return;
                        function updateScore() {
                            var rid = sel.value;
                            if (!rid) {
                                card.style.display = 'none';
                                return;
                            }
                            card.style.display = '';
                            scoreEl.textContent = '…';
                            explEl.textContent = 'Loading…';
                            fetch(url + '?resume_id=' + encodeURIComponent(rid), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                                .then(function(r) { return r.json(); })
                                .then(function(d) {
                                    scoreEl.textContent = (d.score != null ? d.score : '–') + '%';
                                    explEl.textContent = d.explanation || '';
                                })
                                .catch(function() {
                                    scoreEl.textContent = '–%';
                                    explEl.textContent = 'Could not load score.';
                                });
                        }
                        sel.addEventListener('change', updateScore);
                    })();
                    </script>
                    @endif

                    <div class="card border-0 shadow-sm rounded-4 mt-4 border-success">
                        <div class="card-body p-4">
                            <h6 class="mb-2"><i class="uil uil-user-plus me-1"></i> After you apply</h6>
                            <p class="text-muted small mb-2">Get a referral to increase your chance of selection. Premium members can request referrals from verified employees.</p>
                            <a href="{{ route('pricing') }}" class="btn btn-success btn-sm">Get Referral – View Premium</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
