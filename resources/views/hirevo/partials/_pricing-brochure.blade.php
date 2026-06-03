@php
    $context = $context ?? 'employer';
    $hero = $hero ?? config('hirevo_plans.hero', []);
    $plans = $plans ?? config('hirevo_plans.plans', []);
    $comparison = $comparison ?? config('hirevo_plans.comparison', []);
    $payPerHire = $payPerHire ?? config('hirevo_plans.pay_per_hire', []);
    $addons = $addons ?? config('hirevo_plans.addons', []);
    $cta = $cta ?? config('hirevo_plans.cta', []);
    $contactUrl = route('contact');
@endphp

@include('hirevo.partials._pricing-brochure-styles')

<div class="hp-pricing-bleed">
<div class="hp-pricing-wrap">

@if($context === 'employer')
    <div class="hp-section-wrap" style="padding-top: 8px;">
        <div class="hp-employer-bar">
            <div>
                @if(!empty($hasSubscription) && !empty($currentPlan))
                    <span>Current plan: <strong>{{ $plans[$currentPlan]['name'] ?? ucfirst($currentPlan) }}</strong></span>
                @else
                    <span><strong>Talent Pool</strong> resume database requires any active plan. Credits are only for job postings.</span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small">Job posting credits: <strong>{{ $credits ?? 0 }}</strong></span>
                <a href="{{ route('contact') }}?subject=Job%20posting%20credits" class="btn btn-sm btn-outline-secondary">Get job credits</a>
            </div>
        </div>
    </div>
@endif

<section class="hp-hero">
    <div class="hp-hero-badge">{{ $hero['badge'] ?? 'Pricing Plans 2025' }}</div>
    <h1>{{ $hero['title'] ?? 'Transparent Pricing.' }}<br>Exceptional <span class="hp-gradient">{{ $hero['title_highlight'] ?? 'Hiring Results.' }}</span></h1>
    <p>{{ $hero['subtitle'] ?? '' }}</p>
  @if(!empty($hero['stats']))
    <div class="hp-hero-stats">
        @foreach($hero['stats'] as $i => $stat)
            @if($i > 0)<div class="hp-stat-divider"></div>@endif
            <div class="hp-stat">
                <div class="hp-stat-number">{{ $stat['value'] }}<span>{{ $stat['suffix'] ?? '' }}</span></div>
                <div class="hp-stat-label">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>
  @endif
</section>

<section class="hp-plans-section">
    <div class="hp-section-wrap">
        <div class="hp-section-header">
            <div class="hp-section-tag">Subscription Plans</div>
            <h2>Choose Your Hiring Plan</h2>
            <p>All plans include access to Hirevo's AI-powered matching engine and verified referral network.</p>
        </div>

        <div class="hp-plans-grid">
            @foreach($plans as $key => $plan)
                <div class="hp-plan-card {{ !empty($plan['popular']) ? 'hp-popular' : '' }}">
                    @if(!empty($plan['popular']))
                        <div class="hp-popular-badge">⭐ Most Popular</div>
                    @endif
                    @if($context === 'employer' && !empty($currentPlan) && $currentPlan === $key)
                        <div class="hp-plan-current">Your current plan</div>
                    @endif
                    <div class="hp-plan-tier">{{ $plan['tier'] ?? '' }}</div>
                    <div class="hp-plan-name">{{ $plan['name'] }}</div>
                    <div class="hp-plan-best">{{ $plan['tagline'] ?? '' }}</div>
                    @if(!empty($plan['custom_price']))
                        <div class="hp-plan-custom-price">Custom Pricing</div>
                    @else
                        <div class="hp-plan-price"><span class="hp-currency">₹</span>{{ number_format($plan['price_inr']) }}</div>
                    @endif
                    <div class="hp-plan-price-sub">{{ $plan['price_sub'] ?? '' }}</div>
                    <div class="hp-plan-divider"></div>
                    @if($context === 'employer' && empty($plan['custom_price']) && config('hirevo_plans.checkout.mode', 'cheque') === 'cheque')
                        <button type="button" class="hp-plan-cta js-plan-checkout" data-plan-key="{{ $key }}">
                            {{ $plan['cta'] ?? 'Get Started' }}
                        </button>
                    @else
                        <a class="hp-plan-cta" href="{{ $contactUrl }}?subject=Hirevo%20{{ urlencode($plan['name']) }}%20plan">
                            {{ $plan['cta'] ?? 'Get Started' }}
                        </a>
                    @endif
                    <div class="hp-features-label">What's included</div>
                    <ul class="hp-feature-list">
                        @foreach($plan['features'] ?? [] as $feature)
                            <li class="hp-feature-item">
                                <span class="hp-check-icon">
                                    <svg viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="#2EC4B6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="hp-section-divider"></div>
<section class="hp-comparison-section">
    <div class="hp-section-wrap">
        <div class="hp-section-header">
            <div class="hp-section-tag">Full Comparison</div>
            <h2>Feature Comparison Table</h2>
            <p>See exactly what's included across every plan — no hidden asterisks.</p>
        </div>

        <div class="hp-compare-scroll">
            <table class="hp-compare-table">
                <thead>
                    <tr>
                        <th style="width:28%;">Feature</th>
                        @foreach($comparison['columns'] ?? array_keys($plans) as $colKey)
                            @php $colPlan = $plans[$colKey] ?? []; @endphp
                            <th class="{{ ($comparison['popular_column'] ?? '') === $colKey ? 'hp-popular-col' : '' }}">
                                {{ $colPlan['name'] ?? ucfirst($colKey) }}
                                @if(($comparison['popular_column'] ?? '') === $colKey) ⭐@endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparison['rows'] ?? [] as $row)
                        @if(!empty($row['category']))
                            <tr class="hp-cat-row"><td colspan="{{ count($comparison['columns'] ?? []) + 1 }}">{{ $row['category'] }}</td></tr>
                        @else
                            <tr>
                                <td>{{ $row['feature'] ?? '' }}</td>
                                @foreach($row['cells'] ?? [] as $ci => $cell)
                                    @php $colKey = ($comparison['columns'] ?? [])[$ci] ?? null; @endphp
                                    <td class="{{ ($comparison['popular_column'] ?? '') === $colKey ? 'hp-popular-col' : '' }}">
                                        @if($cell === 'yes')
                                            <span class="hp-check">✓</span>
                                        @elseif($cell === '—' || $cell === '-')
                                            <span class="hp-dash">—</span>
                                        @elseif(in_array($cell, ['Limited', 'Premium', 'Custom', 'Advanced', 'Priority'], true))
                                            <span class="hp-partial">{{ $cell }}</span>
                                        @else
                                            {{ $cell }}
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
</section>

<div class="hp-section-divider"></div>
<section class="hp-pph-section">
    <div class="hp-section-wrap">
        <div class="hp-section-header">
            <div class="hp-section-tag">Flexible Model</div>
            <h2>Pay Per Hire</h2>
            <p>No monthly commitment. Pay only when you successfully hire. Perfect for occasional or specialized hiring needs.</p>
        </div>

        <div class="hp-pph-grid">
            @foreach($payPerHire['items'] ?? [] as $item)
                <div class="hp-pph-card">
                    <div class="hp-pph-icon {{ $item['icon_class'] ?? 'teal' }}">{{ $item['icon'] }}</div>
                    <div>
                        <div class="hp-pph-level">{{ $item['level'] }}</div>
                        <div class="hp-pph-name">{{ $item['name'] }}</div>
                        <div class="hp-pph-price">{!! $item['price'] !!}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(!empty($payPerHire['note']))
            <div class="hp-pph-note">
                <div class="hp-pph-note-icon">💡</div>
                <div>
                    <div style="font-weight:600;font-size:14px;color:var(--hp-dark);margin-bottom:2px;">{{ $payPerHire['note']['title'] }}</div>
                    <div style="font-size:13px;color:var(--hp-dark-500);">{{ $payPerHire['note']['body'] }}</div>
                </div>
            </div>
        @endif
    </div>
</section>

<div class="hp-section-divider"></div>
<section class="hp-addons-section">
    <div class="hp-section-wrap">
        <div class="hp-section-header">
            <div class="hp-section-tag">À La Carte</div>
            <h2>Additional Services</h2>
            <p>Supplement any plan with specialized services designed for specific hiring scenarios and scale.</p>
        </div>

        <div class="hp-addons-grid">
            @foreach($addons as $addon)
                <div class="hp-addon-card {{ !empty($addon['dark']) ? 'hp-dark' : '' }}">
                    <div class="hp-addon-icon-wrap">{{ $addon['icon'] }}</div>
                    <div class="hp-addon-name">{{ $addon['name'] }}</div>
                    <div class="hp-addon-price">{!! $addon['price'] !!}</div>
                    <div class="hp-addon-desc">{{ $addon['desc'] }}</div>
                    @if(!empty($addon['link']))
                        <a href="{{ $contactUrl }}" class="hp-addon-link">{{ $addon['link'] }}</a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="hp-cta-section">
    <div class="hp-section-wrap">
        <div class="hp-cta-inner">
            <div class="hp-cta-tag">{{ $cta['tag'] ?? 'Free Consultation' }}</div>
            <h2>{{ $cta['title'] ?? 'Book a Free Hiring Consultation' }}</h2>
            <p>{{ $cta['subtitle'] ?? '' }}</p>
            <div class="hp-cta-buttons">
                <a href="{{ $contactUrl }}?subject=Free%20Hiring%20Consultation" class="hp-btn-primary">{{ $cta['primary'] ?? '📅 Book Free Consultation' }}</a>
                <a href="{{ route('home') }}" class="hp-btn-secondary">{{ $cta['secondary'] ?? 'View Case Studies →' }}</a>
            </div>
            @if(!empty($cta['trust']))
                <div class="hp-cta-trust">
                    @foreach($cta['trust'] as $trust)
                        <div class="hp-trust-item"><div class="hp-trust-dot"></div> {{ $trust }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($context === 'employer')
            <div class="hp-credits-box">
                <h3>Credits &amp; Talent Pool access</h3>
                <ul>
                    <li><strong>1 credit = 1 job post</strong> (create or repost a job listing).</li>
                    <li><strong>Talent Pool resume database</strong> is included with any active subscription plan — not credits.</li>
                    <li>Without a plan, use <strong>Phone number</strong> on candidate cards to subscribe and unlock contacts.</li>
                </ul>
                <a href="{{ route('contact') }}?subject=Job%20posting%20credits" class="btn btn-outline-secondary btn-sm">Contact for job credits</a>
            </div>
        @endif
    </div>
</section>

</div>
</div>
