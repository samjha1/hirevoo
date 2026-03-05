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
    <div class="card employer-card border-0 mb-0">
        <div class="card-body p-0">
            <div class="employer-tabs">
                <a href="{{ route('employer.jobs.index') }}" class="tab-link {{ !request('status') ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="tab-link {{ request('status') === 'active' ? 'active' : '' }}">Active ({{ $counts['active'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'draft']) }}" class="tab-link {{ request('status') === 'draft' ? 'active' : '' }}">Draft ({{ $counts['draft'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'closed']) }}" class="tab-link {{ request('status') === 'closed' ? 'active' : '' }}">Closed ({{ $counts['closed'] }})</a>
            </div>
            <div class="p-4 pt-0">
                @if($jobs->isEmpty())
                    <div class="text-center py-5">
                        <i class="mdi mdi-briefcase-outline text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 fw-600">No jobs yet</h5>
                        <p class="text-muted mb-4">Post your first job to start receiving applications from candidates.</p>
                        <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">Post a new job</a>
                    </div>
                @else
                    @foreach($jobs as $job)
                        @include('hirevo.employer._job-card', ['job' => $job])
                    @endforeach
                    @if($counts['all'] > 10)
                        <div class="mt-3 text-center">
                            <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-primary">View all jobs</a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif
@endsection
