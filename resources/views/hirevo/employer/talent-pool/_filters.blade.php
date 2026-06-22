@php
    use App\Models\CandidateProfile;
    $selectedLocations = $selectedLocations ?? [];
    $facets = $facets ?? ['locations' => [], 'preferred_locations' => [], 'education' => [], 'experience' => [], 'salary' => []];
    $activeFilterCount = $activeFilterCount ?? 0;
    $educationOptions = $educationOptions ?? CandidateProfile::educationDegreeValues();
@endphp
<div class="tp-filters-inner" id="tp-filters-panel">
    <div class="tp-filters-head">
        <h6 class="mb-0 fw-600">Filters <span class="text-muted fw-normal">({{ $activeFilterCount }})</span></h6>
        @if($activeFilterCount > 0)
            <a href="{{ route('employer.talent-pool.results') }}" class="tp-clear-filters small">Clear all</a>
        @endif
    </div>

    <div class="tp-filter-block">
        <button type="button" class="tp-filter-toggle" data-bs-toggle="collapse" data-bs-target="#tp-loc-panel" aria-expanded="true">
            <span>Location</span>
            <i class="mdi mdi-chevron-down"></i>
        </button>
        <div class="collapse show" id="tp-loc-panel">
            @include('hirevo.employer.talent-pool._location-city-select', [
                'locationFacets' => $locationFacets ?? ($facets['locations'] ?? []),
                'filters' => $filters,
                'selectedLocations' => $selectedLocations,
                'onchangeFilter' => true,
            ])
        </div>
    </div>

    <div class="tp-filter-block">
        <button type="button" class="tp-filter-toggle" data-bs-toggle="collapse" data-bs-target="#tp-pref-loc-panel" aria-expanded="true">
            <span>Preferred location</span>
            <i class="mdi mdi-chevron-down"></i>
        </button>
        <div class="collapse show" id="tp-pref-loc-panel">
            @include('hirevo.employer.talent-pool._location-city-select', [
                'locationFacets' => $preferredLocationFacets ?? ($facets['preferred_locations'] ?? []),
                'filters' => $filters,
                'paramName' => 'preferred_location',
                'allOptionLabel' => 'All preferred cities',
                'hintText' => 'Matching preferred city appears first in results.',
                'onchangeFilter' => true,
            ])
        </div>
    </div>

    <div class="tp-filter-block">
        <button type="button" class="tp-filter-toggle" data-bs-toggle="collapse" data-bs-target="#tp-salary-panel" aria-expanded="true">
            <span>Expected salary</span>
            <i class="mdi mdi-chevron-down"></i>
        </button>
        <div class="collapse show" id="tp-salary-panel">
            @php
                $salaryFacets = collect($facets['salary'] ?? [])->keyBy('min_lpa');
                $selectedSalary = (string) ($filters['salary_min_lpa'] ?? '');
            @endphp
            <select class="form-select form-select-sm tp-filter" id="tp-salary-min-lpa" name="salary_min_lpa" form="tp-search-form">
                <option value="">Any salary</option>
                @foreach(\App\Support\TalentPoolSalary::buckets() as $bucket)
                    @php
                        $facet = $salaryFacets->get($bucket['min_lpa']);
                        $count = (int) ($facet['count'] ?? 0);
                    @endphp
                    <option value="{{ $bucket['min_lpa'] }}" @selected($selectedSalary === (string) $bucket['min_lpa'])>
                        {{ $bucket['label'] }}@if($count > 0) ({{ number_format($count) }})@endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="tp-filter-block">
        <button type="button" class="tp-filter-toggle" data-bs-toggle="collapse" data-bs-target="#tp-exp-panel" aria-expanded="true">
            <span>Experience</span>
            <i class="mdi mdi-chevron-down"></i>
        </button>
        <div class="collapse show" id="tp-exp-panel">
            <div class="tp-facet-list">
                @foreach($facets['experience'] as $exp)
                    <label class="tp-facet-option">
                        <input type="radio" class="tp-exp-radio tp-filter" name="experience_bucket"
                               data-min="{{ $exp['min'] }}" data-max="{{ $exp['max'] ?? '' }}"
                               {{ (string)($filters['experience_min'] ?? '') === (string)$exp['min'] && (string)($filters['experience_max'] ?? '') === (string)($exp['max'] ?? '') ? 'checked' : '' }}>
                        <span>{{ $exp['label'] }}</span>
                        <span class="tp-facet-count">({{ number_format($exp['count']) }})</span>
                    </label>
                @endforeach
            </div>
            <div class="row g-2 mt-2">
                <div class="col-6">
                    <input type="number" min="0" max="50" class="form-control form-control-sm tp-filter" id="tp-exp-min" name="experience_min"
                           form="tp-search-form" value="{{ $filters['experience_min'] ?? '' }}" placeholder="Min yrs">
                </div>
                <div class="col-6">
                    <input type="number" min="0" max="50" class="form-control form-control-sm tp-filter" id="tp-exp-max" name="experience_max"
                           form="tp-search-form" value="{{ $filters['experience_max'] ?? '' }}" placeholder="Max yrs">
                </div>
            </div>
        </div>
    </div>

    <div class="tp-filter-block">
        <button type="button" class="tp-filter-toggle" data-bs-toggle="collapse" data-bs-target="#tp-edu-panel" aria-expanded="false">
            <span>Education</span>
            <i class="mdi mdi-chevron-down"></i>
        </button>
        <div class="collapse" id="tp-edu-panel">
            <div class="tp-facet-list tp-edu-list mb-2" style="max-height: 180px; overflow-y: auto;">
                @foreach($facets['education'] as $edu)
                    <label class="tp-facet-option">
                        <input type="radio" class="tp-edu-radio tp-filter" name="education_pick" value="{{ $edu['label'] }}"
                               {{ ($filters['education'] ?? '') === $edu['label'] ? 'checked' : '' }}>
                        <span>{{ \Illuminate\Support\Str::limit($edu['label'], 42) }}</span>
                        <span class="tp-facet-count">({{ number_format($edu['count']) }})</span>
                    </label>
                @endforeach
            </div>
            <input type="text" class="form-control form-control-sm tp-filter" id="tp-education" name="education"
                   form="tp-search-form" value="{{ $filters['education'] ?? '' }}"
                   placeholder="Search degree / specialization" list="tp-education-list">
            <datalist id="tp-education-list">
                @foreach($educationOptions ?? [] as $deg)
                    <option value="{{ $deg }}">
                @endforeach
            </datalist>
        </div>
    </div>

    <div class="tp-filter-block">
        <label class="form-label tp-filter-label" for="tp-skills">Skills</label>
        <input type="text" class="form-control form-control-sm tp-filter" id="tp-skills" name="skills"
               form="tp-search-form" value="{{ $filters['skills'] ?? '' }}"
               placeholder="e.g. Flutter, Dart">
    </div>

    <div class="tp-filter-block mb-0">
        <label class="tp-facet-option mb-2">
            <input class="tp-filter" type="checkbox" id="tp-saved-only" name="saved_only" value="1"
                   form="tp-search-form" {{ !empty($filters['saved_only']) ? 'checked' : '' }}>
            <span>Saved only</span>
        </label>
        <label class="tp-facet-option">
            <input class="tp-filter" type="checkbox" id="tp-shortlisted-only" name="shortlisted_only" value="1"
                   form="tp-search-form" {{ !empty($filters['shortlisted_only']) ? 'checked' : '' }}>
            <span>Shortlisted only</span>
        </label>
    </div>

    <button type="submit" form="tp-search-form" class="btn btn-success w-100 mt-3 tp-apply-btn">
        Apply{{ $activeFilterCount > 0 ? ' '.$activeFilterCount.' filter'.($activeFilterCount > 1 ? 's' : '') : '' }}
    </button>
</div>
