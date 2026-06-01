@php
    $skills = is_array($candidate['skills'] ?? null) ? $candidate['skills'] : [];
    $skillsVisible = array_slice($skills, 0, 5);
    $skillsMore = count($skills) - count($skillsVisible);
    $initials = collect(explode(' ', (string) ($candidate['full_name'] ?? '')))
        ->filter()
        ->take(2)
        ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
        ->implode('');
@endphp
<div class="tp-candidate-card card employer-card h-100"
     data-source="{{ $candidate['source'] }}"
     data-source-id="{{ $candidate['source_id'] }}">
    <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex align-items-start gap-3 mb-3">
            @if(!empty($candidate['profile_image']))
                <img src="{{ $candidate['profile_image'] }}" alt="" class="tp-avatar rounded-circle" width="52" height="52">
            @else
                <div class="tp-avatar tp-avatar-fallback rounded-circle">{{ $initials ?: '?' }}</div>
            @endif
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h6 class="mb-0 fw-600 text-truncate">{{ $candidate['full_name'] }}</h6>
                    <span class="hbadge hbadge-sm tp-badge-{{ $candidate['badge_class'] ?? 'default' }}">{{ $candidate['badge'] }}</span>
                </div>
                @if(!empty($candidate['title']))
                    <p class="text-muted small mb-0 text-truncate">{{ $candidate['title'] }}</p>
                @endif
            </div>
        </div>

        <ul class="list-unstyled small text-muted mb-3 tp-meta">
            @if(!empty($candidate['experience_label']) || !empty($candidate['experience_years']))
                <li><i class="mdi mdi-briefcase-outline me-1"></i>{{ $candidate['experience_label'] ?? ($candidate['experience_years'].' yrs') }}</li>
            @endif
            @if(!empty($candidate['location']))
                <li><i class="mdi mdi-map-marker-outline me-1"></i>{{ $candidate['location'] }}</li>
            @endif
            @if(!empty($candidate['education']))
                <li><i class="mdi mdi-school-outline me-1"></i>{{ $candidate['education'] }}</li>
            @endif
            @if(!empty($candidate['expected_salary']))
                <li><i class="mdi mdi-currency-inr me-1"></i>{{ $candidate['expected_salary'] }}</li>
            @endif
        </ul>

        @if(count($skillsVisible) > 0)
            <div class="tp-skills mb-3">
                @foreach($skillsVisible as $skill)
                    <span class="skill-tag">{{ $skill }}</span>
                @endforeach
                @if($skillsMore > 0)
                    <span class="skill-tag skill-more">+{{ $skillsMore }}</span>
                @endif
            </div>
        @endif

        @if(!empty($candidate['profile_summary']))
            <p class="small text-muted mb-3 flex-grow-1 tp-summary">{{ \Illuminate\Support\Str::limit($candidate['profile_summary'], 120) }}</p>
        @else
            <div class="flex-grow-1"></div>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-auto pt-2 border-top">
            <button type="button" class="btn btn-primary btn-sm tp-view-btn" data-source="{{ $candidate['source'] }}" data-source-id="{{ $candidate['source_id'] }}">
                View profile
            </button>
            <button type="button"
                    class="btn btn-sm {{ !empty($candidate['is_saved']) ? 'btn-warning' : 'btn-outline-secondary' }} tp-save-btn"
                    data-source="{{ $candidate['source'] }}"
                    data-source-id="{{ $candidate['source_id'] }}"
                    data-saved="{{ !empty($candidate['is_saved']) ? '1' : '0' }}">
                <i class="mdi {{ !empty($candidate['is_saved']) ? 'mdi-bookmark' : 'mdi-bookmark-outline' }}"></i>
                {{ !empty($candidate['is_saved']) ? 'Saved' : 'Save' }}
            </button>
            <button type="button"
                    class="btn btn-sm {{ !empty($candidate['is_shortlisted']) ? 'btn-success' : 'btn-outline-success' }} tp-shortlist-btn"
                    data-source="{{ $candidate['source'] }}"
                    data-source-id="{{ $candidate['source_id'] }}"
                    data-shortlisted="{{ !empty($candidate['is_shortlisted']) ? '1' : '0' }}">
                <i class="mdi {{ !empty($candidate['is_shortlisted']) ? 'mdi-star' : 'mdi-star-outline' }}"></i>
                {{ !empty($candidate['is_shortlisted']) ? 'Shortlisted' : 'Shortlist' }}
            </button>
        </div>
    </div>
</div>
