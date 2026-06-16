@php
    $context = $context ?? 'employer';
    $plans = $plans ?? config('hirevo_plans.plans', []);
    $comparison = $comparison ?? config('hirevo_plans.comparison', []);
    $payPerHire = $payPerHire ?? config('hirevo_plans.pay_per_hire', []);
    $addons = $addons ?? config('hirevo_plans.addons', []);
    $cta = $cta ?? config('hirevo_plans.cta', []);
    $contactUrl = route('contact');

    $planMeta = [
        'hiring-launch' => ['icon' => 'mdi-rocket-launch',       'accent' => 'bp-plan--launch'],
        'starter'       => ['icon' => 'mdi-rocket-launch-outline', 'accent' => 'bp-plan--starter'],
        'growth'        => ['icon' => 'mdi-trending-up',           'accent' => 'bp-plan--growth'],
        'scale'         => ['icon' => 'mdi-domain',                'accent' => 'bp-plan--scale'],
        'enterprise'    => ['icon' => 'mdi-crown-outline',         'accent' => 'bp-plan--enterprise'],
    ];

    $launchPlanKey = null;
    $launchPlan = null;
    $subscriptionPlans = [];
    foreach ($plans as $key => $plan) {
        $isLaunch = $key === 'hiring-launch'
            || !empty($plan['extras']['is_launch_offer'])
            || in_array($plan['billing_period'] ?? '', ['one_time_7d', 'launch_7d'], true);
        if ($isLaunch) {
            $launchPlanKey = $key;
            $launchPlan = $plan;
        } else {
            $subscriptionPlans[$key] = $plan;
        }
    }
@endphp

@include('hirevo.partials._pricing-brochure-styles')

<div class="bp-page">

    @if($context === 'employer')
        @if(empty($isApproved) && ($employerCheckoutMode ?? 'cheque') === 'cheque')
            <div class="bp-alert bp-alert--warning">
                <i class="mdi mdi-shield-alert-outline"></i>
                <div>
                    <strong>Account pending approval</strong>
                    <span>You can review plans now. Checkout opens after approval.</span>
                </div>
            </div>
        @endif
        @if(!empty($pendingPayment))
            @php $pendingMeta = $pendingPayment->meta ?? []; @endphp
            <div class="bp-alert bp-alert--info">
                <i class="mdi mdi-clock-outline"></i>
                <div>
                    <strong>Payment pending verification</strong>
                    <span>{{ $pendingMeta['plan_name'] ?? 'Your plan' }} ({{ $pendingPayment->payment_gateway === 'netbanking' ? 'UTR' : 'cheque' }} {{ $pendingPayment->payment_reference }}) — {{ config('hirevo_plans.checkout.pending_message') }}</span>
                </div>
            </div>
        @endif

        <div class="bp-status-row">
            <div class="bp-status-card bp-status-card--plan">
                <div class="bp-status-card__icon"><i class="mdi mdi-shield-check-outline"></i></div>
                <div class="bp-status-card__body">
                    <span class="bp-status-card__label">Current plan</span>
                    @if(!empty($hasSubscription) && !empty($currentPlan))
                        <span class="bp-status-card__value">{{ $plans[$currentPlan]['name'] ?? ucfirst($currentPlan) }}</span>
                        @if(!empty($subscriptionExpiresAt))
                            <span class="bp-status-card__meta"><i class="mdi mdi-calendar-clock"></i> Expires {{ $subscriptionExpiresAt->format('d M Y') }}</span>
                        @endif
                    @else
                        <span class="bp-status-card__value bp-status-card__value--muted">No active plan</span>
                        <span class="bp-status-card__meta">Subscribe to unlock Talent Pool</span>
                    @endif
                </div>
            </div>
            <div class="bp-status-card bp-status-card--pool">
                <div class="bp-status-card__icon"><i class="mdi mdi-database-outline"></i></div>
                <div class="bp-status-card__body">
                    <span class="bp-status-card__label">Talent Pool</span>
                    @if(!empty($hasSubscription))
                        <span class="bp-status-card__value"><span class="bp-dot bp-dot--active"></span> Active</span>
                        <span class="bp-status-card__meta">Resume database included</span>
                    @else
                        <span class="bp-status-card__value bp-status-card__value--muted"><span class="bp-dot"></span> Locked</span>
                        <span class="bp-status-card__meta">Requires any subscription</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('contact') }}?subject=Free%20Hiring%20Consultation" class="bp-status-card bp-status-card--link bp-status-card--help">
                <div class="bp-status-card__icon"><i class="mdi mdi-headset"></i></div>
                <div class="bp-status-card__body">
                    <span class="bp-status-card__label">Need help choosing?</span>
                    <span class="bp-status-card__value">Free consultation</span>
                    <span class="bp-status-card__meta">Talk to a hiring expert</span>
                </div>
                <i class="mdi mdi-chevron-right bp-status-card__arrow"></i>
            </a>
        </div>
    @endif

    <div class="bp-shell">
        <div class="bp-tabs-wrap">
            <div class="bp-tabs" role="tablist">
                <button type="button" class="bp-tab is-active" data-bp-tab="plans" role="tab" aria-selected="true">
                    <i class="mdi mdi-tag-multiple-outline"></i> Subscription Plans
                </button>
                <button type="button" class="bp-tab" data-bp-tab="compare" role="tab" aria-selected="false">
                    <i class="mdi mdi-table-large"></i> Compare
                </button>
                <button type="button" class="bp-tab" data-bp-tab="pph" role="tab" aria-selected="false">
                    <i class="mdi mdi-handshake-outline"></i> Pay Per Hire
                </button>
                <button type="button" class="bp-tab" data-bp-tab="addons" role="tab" aria-selected="false">
                    <i class="mdi mdi-puzzle-outline"></i> Add-ons
                </button>
            </div>
        </div>

        <div class="bp-panels">
            {{-- Plans --}}
            <div class="bp-panel is-active" data-bp-panel="plans" role="tabpanel">
                <div class="bp-panel-head">
                    <div>
                        <h2 class="bp-panel-title">Choose your hiring plan</h2>
                        <p class="bp-panel-desc">Start with the 7-day Launch Program or pick a monthly subscription. Job posting credits are shown in the top bar.</p>
                    </div>
                    <button type="button" class="bp-link-btn" data-bp-goto="compare">
                        <i class="mdi mdi-table-large"></i> Full comparison
                    </button>
                </div>

                @if($launchPlan && $launchPlanKey)
                    @php
                        $launchExtras = $launchPlan['extras'] ?? [];
                        $launchIsCurrent = $context === 'employer' && !empty($currentPlan) && $currentPlan === $launchPlanKey && !empty($hasSubscription);
                        $launchPending = $context === 'employer' && !empty($pendingPayment) && (($pendingPayment->meta['plan_key'] ?? '') === $launchPlanKey);
                    @endphp
                    <div class="bp-launch-card {{ $launchIsCurrent ? 'bp-launch-card--current' : '' }}">
                        <div class="bp-launch-card__badges">
                            <span class="bp-launch-card__badge"><i class="mdi mdi-flash"></i> Launch Offer</span>
                            @if(!empty($launchExtras['duration']))
                                <span class="bp-launch-card__badge bp-launch-card__badge--duration"><i class="mdi mdi-calendar-range"></i> {{ $launchExtras['duration'] }}</span>
                            @endif
                            <span class="bp-launch-card__badge bp-launch-card__badge--new">New companies only</span>
                        </div>

                        <div class="bp-launch-card__grid">
                            <div class="bp-launch-card__main">
                                <span class="bp-launch-card__tier">{{ $launchPlan['tier'] ?? 'Launch Offer' }}</span>
                                <h3 class="bp-launch-card__title">Hirevoo {{ $launchPlan['name'] }}</h3>
                                <p class="bp-launch-card__tagline">{{ $launchPlan['tagline'] ?? '' }}</p>

                                <div class="bp-launch-card__price">
                                    <span class="bp-launch-card__currency">₹</span>
                                    <span class="bp-launch-card__amount">{{ number_format($launchPlan['price_inr']) }}</span>
                                    <span class="bp-launch-card__period">{{ $launchPlan['price_sub'] ?? 'one-time' }}</span>
                                </div>

                                @if(!empty($launchExtras['ideal_for']))
                                    <div class="bp-launch-card__ideal">
                                        <span class="bp-launch-card__ideal-label">Ideal for</span>
                                        <div class="bp-launch-card__ideal-pills">
                                            @foreach($launchExtras['ideal_for'] as $ideal)
                                                <span>{{ $ideal }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="bp-launch-card__action">
                                    @if($launchIsCurrent)
                                        <button type="button" class="bp-btn bp-btn--disabled" disabled><i class="mdi mdi-check"></i> Current plan</button>
                                    @elseif($launchPending)
                                        <button type="button" class="bp-btn bp-btn--pending" disabled><i class="mdi mdi-clock-outline"></i> Payment pending</button>
                                    @elseif($context === 'employer' && empty($plan['custom_price']) && in_array($employerCheckoutMode ?? null, ['cheque', 'razorpay'], true))
                                        <button type="button" class="bp-btn bp-btn--launch js-plan-checkout" data-plan-key="{{ $launchPlanKey }}" @if(($employerCheckoutMode ?? '') === 'cheque' && empty($isApproved)) disabled title="Available after account approval" @endif>
                                            {{ $launchPlan['cta'] ?? 'Launch Now' }} <i class="mdi mdi-arrow-right"></i>
                                        </button>
                                    @else
                                        <a class="bp-btn bp-btn--launch" href="{{ $contactUrl }}?subject=Hirevoo%20Hiring%20Launch%20Program">
                                            {{ $launchPlan['cta'] ?? 'Launch Now' }} <i class="mdi mdi-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="bp-launch-card__details">
                                <div class="bp-launch-card__features">
                                    <h4>What's included</h4>
                                    <ul>
                                        @foreach($launchPlan['features'] ?? [] as $feature)
                                            <li><i class="mdi mdi-check-circle"></i> {{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @if(!empty($launchExtras['bonus']))
                                    <div class="bp-launch-card__bonus">
                                        <h4><i class="mdi mdi-gift-outline"></i> Bonus</h4>
                                        <ul>
                                            @foreach($launchExtras['bonus'] as $bonus)
                                                <li>{{ $bonus }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bp-plans-divider">
                        <span>Monthly subscription plans</span>
                    </div>
                @endif

                <div class="bp-plans-track">
                    @foreach($subscriptionPlans as $key => $plan)
                        @php
                            $meta = $planMeta[$key] ?? ['icon' => 'mdi-briefcase-outline', 'accent' => ''];
                            $isCurrentPlan = $context === 'employer' && !empty($currentPlan) && $currentPlan === $key && !empty($hasSubscription);
                            $hasPendingForPlan = $context === 'employer' && !empty($pendingPayment) && (($pendingPayment->meta['plan_key'] ?? '') === $key);
                            $featureCount = count($plan['features'] ?? []);
                        @endphp
                        <article class="bp-plan {{ $meta['accent'] }} {{ !empty($plan['popular']) ? 'bp-plan--popular' : '' }} {{ $isCurrentPlan ? 'bp-plan--current' : '' }}">
                            @if(!empty($plan['popular']))
                                <span class="bp-plan__ribbon"><i class="mdi mdi-star"></i> Recommended</span>
                            @elseif($isCurrentPlan)
                                <span class="bp-plan__ribbon bp-plan__ribbon--current"><i class="mdi mdi-check-circle"></i> Current</span>
                            @endif

                            <div class="bp-plan__top">
                                <div class="bp-plan__icon"><i class="mdi {{ $meta['icon'] }}"></i></div>
                                <div>
                                    @if(!empty($plan['tier']))
                                        <span class="bp-plan__tier">{{ $plan['tier'] }}</span>
                                    @endif
                                    <h3 class="bp-plan__name">{{ $plan['name'] }}</h3>
                                </div>
                            </div>

                            <p class="bp-plan__tagline">{{ $plan['tagline'] ?? '' }}</p>

                            <div class="bp-plan__price">
                                @if(!empty($plan['custom_price']))
                                    <span class="bp-plan__amount">Custom</span>
                                    <span class="bp-plan__period">Tailored for your org</span>
                                @else
                                    <div class="bp-plan__price-row">
                                        <span class="bp-plan__currency">₹</span>
                                        <span class="bp-plan__amount">{{ number_format($plan['price_inr']) }}</span>
                                    </div>
                                    <span class="bp-plan__period">{{ $plan['price_sub'] ?? 'per month' }}</span>
                                @endif
                            </div>

                            <div class="bp-plan__feat-head">
                                <span>What's included</span>
                                <span class="bp-plan__feat-count">{{ $featureCount }} features</span>
                            </div>
                            <ul class="bp-plan__features">
                                @foreach(array_slice($plan['features'] ?? [], 0, 5) as $feature)
                                    <li><i class="mdi mdi-check-circle"></i><span>{{ $feature }}</span></li>
                                @endforeach
                                @if($featureCount > 5)
                                    <li class="bp-plan__more">+ {{ $featureCount - 5 }} more in comparison</li>
                                @endif
                            </ul>

                            <div class="bp-plan__action">
                                @if($isCurrentPlan)
                                    <button type="button" class="bp-btn bp-btn--disabled" disabled><i class="mdi mdi-check"></i> Current plan</button>
                                @elseif($hasPendingForPlan)
                                    <button type="button" class="bp-btn bp-btn--pending" disabled><i class="mdi mdi-clock-outline"></i> Payment pending</button>
                                @elseif($context === 'employer' && empty($plan['custom_price']) && in_array($employerCheckoutMode ?? null, ['cheque', 'razorpay'], true))
                                    <button type="button" class="bp-btn bp-btn--primary js-plan-checkout" data-plan-key="{{ $key }}" @if(($employerCheckoutMode ?? '') === 'cheque' && empty($isApproved)) disabled title="Available after account approval" @endif>
                                        Buy now <i class="mdi mdi-arrow-right"></i>
                                    </button>
                                @else
                                    <a class="bp-btn bp-btn--primary" href="{{ $contactUrl }}?subject=Hirevo%20{{ urlencode($plan['name']) }}%20plan">
                                        {{ $plan['cta'] ?? 'Contact sales' }} <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="bp-info-card">
                    <div class="bp-info-card__icon"><i class="mdi mdi-information-outline"></i></div>
                    <div>
                        <strong>How credits work</strong>
                        <p><strong>1 credit = 1 job post.</strong> Talent Pool access comes with any active subscription. Without a plan, use <strong>Phone number</strong> on candidate cards to subscribe.</p>
                    </div>
                </div>
            </div>

            {{-- Compare --}}
            <div class="bp-panel" data-bp-panel="compare" role="tabpanel" hidden>
                <div class="bp-panel-head">
                    <div>
                        <h2 class="bp-panel-title">Feature comparison</h2>
                        <p class="bp-panel-desc">A detailed look at what's included in each plan.</p>
                    </div>
                </div>
                <div class="bp-table-wrap">
                    <table class="bp-table">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                @foreach($comparison['columns'] ?? array_keys($plans) as $colKey)
                                    @php $colPlan = $plans[$colKey] ?? []; @endphp
                            <th class="{{ $colKey === 'hiring-launch' ? 'bp-table__launch' : '' }} {{ ($comparison['popular_column'] ?? '') === $colKey ? 'bp-table__highlight' : '' }}">
                                {{ $colPlan['name'] ?? ucfirst($colKey) }}
                                @if($colKey === 'hiring-launch')
                                    <span class="bp-table__launch-badge">Launch</span>
                                @elseif(($comparison['popular_column'] ?? '') === $colKey)
                                    <span class="bp-table__badge">Popular</span>
                                @endif
                            </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparison['rows'] ?? [] as $row)
                                @if(!empty($row['category']))
                                    <tr class="bp-table__cat"><td colspan="{{ count($comparison['columns'] ?? []) + 1 }}">{{ $row['category'] }}</td></tr>
                                @else
                                    <tr>
                                        <td>{{ $row['feature'] ?? '' }}</td>
                                        @foreach($row['cells'] ?? [] as $ci => $cell)
                                            @php $colKey = ($comparison['columns'] ?? [])[$ci] ?? null; @endphp
                                            <td class="{{ $colKey === 'hiring-launch' ? 'bp-table__launch' : '' }} {{ ($comparison['popular_column'] ?? '') === $colKey ? 'bp-table__highlight' : '' }}">
                                                @if($cell === 'yes')
                                                    <span class="bp-table__check"><i class="mdi mdi-check-bold"></i></span>
                                                @elseif($cell === '—' || $cell === '-')
                                                    <span class="bp-table__no">—</span>
                                                @elseif(in_array($cell, ['Limited', 'Premium', 'Custom', 'Advanced', 'Priority'], true))
                                                    <span class="bp-table__tag">{{ $cell }}</span>
                                                @else
                                                    <span class="bp-table__text">{{ $cell }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pay Per Hire --}}
            <div class="bp-panel" data-bp-panel="pph" role="tabpanel" hidden>
                <div class="bp-panel-head">
                    <div>
                        <h2 class="bp-panel-title">Pay per hire</h2>
                        <p class="bp-panel-desc">No monthly commitment — pay only on successful placement.</p>
                    </div>
                </div>
                <div class="bp-pph-grid">
                    @foreach($payPerHire['items'] ?? [] as $item)
                        <div class="bp-pph-item">
                            <div class="bp-pph-item__icon">{{ $item['icon'] }}</div>
                            <div class="bp-pph-item__body">
                                <span class="bp-pph-item__level">{{ $item['level'] }}</span>
                                <span class="bp-pph-item__name">{{ $item['name'] }}</span>
                            </div>
                            <div class="bp-pph-item__price">{!! $item['price'] !!}</div>
                        </div>
                    @endforeach
                </div>
                @if(!empty($payPerHire['note']))
                    <div class="bp-info-card bp-info-card--tip">
                        <div class="bp-info-card__icon"><i class="mdi mdi-lightbulb-on-outline"></i></div>
                        <div>
                            <strong>{{ $payPerHire['note']['title'] }}</strong>
                            <p>{{ $payPerHire['note']['body'] }}</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Add-ons --}}
            <div class="bp-panel" data-bp-panel="addons" role="tabpanel" hidden>
                <div class="bp-panel-head">
                    <div>
                        <h2 class="bp-panel-title">Add-on services</h2>
                        <p class="bp-panel-desc">Extend your plan with specialized hiring support.</p>
                    </div>
                </div>
                <div class="bp-addons-grid">
                    @foreach($addons as $addon)
                        <div class="bp-addon {{ !empty($addon['dark']) ? 'bp-addon--featured' : '' }}">
                            <div class="bp-addon__top">
                                <span class="bp-addon__icon">{{ $addon['icon'] }}</span>
                                @if(!empty($addon['dark']))
                                    <span class="bp-addon__chip">Recommended</span>
                                @endif
                            </div>
                            <h4 class="bp-addon__name">{{ $addon['name'] }}</h4>
                            <div class="bp-addon__price">{!! $addon['price'] !!}</div>
                            <p class="bp-addon__desc">{{ $addon['desc'] }}</p>
                            @if(!empty($addon['link']))
                                <a href="{{ $contactUrl }}" class="bp-addon__link">{{ $addon['link'] }} <i class="mdi mdi-arrow-right"></i></a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bp-footer-cta">
        <div class="bp-footer-cta__glow"></div>
        <div class="bp-footer-cta__content">
            <span class="bp-footer-cta__tag">{{ $cta['tag'] ?? 'Free Consultation' }}</span>
            <h3>{{ $cta['title'] ?? 'Book a Free Hiring Consultation' }}</h3>
            <p>{{ $cta['subtitle'] ?? '' }}</p>
            @if(!empty($cta['trust']))
                <div class="bp-trust-pills">
                    @foreach($cta['trust'] as $trust)
                        <span class="bp-trust-pill"><i class="mdi mdi-check"></i> {{ $trust }}</span>
                    @endforeach
                </div>
            @endif
        </div>
        <a href="{{ $contactUrl }}?subject=Free%20Hiring%20Consultation" class="bp-btn bp-btn--cta">
            <i class="mdi mdi-calendar-check"></i> Book consultation
        </a>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var tabs = document.querySelectorAll('[data-bp-tab]');
    var panels = document.querySelectorAll('[data-bp-panel]');
    if (!tabs.length) return;

    function activateTab(target) {
        tabs.forEach(function (t) {
            var on = t.getAttribute('data-bp-tab') === target;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            var show = panel.getAttribute('data-bp-panel') === target;
            panel.classList.toggle('is-active', show);
            panel.hidden = !show;
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activateTab(tab.getAttribute('data-bp-tab'));
        });
    });

    document.querySelectorAll('[data-bp-goto]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            activateTab(btn.getAttribute('data-bp-goto'));
            document.querySelector('.bp-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
})();
</script>
@endpush
