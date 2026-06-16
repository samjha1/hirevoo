@php
    $countryFilter = $countryFilter ?? '';
    $countryLabels = $countryLabels ?? config('hirevo.job_openings_country_labels', []);
    $searchQuery = $searchQuery ?? '';
    $searchLocation = $searchLocation ?? '';
    $hasActiveFilters = $hasActiveFilters ?? false;
    $showLaunchingSoon = $showLaunchingSoon ?? ($hasActiveFilters && ($jobs->total() ?? 0) === 0);

    $countryLabel = ($countryFilter !== '' && isset($countryLabels[$countryFilter]))
        ? ($countryLabels[$countryFilter]['label'] ?? strtoupper($countryFilter))
        : null;

    if ($searchQuery !== '' && $countryLabel) {
        $launchHeadline = 'Launching soon in ' . $countryLabel;
        $launchMessage = 'We are onboarding employers for "' . $searchQuery . '" roles in ' . $countryLabel . '. Check back shortly or browse all openings.';
    } elseif ($countryLabel) {
        $launchHeadline = 'Launching soon in ' . $countryLabel;
        $launchMessage = 'International job listings for ' . $countryLabel . ' are going live soon as more companies join Hirevo.';
    } elseif ($searchQuery !== '') {
        $launchHeadline = 'Launching soon';
        $launchMessage = 'We are expanding "' . $searchQuery . '" roles on Hirevo. Try a broader keyword or view all openings.';
    } elseif ($searchLocation !== '') {
        $launchHeadline = 'Launching soon';
        $launchMessage = 'Openings in "' . $searchLocation . '" are coming soon. Try another location or view all jobs.';
    } else {
        $launchHeadline = 'Launching soon';
        $launchMessage = 'No roles match these filters yet. We are adding more employers every week.';
    }

    $clearUrl = route('job-openings');
    $countryOnlyUrl = $searchQuery !== ''
        ? route('job-openings', array_filter(['q' => $searchQuery]))
        : null;
@endphp

<div class="card border-0 jo-filters-card jo-empty-state jo-launching-soon text-center py-5 px-3">
    <div class="card-body py-5">
        <div class="jo-launching-soon__badge mb-3">
            <span class="jo-launching-soon__pulse" aria-hidden="true"></span>
            Launching soon
        </div>
        <div class="jo-empty-icon d-inline-flex align-items-center justify-content-center mb-4">
            <i class="mdi mdi-earth" style="font-size: 2rem; color: #0d9488;" aria-hidden="true"></i>
        </div>
        <h2 class="h5 fw-bold mb-2" style="color: var(--jo-ink); letter-spacing:-0.02em;">{{ $launchHeadline }}</h2>
        <p class="text-muted mb-4 mx-auto" style="max-width: 440px;">{{ $launchMessage }}</p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ $clearUrl }}" class="btn btn-primary rounded-pill px-4 jo-apply-btn">View all openings</a>
            @if($countryFilter !== '' && $countryOnlyUrl)
                <a href="{{ $countryOnlyUrl }}" class="btn btn-outline-secondary rounded-pill px-4 fw-700">Search without country</a>
            @elseif($hasActiveFilters)
                <a href="{{ $clearUrl }}" class="btn btn-outline-secondary rounded-pill px-4 fw-700">Clear filters</a>
            @endif
        </div>
        @if($countryFilter !== '')
            <p class="small text-muted mt-4 mb-0">
                Hiring in Canada, the US, UK, and UAE is rolling out in phases.
            </p>
        @endif
    </div>
</div>
