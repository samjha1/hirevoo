@extends('layouts.employer')

@section('title', 'Plans & Pricing')
@section('header_title', 'Buy Packages')

@section('content')
    <div class="employer-plans-page">
        @include('hirevo.partials._pricing-brochure', [
            'context' => 'employer',
            'plans' => $plans,
            'hero' => $hero,
            'comparison' => $comparison,
            'payPerHire' => $payPerHire,
            'addons' => $addons,
            'cta' => $cta,
            'currentPlan' => $currentPlan,
            'hasSubscription' => $hasSubscription,
            'credits' => $credits,
            'subscriptionStartedAt' => $subscriptionStartedAt ?? null,
            'subscriptionExpiresAt' => $subscriptionExpiresAt ?? null,
            'pendingPayment' => $pendingPayment ?? null,
            'isApproved' => $isApproved ?? true,
        ])
    </div>
    @if(config('hirevo_plans.checkout.mode', 'cheque') === 'cheque')
        @include('hirevo.employer.plans._checkout-modal')
        @include('hirevo.employer.plans._checkout-scripts')
    @endif
@endsection
