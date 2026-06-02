@php
    $queryAll = $queryAll ?? array_filter(request()->query(), fn ($v) => $v !== '' && $v !== null);
@endphp
<form action="{{ route('job-openings') }}" method="GET" id="{{ $filtersFormId ?? 'filters-form' }}">
    @if($searchQuery ?? '')
        <input type="hidden" name="q" value="{{ $searchQuery }}">
    @endif
    @if($searchLocation ?? '')
        <input type="hidden" name="location" value="{{ $searchLocation }}">
    @endif
    @if(($countryFilter ?? '') !== '')
        <input type="hidden" name="country" value="{{ $countryFilter }}">
    @endif

    <div class="mb-4">
        <label class="form-label small fw-bold text-muted mb-2 d-block text-uppercase jo-filter-label">Job type</label>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('job-openings', array_diff_key($queryAll, ['job_type' => 1, 'page' => 1])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === '' ? 'jo-filter-chip--active' : '' }}">All</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'internship'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'internship' ? 'jo-filter-chip--active' : '' }}">Internship</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'full_time'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'full_time' ? 'jo-filter-chip--active' : '' }}">Full-time</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'part_time'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'part_time' ? 'jo-filter-chip--active' : '' }}">Part-time</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'contract'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'contract' ? 'jo-filter-chip--active' : '' }}">Contract</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'temporary'])) }}" class="jo-filter-chip {{ ($filterJobType ?? '') === 'temporary' ? 'jo-filter-chip--active' : '' }}">Temporary</a>
        </div>
    </div>

    <div class="mb-0">
        <label class="form-label small fw-bold text-muted mb-2 d-block text-uppercase jo-filter-label">Workplace</label>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('job-openings', array_diff_key($queryAll, ['work_location_type' => 1, 'page' => 1])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === '' ? 'jo-filter-chip--active' : '' }}">All</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'remote'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'remote' ? 'jo-filter-chip--active' : '' }}">Remote</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'office'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'office' ? 'jo-filter-chip--active' : '' }}">On-site</a>
            <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'hybrid'])) }}" class="jo-filter-chip {{ ($filterWorkType ?? '') === 'hybrid' ? 'jo-filter-chip--active' : '' }}">Hybrid</a>
        </div>
    </div>
</form>
