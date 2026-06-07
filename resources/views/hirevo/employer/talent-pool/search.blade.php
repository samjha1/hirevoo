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

        <form method="GET" action="{{ route('employer.talent-pool.results') }}" id="tp-search-form-start">
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
                <p class="small fw-600 mb-0 mt-1 text-muted" id="tp-search-preview" hidden></p>
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
            <button type="submit" class="btn btn-success tp-search-submit" id="tp-search-submit">
                <i class="mdi mdi-magnify me-1"></i> Search candidates
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('tp-search-form-start');
    var preview = document.getElementById('tp-search-preview');
    var submitBtn = document.getElementById('tp-search-submit');
    var facetsUrl = @json(route('employer.talent-pool.facets'));
    var countCacheKey = 'tp_search_count_v1';
    var minLen = 2;
    var timer;
    var previewRequest = null;

    function collectParams() {
        if (!form) return '';
        var fd = new FormData(form);
        var params = new URLSearchParams();
        fd.forEach(function (v, k) { if (v !== '' && v !== null) params.append(k, v); });
        return params.toString();
    }

    function hasCriteria() {
        var q = (form?.querySelector('[name=q]')?.value || '').trim();
        var skills = (form?.querySelector('[name=skills]')?.value || '').trim();
        return q.length >= minLen || skills.length >= minLen
            || (form?.querySelector('[name=location]')?.value || '').trim() !== ''
            || (form?.querySelector('[name=education]')?.value || '').trim() !== ''
            || (form?.querySelector('[name=experience_min]')?.value || '').trim() !== ''
            || (form?.querySelector('[name=experience_max]')?.value || '').trim() !== '';
    }

    function setPreview(text, tone) {
        if (!preview) return;
        if (!text) {
            preview.hidden = true;
            preview.textContent = '';
            return;
        }
        preview.hidden = false;
        preview.textContent = text;
        preview.className = 'small fw-600 mb-0 mt-1 ' + (tone || 'text-success');
    }

    function showCachedPreview() {
        try {
            var key = collectParams();
            var raw = sessionStorage.getItem(countCacheKey);
            if (!raw) return;
            var cached = JSON.parse(raw);
            if (cached && cached.key === key && typeof cached.n === 'number') {
                setPreview(cached.n.toLocaleString() + ' ' + (cached.n === 1 ? 'candidate matches' : 'candidates match') + ' this search');
            }
        } catch (e) {}
    }

    function fetchPreview() {
        if (!hasCriteria()) {
            setPreview('');
            return;
        }
        if (!preview.textContent) {
            setPreview('Counting matches…', 'text-muted');
        }
        showCachedPreview();
        if (previewRequest) previewRequest.abort();
        var controller = new AbortController();
        previewRequest = controller;
        fetch(facetsUrl + '?' + collectParams(), {
            signal: controller.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var n = data.total_count;
                if (typeof n !== 'number') {
                    setPreview('');
                    return;
                }
                var label = n.toLocaleString() + ' ' + (n === 1 ? 'candidate matches' : 'candidates match') + ' this search';
                setPreview(label);
                try {
                    sessionStorage.setItem(countCacheKey, JSON.stringify({ key: collectParams(), n: n }));
                } catch (e) {}
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
                if (!preview.textContent) setPreview('');
            })
            .finally(function () {
                if (previewRequest === controller) previewRequest = null;
            });
    }

    function debouncedPreview() {
        clearTimeout(timer);
        timer = setTimeout(fetchPreview, 900);
    }

    form?.addEventListener('submit', function () {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Searching…';
        }
    });

    form?.querySelectorAll('input, select').forEach(function (el) {
        el.addEventListener('input', debouncedPreview);
        el.addEventListener('change', debouncedPreview);
    });

    if (hasCriteria()) {
        showCachedPreview();
        fetchPreview();
    }
})();
</script>
@endpush
