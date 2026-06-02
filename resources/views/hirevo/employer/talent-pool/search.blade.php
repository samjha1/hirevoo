@extends('layouts.employer')

@section('title', 'Talent Pool — Search')

@section('header_title', 'Candidate Search')

@push('styles')
<style>
    .tp-search-page { max-width: 720px; margin: 0 auto; padding: 1rem 0 2rem; }
    .tp-search-card {
        background: var(--surface);
        border: 1px solid #e8ecf1;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 1.75rem;
    }
    .tp-search-card h5 { font-weight: 600; margin-bottom: .25rem; }
    .tp-search-card .lead-hint { color: var(--ink-500); font-size: .9rem; margin-bottom: 1.5rem; }
    .tp-search-card .form-label {
        font-size: .75rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: .04em; color: var(--ink-500);
    }
    .tp-search-card .form-control, .tp-search-card .form-select {
        border-radius: var(--radius-sm);
        padding: .625rem .875rem;
    }
    .tp-search-submit {
        width: 100%;
        padding: .75rem;
        font-weight: 600;
        border-radius: var(--radius-sm);
        margin-top: .5rem;
    }
</style>
@endpush

@section('content')
<div class="tp-search-page">
    <div class="tp-search-card">
        <h5>Find candidates</h5>
        <p class="lead-hint">Search verified Hirevo profiles and your talent pool. Results open on the next screen.</p>

        @if(empty($canAccessTalentPool))
            <div class="alert alert-warning mb-3">
                <i class="mdi mdi-lock-outline me-1"></i>
                You can search candidates, but phone numbers and full profiles need an active plan.
                <a href="{{ route('employer.plans.index') }}" class="alert-link fw-600">View plans & pricing</a>
            </div>
        @endif

        <form method="GET" action="{{ route('employer.talent-pool.results') }}">
            <div class="mb-3">
                <label class="form-label" for="tp-q">Role / keywords</label>
                <input type="search" class="form-control form-control-lg" id="tp-q" name="q"
                       value="{{ old('q', request('q')) }}"
                       placeholder="e.g. Flutter Developer, PHP, Product Manager"
                       autofocus autocomplete="off">
            </div>
            <div class="mb-3">
                <label class="form-label" for="tp-skills">Skills</label>
                <input type="text" class="form-control" id="tp-skills" name="skills"
                       value="{{ old('skills', request('skills')) }}"
                       placeholder="e.g. Flutter, Dart, Laravel">
            </div>
            <div class="mb-3">
                <label class="form-label" for="tp-location">City</label>
                @include('hirevo.employer.talent-pool._location-city-select', [
                    'locationFacets' => $locationFacets ?? [],
                    'filters' => $filters ?? [],
                    'selectId' => 'tp-location',
                    'selectClass' => 'form-control',
                    'formId' => null,
                ])
                @if(!empty($totalCount))
                    <p class="small text-success fw-600 mb-0 mt-1">{{ number_format($totalCount) }} candidates match this search</p>
                @endif
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label" for="tp-exp-min">Min experience (years)</label>
                    <input type="number" min="0" max="50" class="form-control" id="tp-exp-min" name="experience_min"
                           value="{{ old('experience_min', request('experience_min')) }}">
                </div>
                <div class="col-6">
                    <label class="form-label" for="tp-exp-max">Max experience (years)</label>
                    <input type="number" min="0" max="50" class="form-control" id="tp-exp-max" name="experience_max"
                           value="{{ old('experience_max', request('experience_max')) }}">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="tp-education">Education</label>
                <input type="text" class="form-control" id="tp-education" name="education"
                       value="{{ old('education', request('education')) }}"
                       placeholder="Degree or specialization" list="tp-education-list">
                <datalist id="tp-education-list">
                    @foreach($educationOptions as $deg)
                        <option value="{{ $deg }}">
                    @endforeach
                </datalist>
            </div>
            <button type="submit" class="btn btn-success tp-search-submit">
                <i class="mdi mdi-magnify me-1"></i> Search candidates
            </button>
        </form>
    </div>
</div>
@endsection
