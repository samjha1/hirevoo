@extends('layouts.candidate')

@section('title', 'Mock Interviews')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Mock Interviews</h1>
        <p class="cp-greeting-sub">Practice like Pramp & Big Interview — behavioral, technical, and HR rounds.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline"><i class="mdi mdi-arrow-left"></i><span>Dashboard</span></a>
@endsection

@section('content')
@php $mock = $mockInterviews ?? []; @endphp
<div class="cf-page">
    <div class="cf-readiness">
        <div class="cf-meta">Interview readiness · {{ $mock['role_title'] ?? 'Your role' }}</div>
        <strong>{{ (int) ($mock['readiness_score'] ?? 0) }}</strong><span class="cf-meta">/100</span>
        <div class="fw-600 mt-1">{{ $mock['readiness_label'] ?? '' }}</div>
    </div>

    <div class="cf-card">
        <h3 class="cf-card-title">Pre-interview checklist</h3>
        <ul class="cf-checklist">
            @foreach($mock['checklist'] ?? [] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>

    <div class="cf-tabs" role="tablist">
        @foreach($mock['sections'] ?? [] as $i => $section)
            <button type="button" class="cf-tab {{ $i === 0 ? 'is-active' : '' }}" data-tab="{{ $section['key'] }}">
                <i class="mdi {{ $section['icon'] }} me-1"></i>{{ $section['title'] }}
            </button>
        @endforeach
    </div>

    @foreach($mock['sections'] ?? [] as $i => $section)
        <div class="cf-panel {{ $i === 0 ? 'is-active' : '' }}" data-panel="{{ $section['key'] }}">
            @foreach($section['questions'] ?? [] as $q)
                <div class="cf-mock-q">
                    <h4>{{ $q['question'] }}</h4>
                    <p class="cf-tip"><i class="mdi mdi-lightbulb-outline"></i> {{ $q['tip'] }}</p>
                    <div class="cf-sample"><strong>Sample approach:</strong> {{ $q['sample'] }}</div>
                </div>
            @endforeach
        </div>
    @endforeach

    @if(!empty($skillGaps['gaps']))
        <div class="cf-card mt-3">
            <h3 class="cf-card-title">Close these gaps before your next interview</h3>
            <div class="cf-pills">
                @foreach(array_slice($skillGaps['gaps'], 0, 6) as $gap)
                    <span class="cf-pill cf-pill--miss">{{ $gap }}</span>
                @endforeach
            </div>
            <a href="{{ route('candidate.skill-gaps') }}" class="cf-btn cf-btn--outline mt-2">Skill gap analysis →</a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.cf-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.dataset.tab;
            document.querySelectorAll('.cf-tab').forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            document.querySelectorAll('.cf-panel').forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.panel === key);
            });
        });
    });
})();
</script>
@endpush
