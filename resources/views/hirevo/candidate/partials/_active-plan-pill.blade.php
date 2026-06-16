@if(!empty($candidateActivePlanName) && ($candidateHasPremium ?? false))
    <a href="{{ route('pricing') }}" class="cp-plan-pill" title="Your active plan">
        <i class="mdi mdi-crown"></i>
        <span class="cp-plan-pill__name">{{ $candidateActivePlanName }}</span>
        @if(!empty($candidatePlanExpiresAt))
            <span class="cp-plan-pill__meta">until {{ $candidatePlanExpiresAt->format('d M Y') }}</span>
        @endif
    </a>
@endif
