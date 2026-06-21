@php
    $locationFacets = $locationFacets ?? ($facets['locations'] ?? []);
    $selectedCity = $selectedCity ?? ($filters['location'] ?? '');
    if ($selectedCity === '' && !empty($selectedLocations[0] ?? null)) {
        $selectedCity = $selectedLocations[0];
    }
    $selectId = $selectId ?? 'tp-location';
    $selectClass = $selectClass ?? 'form-select form-select-sm tp-filter';
    $formId = $formId ?? 'tp-search-form';
    $showCounts = $showCounts ?? true;
@endphp
<select class="{{ $selectClass }}" id="{{ $selectId }}" name="location"
        @if($formId) form="{{ $formId }}" @endif
        @if(!empty($onchangeFilter)) data-tp-filter="1" @endif>
    <option value="">All cities</option>
    @foreach($locationFacets as $loc)
        @php
            $label = (string) ($loc['label'] ?? '');
            $count = (int) ($loc['count'] ?? 0);
        @endphp
        <option value="{{ $label }}" @selected($selectedCity === $label)>
            {{ $label }}@if($showCounts && $count > 0) ({{ number_format($count) }})@endif
        </option>
    @endforeach
    @if($selectedCity !== '' && !collect($locationFacets)->contains(fn ($loc) => ($loc['label'] ?? '') === $selectedCity))
        <option value="{{ $selectedCity }}" selected>{{ $selectedCity }}</option>
    @endif
</select>
@if(empty($locationFacets) && ($selectedCity === ''))
    <p class="small text-muted mb-0 mt-1">Loading cities from candidate profiles…</p>
@endif
