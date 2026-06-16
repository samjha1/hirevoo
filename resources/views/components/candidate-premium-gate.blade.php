@props([
    'feature' => 'this feature',
    'compact' => false,
])

<div @class([
    'cf-premium-gate',
    'cf-premium-gate--unlocked' => $unlocked,
    'cf-premium-gate--locked' => ! $unlocked,
    'cf-premium-gate--compact' => $compact,
]) @unless($unlocked) data-premium-gate data-feature="{{ $feature }}" @endunless>
    <div class="cf-premium-gate__content" @unless($unlocked) aria-hidden="true" @endunless>
        {{ $slot }}
    </div>
    @unless($unlocked)
        <button type="button" class="cf-premium-gate__overlay" data-premium-trigger aria-label="Unlock {{ $feature }}">
            <span class="cf-premium-gate__badge"><i class="mdi mdi-crown"></i> Premium</span>
            <strong class="cf-premium-gate__title">Unlock {{ $feature }}</strong>
            <span class="cf-premium-gate__sub">Upgrade to Advantage or above to access AI-powered career tools</span>
            <span class="cf-premium-gate__tap">Tap to view plans</span>
        </button>
    @endunless
</div>
