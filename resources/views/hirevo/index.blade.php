@extends('layouts.app')

@section('title', 'Home')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hirevo-home-v2.css') }}">
@endpush

@section('content')
<div class="hirevo-home-page">
    @php $siteImg = fn(string $f) => asset('images/webisteimages/' . rawurlencode($f)); @endphp

    {{-- ═══════════════ HERO ═══════════════ --}}
    <section class="hv2-hero" id="home">
        <div class="hv2-hero__bg"   aria-hidden="true"></div>
        <div class="hv2-hero__grid" aria-hidden="true"></div>
        <div class="hv2-hero__orb"  aria-hidden="true"></div>
        <div class="hv2-hero__orb2" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">

                {{-- Left copy --}}
                <div class="col-lg-6">
                    <div class="hv2-badge">
                        <span class="hv2-badge-dot" aria-hidden="true"></span>
                        Built for students &amp; freshers
                    </div>
                    <h1 class="hv2-display">
                        Get <span class="hv2-accent">clarity.</span><br>
                        Land the <span class="hv2-teal">right</span> role.
                    </h1>
                    <p class="hv2-lead">
                        Stop applying blindly. Hirevo shows where your profile stands,
                        <strong>what to improve</strong>, and <strong>real roles</strong> that fit 
                        so every application counts.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('resume.upload') }}" class="hv2-btn hv2-btn--primary hv2-btn-lg">
                            Analyse my resume free <i class="uil uil-arrow-right"></i>
                        </a>
                        <a href="{{ route('job-openings') }}" class="hv2-btn hv2-btn--ghost hv2-btn-lg">
                            Explore openings
                        </a>
                    </div>

                    <div class="hv2-search-wrap">
                        <form action="{{ route('job-list') }}" method="GET" class="mb-0">
                            <div class="hv2-search-bar">
                                <div class="hv2-search-field flex-grow-1">
                                    <i class="uil uil-briefcase-alt" aria-hidden="true"></i>
                                    <input type="search" name="q" class="form-control border-0 rounded-0 shadow-none"
                                           placeholder="Job goal e.g. Data Analyst, Developer…"
                                           id="job-title" autocomplete="off">
                                </div>
                                <div class="hv2-search-field" style="min-width:140px;">
                                    <i class="uil uil-map-marker d-none d-md-inline" aria-hidden="true"></i>
                                    <select class="form-select border-0 rounded-0 shadow-none flex-grow-1"
                                            name="location" id="choices-single-location" aria-label="Location">
                                        <option value="IN">India</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="AE">UAE</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-search d-flex align-items-center justify-content-center gap-1">
                                    <i class="uil uil-search"></i> Find job goals
                                </button>
                            </div>
                        </form>
                        <p class="small mt-2 mb-0" style="color:var(--hv2-faint);">
                            Unsure where to start? Upload your resume, we'll map skills and gaps in minutes.
                        </p>
                        <div class="hv2-intl-openings mt-3">
                            <span class="hv2-intl-openings__label">International job openings</span>
                            <div class="hv2-intl-openings__chips">
                                <a class="hv2-intl-chip" href="{{ route('job-openings', ['country' => 'ca']) }}"><span class="hv2-intl-chip__fi" aria-hidden="true">🇨🇦</span>Canada</a>
                                <a class="hv2-intl-chip" href="{{ route('job-openings', ['country' => 'us']) }}"><span class="hv2-intl-chip__fi" aria-hidden="true">🇺🇸</span>United States</a>
                                <a class="hv2-intl-chip" href="{{ route('job-openings', ['country' => 'gb']) }}"><span class="hv2-intl-chip__fi" aria-hidden="true">🇬🇧</span>United Kingdom</a>
                                <a class="hv2-intl-chip" href="{{ route('job-openings', ['country' => 'ae']) }}"><span class="hv2-intl-chip__fi" aria-hidden="true">🇦🇪</span>UAE</a>
                            </div>
                        </div>
                    </div>

                    <div class="hv2-trust-row">
                        <div class="d-flex align-items-center gap-3">
                            <div class="hv2-trust-avatars" aria-hidden="true">
                                <div class="hv2-trust-av" style="background:linear-gradient(135deg,#6C63FF,#9d97ff);color:#fff;">HV</div>
                                <div class="hv2-trust-av" style="background:linear-gradient(135deg,#00D4AA,#00F5C8);color:#0A0A14;">CV</div>
                                <div class="hv2-trust-av" style="background:linear-gradient(135deg,#FF6B6B,#ff9b9b);color:#fff;">JR</div>
                                <div class="hv2-trust-av" style="background:linear-gradient(135deg,#FFB347,#ffd080);color:#0A0A14;">SK</div>
                            </div>
                            <div class="hv2-trust-copy"><strong>Resume first</strong> clarity for early talent</div>
                        </div>
                    </div>
                </div>

                {{-- Right visual card --}}
                <div class="col-lg-6">
                    <div class="hv2-visual d-none d-lg-block">
                        <div class="position-relative">
                            <div class="hv2-card-main">
                                <div class="hv2-ring-row">
                                    <div class="hv2-ring" id="heroRing">
                                        <span id="heroRingNum">0%</span>
                                    </div>
                                    <div class="hv2-ring-info">
                                        <p>Your skill match snapshot</p>
                                        <span>After resume analysis · job goals aligned</span>
                                        <div class="hv2-pill-tag">Strong profile direction</div>
                                    </div>
                                </div>
                                <div class="hv2-mini-label">Skill signals (example)</div>
                                <div class="hv2-bar-row">
                                    <span class="hv2-bar-name">Core skills</span>
                                    <div class="hv2-bar-track">
                                        <div class="hv2-bar-fill hv2-anim-bar" style="background:var(--hv2-teal);" data-w="88"></div>
                                    </div>
                                    <span class="hv2-bar-pct">88%</span>
                                </div>
                                <div class="hv2-bar-row">
                                    <span class="hv2-bar-name">Role fit</span>
                                    <div class="hv2-bar-track">
                                        <div class="hv2-bar-fill hv2-anim-bar" style="background:var(--hv2-violet);" data-w="72"></div>
                                    </div>
                                    <span class="hv2-bar-pct">72%</span>
                                </div>
                                <div class="hv2-bar-row">
                                    <span class="hv2-bar-name">Gaps to close</span>
                                    <div class="hv2-bar-track">
                                        <div class="hv2-bar-fill hv2-anim-bar" style="background:var(--hv2-amber);" data-w="34"></div>
                                    </div>
                                    <span class="hv2-bar-pct" style="color:var(--hv2-amber);">↑</span>
                                </div>
                            </div>
                            <div class="hv2-float hv2-float-tr">
                                <span style="font-size:1.15rem">📈</span>
                                <div>
                                    <strong style="color:var(--hv2-text);font-size:0.78rem;">Match score updated</strong><br>
                                    <span style="color:var(--hv2-faint);font-size:0.68rem;">After resume refresh</span>
                                </div>
                            </div>
                            <div class="hv2-float hv2-float-bl hv2-float--2">
                                <span style="font-size:1.15rem">🎯</span>
                                <div>
                                    <strong style="color:var(--hv2-text);font-size:0.78rem;">New job goals found</strong><br>
                                    <span style="color:var(--hv2-faint);font-size:0.68rem;">Based on your skills</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-lg-none text-center mt-3">
                        <img src="{{ asset($theme.'/assets/images/process-02.png') }}" alt="" class="img-fluid opacity-75" style="max-height:220px;" loading="lazy" width="400" height="300">
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════ SOCIAL-PROOF TICKER ═══════════════ --}}
    <div class="hv2-ticker">
        <div class="hv2-ticker-track">
            {{-- Items duplicated for seamless loop --}}
            @foreach([
                ['✅','Resume scored in 2 min'],['🎯','Skills mapped to real roles'],['📈','ATS score explained'],
                ['🚀','Job goals matched instantly'],['🔍','Gaps identified clearly'],['💼','Live openings explored'],
                ['✅','Resume scored in 2 min'],['🎯','Skills mapped to real roles'],['📈','ATS score explained'],
                ['🚀','Job goals matched instantly'],['🔍','Gaps identified clearly'],['💼','Live openings explored'],
            ] as [$icon, $text])
                <div class="hv2-ticker-item"><span>{{ $icon }}</span>{{ $text }}</div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════ RESUME CTA ═══════════════ --}}
    @php
        $resumeDreamJobHireMin = 65;
        $resumeDreamJobHireMax = 75;
        $resumeDreamJobHirePct = random_int($resumeDreamJobHireMin, $resumeDreamJobHireMax);
    @endphp
    <section class="hv2-section hv2-section--tight">
        <div class="container">
            <div class="hv2-reveal">
                <a href="{{ route('resume.upload') }}" class="hv2-resume-card">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="d-flex flex-column flex-md-row align-items-md-start gap-4">
                                <div class="hv2-resume-icon mx-auto mx-md-0 flex-shrink-0">
                                    <i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
                                </div>
                                <div class="text-center text-md-start flex-grow-1 min-w-0 position-relative" style="z-index:1;">
                                    <h2 class="h4 fw-bold mb-2" style="color:var(--hv2-text);">Start with your resume</h2>
                                    <p class="hv2-resume-hire-boost mb-2 mb-md-3">
                                        <span class="hv2-resume-hire-boost__accent">Upload your resume</span>
                                        and increase your chances of getting hired in your dream job by up to
                                        <strong class="hv2-resume-hire-boost__pct">+{{ $resumeDreamJobHirePct }}%</strong>
                                        <span class="d-none d-sm-inline">— based on clearer targeting vs. blind applications.</span>
                                    </p>
                                    <!-- <p class="mb-0 mb-md-3" style="color:var(--hv2-muted);max-width:36rem;">
                                        Know if your profile is working <em>before</em> you apply more.
                                        Upload once — see strengths, gaps, and direction.
                                        <strong style="color:var(--hv2-text);">Under two minutes. No account needed.</strong>
                                    </p> -->
                                    <span class="hv2-btn hv2-btn--teal hv2-btn-lg mt-3 d-inline-flex align-items-center" style="pointer-events:none;">
                                        <i class="mdi mdi-upload me-1" aria-hidden="true"></i> Upload resume free
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center">
                            <img src="{{ $siteImg('Image 3.PNG') }}" alt="" class="img-fluid" style="max-height:200px;" loading="lazy" width="400" height="260">
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════════ HOW IT WORKS ═══════════════ --}}
    <section class="hv2-section hv2-section--surface">
        <div class="container">
            <div class="text-center mb-5 hv2-reveal">
                <div class="hv2-eyebrow hv2-eyebrow--pill hv2-eyebrow--violet">How it works</div>
                <h2 class="hv2-display mt-3" style="font-size:clamp(1.75rem,3vw,2.55rem);">
                    Three steps to <span class="hv2-teal">career ready.</span>
                </h2>
                <p class="hv2-lead mx-auto text-center mb-0" style="max-width:40rem;">
                    Clarity first. Then direction. Then smarter applications no guesswork.
                </p>
            </div>
            <div class="hv2-steps">
                <div class="hv2-step hv2-reveal hv2-reveal-d1">
                    <span class="hv2-step-num" aria-hidden="true">01</span>
                    <div class="hv2-step-icon hv2-step-icon--v">🔬</div>
                    <h3>Know your real profile</h3>
                    <p>Upload your resume we map skills, surface gaps, and benchmark you against realistic role expectations.</p>
                </div>
                <div class="hv2-step hv2-reveal hv2-reveal-d2">
                    <span class="hv2-step-num" aria-hidden="true">02</span>
                    <div class="hv2-step-icon hv2-step-icon--t">🎯</div>
                    <h3>See what fits</h3>
                    <p>Explore job goals and openings with context: what each role needs and how your profile compares right now.</p>
                </div>
                <div class="hv2-step hv2-reveal hv2-reveal-d3">
                    <span class="hv2-step-num" aria-hidden="true">03</span>
                    <div class="hv2-step-icon hv2-step-icon--a">🚀</div>
                    <h3>Apply smarter</h3>
                    <p>Focus on opportunities that match fewer cold applications, better use of your time and energy.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ PAIN + DIFFERENTIATION ═══════════════ --}}
    <section class="hv2-section">
        <div class="container">
            <div class="hv2-pain-grid">
                <div class="hv2-reveal hv2-reveal--left">
                    <div class="hv2-eyebrow" style="color:var(--hv2-coral);">The problem</div>
                    <h2 class="hv2-statement mb-4">You're not <em>unqualified.</em><br>You're under guided.</h2>
                    <p class="hv2-lead mb-4">
                        Most platforms push volume.
                        <strong>Hirevo pushes clarity</strong>  so you know what to fix, what to learn, and where to aim.
                    </p>
                    <a href="{{ route('resume.upload') }}" class="hv2-btn hv2-btn--primary hv2-btn-lg">
                        Fix this for me <i class="uil uil-arrow-right"></i>
                    </a>
                </div>
                <div class="hv2-pain-list hv2-reveal hv2-reveal--right">
                    <div class="hv2-pain-item">
                        <div class="hv2-pain-ico" aria-hidden="true">📭</div>
                        <div>
                            <strong>Too many applications, too little signal</strong>
                            <span>Applying everywhere without match data burns time and confidence.</span>
                        </div>
                    </div>
                    <div class="hv2-pain-item">
                        <div class="hv2-pain-ico" aria-hidden="true">🗺️</div>
                        <div>
                            <strong>"Which role is actually for me?"</strong>
                            <span>Job titles hide skill reality  we map roles to what you bring.</span>
                        </div>
                    </div>
                    <div class="hv2-pain-item">
                        <div class="hv2-pain-ico" aria-hidden="true">🔍</div>
                        <div>
                            <strong>Rejected without knowing why</strong>
                            <span>ATS and skill mismatches filter you out early  we show what to address.</span>
                        </div>
                    </div>
                    <div class="hv2-pain-item">
                        <div class="hv2-pain-ico" aria-hidden="true">✨</div>
                        <div>
                            <strong>Not just another job board</strong>
                            <span>We focus on preparation and fit  not endless scrolling listings.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-5 pt-lg-4">
                <div class="col-lg-6 hv2-reveal hv2-reveal-d1">
                    <div class="hv2-card h-100">
                        <h2 class="h4 mb-3">Not just another job platform</h2>
                        <p class="mb-0" style="color:var(--hv2-muted);">
                            Instead of pushing you to apply more, we help you understand
                            <strong style="color:var(--hv2-text);">what works for your profile</strong> and where to focus.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 hv2-reveal hv2-reveal-d2">
                    <div class="hv2-card h-100">
                        <h2 class="h4 mb-3">What you actually get</h2>
                        <ul class="hv2-list mb-0">
                            <li>Better visibility for your profile</li>
                            <li>Clear direction on what to improve</li>
                            <li>Access to relevant opportunities</li>
                            <li>Support in becoming job-ready</li>
                        </ul>
                        <p class="small mt-3 mb-0" style="color:var(--hv2-faint);">Focus on quality, not quantity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ CAREER PATHS / JOB GOALS ═══════════════ --}}
    @php
        $goalPalette = ['violet', 'teal', 'amber', 'coral', 'indigo', 'emerald', 'sky', 'fuchsia'];
        $goalJobsRandMin = 1001;
        $goalJobsRandMax = 25000;
        $referralHirePctMin = 72;
        $referralHirePctMax = 88;
        $goalFallbackRoles = [
            ['label' => 'Software Engineer', 'icon' => 'uil-laptop'],
            ['label' => 'HR & Recruitment', 'icon' => 'uil-users-alt'],
            ['label' => 'Data Analyst', 'icon' => 'uil-chart-bar'],
            ['label' => 'Sales & Business Dev', 'icon' => 'uil-phone'],
            ['label' => 'Healthcare & Nursing', 'icon' => 'uil-heartbeat'],
            ['label' => 'Retail & Store Ops', 'icon' => 'uil-store'],
            ['label' => 'Teaching & Education', 'icon' => 'uil-graduation-cap'],
            ['label' => 'Finance & Accounting', 'icon' => 'uil-money-bill'],
            ['label' => 'Content & Marketing', 'icon' => 'uil-edit-alt'],
            ['label' => 'Customer Support', 'icon' => 'uil-headphones'],
            ['label' => 'Hospitality & Food Service', 'icon' => 'uil-utensils'],
            ['label' => 'Logistics & Warehousing', 'icon' => 'uil-truck'],
            ['label' => 'Real Estate & Property', 'icon' => 'uil-building'],
        ];
    @endphp
    <section class="hv2-goals" id="career-paths" aria-labelledby="hv2-goals-heading">
        <div class="hv2-goals__bg" aria-hidden="true"></div>
        <div class="hv2-goals__orb hv2-goals__orb--1" aria-hidden="true"></div>
        <div class="hv2-goals__orb hv2-goals__orb--2" aria-hidden="true"></div>
        <div class="container position-relative">
            <div class="row align-items-start g-4 g-lg-5 mb-4 mb-lg-5">
                <div class="col-lg-7 hv2-reveal">
                    <div class="hv2-goals__eyebrow">
                        <span class="hv2-goals__eyebrow-dot" aria-hidden="true"></span>
                        Career paths
                    </div>
                    <h2 class="hv2-goals__title" id="hv2-goals-heading">
                        Popular <span>job goals</span>
                    </h2>
                    <p class="hv2-goals__lead">
                        Pick a role—we surface <strong>what employers expect</strong>, map it to your profile, and highlight <strong>gaps you can close</strong> with purpose (not guesswork).
                    </p>
                    <ul class="hv2-goals__flow list-unstyled mb-0" role="list">
                        <li class="hv2-goals__flow-item">
                            <span class="hv2-goals__flow-ico" aria-hidden="true"><i class="uil uil-briefcase-alt"></i></span>
                            <span class="hv2-goals__flow-step">01</span>
                            <span class="hv2-goals__flow-label">Choose a goal</span>
                        </li>
                        <li class="hv2-goals__flow-join" aria-hidden="true"></li>
                        <li class="hv2-goals__flow-item">
                            <span class="hv2-goals__flow-ico" aria-hidden="true"><i class="uil uil-chart-line"></i></span>
                            <span class="hv2-goals__flow-step">02</span>
                            <span class="hv2-goals__flow-label">See skill map</span>
                        </li>
                        <li class="hv2-goals__flow-join" aria-hidden="true"></li>
                        <li class="hv2-goals__flow-item">
                            <span class="hv2-goals__flow-ico" aria-hidden="true"><i class="uil uil-bolt-alt"></i></span>
                            <span class="hv2-goals__flow-step">03</span>
                            <span class="hv2-goals__flow-label">Close the gaps</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-5 hv2-reveal hv2-reveal--right">
                    <div class="hv2-goals__panel">
                        <div class="hv2-goals__panel-row">
                            <span class="hv2-goals__panel-kicker">Why it matters</span>
                        </div>
                        <p class="hv2-goals__panel-copy mb-0">
                            Every card is a <strong>live skill blueprint</strong> not a generic job title. Tap in to compare against your resume and move with a plan.
                        </p>
                        <div class="hv2-goals__panel-tags">
                            <span>ATS-aware</span>
                            <span>Skill gaps</span>
                            <span>Role fit</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hv2-goals__grid hv2-reveal">
                @forelse(($jobRoles ?? []) as $role)
                    @php $pv = $goalPalette[$loop->index % count($goalPalette)]; @endphp
                    <div class="hv2-goal-card hv2-goal-card--{{ $pv }} {{ $loop->first ? 'hv2-goal-card--featured' : '' }}">
                        <span class="hv2-goal-card__glow" aria-hidden="true"></span>
                        <a href="{{ route('job-goal.show', $role) }}" class="hv2-goal-card__main">
                            <div class="hv2-goal-card__head">
                                <span class="hv2-goal-card__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="hv2-goal-card__icon" aria-hidden="true">
                                    <i class="uil uil-briefcase-alt"></i>
                                </div>
                            </div>
                            <h3 class="hv2-goal-card__name">{{ $role->title }}</h3>
                            <div class="hv2-goal-card__stats" role="status">
                                <span class="hv2-goal-card__stat-pulse" aria-hidden="true"></span>
                                <span class="hv2-goal-card__stat-num">{{ number_format(random_int($goalJobsRandMin, $goalJobsRandMax)) }}</span>
                                <span class="hv2-goal-card__stat-label">open roles</span>
                            </div>
                        </a>
                        <div class="hv2-goal-card__actions">
                            <a href="{{ route('referral.intent', ['source' => 'home_goal', 'job_role_id' => $role->id]) }}" class="hv2-goal-card__referral">
                                <span class="hv2-goal-card__referral-icon" aria-hidden="true"><i class="uil uil-gift"></i></span>
                                <span class="hv2-goal-card__referral-body">
                                    <span class="hv2-goal-card__referral-label">Get referral</span>
                                    <span class="hv2-goal-card__referral-stat">Up to <strong>+{{ random_int($referralHirePctMin, $referralHirePctMax) }}%</strong> better odds to get hired</span>
                                </span>
                            </a>
                            <a href="{{ route('job-goal.show', $role) }}" class="hv2-goal-card__cta">
                                <span>Open goal</span>
                                <i class="uil uil-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    @foreach($goalFallbackRoles as $goalRow)
                        @php $pv = $goalPalette[$loop->index % count($goalPalette)]; @endphp
                        <div class="hv2-goal-card hv2-goal-card--{{ $pv }} {{ $loop->first ? 'hv2-goal-card--featured' : '' }}">
                            <span class="hv2-goal-card__glow" aria-hidden="true"></span>
                            <a href="{{ route('job-list') }}" class="hv2-goal-card__main">
                                <div class="hv2-goal-card__head">
                                    <span class="hv2-goal-card__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="hv2-goal-card__icon" aria-hidden="true">
                                        <i class="uil {{ $goalRow['icon'] }}"></i>
                                    </div>
                                </div>
                                <h3 class="hv2-goal-card__name">{{ $goalRow['label'] }}</h3>
                                <div class="hv2-goal-card__stats" role="status">
                                    <span class="hv2-goal-card__stat-pulse" aria-hidden="true"></span>
                                    <span class="hv2-goal-card__stat-num">{{ number_format(random_int($goalJobsRandMin, $goalJobsRandMax)) }}</span>
                                    <span class="hv2-goal-card__stat-label">open roles</span>
                                </div>
                            </a>
                            <div class="hv2-goal-card__actions">
                                <a href="{{ route('referral.intent', ['source' => 'home_goal_demo']) }}" class="hv2-goal-card__referral">
                                    <span class="hv2-goal-card__referral-icon" aria-hidden="true"><i class="uil uil-gift"></i></span>
                                    <span class="hv2-goal-card__referral-body">
                                        <span class="hv2-goal-card__referral-label">Get referral</span>
                                        <span class="hv2-goal-card__referral-stat">Up to <strong>+{{ random_int($referralHirePctMin, $referralHirePctMax) }}%</strong> better odds to get hired</span>
                                    </span>
                                </a>
                                <a href="{{ route('job-list') }}" class="hv2-goal-card__cta">
                                    <span>Explore</span>
                                    <i class="uil uil-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>

            <div class="text-center mt-5 pt-lg-2 hv2-reveal">
                <a href="{{ route('job-list') }}" class="hv2-btn hv2-btn--primary hv2-btn-lg hv2-goals__cta-all">
                    <i class="uil uil-apps"></i>
                    View all job goals
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════════ LIVE OPENINGS STRIP ═══════════════ --}}
    <section class="hv2-section hv2-jobs-strip hv2-section--tight">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-2 hv2-reveal hv2-reveal--right">
                    <div class="hv2-eyebrow hv2-eyebrow--pill hv2-eyebrow--violet">Live openings</div>
                    <h2 class="hv2-display mt-3" style="font-size:clamp(1.6rem,2.8vw,2.25rem);">
                        Find work that <span class="hv2-accent">fits</span> your profile
                    </h2>
                    <p class="hv2-lead">
                        Employer posted roles you can explore and apply to  with the same clarity mindset, not spray and pray.
                    </p>
                    <a href="{{ route('job-openings') }}" class="hv2-btn hv2-btn--primary hv2-btn-lg">
                        <i class="uil uil-search"></i> Browse job openings
                    </a>
                </div>
                <div class="col-lg-6 order-lg-1 text-center hv2-reveal hv2-reveal--left">
                    <img src="{{ $siteImg('image3.jpeg') }}" alt="" class="img-fluid" style="max-height:260px;" loading="lazy" width="520" height="280">
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ STATS ═══════════════ --}}
    <div class="hv2-stats" id="hv2-stats-strip">
        <div class="hv2-stat-cell">
            <div class="hv2-stat-num" data-count="1000" data-suffix="+">1000+</div>
            <div class="hv2-stat-label">Candidates exploring matches</div>
        </div>
        <div class="hv2-stat-cell">
            <div class="hv2-stat-num">2 min</div>
            <div class="hv2-stat-label">Typical resume check</div>
        </div>
        <div class="hv2-stat-cell">
            <div class="hv2-stat-num">Goals</div>
            <div class="hv2-stat-label">Skills mapped per role</div>
        </div>
        <div class="hv2-stat-cell">
            <div class="hv2-stat-num">24/7</div>
            <div class="hv2-stat-label">Explore at your pace</div>
        </div>
    </div>

    {{-- ═══════════════ WHY HIREVO ═══════════════ --}}
    <section class="hv2-section">
        <div class="container">
            <div class="text-center mb-5 hv2-reveal">
                <div class="hv2-eyebrow" style="letter-spacing:0.08em;">Why Hirevo</div>
                <p class="hv2-lead mx-auto text-center mb-0" style="max-width:34rem;">
                    For learners, job seekers, and employers  one platform, clearer outcomes.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-lg-6 hv2-reveal hv2-reveal-d1">
                    <div class="hv2-card h-100 d-flex flex-column">
                        <img src="{{ $siteImg('Image1.jpeg') }}" alt="" class="hv2-card-img" loading="lazy" width="560" height="176">
                        <div class="small fw-bold text-uppercase mb-2" style="color:#a5a0ff;letter-spacing:0.08em;">Community &amp; focus</div>
                        <h3 class="h4 mb-2">Built for people serious about careers</h3>
                        <p style="color:var(--hv2-muted);" class="mb-3">
                            Students and freshers who want <strong style="color:var(--hv2-text);">direction</strong>, not endless blind applications.
                        </p>
                        <ul class="hv2-list mb-3">
                            <li><strong style="color:var(--hv2-text);">1000+</strong> candidates exploring roles and skill matches</li>
                            <li>Resume first: know gaps before you apply</li>
                            <li>Job &amp; internship signals you can act on</li>
                        </ul>
                        <p class="small mt-auto mb-0" style="color:var(--hv2-faint);">Growing community — relevant updates, not noise.</p>
                    </div>
                </div>
                <div class="col-lg-6 hv2-reveal hv2-reveal-d2">
                    <div class="hv2-card h-100 d-flex flex-column">
                        <img src="{{ $siteImg('image5.jpeg') }}" alt="" class="hv2-card-img" loading="lazy" width="560" height="176">
                        <div class="small fw-bold text-uppercase mb-2" style="color:var(--hv2-teal);letter-spacing:0.08em;">Career clarity</div>
                        <h3 class="h4 mb-2">Not sure what direction to take?</h3>
                        <p style="color:var(--hv2-muted);" class="mb-3">
                            Compare <strong style="color:var(--hv2-text);">roles, skills, and fit</strong> instead of guessing from titles alone.
                        </p>
                        <ul class="hv2-list mb-4">
                            <li>Browse job goals and see what each role expects</li>
                            <li>Spot skill gaps before you invest in the wrong path</li>
                            <li>Move from "I'm confused" to "here's my next step"</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('job-list') }}" class="hv2-btn hv2-btn--ghost">Explore career paths</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hv2-reveal hv2-reveal-d1">
                    <div class="hv2-card h-100 d-flex flex-column">
                        <img src="{{ $siteImg('image4.jpeg') }}" alt="" class="hv2-card-img hv2-card-img--photo w-100" loading="lazy" width="560" height="160">
                        <div class="small fw-bold text-uppercase mb-2" style="color:var(--hv2-amber);letter-spacing:0.08em;">Updates &amp; opportunities</div>
                        <h3 class="h4 mb-2">Stay connected</h3>
                        <p style="color:var(--hv2-muted);" class="mb-3">
                            <strong style="color:var(--hv2-text);">Curated openings and useful signals</strong>  not a flood of irrelevant listings.
                        </p>
                        <ul class="hv2-list mb-4">
                            <li>Roles and timely nudges in one place</li>
                            <li><strong style="color:var(--hv2-text);">No spam</strong>  only what helps you move forward</li>
                            <li>Join to personalise what you hear about</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('register') }}" class="hv2-btn hv2-btn--primary">Join now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hv2-reveal hv2-reveal-d2">
                    <div class="hv2-card h-100 d-flex flex-column">
                        <img src="{{ $siteImg('image7.jpeg') }}" alt="" class="hv2-card-img" loading="lazy" width="560" height="176">
                        <div class="small fw-bold text-uppercase mb-2" style="color:#c4bfff;letter-spacing:0.08em;">For employers</div>
                        <h3 class="h4 mb-2">Hiring made simpler</h3>
                        <p style="color:var(--hv2-muted);" class="mb-3">
                            Reach candidates who are <strong style="color:var(--hv2-text);">already preparing</strong> clearer profiles, fewer mismatches.
                        </p>
                        <ul class="hv2-list mb-4">
                            <li>List jobs with clear requirements and work modes</li>
                            <li>Review applications in one workflow</li>
                            <li>Optional external apply link for your ATS or site</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('employer.jobs.create') }}" class="hv2-btn hv2-btn--ghost">Post a job</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ TESTIMONIALS ═══════════════ --}}
    <section class="hv2-testi-strip">
        <div class="container">
            <div class="text-center mb-5 hv2-reveal">
                <div class="hv2-eyebrow hv2-eyebrow--pill hv2-eyebrow--violet">What people say</div>
                <h2 class="hv2-display mt-3" style="font-size:clamp(1.6rem,3vw,2.3rem);">
                    Real <span class="hv2-accent">results</span> from real candidates
                </h2>
            </div>
            <div class="hv2-testi-grid">
                <div class="hv2-testi-card hv2-reveal hv2-reveal-d1">
                    <div class="hv2-testi-stars">★★★★★</div>
                    <p class="hv2-testi-text">"Uploaded my resume and within two minutes I had a clear picture of what was missing. Applied to 3 roles that actually fit  got 2 interviews."</p>
                    <div class="hv2-testi-author">
                        <div class="hv2-testi-av" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;">PK</div>
                        <div>
                            <div class="hv2-testi-name">Priya K.</div>
                            <div class="hv2-testi-role">CS Graduate · Exploring Data roles</div>
                        </div>
                    </div>
                </div>
                <div class="hv2-testi-card hv2-reveal hv2-reveal-d2">
                    <div class="hv2-testi-stars">★★★★★</div>
                    <p class="hv2-testi-text">"I had no idea why I kept getting rejected. Hirevo showed exactly which skills were missing for Product Manager roles. Changed my approach completely."</p>
                    <div class="hv2-testi-author">
                        <div class="hv2-testi-av" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;">RS</div>
                        <div>
                            <div class="hv2-testi-name">Rahul S.</div>
                            <div class="hv2-testi-role">MBA Fresher · PM track</div>
                        </div>
                    </div>
                </div>
                <div class="hv2-testi-card hv2-reveal hv2-reveal-d3">
                    <div class="hv2-testi-stars">★★★★★</div>
                    <p class="hv2-testi-text">"Stop applying to 100 jobs and getting nowhere. Hirevo made me realise my profile was strong for 3 specific roles. That focus made all the difference."</p>
                    <div class="hv2-testi-author">
                        <div class="hv2-testi-av" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;">AM</div>
                        <div>
                            <div class="hv2-testi-name">Anjali M.</div>
                            <div class="hv2-testi-role">BCA Final Year · Frontend track</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════ FINAL CTA ═══════════════ --}}
    <section class="hv2-final-cta">
        <div class="container position-relative" style="z-index:1;">
            <div class="hv2-reveal">
                <div class="hv2-eyebrow text-center hv2-eyebrow--pill" style="background:rgba(16,185,129,0.1);border-color:rgba(16,185,129,0.25);color:#059669;">Start today — it's free</div>
                <h2 class="text-center mt-3">
                    Stop guessing.<br>
                    Start with <span class="hv2-glow">clarity.</span>
                </h2>
                <p class="hv2-lead text-center mx-auto mt-3 mb-4" style="max-width:34rem;">
                    Upload your resume  no account needed. Get your ATS score, skill gaps, and matched jobs in under 2 minutes.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                    <a href="{{ route('resume.upload') }}" class="hv2-btn hv2-btn--primary hv2-btn-lg" style="background:linear-gradient(135deg,#6366f1,#4f46e5);box-shadow:0 8px 28px rgba(99,102,241,0.38);">
                        <i class="uil uil-upload"></i> Analyse my resume free
                    </a>
                    <a href="{{ route('job-openings') }}" class="hv2-btn hv2-btn--ghost hv2-btn-lg">Browse openings</a>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <span class="d-flex align-items-center gap-1 small" style="color:var(--hv2-faint);"><i class="uil uil-check-circle text-success"></i> No account needed</span>
                    <span class="d-flex align-items-center gap-1 small" style="color:var(--hv2-faint);"><i class="uil uil-bolt-alt text-warning"></i> Under 2 minutes</span>
                    <span class="d-flex align-items-center gap-1 small" style="color:var(--hv2-faint);"><i class="uil uil-lock-alt" style="color:#6366f1;"></i> 100% free</span>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="{{ asset($theme.'/assets/js/pages/index.init.js') }}"></script>
<script>
/* ── Navbar scroll elevation ─────────────────────────── */
(function () {
    var nav = document.getElementById('navbar');
    if (!nav) return;
    var onScroll = function () {
        nav.classList.toggle('nav-scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

document.addEventListener('DOMContentLoaded', function () {

    /* ── Choices.js ──────────────────────────────── */
    if (typeof Choices !== 'undefined' && document.getElementById('choices-single-location')) {
        new Choices('#choices-single-location', { searchEnabled: false });
    }

    /* ── Scroll-reveal (IntersectionObserver) ────── */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('hv2-revealed');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.14 });
        document.querySelectorAll('.hv2-reveal').forEach(function (el) { io.observe(el); });
    } else {
        /* No observer support — reveal everything immediately */
        document.querySelectorAll('.hv2-reveal').forEach(function (el) { el.classList.add('hv2-revealed'); });
    }

    /* ── Hero ring & bars animate on load ───────── */
    function animateHeroCard() {
        /* Count-up ring */
        var ringNum = document.getElementById('heroRingNum');
        var target = 78; var current = 0;
        var step = function () {
            current = Math.min(current + 2, target);
            if (ringNum) ringNum.textContent = current + '%';
            if (current < target) requestAnimationFrame(step);
        };
        setTimeout(function () { requestAnimationFrame(step); }, 600);

        /* Animate bar fills */
        document.querySelectorAll('.hv2-anim-bar').forEach(function (bar) {
            var w = bar.getAttribute('data-w') || '0';
            setTimeout(function () { bar.style.width = w + '%'; }, 700);
        });
    }
    animateHeroCard();

});
</script>
@endpush
