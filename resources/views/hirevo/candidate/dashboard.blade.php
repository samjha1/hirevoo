@extends('layouts.candidate')

@section('title', 'Dashboard')

@section('body_class', 'candidate-dashboard-v2')

@php
    $user = auth()->user();
    $firstName = \Illuminate\Support\Str::before(trim($user->name), ' ') ?: 'there';
    $displayName = trim($user->name) !== '' ? \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim($user->name))) : 'Account';
    $profileCompletion = $profileCompletion ?? ['percent' => 0, 'filled' => 0, 'total' => 0];
    $profilePct = (int) ($profileCompletion['percent'] ?? 0);
    $hiringScore = (int) ($hiringScore ?? 0);
    $hiringScoreLabel = $hiringScoreLabel ?? 'Needs work';
    $scorePercentile = (int) ($scorePercentile ?? 50);
    $scoreBreakdown = $scoreBreakdown ?? [];
    $scoreTrend = $scoreTrend ?? [];
    $roadmapSteps = $roadmapSteps ?? [];
    $skillGapChart = $skillGapChart ?? [];
    $jobMatches = $jobMatches ?? [];
    $trackerCounts = $trackerCounts ?? ['applied' => 0, 'shortlisted' => 0, 'interview' => 0, 'offer' => 0];
    $insights = $insights ?? [];
    $nextStep = $nextStep ?? [
        'title' => 'Complete Your Profile',
        'description' => 'Build a stronger profile to unlock personalized insights.',
        'url' => route('profile'),
        'label' => 'Get Started',
    ];
    $scoreCirc = 2 * M_PI * 54;
    $scoreOffset = $scoreCirc - (($hiringScore / 100) * $scoreCirc);
    $hiringScoreDetails = $hiringScoreDetails ?? ['factors' => [], 'resume' => null, 'role_match' => null, 'avg_job_match' => null];
    $hsResume = $hiringScoreDetails['resume'] ?? null;
    $hsRoleMatch = $hiringScoreDetails['role_match'] ?? null;
    $hsFactors = $hiringScoreDetails['factors'] ?? [];
    $hsAvgJobMatch = $hiringScoreDetails['avg_job_match'] ?? null;
    $dashboardSkillMatched = $dashboardSkillMatched ?? [];
    $dashboardSkillGaps = $dashboardSkillGaps ?? [];
    $skillFocusRole = $skillFocusRole ?? null;
    $primaryResume = $primaryResume ?? null;
    $dashboardStats = $dashboardStats ?? [];
    $dashboardSkillMatchPct = $dashboardSkillMatchPct ?? null;
    $allApplications = $allApplications ?? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 8);
    $applicationStatusFilter = $applicationStatusFilter ?? 'all';
    $applicationStatusCounts = $applicationStatusCounts ?? ['all' => 0];
@endphp

@section('header_greeting')
    <div class="cp-greeting" id="cp-greeting">
        <h1 class="cp-greeting-title" data-default-title="Welcome back, {{ $firstName }}! 👋">Welcome back, {{ $firstName }}! 👋</h1>
        <p class="cp-greeting-sub" data-default-sub="Here's your career growth overview.">Here's your career growth overview.</p>
    </div>
@endsection

@section('header_actions')
    <button type="button" class="cp-btn cp-btn--outline" data-bs-toggle="modal" data-bs-target="#referralSignupModal">
        <i class="mdi mdi-gift-outline"></i>
        <span>Refer & Earn</span>
    </button>

    <div class="dropdown cp-notify-wrap">
        <button type="button" class="cp-icon-btn" data-bs-toggle="dropdown" aria-label="Notifications">
            <i class="mdi mdi-bell-outline"></i>
            @if(($navUnreadCount ?? 0) > 0)
                <span class="cp-notify-badge">{{ ($navUnreadCount ?? 0) > 9 ? '9+' : $navUnreadCount }}</span>
            @endif
        </button>
        <div class="dropdown-menu dropdown-menu-end cp-notify-menu shadow border-0">
            <div class="cp-notify-head"><strong>Notifications</strong></div>
            <div class="cp-notify-list">
                @forelse($navNotifications ?? [] as $note)
                    @php $payload = is_array($note->data) ? $note->data : []; @endphp
                    <form action="{{ route('notifications.read', $note->id) }}" method="post" class="mb-0">
                        @csrf
                        <button type="submit" class="cp-notify-item {{ $note->read_at ? 'is-read' : '' }}">
                            <strong>{{ $payload['title'] ?? 'Update' }}</strong>
                            <span>{{ \Illuminate\Support\Str::limit($payload['body'] ?? '', 120) }}</span>
                        </button>
                    </form>
                @empty
                    <div class="cp-notify-empty">No notifications yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <a href="{{ route('profile') }}" class="cp-user-chip">
        <span class="cp-user-avatar">{{ $user->initials() }}</span>
        <span class="cp-user-meta">
            <span class="cp-user-name">{{ $displayName }}</span>
            <span class="cp-user-progress-label">Profile: {{ $profilePct }}%</span>
            <span class="cp-user-progress-bar"><span style="width: {{ $profilePct }}%"></span></span>
        </span>
    </a>
@endsection

@section('content')
<div class="cd-dashboard" id="cd-dashboard">

    <div class="cd-page is-active" data-page="overview" id="dashboard-overview">
        @include('hirevo.candidate.partials.dashboard._overview')
    </div>

    <div class="cd-page" data-page="hiring-score" id="hiring-score">
        @include('hirevo.candidate.partials.dashboard._hiring-score-page')
    </div>

    <div class="cd-page" data-page="career-report" id="career-report">
        @include('hirevo.candidate.partials.dashboard._career-report-page')
    </div>

    <div class="cd-page" data-page="roadmap" id="roadmap">
        @include('hirevo.candidate.partials.dashboard._roadmap-page')
    </div>

    <div class="cd-page" data-page="applications" id="applications">
        @include('hirevo.candidate.partials.dashboard._applications-page')
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var trend = @json($scoreTrend);
    var charts = {};

    function buildChart(canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof Chart === 'undefined' || charts[canvasId]) return;
        var labels = trend.map(function (d) { return d.month; });
        var data = trend.map(function (d) { return d.score; });
        charts[canvasId] = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: function (ctx) { return ctx.dataIndex === data.length - 1 ? 5 : 0; }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { min: 0, max: 100, grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 25 } }
                }
            }
        });
    }

    var PAGE_HEADERS = {
        'overview': { title: null, sub: null },
        'hiring-score': { title: 'My Hiring Score', sub: 'Full breakdown from your resume, profile, and applications.' },
        'career-report': { title: 'Career Report', sub: 'Insights, profile strength, and application performance.' },
        'roadmap': { title: 'Career Roadmap', sub: 'Your step-by-step path from assessments to getting hired.' },
        'applications': { title: 'Applications Tracker', sub: 'All jobs you applied to with current status.' }
    };

    function pageFromHash() {
        var h = window.location.hash.replace('#', '');
        if (h === 'hiring-score' || h === 'career-report' || h === 'roadmap' || h === 'applications') return h;
        return 'overview';
    }

    function showPage(page) {
        document.querySelectorAll('.cd-page').forEach(function (el) {
            el.classList.toggle('is-active', el.dataset.page === page);
        });

        var titleEl = document.querySelector('.cp-greeting-title');
        var subEl = document.querySelector('.cp-greeting-sub');
        var meta = PAGE_HEADERS[page] || PAGE_HEADERS.overview;
        if (titleEl && subEl) {
            if (meta.title) {
                titleEl.textContent = meta.title;
                subEl.textContent = meta.sub;
            } else {
                titleEl.textContent = titleEl.dataset.defaultTitle || titleEl.textContent;
                subEl.textContent = subEl.dataset.defaultSub || subEl.textContent;
            }
        }

        document.querySelectorAll('.cp-nav-link').forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var hash = href.indexOf('#') >= 0 ? href.slice(href.indexOf('#')) : '';
            var isDash = href.indexOf('/dashboard') >= 0 && hash === '';
            var active = (page === 'overview' && isDash) || (hash === '#' + page);
            link.classList.toggle('is-active', active);
        });

        window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });

        if (page === 'overview') buildChart('cdScoreTrendChartOverview');
        if (page === 'hiring-score') buildChart('cdScoreTrendChartFull');
    }

    showPage(pageFromHash());
    window.addEventListener('hashchange', function () { showPage(pageFromHash()); });
})();
</script>
@endpush
