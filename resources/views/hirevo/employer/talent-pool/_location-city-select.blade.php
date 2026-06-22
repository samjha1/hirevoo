@php
    $paramName = $paramName ?? 'location';
    $locationFacets = $locationFacets ?? ($facets['locations'] ?? []);
    $selectedCity = $selectedCity ?? ($filters[$paramName] ?? '');
    if ($selectedCity === '' && $paramName === 'location' && !empty($selectedLocations[0] ?? null)) {
        $selectedCity = $selectedLocations[0];
    }
    $selectId = $selectId ?? ($paramName === 'preferred_location' ? 'tp-preferred-location' : 'tp-location');
    $selectClass = $selectClass ?? 'form-select form-select-sm tp-filter';
    $formId = $formId ?? 'tp-search-form';
    $showCounts = $showCounts ?? true;
    $allOptionLabel = $allOptionLabel ?? 'All cities';
    $hintText = $hintText ?? 'Main metro cities (Mumbai, Delhi NCR, Bangalore, etc.).';
@endphp
<select class="{{ $selectClass }}" id="{{ $selectId }}" name="{{ $paramName }}"
        @if($formId) form="{{ $formId }}" @endif
        @if(!empty($onchangeFilter)) data-tp-filter="1" @endif>
    <option value="">{{ $allOptionLabel }}</option>
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
@if(empty($locationFacets) && ($selectedCity === '') && $hintText !== '')
    <p class="small text-muted mb-0 mt-1">{{ $hintText }}</p>
@endif
