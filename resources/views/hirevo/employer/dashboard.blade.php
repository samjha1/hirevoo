@extends('layouts.employer')

@section('title', $isApproved ? 'Dashboard' : 'Pending Approval')

@section('header_title', $isApproved ? 'All Jobs (' . $counts['all'] . ')' : 'Pending Approval')

@section('header_actions')
    @if($isApproved)
        <a href="{{ route('employer.jobs.create') }}" class="btn btn-success employer-btn-post-job" style="color: #fff !important;"><i class="mdi mdi-plus me-1" style="color: #fff !important;"></i><span style="color: #fff !important;">Post a new job</span></a>
    @endif
@endsection

@section('content')
    @if(!$isApproved)
        <div class="card employer-card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-clock-outline text-warning fs-24"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-600">Account under review</h5>
                        <p class="text-muted small mb-0">{{ $profile->company_name ?? 'Company' }}</p>
                        <span class="badge bg-warning text-dark">Pending approval</span>
                    </div>
                </div>
                <p class="text-muted mb-3">Your company profile has been submitted. Our team will verify your details and approve your account shortly. You will be able to post jobs and view applications once approved.</p>
                <p class="text-muted small mb-0">Questions? <a href="{{ route('contact') }}">Contact us</a>.</p>
                <a href="{{ route('employer.profile') }}" class="btn btn-soft-primary btn-sm mt-3">Edit company profile</a>
            </div>
        </div>
    @else
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <p class="text-muted small mb-1">Total Jobs Posted</p>
                    <h3 class="mb-0 fw-700">{{ $counts['all'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <p class="text-muted small mb-1">Active Jobs</p>
                    <h3 class="mb-0 fw-700">{{ $counts['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <p class="text-muted small mb-1">Total Applications</p>
                    <h3 class="mb-0 fw-700">{{ $applicationCounts['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <p class="text-muted small mb-1">Total Hires</p>
                    <h3 class="mb-0 fw-700">{{ $applicationCounts['hired'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-600 mb-3">Application Funnel</h6>
                    <div class="row g-2">
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Applied</small><strong>{{ $applicationCounts['applied'] }}</strong></div></div>
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Shortlisted</small><strong>{{ $applicationCounts['shortlisted'] }}</strong></div></div>
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Interviewed</small><strong>{{ $applicationCounts['interviewed'] }}</strong></div></div>
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Offered</small><strong>{{ $applicationCounts['offered'] }}</strong></div></div>
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Hired</small><strong>{{ $applicationCounts['hired'] }}</strong></div></div>
                        <div class="col-6 col-lg-4"><div class="border rounded p-2"><small class="text-muted d-block">Rejected</small><strong>{{ $applicationCounts['rejected'] }}</strong></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card employer-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-600 mb-3">Performance Report</h6>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Shortlist Rate</span><strong>{{ $report['shortlist_rate'] }}%</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Hire Rate</span><strong>{{ $report['hire_rate'] }}%</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small">Avg Match Score</span><strong>{{ $report['avg_match_score'] ?? '—' }}</strong></div>
                    <div class="d-flex justify-content-between py-2"><span class="text-muted small">Avg ATS Score</span><strong>{{ $report['avg_ats_score'] ?? '—' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card employer-card">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-600 mb-0">Top Jobs by Applications</h6>
                <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-primary btn-sm">View all jobs</a>
            </div>
            @if($topJobs->isEmpty())
                <p class="text-muted mb-0">No jobs yet. Post your first job to start tracking applications.</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Status</th>
                                <th class="text-end">Applications</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topJobs as $job)
                                <tr>
                                    <td>
                                        <a class="text-decoration-none fw-600" href="{{ route('employer.jobs.edit', $job) }}">{{ $job->title }}</a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ ucfirst($job->status) }}</span></td>
                                    <td class="text-end fw-600">{{ $job->applications_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
    @endif
@endsection
