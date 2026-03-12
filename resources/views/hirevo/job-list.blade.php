@extends('layouts.app')

@section('title', 'Job Goals')

@push('styles')
<style>
    .job-goal-card { border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); transition: all 0.2s ease; }
    .job-goal-card:hover { border-color: var(--hirevo-primary); box-shadow: 0 8px 24px rgba(11, 31, 59, 0.08); }
    .job-list-search-card { border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); }
    .job-goal-icon-wrap { flex-shrink: 0; }
    .job-goal-icon { color: var(--hirevo-primary, #0B1F3B); }
</style>
@endpush

@section('content')
    <section class="section py-4">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-14">
                    <!-- <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li> -->
                    <!-- <li class="breadcrumb-item active" aria-current="page">Job Goals</li> -->
                </ol>
            </nav>

            <div class="row align-items-end mb-4">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <h1 class="h4 fw-bold mb-1">Job Goals</h1>
                    <p class="text-muted mb-0 small">Select your target role to see skill match and missing skills. AI will suggest a learning path.</p>
                </div>
            </div>

            <form action="{{ route('job-list') }}" method="GET" class="mb-4">
                <div class="job-list-search-card bg-white p-3 p-md-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="job-list-q" class="form-label small text-muted mb-1">Job goal</label>
                            <input type="search" name="q" id="job-list-q" class="form-control rounded-3" placeholder="e.g. Data Analyst, Software Engineer..." value="{{ old('q', $searchQuery ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="job-list-location" class="form-label small text-muted mb-1">Location</label>
                            <select name="location" id="job-list-location" class="form-select rounded-3">
                                <option value="">All</option>
                                <option value="IN" {{ ($searchLocation ?? '') === 'IN' ? 'selected' : '' }}>India</option>
                                <option value="US" {{ ($searchLocation ?? '') === 'US' ? 'selected' : '' }}>United States</option>
                                <option value="GB" {{ ($searchLocation ?? '') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                <option value="AE" {{ ($searchLocation ?? '') === 'AE' ? 'selected' : '' }}>UAE</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 rounded-3"><i class="uil uil-search me-1"></i> Search</button>
                        </div>
                    </div>
                </div>
            </form>

            @if(isset($searchQuery) && $searchQuery !== '')
                <p class="text-muted mb-3">
                    <strong>{{ $jobRoles->count() }}</strong> {{ Str::plural('result', $jobRoles->count()) }} for "<strong>{{ e($searchQuery) }}</strong>"
                </p>
            @endif

            <div class="row g-3 g-lg-4">
                @forelse(($jobRoles ?? []) as $role)
                @php
                    $titleLower = strtolower($role->title ?? '');
                    $icon = 'briefcase';
                    if (str_contains($titleLower, 'backend') || str_contains($titleLower, 'developer') || str_contains($titleLower, 'engineer') || str_contains($titleLower, 'software') || str_contains($titleLower, 'dev')) {
                        $icon = 'code';
                    } elseif (str_contains($titleLower, 'data') || str_contains($titleLower, 'analyst') || str_contains($titleLower, 'analytics')) {
                        $icon = 'chart';
                    } elseif (str_contains($titleLower, 'business')) {
                        $icon = 'briefcase';
                    } elseif (str_contains($titleLower, 'frontend') || str_contains($titleLower, 'front-end')) {
                        $icon = 'desktop';
                    } elseif (str_contains($titleLower, 'design') || str_contains($titleLower, 'ux') || str_contains($titleLower, 'ui')) {
                        $icon = 'palette';
                    } elseif (str_contains($titleLower, 'product') || str_contains($titleLower, 'manager')) {
                        $icon = 'clipboard';
                    }
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm job-goal-card h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary mb-3 job-goal-icon-wrap" style="width: 56px; height: 56px;">
                                @if($icon === 'code')
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                                @elseif($icon === 'chart')
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                @elseif($icon === 'desktop')
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                @elseif($icon === 'palette')
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="0.5"/><circle cx="17.5" cy="10.5" r="0.5"/><circle cx="8.5" cy="7.5" r="0.5"/><circle cx="6.5" cy="12.5" r="0.5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.648 0-.437-.18-.835-.437-1.125-.29-.29-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.555C21.965 6.012 17.461 2 12 2z"/></svg>
                                @elseif($icon === 'clipboard')
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14h6"/><path d="M9 18h6"/></svg>
                                @else
                                    <svg class="job-goal-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                @endif
                            </div>
                            <h5 class="mb-2 fw-600"><a href="{{ route('job-goal.show', $role) }}" class="text-dark text-decoration-none stretched-link">{{ $role->title }}</a></h5>
                            <p class="text-muted mb-0 fs-14 flex-grow-1">{{ Str::limit($role->description, 80) }}</p>
                            <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                                <a href="{{ route('job-goal.show', $role) }}" class="btn btn-soft-primary btn-sm rounded-pill">View skill match</a>
                                @auth
                                    @if(in_array($role->id, $appliedJobIds ?? []))
                                        <span class="badge bg-success rounded-pill">Applied</span>
                                    @else
                                        <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-import me-1"></i> Apply</a>
                                    @endif
                                @else
                                    <a href="{{ route('login', ['redirect' => route('job-goal.apply', $role)]) }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-import me-1"></i> Apply</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <div class="card-body p-5">
                            <div class="rounded-3 bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                <i class="uil uil-search text-muted fs-28"></i>
                            </div>
                            @if(isset($searchQuery) && $searchQuery !== '')
                                <p class="text-muted mb-0">No job goals match "{{ e($searchQuery) }}". Try a different keyword or <a href="{{ route('job-list') }}" class="text-primary">view all job goals</a>.</p>
                            @else
                                <p class="text-muted mb-0">Job goals will appear here. Add roles from admin or run seeders.</p>
                            @endif
                            <a href="{{ route('home') }}" class="btn btn-primary rounded-pill mt-3">Back to Home</a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
