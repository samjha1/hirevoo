@extends('layouts.app')

@section('title', $jobRole->title . ' — Skill match')

@push('styles')
<style>
@include('hirevo.partials.rr-layout-styles')
</style>
@endpush

@section('content')
    @php
        $smGoalTeasers = [
            'Strong overlap with your resume — worth opening while it’s fresh.',
            'High overlap — small skill tweaks unlock this path.',
            'You’re in the running — see the full picture and next steps.',
            'Another door from your skills — explore before you decide.',
            'Still viable — match grows as you close gaps.',
        ];
        $matchingFiltered = collect($matchingJobGoals ?? [])->filter(fn ($it) => $it['job_role']->id !== $jobRole->id)->values();
        $smLeftRows = [];
        $r = 0;
        foreach (($relatedJobs ?? collect()) as $job) {
            $r++;
            $smLeftRows[] = ['kind' => 'employer', 'rank' => $r, 'job' => $job];
        }
        foreach ($matchingFiltered as $item) {
            $r++;
            $smLeftRows[] = [
                'kind' => 'goal',
                'rank' => $r,
                'item' => $item,
                'teaser' => $smGoalTeasers[($r - 1) % count($smGoalTeasers)],
            ];
        }
        $smListCount = count($smLeftRows);
        $reqSkillCount = $requiredSkills->count();
        $smScore = (int) $matchPercentage;
        $smScoreClass = $smScore >= 70 ? 'score-high' : ($smScore >= 40 ? 'score-mid' : 'score-low');
        $smBand = $reqSkillCount === 0 ? '—' : ($smScore >= 70 ? 'Strong fit' : ($smScore >= 40 ? 'Building' : 'Early'));
        $smBandBg = $reqSkillCount === 0 ? 'secondary' : ($smScore >= 70 ? 'success' : ($smScore >= 40 ? 'warning' : 'danger'));
        $resumeSkills = is_array($userSkillsForUpskill ?? null) ? $userSkillsForUpskill : [];
    @endphp

    <section class="section pb-5 rr-page pt-3">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-list') }}" class="text-decoration-none">Job goals</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($jobRole->title, 48) }}</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="rr-hero mb-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div>
                        <p class="text-uppercase small fw-bold text-primary mb-1" style="letter-spacing: 0.08em;">Job goal</p>
                        <h1 class="h3 fw-bold text-dark mb-2 mb-md-0">{{ $jobRole->title }}</h1>
                        <p class="text-muted small mb-0">Openings &amp; related goals on the <strong>left</strong> (scroll the page). Your match, skills, and next steps stay compact on the <strong>right</strong> — same layout as resume results.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <a href="{{ route('job-list') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">All goals</a>
                        @auth
                            @if(auth()->user()->isCandidate() ?? false)
                                <a href="{{ route('resume.upload') }}" class="btn btn-primary btn-sm rounded-pill px-3"><i class="uil uil-file-upload me-1"></i> Resume</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <div class="row g-4 g-xl-5 align-items-start">
                <div class="col-lg-7 col-xl-7 rr-jobs-scroll-col order-1">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 px-1">
                        <span class="rr-jobs-only-kicker mb-0"><i class="uil uil-list-ol text-primary me-1"></i> Openings &amp; related goals</span>
                        @if($smListCount > 0)
                            <span class="badge bg-dark rounded-pill">{{ $smListCount }}</span>
                        @endif
                    </div>
                    <div class="rr-match-feed rr-match-feed--jobs-panel mb-0 border-0 shadow-sm">
                        @forelse($smLeftRows as $row)
                            @php
                                $rank = $row['rank'];
                                $delay = ($rank - 1) * 0.075;
                                $rankClass = $rank === 1 ? 'top-1' : ($rank === 2 ? 'top-2' : ($rank === 3 ? 'top-3' : ''));
                            @endphp
                            <div class="rr-match-row" style="animation-delay: {{ $delay }}s">
                                <div class="rr-match-rank {{ $rankClass }}">{{ $rank }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    @if($row['kind'] === 'employer')
                                        @php
                                            $job = $row['job'];
                                            $co = $job->user->referrerProfile?->company_name ?? $job->company_name ?? 'Company';
                                        @endphp
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-success bg-opacity-15 text-success rounded-pill small">Live opening</span>
                                        </div>
                                        <a href="{{ route('job-openings.apply', $job) }}" class="h6 mb-1 text-dark text-decoration-none d-block lh-sm">{{ $job->title }}</a>
                                        <p class="text-muted small mb-2 mb-md-1">{{ $co }}</p>
                                        <p class="rr-match-teaser mb-2">Opening aligned with this job goal — apply while it’s active.</p>
                                        @if($job->formatted_location)
                                            <p class="small text-muted mb-2 mb-md-1"><i class="uil uil-map-marker me-1"></i>{{ $job->formatted_location }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2">
                                            @if(in_array($job->id, $appliedEmployerJobIds ?? []))
                                                <span class="badge bg-success px-3 py-2 rounded-pill">Applied</span>
                                            @else
                                                <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm rounded-pill">{{ $job->apply_link ? 'Apply on site' : 'Apply' }}</a>
                                            @endif
                                            <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-sm rounded-pill"><i class="uil uil-user-plus me-1"></i> Referral</a>
                                        </div>
                                    @else
                                        @php
                                            $item = $row['item'];
                                            $role = $item['job_role'];
                                            $matchPct = (int) ($item['match_percentage'] ?? 0);
                                            $teaserLine = $row['teaser'] ?? $smGoalTeasers[0];
                                        @endphp
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small">Job goal</span>
                                        </div>
                                        <a href="{{ route('job-goal.show', $role) }}" class="h6 mb-1 text-dark text-decoration-none d-block lh-sm">{{ $role->title }}</a>
                                        <p class="rr-match-teaser mb-2">{{ $teaserLine }}</p>
                                        <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                                            <div class="match-bar" style="width: 140px;">
                                                <div class="match-bar-fill {{ $matchPct >= 70 ? 'bg-success' : ($matchPct >= 40 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ min(100, $matchPct) }}%;"></div>
                                            </div>
                                            <span class="small text-muted fw-700">{{ $matchPct }}% match</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('job-goal.show', $role) }}" class="btn btn-soft-primary btn-sm rounded-pill">View role</a>
                                            @if(in_array($role->id, $appliedJobIds ?? []))
                                                <span class="badge bg-success px-3 py-2 rounded-pill align-self-center">Applied</span>
                                                <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-user-plus me-1"></i> Referral</a>
                                            @else
                                                <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm rounded-pill">Apply</a>
                                                <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-sm rounded-pill"><i class="uil uil-user-plus me-1"></i> Referral</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-4 p-md-5 text-center bg-light bg-opacity-50">
                                <i class="uil uil-briefcase-alt text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted small mt-2 mb-3">No live openings or extra goals matched yet. Try <a href="{{ route('job-openings') }}?q={{ urlencode($jobRole->title) }}">browsing openings</a> or upload a resume to unlock more.</p>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <a href="{{ route('job-openings') }}?q={{ urlencode($jobRole->title) }}" class="btn btn-primary btn-sm rounded-pill">Explore openings</a>
                                    <a href="{{ route('job-list') }}" class="btn btn-outline-primary btn-sm rounded-pill">Job goals</a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="col-lg-5 col-xl-5 order-2">
                    <div class="rr-rail-sticky rr-rail-compact">
                        <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                            <div class="card-body p-3">
                                <h2 class="h6 fw-bold mb-1">This goal</h2>
                                @if($jobRole->description)
                                    <p class="text-muted mb-3" style="font-size: 0.8rem; line-height: 1.5;">{{ Str::limit(strip_tags($jobRole->description), 220) }}</p>
                                @endif
                                <div class="d-flex flex-wrap gap-2">
                                    @auth
                                        @if($hasApplied ?? false)
                                            <span class="badge bg-success rounded-pill px-3 py-2 align-self-center">Applied</span>
                                            <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-user-plus me-1"></i> Referral</a>
                                        @else
                                            <a href="{{ route('job-goal.apply', $jobRole) }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-import me-1"></i> Apply to goal</a>
                                        @endif
                                    @else
                                        <a href="{{ route('login', ['redirect' => route('job-goal.apply', $jobRole)]) }}" class="btn btn-primary btn-sm rounded-pill"><i class="uil uil-import me-1"></i> Apply to goal</a>
                                    @endauth
                                </div>
                            </div>
                        </div>

                        @auth
                            @if(($hasProfile ?? false) && $reqSkillCount > 0)
                                <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center g-3">
                                            <div class="col-auto text-center">
                                                <div class="rr-score-ring rr-score-ring--rail {{ $smScoreClass }}">{{ $smScore }}<span class="pct">%</span></div>
                                                <span class="badge bg-{{ $smBandBg }} mt-1 px-2 py-1 rounded-pill small">{{ $smBand }}</span>
                                            </div>
                                            <div class="col min-w-0">
                                                <h2 class="h6 fw-bold mb-1">Profile match</h2>
                                                <div class="match-bar mb-2" style="max-width: 100%;"><div class="match-bar-fill bg-{{ $smBandBg }}" style="width: {{ min(100, $smScore) }}%;"></div></div>
                                                <p class="mb-0 text-muted" style="font-size: 0.78rem; line-height: 1.45;">Based on your profile skills vs this role’s required skills.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif(auth()->user()->isCandidate())
                                <div class="card border-0 shadow-sm rounded-4 mb-3">
                                    <div class="card-body p-3">
                                        <h2 class="h6 fw-bold mb-2">Your match</h2>
                                        <p class="text-muted small mb-2">Complete your profile or upload a resume to see how you stack up for <strong>{{ $jobRole->title }}</strong>.</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('profile') }}" class="btn btn-soft-primary btn-sm rounded-pill">Profile</a>
                                            <a href="{{ route('resume.upload') }}" class="btn btn-outline-primary btn-sm rounded-pill">Resume</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="card border-0 shadow-sm rounded-4 mb-3">
                                <div class="card-body p-3">
                                    <h2 class="h6 fw-bold mb-2">Save your progress</h2>
                                    <p class="text-muted small mb-3">Sign in to track match, apply, and get resume-powered recommendations.</p>
                                    <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary btn-sm rounded-pill me-1">Sign in</a>
                                    <a href="{{ route('register', ['role' => 'candidate']) }}" class="btn btn-outline-secondary btn-sm rounded-pill">Sign up</a>
                                </div>
                            </div>
                        @endauth

                        <div class="rr-funnel-rail d-flex flex-column gap-3">
                            <div class="rr-funnel-hero rounded-4 px-3 py-3 mb-0">
                                <p class="small fw-bold text-primary text-uppercase mb-1" style="letter-spacing: 0.12em;">At a glance</p>
                                <h2 class="h6 fw-bold text-dark mb-0">Skills → consult → referrals. The list on the left stays scrollable.</h2>
                            </div>

                            <div class="rr-funnel-step rr-funnel-step--skills">
                                <span class="rr-funnel-badge" aria-hidden="true">1</span>
                                <div class="rr-funnel-step-body rr-skills-sequenced card border-0 rounded-4 shadow-sm overflow-hidden">
                                    <div class="rr-skills-sequenced-accent"></div>
                                    <div class="card-body p-3">
                                        <h3 class="small fw-bold text-dark mb-1 d-flex align-items-center gap-2 flex-wrap">
                                            <span class="rr-funnel-icon rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:1.85rem;height:1.85rem;"><i class="uil uil-layer-group"></i></span>
                                            Skills for this role
                                        </h3>
                                        <p class="text-muted mb-2" style="font-size: 0.78rem;">What you already cover vs what’s left to learn for <strong>{{ Str::limit($jobRole->title, 36) }}</strong>.</p>

                                        @if($primaryResume && count($resumeSkills) > 0)
                                            <p class="rr-skill-section-label mb-1">From your resume</p>
                                            <div class="d-flex flex-wrap gap-1 mb-3">
                                                @foreach(array_slice($resumeSkills, 0, 12) as $sk)
                                                    <span class="rr-skill-chip" style="font-size: 0.7rem; padding: 0.28rem 0.6rem;">{{ $sk }}</span>
                                                @endforeach
                                                @if(count($resumeSkills) > 12)
                                                    <span class="rr-skill-chip rr-skill-chip--more" style="font-size: 0.7rem;">+{{ count($resumeSkills) - 12 }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if(($hasProfile ?? false) && $reqSkillCount > 0)
                                            @if(count($matchedSkills) > 0)
                                                <p class="rr-skill-section-label mb-1">You have</p>
                                                <div class="d-flex flex-wrap gap-1 mb-2">
                                                    @foreach(array_slice($matchedSkills, 0, 10) as $skill)
                                                        <span class="rr-pill-focus" style="font-size: 0.68rem;">{{ ucfirst($skill) }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(count($missingSkills) > 0)
                                                <p class="rr-skill-section-label mb-1">To strengthen</p>
                                                <div class="d-flex flex-wrap gap-1 mb-0">
                                                    @foreach(array_slice($missingSkills, 0, 10) as $skill)
                                                        <span class="rr-pill-gap" style="font-size: 0.68rem;">{{ ucfirst($skill) }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @elseif($requiredSkills->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($requiredSkills->take(14) as $sk)
                                                    <span class="rr-skill-chip" style="font-size: 0.7rem; padding: 0.28rem 0.6rem;">{{ $sk->skill_name }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="rounded-3 p-2 bg-light border border-dashed mb-0">
                                                <p class="text-muted mb-0" style="font-size: 0.78rem;">No required skills listed for this goal yet.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="rr-funnel-step rr-funnel-step--consult">
                                <span class="rr-funnel-badge" aria-hidden="true">2</span>
                                <div class="rr-funnel-step-body card rr-consult-card border-0 mb-0">
                                    <div class="card-body p-3">
                                        <h3 class="small fw-bold text-dark mb-1"><i class="uil uil-comment-dots text-primary me-1"></i>Get consulted</h3>
                                        <p class="text-muted mb-2" style="font-size: 0.78rem;">Decode gaps, prioritize this goal vs openings — quick session.</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('contact') . '?' . http_build_query(['subject' => 'Career consultation — '.$jobRole->title]) }}" class="btn btn-primary btn-sm rounded-pill px-3">Request</a>
                                            <a href="{{ route('help') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Help</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rr-funnel-step rr-funnel-step--shortlist">
                                <span class="rr-funnel-badge" aria-hidden="true">3</span>
                                <div class="rr-funnel-step-body">
                                    <div class="rr-match-feed rr-match-feed--shortlist-teaser mb-0 rounded-4 overflow-hidden shadow-sm border-0">
                                        <div class="rr-match-feed-head">
                                            <div class="rr-suspense-inner">
                                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                                    <div class="flex-grow-1 min-w-0">
                                                        <p class="rr-suspense-eyebrow mb-0"><span class="rr-suspense-dot" aria-hidden="true"></span> Your list</p>
                                                        <h3 class="rr-suspense-title">Openings &amp; goals</h3>
                                                        <p class="rr-suspense-sub mb-0">
                                                            <span class="rr-suspense-drama">Live list on the left</span> — employer jobs first, then resume-matched goals when you’re logged in with a CV.
                                                        </p>
                                                        <p class="rr-suspense-pointer small mb-0 mt-1"><i class="uil uil-angle-double-left text-success me-1"></i><span class="text-white text-opacity-90">Scroll the page for every row.</span></p>
                                                    </div>
                                                    <div class="flex-shrink-0 text-center">
                                                        @if($smListCount > 0)
                                                            <div class="rr-unlock-vault" role="status">
                                                                <span class="rr-unlock-label">Items</span>
                                                                <span class="rr-unlock-num">{{ $smListCount }}</span>
                                                            </div>
                                                        @else
                                                            <div class="rr-unlock-vault rr-unlock-vault--empty">
                                                                <span class="rr-unlock-label">—</span>
                                                                <span class="rr-unlock-num" style="font-size: 0.9rem;">0</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="rr-suspense-foot">
                                                    <a href="{{ route('job-openings') }}?q={{ urlencode($jobRole->title) }}">Openings</a>
                                                    <span class="sep">·</span>
                                                    <a href="{{ route('job-list') }}">Goals</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rr-funnel-step rr-funnel-step--refer">
                                <span class="rr-funnel-badge" aria-hidden="true">4</span>
                                <div class="rr-funnel-step-body">
                                    <div class="card rr-referral-card mb-0">
                                        <div class="card-body p-3">
                                            <h3 class="small fw-bold text-white mb-1">Referral</h3>
                                            <p class="small text-white text-opacity-90 mb-2" style="font-size: 0.78rem;">Premium referrals from verified employees.</p>
                                            <a href="{{ route('pricing') }}" class="btn btn-light btn-sm rounded-pill w-100">Premium</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rr-funnel-step rr-funnel-step--upskill">
                                <span class="rr-funnel-badge" aria-hidden="true">5</span>
                                <div class="rr-funnel-step-body">
                                    <div class="rr-upskill-shell mb-0">
                                        <div class="rr-upskill-head">
                                            <p class="small fw-bold text-primary text-uppercase mb-0" style="letter-spacing: 0.08em;">Upskill</p>
                                            <h3 class="h6 fw-bold text-dark mb-0"><i class="uil uil-chart-line text-primary me-1"></i>Featured track</h3>
                                        </div>
                                        <div class="rr-upskill-body pt-2 pb-3">
                                            @forelse(collect($upskillOpportunities ?? [])->take(1) as $opp)
                                                <p class="small fw-semibold text-dark mb-1">{{ $opp->title }}</p>
                                                <p class="text-muted small mb-2">{{ Str::limit($opp->description ?? '', 90) }}</p>
                                                <form action="{{ route('leads.upskill-contact') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="upskill_opportunity_id" value="{{ $opp->id }}">
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-600">I’m interested</button>
                                                </form>
                                            @empty
                                                <p class="text-muted small mb-0"><a href="{{ route('contact') }}" class="text-decoration-none">Contact</a> for guidance.</p>
                                            @endforelse
                                            <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-2 fw-600">Premium &amp; more tracks</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 mt-3">
                            @auth
                                @if(auth()->user()->isCandidate())
                                    <a href="{{ route('resume.upload') }}" class="btn btn-outline-primary btn-sm rounded-pill"><i class="uil uil-file-upload me-1"></i> New upload</a>
                                    <a href="{{ route('candidate.dashboard') }}" class="btn btn-soft-primary btn-sm rounded-pill">My applications</a>
                                @endif
                            @else
                                <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-outline-primary btn-sm rounded-pill">Sign in</a>
                            @endauth
                            <a href="{{ route('job-list') }}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="uil uil-arrow-left me-1"></i> Back to job goals</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
