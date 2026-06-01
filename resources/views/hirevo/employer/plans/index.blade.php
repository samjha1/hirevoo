@extends('layouts.employer')

@section('title', 'Plans & Pricing')
@section('header_title', 'Plans & Pricing')

@section('header_actions')
    <span class="btn btn-outline-secondary btn-sm pe-none">
        <i class="mdi mdi-coin-outline me-1"></i> Job posting credits: {{ $credits }}
    </span>
@endsection

@section('content')
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
    ])
@endsection
