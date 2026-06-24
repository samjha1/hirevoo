@extends('layouts.employer')

@section('title', 'Bulk import jobs')
@section('header_title', 'Bulk import jobs')

@section('content')
    <div class="post-job-page">
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h2 class="h5 mb-0 fw-600 text-dark">Import jobs from CSV</h2>
            <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary btn-sm">Back to jobs</a>
        </div>

        <div class="card employer-card mb-4">
            <div class="card-body p-4">
                <p class="text-muted mb-4">
                    Upload a CSV file to post many jobs at once. Imported jobs appear on
                    <a href="{{ route('job-openings') }}" target="_blank" rel="noopener">Job openings</a>
                    exactly like manually posted jobs.
                </p>

                <div class="mb-4">
                    <a href="{{ route('employer.jobs.import.template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-download me-1"></i>Download CSV template
                    </a>
                </div>

                <form method="POST" action="{{ route('employer.jobs.import.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label fw-500">CSV file <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control @error('csv_file') is-invalid @enderror"
                               id="csv_file"
                               name="csv_file"
                               accept=".csv,text/csv"
                               required>
                        @error('csv_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <p class="small text-muted mt-1 mb-0">Max 5 MB. Up to 1,000 rows recommended per upload.</p>
                    </div>
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="skip_duplicates" name="skip_duplicates" value="1" {{ old('skip_duplicates') ? 'checked' : '' }}>
                        <label class="form-check-label" for="skip_duplicates">Skip duplicate rows (same title + company)</label>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="mdi mdi-upload me-1"></i>Import jobs
                    </button>
                </form>
            </div>
        </div>

        <div class="card employer-card">
            <div class="card-body p-4">
                <h3 class="h6 fw-600 mb-3">Required CSV columns</h3>
                <p class="small text-muted mb-2">{{ implode(', ', $templateHeaders) }}</p>
                <ul class="small text-muted mb-0">
                    <li><code>job_type</code>: full_time, part_time, contract, internship, temporary, volunteer, other</li>
                    <li><code>work_location_type</code>: office, remote, hybrid</li>
                    <li><code>pay_type</code>: fixed, hourly, negotiable, not_disclosed, other</li>
                    <li><code>required_skills</code>: pipe-separated, e.g. Java|Spring|SQL</li>
                    <li><code>apply_link</code>: external careers URL (optional but recommended)</li>
                    <li><code>display_applications_count</code>: optional social-proof number shown on job cards</li>
                    <li><code>posted_days_ago</code>: optional 0–30 for realistic "Posted X days ago"</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
