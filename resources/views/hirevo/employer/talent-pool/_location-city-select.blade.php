@php
    $locationFacets = $locationFacets ?? ($facets['locations'] ?? []);
    $selectedCity = $selectedCity ?? ($filters['location'] ?? '');
    if ($selectedCity === '' && !empty($selectedLocations[0] ?? null)) {
        $selectedCity = $selectedLocations[0];
    }
    $selectId = $selectId ?? 'tp-location';
    $selectClass = $selectClass ?? 'form-select form-select-sm tp-filter';
    $formId = $formId ?? 'tp-search-form';
@endphp
<select class="{{ $selectClass }}" id="{{ $selectId }}" name="location"
        @if($formId) form="{{ $formId }}" @endif
        @if(!empty($onchangeFilter)) data-tp-filter="1" @endif>
    <option value="">All cities</option>
    @foreach($locationFacets as $loc)
        <option value="{{ $loc['label'] }}" @selected($selectedCity === $loc['label'])>
            {{ $loc['label'] }} ({{ number_format($loc['count']) }})
        </option>
    @endforeach
    @if($selectedCity !== '' && !collect($locationFacets)->contains(fn ($loc) => ($loc['label'] ?? '') === $selectedCity))
        <option value="{{ $selectedCity }}" selected>{{ $selectedCity }}</option>
    @endif
</select>
@if(empty($locationFacets) && ($selectedCity === ''))
    <p class="small text-muted mb-0 mt-1">Run a search with keywords or skills to see cities with candidate counts.</p>
@endif
