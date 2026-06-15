@extends('layouts.candidate')

@section('title', 'Skill Assessments')

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Skill Assessments</h1>
        <p class="cp-greeting-sub">Quick checks based on your resume skills and target role gaps.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('candidate.dashboard') }}" class="cp-btn cp-btn--outline"><i class="mdi mdi-arrow-left"></i><span>Dashboard</span></a>
@endsection

@section('content')
<div class="cf-page">
    @if(empty($resume))
        <div class="cf-alert cf-alert--warn">Upload your resume to get personalized assessments. <a href="{{ route('resume.upload') }}" class="fw-600">Upload</a></div>
    @elseif(empty($assessments))
        <div class="cf-card"><p class="mb-0 text-muted">Complete your profile and add skills to unlock assessments.</p></div>
    @else
        <p class="cf-intro">{{ count($assessments) }} assessment(s) tailored to your profile. Expand each, answer all questions, then check score.</p>
        @foreach($assessments as $index => $pack)
            <div class="cf-assess" id="assess-{{ $index }}">
                <div class="cf-assess-head" data-toggle-assess="{{ $index }}">
                    <div>
                        <strong>{{ $pack['title'] }}</strong>
                        <div class="cf-meta">{{ $pack['question_count'] }} questions · {{ $pack['skill'] }}</div>
                    </div>
                    <i class="mdi mdi-chevron-down"></i>
                </div>
                <div class="cf-assess-body">
                    <form class="cf-assess-form" data-pack="{{ $index }}">
                        @foreach($pack['questions'] as $qi => $q)
                            <div class="cf-q">
                                <label>{{ $qi + 1 }}. {{ $q['question'] }}</label>
                                @foreach($q['options'] as $oi => $opt)
                                    <label class="cf-opt">
                                        <input type="radio" name="q{{ $qi }}" value="{{ $oi }}" required>
                                        <span>{{ $opt }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                        <button type="submit" class="cf-btn cf-btn--primary">Check score</button>
                        <div class="cf-score-result" data-result="{{ $index }}"></div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
@php
    $assessmentsForJs = [];
    foreach ($assessments ?? [] as $i => $pack) {
        $assessmentsForJs[$i] = array_column($pack['questions'] ?? [], 'answer');
    }
@endphp
<script>
(function () {
    var answers = @json($assessmentsForJs);
    document.querySelectorAll('[data-toggle-assess]').forEach(function (el) {
        el.addEventListener('click', function () {
            var box = document.getElementById('assess-' + el.dataset.toggleAssess);
            if (box) box.classList.toggle('is-open');
        });
    });
    document.querySelectorAll('.cf-assess-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var pack = parseInt(form.dataset.pack, 10);
            var key = answers[pack] || [];
            var correct = 0;
            key.forEach(function (ans, qi) {
                var picked = form.querySelector('input[name="q' + qi + '"]:checked');
                if (picked && parseInt(picked.value, 10) === ans) correct++;
            });
            var pct = key.length ? Math.round((correct / key.length) * 100) : 0;
            var el = form.querySelector('[data-result="' + pack + '"]');
            if (el) {
                el.style.display = 'block';
                el.innerHTML = '<strong>' + pct + '%</strong> — ' + correct + '/' + key.length + ' correct.'
                    + (pct >= 70 ? ' Great — keep practicing mock interviews.' : ' Review this skill in Learning Hub and retry.');
            }
        });
    });
})();
</script>
@endpush
