@extends('layouts.employer')

@section('title', 'Jobs')

@section('header_title', 'All Jobs (' . $counts['all'] . ')')

@section('header_actions')
    <a href="{{ route('employer.jobs.create') }}" class="btn btn-success employer-btn-post-job"><i class="mdi mdi-plus me-1"></i>Post a new job</a>
@endsection

@section('content')
    @php
        $statusParam = request()->query('status');
        $statusParam = is_string($statusParam) ? strtolower(trim($statusParam)) : '';
        $statusFilter = in_array($statusParam, ['active', 'draft', 'closed'], true) ? $statusParam : null;
    @endphp
    <div class="card employer-card border border-light shadow-sm mb-0 rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="employer-tabs" role="tablist" aria-label="Job status">
                <a href="{{ route('employer.jobs.index') }}" class="tab-link {{ $statusFilter === null ? 'active' : '' }}" @if($statusFilter === null) aria-current="page" @endif>All ({{ $counts['all'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'active']) }}" class="tab-link {{ $statusFilter === 'active' ? 'active' : '' }}" @if($statusFilter === 'active') aria-current="page" @endif>Active ({{ $counts['active'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'draft']) }}" class="tab-link {{ $statusFilter === 'draft' ? 'active' : '' }}" @if($statusFilter === 'draft') aria-current="page" @endif>Draft ({{ $counts['draft'] }})</a>
                <a href="{{ route('employer.jobs.index', ['status' => 'closed']) }}" class="tab-link {{ $statusFilter === 'closed' ? 'active' : '' }}" @if($statusFilter === 'closed') aria-current="page" @endif>Closed ({{ $counts['closed'] }})</a>
            </div>
            <div class="employer-jobs-list-wrap pb-4 pt-3 bg-light bg-opacity-50">
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
