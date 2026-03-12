@extends('layouts.app')

@section('title', 'Job Openings')

@push('styles')
<style>
    .job-opening-card { border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); transition: all 0.2s ease; }
    .job-opening-card:hover { border-color: var(--hirevo-primary); box-shadow: 0 8px 24px rgba(11, 31, 59, 0.08); }
</style>
@endpush

@section('content')
    <section class="section py-4">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <!-- <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Job Openings</li>
                </ol> -->
            </nav>

            <div class="row align-items-end mb-4">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <h1 class="h4 fw-bold mb-1">Job Openings</h1>
                    <p class="text-muted mb-0 small">Browse and apply to jobs posted by employers. Filter by location, job type, and work mode.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show rounded-3" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('job-openings') }}" method="GET" class="mb-4">
                <div class="bg-white border rounded-3 p-3 p-md-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="job-openings-q" class="form-label small text-muted mb-1">Job title or company</label>
                            <input type="search" name="q" id="job-openings-q" class="form-control rounded-3" placeholder="e.g. Developer, Company name..." value="{{ old('q', $searchQuery ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="job-openings-location" class="form-label small text-muted mb-1">Location</label>
                            <input type="text" name="location" id="job-openings-location" class="form-control rounded-3" placeholder="e.g. Remote, Mumbai..." value="{{ old('location', $searchLocation ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="uil uil-search me-1"></i> Search</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="job_type" value="{{ $filterJobType ?? '' }}">
                <input type="hidden" name="work_location_type" value="{{ $filterWorkType ?? '' }}">
            </form>

            <div class="row">
                {{-- Sidebar filters (Apna-style) --}}
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                        <div class="card-body p-4">
                            <h6 class="fw-600 mb-3">Filters</h6>
                            <form action="{{ route('job-openings') }}" method="GET" id="filters-form">
                                @if($searchQuery ?? '')
                                    <input type="hidden" name="q" value="{{ $searchQuery }}">
                                @endif
                                @if($searchLocation ?? '')
                                    <input type="hidden" name="location" value="{{ $searchLocation }}">
                                @endif

                                @php
                                    $queryAll = array_filter(request()->query(), fn ($v) => $v !== '' && $v !== null);
                                @endphp
                                <div class="mb-4">
                                    <label class="form-label small fw-600 text-dark">Job type</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('job-openings', array_diff_key($queryAll, ['job_type' => 1])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === '' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">All</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'internship'])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === 'internship' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Freshers / Internship</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'full_time'])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === 'full_time' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Full-time</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'part_time'])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === 'part_time' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Part-time</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'contract'])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === 'contract' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Contract</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['job_type' => 'temporary'])) }}" class="badge rounded-pill {{ ($filterJobType ?? '') === 'temporary' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Temporary</a>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small fw-600 text-dark">Work location</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('job-openings', array_diff_key($queryAll, ['work_location_type' => 1])) }}" class="badge rounded-pill {{ ($filterWorkType ?? '') === '' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">All</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'remote'])) }}" class="badge rounded-pill {{ ($filterWorkType ?? '') === 'remote' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Remote</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'office'])) }}" class="badge rounded-pill {{ ($filterWorkType ?? '') === 'office' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Office</a>
                                        <a href="{{ route('job-openings', array_merge($queryAll, ['work_location_type' => 'hybrid'])) }}" class="badge rounded-pill {{ ($filterWorkType ?? '') === 'hybrid' ? 'bg-primary' : 'bg-soft-primary text-dark' }} text-decoration-none">Hybrid</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Job list (Apna-style list view) --}}
                <div class="col-lg-9">
                    <p class="text-muted mb-3">
                        @if((isset($searchQuery) && $searchQuery !== '') || (isset($searchLocation) && $searchLocation !== '') || (isset($filterJobType) && $filterJobType !== '') || (isset($filterWorkType) && $filterWorkType !== ''))
                            <strong>{{ $jobs->total() }}</strong> {{ Str::plural('job', $jobs->total()) }} found
                            @if(isset($searchQuery) && $searchQuery !== '')
                                for "<strong>{{ e($searchQuery) }}</strong>"
                            @endif
                            @if(isset($searchLocation) && $searchLocation !== '')
                                in {{ e($searchLocation) }}
                            @endif
                        @else
                            <strong>{{ $jobs->total() }}</strong> {{ Str::plural('job', $jobs->total()) }} available
                        @endif
                    </p>

                    <div class="list-group list-group-flush">
                        @forelse($jobs as $job)
                            <div class="card border-0 shadow-sm job-opening-card mb-3">
                                <div class="card-body p-4">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="mb-1">
                                                <a href="{{ route('job-openings.apply', $job) }}" class="text-dark text-decoration-none fw-600">{{ $job->title }}</a>
                                            </h5>
                                            <p class="text-muted small mb-2">
                                                {{ $job->user->referrerProfile?->company_name ?? 'Company' }}
                                            </p>
                                            <div class="d-flex flex-wrap gap-2 align-items-center small">
                                                @if($job->location)
                                                    <span class="text-muted"><i class="uil uil-map-marker me-1"></i>{{ $job->location }}</span>
                                                @endif
                                                @if($job->job_type)
                                                    @php
                                                        $jobTypeLabels = [
                                                            'full_time' => 'Full-time',
                                                            'part_time' => 'Part-time',
                                                            'contract' => 'Contract',
                                                            'internship' => 'Internship',
                                                            'temporary' => 'Temporary',
                                                            'volunteer' => 'Volunteer',
                                                            'other' => 'Other',
                                                        ];
                                                        $jobTypeLabel = $jobTypeLabels[$job->job_type] ?? $job->job_type;
                                                    @endphp
                                                    <span class="badge bg-soft-primary">{{ $jobTypeLabel }}</span>
                                                @endif
                                                @if($job->work_location_type)
                                                    @php
                                                        $workTypeLabels = ['office' => 'Office', 'remote' => 'Remote', 'hybrid' => 'Hybrid'];
                                                        $workTypeLabel = $workTypeLabels[$job->work_location_type] ?? $job->work_location_type;
                                                    @endphp
                                                    <span class="badge bg-soft-success">{{ $workTypeLabel }}</span>
                                                @endif
                                            </div>
                                            <p class="text-muted mb-0 mt-2 fs-14">{{ Str::limit(strip_tags($job->description), 150) ?: '—' }}</p>
                                        </div>
                                        <div class="col-auto text-end">
                                            @if(in_array($job->id, $appliedIds ?? []))
                                                <span class="badge bg-success">Applied</span>
                                            @else
                                                <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm rounded-pill">Apply</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                                <div class="card-body p-5">
                                    <div class="rounded-3 bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                        <i class="uil uil-briefcase-alt text-muted fs-28"></i>
                                    </div>
                                    @if((isset($searchQuery) && $searchQuery !== '') || (isset($searchLocation) && $searchLocation !== '') || (isset($filterJobType) && $filterJobType !== '') || (isset($filterWorkType) && $filterWorkType !== ''))
                                        <p class="text-muted mb-0">No job openings match your filters. Try different keywords or <a href="{{ route('job-openings') }}" class="text-primary">view all jobs</a>.</p>
                                    @else
                                        <p class="text-muted mb-0">No job openings at the moment. Check back later.</p>
                                    @endif
                                    <a href="{{ route('home') }}" class="btn btn-primary rounded-pill mt-3">Back to Home</a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if($jobs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $jobs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
