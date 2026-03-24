@extends('layouts.employer')

@section('title', 'Applications – ' . $job->title)

@section('header_title', 'Applications')

@section('header_actions')
    <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-primary btn-sm">Back to jobs</a>
    <a href="{{ route('employer.jobs.pipeline', $job) }}"
       class="btn btn-soft-primary btn-lg fw-600 d-flex align-items-center gap-2"
       style="padding:0.65rem 1rem;">
        <i class="mdi mdi-timeline"></i>Pipeline
    </a>
@endsection

@section('content')
    @php
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
    @endphp
    <div class="mb-4">
        <h5 class="mb-1">{{ $job->title }}</h5>
        <p class="text-muted small mb-0">{{ $jobLocationText }} · {{ ucfirst($job->status) }}</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($applications->isEmpty() && !request()->hasAny(['exp_min','exp_max','ats_min','match_min','sort']))
        <div class="card employer-card">
            <div class="card-body p-4">
                <p class="text-muted mb-0">No applications yet for this job.</p>
            </div>
        </div>
    @else
        {{-- Filters: experience, ATS score, match score, sort (like job portals) --}}
        <div class="card employer-card mb-4">
            <div class="card-body p-4">
                <form method="get" action="{{ route('employer.jobs.applications', $job) }}" id="applications-filter-form" class="row g-3 align-items-end">
                    <div class="col-6 col-md-2">
                        <label for="filter_exp_min" class="form-label small text-muted mb-0">Exp. (yrs) min</label>
                        <input type="number" class="form-control form-control-sm" id="filter_exp_min" name="exp_min" value="{{ $filters['exp_min'] ?? '' }}" min="0" max="50" placeholder="0">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_exp_max" class="form-label small text-muted mb-0">Exp. (yrs) max</label>
                        <input type="number" class="form-control form-control-sm" id="filter_exp_max" name="exp_max" value="{{ $filters['exp_max'] ?? '' }}" min="0" max="50" placeholder="Any">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_ats_min" class="form-label small text-muted mb-0">ATS score min</label>
                        <input type="number" class="form-control form-control-sm" id="filter_ats_min" name="ats_min" value="{{ $filters['ats_min'] ?? '' }}" min="0" max="100" placeholder="0–100">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_match_min" class="form-label small text-muted mb-0">Match score min</label>
                        <input type="number" class="form-control form-control-sm" id="filter_match_min" name="match_min" value="{{ $filters['match_min'] ?? '' }}" min="0" max="100" placeholder="0–100">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_sort" class="form-label small text-muted mb-0">Sort by</label>
                        <select class="form-select form-select-sm" id="filter_sort" name="sort">
                            <option value="match" {{ ($filters['sort'] ?? 'match') === 'match' ? 'selected' : '' }}>Highest match</option>
                            <option value="ats" {{ ($filters['sort'] ?? '') === 'ats' ? 'selected' : '' }}>Highest ATS</option>
                            <option value="experience" {{ ($filters['sort'] ?? '') === 'experience' ? 'selected' : '' }}>Experience</option>
                            <option value="date" {{ ($filters['sort'] ?? '') === 'date' ? 'selected' : '' }}>Date applied</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply filters</button>
                    </div>
                </form>
            </div>
        </div>

        @if($applications->isEmpty())
            <div class="card employer-card">
                <div class="card-body p-4">
                    <p class="text-muted mb-0">No applicants match the current filters. Try loosening the criteria.</p>
                </div>
            </div>
        @else
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <p class="text-muted small mb-0">
                    Showing {{ $applications->firstItem() }}-{{ $applications->lastItem() }} of {{ $applications->total() }} applicant(s)
                </p>
                <div class="d-flex align-items-center gap-2">
                    <label for="filter_per_page" class="form-label small text-muted mb-0">Per page</label>
                    <select class="form-select form-select-sm" id="filter_per_page" name="per_page" form="applications-filter-form" style="width:auto; min-width:90px;">
                        <option value="10" {{ (string)($filters['per_page'] ?? 10) === '10' ? 'selected' : '' }}>10</option>
                        <option value="25" {{ (string)($filters['per_page'] ?? '') === '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ (string)($filters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
            @foreach($applications as $app)
                @include('hirevo.employer.applications._applicant-card', ['app' => $app])
            @endforeach
            @if($applications->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $applications->links() }}
                </div>
            @endif
        @endif
    @endif

    @push('scripts')
    <script>
        document.querySelectorAll('.application-status-select').forEach(function(el) {
            el.addEventListener('change', function() {
                // Prevent double-submit when the change event fires twice quickly.
                if (el.dataset.submitting === '1') return;
                el.dataset.submitting = '1';
                el.disabled = true;

                var form = el.closest('form');
                if (form) form.submit();
            });
        });
        var filterForm = document.getElementById('applications-filter-form');
        if (filterForm) {
            var sortSelect = document.getElementById('filter_sort');
            var perPageSelect = document.getElementById('filter_per_page');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() { filterForm.submit(); });
            }
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() { filterForm.submit(); });
            }
        }
    </script>
    @endpush
@endsection
