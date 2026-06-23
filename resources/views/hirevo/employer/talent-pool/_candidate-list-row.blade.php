@php

    $skills = is_array($candidate['skills'] ?? null) ? $candidate['skills'] : [];

    $matchingSkills = $matchingSkills ?? [];

    $canAccessTalentPool = $canAccessTalentPool ?? false;
    $viewTokenCost = $viewTokenCost ?? config('hirevo_plans.unlock_credit_cost', 1);
    $downloadTokenCost = $downloadTokenCost ?? config('hirevo_plans.excel_download_credit_cost', 1);

    $plansUrl = route('employer.plans.index');

    $matchTags = [];

    if ($matchingSkills !== []) {

        foreach ($matchingSkills as $needle) {

            $needleLower = strtolower($needle);

            foreach ($skills as $skill) {

                if (is_string($skill) && str_contains(strtolower($skill), $needleLower)) {

                    $matchTags[] = $skill;

                }

            }

        }

        $matchTags = array_values(array_unique($matchTags));

    }

    $skillsVisible = array_slice($skills, 0, 6);

    $skillsMore = count($skills) - count($skillsVisible);

    $initials = collect(explode(' ', (string) ($candidate['full_name'] ?? '')))

        ->filter()->take(2)->map(fn ($w) => strtoupper(substr($w, 0, 1)))->implode('');

    $currentLine = $candidate['current_role'] ?? null;

    if (!$currentLine && !empty($candidate['title'])) {

        $currentLine = ($candidate['title'] ?? '').(!empty($candidate['current_company']) ? ' at '.$candidate['current_company'] : '');

    }

@endphp

<article class="tp-candidate-row card employer-card tp-row-openable"
         data-source="{{ $candidate['source'] }}"
         data-source-id="{{ $candidate['source_id'] }}">

    <div class="card-body p-0">

        <div class="tp-row-top p-3 pb-2">

            <div class="d-flex align-items-start gap-3">

                @if(!empty($candidate['profile_image']))

                    <img src="{{ $candidate['profile_image'] }}" alt="" class="tp-avatar rounded-circle" width="48" height="48">

                @else

                    <div class="tp-avatar tp-avatar-fallback rounded-circle">{{ $initials ?: '?' }}</div>

                @endif

                <div class="flex-grow-1 min-w-0">

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">

                        <button type="button" class="btn btn-link p-0 tp-name-link fw-600 text-decoration-none tp-open-profile"
                                data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}">
                            {{ $candidate['full_name'] }}
                            <i class="mdi mdi-chevron-right tp-name-chevron"></i>
                        </button>

                        <span class="hbadge hbadge-sm tp-badge-{{ $candidate['badge_class'] ?? 'default' }}">{{ $candidate['badge'] }}</span>

                    </div>

                    @if(!empty($candidate['title']))
                        <p class="text-muted small mb-1 text-truncate">{{ $candidate['title'] }}</p>
                    @endif

                    <div class="tp-highlights d-flex flex-wrap gap-3 small text-muted">

                        @if(!empty($candidate['experience_label']))

                            <span><i class="mdi mdi-briefcase-outline me-1"></i>{{ $candidate['experience_label'] }}</span>

                        @endif

                        @if(!empty($candidate['expected_salary']))

                            <span><i class="mdi mdi-currency-inr me-1"></i>{{ $candidate['expected_salary'] }}</span>

                        @endif

                        @if(!empty($candidate['location']))

                            <span><i class="mdi mdi-map-marker-outline me-1"></i>{{ $candidate['location'] }}</span>

                        @endif

                    </div>

                </div>

                <div class="tp-row-actions d-flex gap-1 flex-shrink-0">

                    <button type="button"

                            class="btn btn-sm btn-icon {{ !empty($candidate['is_saved']) ? 'btn-warning' : 'btn-outline-secondary' }} tp-save-btn"

                            data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}"

                            title="Save"><i class="mdi {{ !empty($candidate['is_saved']) ? 'mdi-bookmark' : 'mdi-bookmark-outline' }}"></i></button>

                    <button type="button"

                            class="btn btn-sm btn-icon {{ !empty($candidate['is_shortlisted']) ? 'btn-success' : 'btn-outline-success' }} tp-shortlist-btn"

                            data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}"

                            title="Shortlist"><i class="mdi {{ !empty($candidate['is_shortlisted']) ? 'mdi-star' : 'mdi-star-outline' }}"></i></button>

                </div>

            </div>

        </div>



        @if(count($matchTags) > 0)

            <div class="tp-matching px-3 pb-2">

                <span class="tp-matching-label">Matching:</span>

                @foreach($matchTags as $tag)

                    <span class="tp-match-pill"><i class="mdi mdi-check"></i> {{ $tag }}</span>

                @endforeach

            </div>

        @endif



        <div class="tp-row-details px-3 pb-3">

            @if($currentLine)

                <p class="tp-detail-line mb-1"><span class="tp-detail-key">Current / Latest</span> {{ $currentLine }}</p>

            @endif

            @if(!empty($candidate['previous_role']))

                <p class="tp-detail-line mb-1"><span class="tp-detail-key">Previous</span> {{ $candidate['previous_role'] }}</p>

            @endif

            @if(!empty($candidate['education']))

                <p class="tp-detail-line mb-1"><span class="tp-detail-key">Education</span> {{ $candidate['education'] }}</p>

            @endif

            @if(!empty($candidate['preferred_location']))

                <p class="tp-detail-line mb-1"><span class="tp-detail-key">Pref. location</span> {{ $candidate['preferred_location'] }}</p>

            @endif

            @if(count($skillsVisible) > 0)

                <p class="tp-detail-line mb-2">

                    <span class="tp-detail-key">Skills</span>

                    @foreach($skillsVisible as $skill)

                        <span class="skill-tag">{{ $skill }}</span>

                    @endforeach

                    @if($skillsMore > 0)<span class="skill-tag skill-more">+{{ $skillsMore }} more</span>@endif

                </p>

            @endif

            <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">

                @if($canAccessTalentPool && !empty($candidate['is_unlocked']) && !empty($candidate['phone']))
                    @php $phoneDigits = preg_replace('/\D+/', '', (string) $candidate['phone']); @endphp
                    <a href="tel:{{ $phoneDigits }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-phone-outline me-1"></i> {{ $candidate['phone'] }}
                    </a>
                @elseif($canAccessTalentPool)
                    <button type="button" class="btn btn-outline-primary btn-sm tp-unlock-phone-btn"
                            data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}">
                        <i class="mdi mdi-phone-lock-outline me-1"></i> View phone ({{ $viewTokenCost }} token)
                    </button>
                @else
                    <a href="{{ $plansUrl }}" class="btn btn-outline-primary btn-sm tp-phone-plans-btn">
                        <i class="mdi mdi-phone-outline me-1"></i> Phone number
                    </a>
                @endif

                <button type="button" class="btn btn-success btn-sm tp-open-profile"
                        data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}">
                    View profile
                </button>

                @if(!empty($candidate['is_saved']))
                    <button type="button" class="btn btn-outline-secondary btn-sm tp-download-btn"
                            data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}"
                            data-can-download="{{ !empty($candidate['can_download']) ? '1' : '0' }}">
                        <i class="mdi mdi-download-outline me-1"></i>
                        @if(!empty($candidate['can_download']))
                            Download
                        @else
                            Download ({{ $downloadTokenCost }} token)
                        @endif
                    </button>
                @endif

                @if(!empty($candidate['has_resume']))
                    <span class="badge bg-light text-dark border"><i class="mdi mdi-paperclip me-1"></i>CV attached</span>
                @endif
                @if($canAccessTalentPool && empty($candidate['is_unlocked']))
                    <span class="badge bg-light text-dark border"><i class="mdi mdi-lock-outline me-1"></i>{{ $viewTokenCost }} token to view contact</span>
                @endif

            </div>

        </div>

    </div>

</article>

