@extends('layouts.candidate')

@section('title', 'Salary Insights')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Salary Insights</h1>
        <p class="cp-greeting-sub">Market bands for your role, experience, and location.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline"><i class="mdi mdi-arrow-left"></i><span>Dashboard</span></a>
@endsection

@section('content')
@php $sal = $salary ?? []; @endphp
<div class="cf-page">
    <div class="cf-card">
        <h2 class="cf-card-title">{{ $sal['role_title'] ?? 'Your role' }}</h2>
        <p class="cf-meta mb-0">{{ $sal['city'] ?? 'India' }} · {{ $sal['experience_years'] ?? 0 }} yrs experience · {{ ucfirst($sal['experience_tier'] ?? 'mid') }} band</p>
    </div>

    <div class="cf-card">
        <h3 class="cf-card-title">Market range (annual CTC)</h3>
        <div class="cf-grid-2 mb-3">
            <div class="cf-stat"><strong>₹{{ $sal['market_min_lpa'] ?? 0 }}L</strong><span class="cf-meta">Low</span></div>
            <div class="cf-stat"><strong>₹{{ $sal['market_mid_lpa'] ?? 0 }}L</strong><span class="cf-meta">Median</span></div>
            <div class="cf-stat"><strong>₹{{ $sal['market_max_lpa'] ?? 0 }}L</strong><span class="cf-meta">High</span></div>
            <div class="cf-stat">
                <strong>{{ $sal['expected_lpa'] !== null ? '₹'.$sal['expected_lpa'].'L' : '—' }}</strong>
                <span class="cf-meta">Your expected</span>
            </div>
        </div>
        <p class="mb-0 small">{{ $sal['comparison_label'] ?? '' }}</p>
        @if(empty($sal['expected_lpa']))
            <a href="{{ route('profile') }}" class="cf-btn cf-btn--outline mt-2">Add expected salary on profile</a>
        @endif
    </div>

    @if(!empty($sal['premium_skills']))
        <div class="cf-card">
            <h3 class="cf-card-title">Skills that often command premium pay</h3>
            <div class="cf-pills">
                @foreach($sal['premium_skills'] as $skill)
                    <span class="cf-pill">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if(!empty($sal['skills_to_increase_pay']))
        <div class="cf-card">
            <h3 class="cf-card-title">Learn these to move toward the upper band</h3>
            <div class="cf-pills mb-2">
                @foreach($sal['skills_to_increase_pay'] as $skill)
                    <span class="cf-pill cf-pill--miss">{{ $skill }}</span>
                @endforeach
            </div>
            <a href="{{ route('candidate.skill-gaps') }}" class="cf-btn cf-btn--outline">Skill gap analysis →</a>
        </div>
    @endif

    <div class="cf-card">
        <h3 class="cf-card-title">Negotiation tips</h3>
        <ul class="cf-tips-list mb-0">
            @foreach($sal['tips'] ?? [] as $tip)
                <li class="mb-1">{{ $tip }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
