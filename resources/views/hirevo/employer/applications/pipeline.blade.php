@extends('layouts.employer')

@section('title', 'ATS Pipeline – ' . $job->title)
@section('header_title', 'ATS Pipeline')

@section('header_actions')
    <a href="{{ route('employer.jobs.applications', $job) }}" class="btn btn-outline-primary btn-sm">
        <i class="mdi mdi-format-list-bulleted me-1"></i>Applications
    </a>
@endsection

@section('content')
    @php
        $statusMap = \App\Models\EmployerJobApplication::statusOptions();
        $stageOrder = ['applied', 'shortlisted', 'interviewed', 'offered', 'hired', 'rejected'];
        $locationDecoded = is_string($job->location) ? json_decode($job->location, true) : null;
        $locationParts = is_array($locationDecoded)
            ? array_filter([
                $locationDecoded['area'] ?? null,
                $locationDecoded['city'] ?? null,
                $locationDecoded['state'] ?? null,
                $locationDecoded['country'] ?? null,
                $locationDecoded['pincode'] ?? null,
            ])
            : [];
        $jobLocationText = !empty($locationParts) ? implode(', ', $locationParts) : ($job->location ?? '—');
        $jobSalaryText = null;
        if (($job->pay_type ?? '') !== 'not_disclosed' && (!is_null($job->salary_min) || !is_null($job->salary_max))) {
            $jobSalaryText = ($job->salary_min ?? '—') . ' - ' . ($job->salary_max ?? '—');
        } elseif (($job->pay_type ?? '') !== 'not_disclosed' && !empty($job->salary_amount)) {
            $jobSalaryText = $job->salary_amount;
        }

        $focusStatus = $focus ?: null;
        if ($focusStatus && !array_key_exists($focusStatus, $statusMap)) {
            $focusStatus = null;
        }

        $grouped = [];
        foreach (($applications ?? []) as $app) {
            $grouped[$app->status][] = $app;
        }
        // Ensure all statuses exist as keys for stable rendering
        foreach ($stageOrder as $st) {
            if (!isset($grouped[$st])) $grouped[$st] = [];
        }
    @endphp

    <style>
        .ats-board { display: grid; gap: 1rem; }
        .ats-board { overflow-x: auto; padding-bottom: 1rem; }
        .ats-column { background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden; min-width: 240px; }
        .ats-column-header { padding: 0.85rem 1rem; border-bottom: 1px solid #F3F4F6; background: #F9FAFB; }
        .ats-column-title { font-size: 0.9rem; font-weight: 700; color: #111827; margin: 0; }
        .ats-column-count { font-size: 0.75rem; font-weight: 700; padding: 0.15rem 0.55rem; border-radius: 999px; background: #EEF2FF; color: #3730A3; }
        .ats-cards { padding: 0.75rem 0.75rem 1rem; min-height: 320px; max-height: 520px; overflow-y: auto; }
        .ats-card { border: 1px solid #E5E7EB; border-radius: 12px; padding: 0.85rem; background: #fff; margin-bottom: 0.75rem; }
        .ats-card:hover { border-color: var(--hirevo-primary); box-shadow: 0 6px 18px rgba(11,31,59,0.06); }
        .ats-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .ats-card-name { font-weight: 700; margin: 0; font-size: 0.95rem; }
        .ats-badge { display: inline-block; font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.6rem; border-radius: 999px; }
        .ats-badge.match { background: #ECFDF5; color: #059669; border: 1px solid rgba(16,185,129,0.25); }
        .ats-badge.ats { background: #EFF6FF; color: #2563EB; border: 1px solid rgba(59,130,246,0.25); }
        .ats-card-meta { color: #6B7280; font-size: 0.8rem; margin: 0.35rem 0 0; line-height: 1.35; }
        .ats-move-row { margin-top: 0.6rem; display: flex; gap: 0.5rem; align-items: center; }
        .ats-move-row select { font-size: 0.8rem; }
        .ats-view-link { color: var(--hirevo-primary, #0B1F3B); text-decoration: none; font-weight: 700; }

        /* Compact mode helps when a stage has many candidates (ex: 100+ applied). */
        .ats-board.ats-compact .ats-badge { display: none; }
        .ats-board.ats-compact .ats-card-meta { display: none; }
    </style>

    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-column">
            <h2 class="h5 mb-0 fw-700 text-dark">{{ $job->title }}</h2>
            <p class="text-muted small mb-0">{{ $jobLocationText }}</p>
            <p class="text-muted small mb-0">
                @if(!empty($job->job_department))
                    <span class="me-2">Department: {{ $job->job_department }}</span>
                @endif
                @if($jobSalaryText)
                    <span class="me-2">Salary: {{ $jobSalaryText }}</span>
                @endif
                @if(!is_null($job->experience_years))
                    <span>Experience: {{ $job->experience_years }} years</span>
                @endif
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small fw-600">Job:</span>
                <select class="form-select form-select-sm" onchange="window.location.href=this.value">
                    @foreach($jobsForSelect as $j)
                        <option value="{{ route('employer.jobs.pipeline', $j) }}" {{ $j->id === $job->id ? 'selected' : '' }}>
                            {{ $j->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <a href="{{ request()->fullUrlWithQuery(['focus' => 'shortlisted']) }}" class="btn btn-soft-primary btn-sm">
                View Shortlisted
            </a>
            @if($focusStatus)
                <a href="{{ request()->fullUrlWithQuery(['focus' => null]) }}" class="btn btn-outline-secondary btn-sm">
                    Show All Stages
                </a>
            @endif

            <div class="d-flex align-items-center gap-2 ms-2">
                <input type="search" id="atsCandidateSearch" class="form-control form-control-sm" style="width: 220px;"
                       placeholder="Search candidate..." autocomplete="off"
                       value="{{ request()->get('q') }}">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="atsCompactToggle"
                           {{ request()->get('density') === 'compact' ? 'checked' : '' }}>
                    <label class="form-check-label text-muted small" for="atsCompactToggle">Compact</label>
                </div>
            </div>
        </div>
    </div>

    <div class="ats-board" style="grid-template-columns: repeat({{ $focusStatus ? 1 : 6 }}, minmax(240px, 1fr));">
        @foreach($stageOrder as $statusKey)
            @if($focusStatus && $focusStatus !== $statusKey)
                @continue
            @endif

            <div class="ats-column" data-status="{{ $statusKey }}">
                <div class="ats-column-header d-flex align-items-center justify-content-between">
                    <p class="ats-column-title">
                        {{ $statusMap[$statusKey] ?? ucfirst($statusKey) }}
                    </p>
                    <span class="ats-column-count">{{ count($grouped[$statusKey] ?? []) }}</span>
                </div>

                <div class="ats-cards">
                    @forelse($grouped[$statusKey] as $app)
                        @php
                            $candidate = $app->user;
                            $candidateProfile = $candidate?->candidateProfile;
                            $candidateNameForSearch = strtolower(trim((string) ($candidate?->name ?? '')));

                            $exp = $candidateProfile?->experience_years;
                            $loc = $candidateProfile?->location ?? null;

                            $jobMatch = $app->job_match_score;
                            $atsScore = $app->ats_score;
                        @endphp

                        <div class="ats-card" data-candidate-name="{{ $candidateNameForSearch }}">
                            <div class="ats-card-top">
                                <div class="min-w-0">
                                    <p class="ats-card-name text-truncate" title="{{ $candidate?->name }}">{{ $candidate?->name ?? '—' }}</p>

                                    @if($jobMatch !== null)
                                        <span class="ats-badge match mb-1">Match {{ $jobMatch }}%</span>
                                    @endif
                                    @if($atsScore !== null)
                                        <div class="ats-badge ats mb-1">ATS {{ $atsScore }}%</div>
                                    @endif

                                    <p class="ats-card-meta">
                                        @if($exp !== null)
                                            {{ $exp }} yrs
                                        @endif
                                        @if($loc)
                                            {{ $exp !== null ? ' · ' : '' }}{{ $loc }}
                                        @endif
                                    </p>
                                </div>

                                <a class="ats-view-link" href="{{ route('employer.applications.show', $app) }}">
                                    View
                                </a>
                            </div>

                            <div class="ats-move-row">
                                <form action="{{ route('employer.applications.status', $app) }}" method="POST" class="d-flex align-items-center gap-2" style="width:100%;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach($statusMap as $val => $label)
                                            <option value="{{ $val }}" {{ $app->status === $val ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">Move</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small" style="padding:0.25rem 0.35rem;">
                            No candidates in this stage.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    @push('scripts')
        <script>
            (function () {
                var searchInput = document.getElementById('atsCandidateSearch');
                var compactToggle = document.getElementById('atsCompactToggle');
                var board = document.querySelector('.ats-board');

                function getVisibleCount(col) {
                    var cards = col.querySelectorAll('.ats-card');
                    var visible = 0;
                    cards.forEach(function (c) {
                        if (window.getComputedStyle(c).display !== 'none') visible++;
                    });
                    return visible;
                }

                function updateColumnCounts() {
                    var cols = document.querySelectorAll('.ats-column');
                    cols.forEach(function (col) {
                        var span = col.querySelector('.ats-column-count');
                        if (!span) return;
                        span.textContent = getVisibleCount(col);
                    });
                }

                function applySearch() {
                    if (!searchInput) return;
                    var q = (searchInput.value || '').toLowerCase().trim();

                    document.querySelectorAll('.ats-card').forEach(function (card) {
                        var name = (card.getAttribute('data-candidate-name') || '');
                        var match = !q || name.includes(q);
                        card.style.display = match ? '' : 'none';
                    });

                    updateColumnCounts();
                }

                if (searchInput) {
                    searchInput.addEventListener('input', applySearch);
                    // Apply initial search value if query param exists.
                    applySearch();
                }

                if (compactToggle && board) {
                    var syncCompact = function () {
                        if (compactToggle.checked) board.classList.add('ats-compact');
                        else board.classList.remove('ats-compact');
                    };
                    compactToggle.addEventListener('change', syncCompact);
                    syncCompact();
                }
            })();
        </script>
    @endpush
@endsection

