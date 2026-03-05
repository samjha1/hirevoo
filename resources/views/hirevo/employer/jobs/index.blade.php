@extends('layouts.employer')

@section('title', 'Jobs')

@section('header_title', 'All Jobs (' . $counts['all'] . ')')

@section('header_actions')
    <a href="{{ route('employer.jobs.create') }}" class="btn btn-success employer-btn-post-job"><i class="mdi mdi-plus me-1"></i>Post a new job</a>
@endsection

@section('content')
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
                @endif
            </div>
        </div>
    </div>
@endsection
