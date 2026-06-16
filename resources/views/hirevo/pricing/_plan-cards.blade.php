@php
    $plans = $candidatePlans ?? config('hirevo_candidate_plans.plans', []);
    $isCandidate = $isCandidate ?? (auth()->check() && auth()->user()->isCandidate());
    $hasPremium = $candidateHasPremium ?? false;
    $activePlanKey = $candidateActivePlanKey ?? null;
    $renewalPlanKey = $candidateRenewalPlanKey ?? null;
    if ($renewalPlanKey !== null) {
        $renewalPlanKey = strtolower(trim((string) $renewalPlanKey));
    }
@endphp
<div class="row g-3" id="candidate-plan-cards">
    @foreach($plans as $key => $plan)
        @php
            $planKey = strtolower((string) $key);
            $isCurrentPlan = $hasPremium && $activePlanKey === $planKey;
            $isScheduledRenewal = $hasPremium && $renewalPlanKey === $planKey;
        @endphp
        <div class="col-lg-3 col-md-6">
            <div class="pricing-card p-4 {{ !empty($plan['popular']) ? 'pricing-popular' : '' }} {{ $isCurrentPlan ? 'pricing-card--current' : '' }} {{ $isScheduledRenewal ? 'pricing-card--scheduled' : '' }}">
                @if($isCurrentPlan)
                    <span class="pricing-badge pricing-badge--active">Your plan</span>
                @elseif($isScheduledRenewal)
                    <span class="pricing-badge pricing-badge--scheduled">At renewal</span>
                @elseif(!empty($plan['popular']))
                    <span class="pricing-badge">Most Popular</span>
                @endif
                <h3 class="h6 fw-bold mb-1">{{ $plan['name'] }}</h3>
                <div class="h3 fw-bold mb-1">₹{{ number_format((float) $plan['price_inr'], 0) }}</div>
                <p class="text-muted small mb-1">+ GST ({{ (int) ($gstRate ?? 18) }}%)</p>
                <p class="text-muted small mb-3">{{ $plan['tagline'] ?? '' }}</p>
                <ul class="text-muted ps-3 mb-4">
                    @foreach($plan['features'] ?? [] as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                </ul>
                @if($isCurrentPlan)
                    <button type="button" class="btn btn-success w-100" disabled>Current plan</button>
                    @if($renewalPlanKey && $renewalPlanKey !== $planKey)
                        @php $scheduledPlan = $plans[$renewalPlanKey] ?? null; @endphp
                        <p class="text-muted small mt-2 mb-0 text-center">
                            Switching to <strong>{{ $scheduledPlan['name'] ?? ucfirst($renewalPlanKey) }}</strong> at renewal.
                        </p>
                    @endif
                @elseif($isScheduledRenewal)
                    <button type="button"
                            class="btn btn-outline-success w-100 candidate-renewal-cancel-btn"
                            data-plan-key="{{ $planKey }}"
                            data-plan-name="{{ $plan['name'] }}">
                        Scheduled — cancel switch
                    </button>
                @elseif($hasPremium)
                    <button type="button"
                            class="btn btn-outline-primary w-100 candidate-renewal-switch-btn"
                            data-plan-key="{{ $planKey }}"
                            data-plan-name="{{ $plan['name'] }}">
                        Switch at renewal
                    </button>
                @elseif($isCandidate)
                    <button type="button"
                            class="btn {{ !empty($plan['popular']) ? 'btn-primary' : 'btn-outline-primary' }} w-100 candidate-plan-checkout-btn"
                            data-plan-key="{{ $planKey }}"
                            data-plan-name="{{ $plan['name'] }}">
                        {{ $plan['cta'] ?? ('Choose '.$plan['name']) }}
                    </button>
                @else
                    <a href="{{ route('register') }}?role=candidate&amp;plan={{ $planKey }}" class="btn {{ !empty($plan['popular']) ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                        {{ $plan['cta'] ?? ('Choose '.$plan['name']) }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>
<div id="candidate-renewal-alert" class="alert alert-success border-0 shadow-sm rounded-3 mt-3 d-none" role="alert"></div>
<p class="text-muted small mt-3 mb-0">* Unlimited referrals subject to fair usage policy. Prices exclude GST until checkout. All plans provide access to tools and features — Hirevoo does not guarantee job placement, interviews, or referral responses.</p>

<style>
    .pricing-card--current { border: 2px solid #22c55e; }
    .pricing-card--scheduled { border: 2px solid #0d6efd; }
    .pricing-badge--active { background: #22c55e; right: auto; left: 18px; }
    .pricing-badge--scheduled { background: #0d6efd; right: auto; left: 18px; }
</style>
