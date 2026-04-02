@extends('layouts.app')

@section('title', 'Home')

@push('styles')
<style>
    .hero-badge { font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase; font-weight: 600; }
    .hirevo-hero-title { font-size: clamp(1.75rem, 5vw, 2.75rem); }
    @media (min-width: 768px) { .hirevo-hero-title { font-size: clamp(2rem, 4vw, 3.25rem); } }
    .hero-search-card { border-radius: 16px; box-shadow: 0 8px 32px rgba(11, 31, 59, 0.12); overflow: visible; }
    .hero-search-card .form-control, .hero-search-card .form-select { border: none; padding: 0.85rem 1rem; font-size: 1rem; }
    .hero-search-card .form-control:focus { box-shadow: none; }
    /* Ensure country dropdown (Choices.js) is visible and on top */
    .hero-search-card .choices { position: relative; z-index: 30; }
    .hero-search-card .choices__list--dropdown { z-index: 50; min-width: 180px; width: auto !important; }
    /* Fix dropdown options: show full text in one line, no letter stacking */
    .hero-search-card .choices__list--dropdown .choices__item--choice,
    .hero-search-card .choices__list--dropdown .choices__item { display: block !important; white-space: nowrap !important; word-break: normal !important; word-spacing: normal; letter-spacing: normal; }
    .hero-search-card .choices__inner { min-width: 0; }
    /* Prevent flex from shrinking dropdown so letters don't stack */
    .hero-search-card .choices__list .choices__item { width: 100%; box-sizing: border-box; }
    .hero-search-card .btn-search { padding: 0.85rem 1.5rem; font-weight: 600; border-radius: 0 12px 12px 0; background: var(--hirevo-primary); border-color: var(--hirevo-primary); box-shadow: 0 2px 8px rgba(11, 31, 59, 0.25); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hero-search-card .btn-search:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(11, 31, 59, 0.35); }
    .resume-hero-card { border-radius: 20px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(11, 31, 59, 0.04) 100%); border: 1px solid rgba(16, 185, 129, 0.25); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .resume-hero-card:hover { transform: translateY(-2px); box-shadow: 0 12px 40px rgba(16, 185, 129, 0.15); }
    .resume-hero-card .resume-cta-icon { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; background: linear-gradient(135deg, #10B981, #059669); color: #fff; }
    .trending-role-card { border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); transition: all 0.2s ease; }
    .trending-role-card:hover { border-color: var(--hirevo-primary); box-shadow: 0 8px 24px rgba(11, 31, 59, 0.08); }
    .why-hirevo-card { border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); transition: all 0.2s ease; }
    .why-hirevo-card:hover { border-color: rgba(11, 31, 59, 0.15); box-shadow: 0 8px 28px rgba(11, 31, 59, 0.1); }
    .hirevo-value-card__eyebrow { font-size: 0.6875rem; letter-spacing: 0.06em; text-transform: uppercase; font-weight: 600; color: #64748b; margin-bottom: 0.5rem; }
    .hirevo-value-card__media { border-radius: 12px; background: linear-gradient(180deg, rgba(11, 31, 59, 0.04) 0%, rgba(255,255,255,0) 100%); padding: 0.75rem; }
    .hirevo-value-card__img { max-height: 176px; width: 100%; object-fit: contain; object-position: center bottom; display: block; margin: 0 auto; }
    .hirevo-value-card__img--photo { object-fit: cover; object-position: center; border-radius: 10px; max-height: 160px; }
    .hirevo-value-list { margin: 0; padding-left: 1.1rem; color: #64748b; font-size: 0.9rem; }
    .hirevo-value-list li { margin-bottom: 0.35rem; }
    .hirevo-value-list li:last-child { margin-bottom: 0; }
</style>
@endpush

@section('content')
    @php
        $siteImg = fn (string $file) => asset('images/webisteimages/' . rawurlencode($file));
    @endphp
    <!-- HERO - Apna-style -->
    <section class="hirevo-hero position-relative" id="home">
        <div class="container position-relative">
            <div class="row align-items-center min-vh-50 py-5">
                <div class="col-lg-7">
                    <span class="hero-badge text-primary mb-3 d-inline-block">Built for students & freshers</span>
                    <h1 class="hirevo-hero-title fw-bold mb-3 lh-tight">Own Your Next <span class="text-primary">Career Move</span></h1>
                    <p class="lead text-muted mb-4" style="max-width: 560px;">
                        Finding the right job shouldn’t feel confusing or random.
                        We help you understand where you stand, what to improve, and where real opportunities exist.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => route('resume.upload')]) }}" class="btn btn-primary rounded-pill px-4">
                            Get Started
                        </a>
                        <a href="{{ route('job-openings') }}" class="btn btn-outline-primary rounded-pill px-4">
                            Explore Opportunities
                        </a>
                    </div>
                    <form action="{{ route('job-list') }}" method="GET" class="mb-0">
                        <div class="hero-search-card bg-white d-flex flex-column flex-md-row align-stretch rounded-3 border">
                            <div class="flex-grow-1 d-flex align-items-center border-end">
                                <i class="uil uil-briefcase-alt text-muted ms-3 fs-20"></i>
                                <input type="search" name="q" class="form-control border-0 rounded-0" placeholder="Job goal e.g. Data Analyst, Developer..." id="job-title">
                            </div>
                            <div class="d-flex align-items-center" style="min-width: 140px;">
                                <i class="uil uil-map-marker text-muted ms-3 fs-20 d-none d-md-inline"></i>
                                <select class="form-select border-0 rounded-0 flex-grow-1" name="location" id="choices-single-location" aria-label="Location">
                                    <option value="IN">India</option>
                                    <option value="US">United States</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="AE">UAE</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-search flex-shrink-0"><i class="uil uil-search me-1"></i> Find Jobs</button>
                        </div>
                    </form>
                    <p class="small text-muted mt-2 mb-0">If you’re unsure where to start, begin with your resume.</p>
                </div>
                <div class="col-lg-5 text-center mt-5 mt-lg-0">
                    <img src="{{ asset($theme.'/assets/images/process-02.png') }}" alt="Career growth" class="img-fluid hirevo-hero-img" width="520" height="400" fetchpriority="high" decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- RESUME ANALYSIS -->
    <section class="py-4 py-lg-5">
        <div class="container">
            <a href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => route('resume.upload')]) }}" class="text-decoration-none text-dark d-block">
                <div class="resume-hero-card p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                                <div class="resume-cta-icon flex-shrink-0 mx-auto mx-md-0">
                                    <i class="uil uil-file-search-alt"></i>
                                </div>
                                <div class="flex-grow-1 text-center text-md-start">
                                    <h3 class="h4 fw-bold mb-2">Start with your resume</h3>
                                    <p class="text-muted mb-0">
                                        Before applying more, know if your profile is actually working for you.
                                        Upload your resume and get a clear idea of where you stand. <span class="fw-semibold">Takes less than 2 minutes.</span>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 text-center text-md-start">
                                @auth
                                    <span class="btn resume-btn btn-lg rounded-pill px-4">
                                        <i class="uil uil-file-upload me-1"></i> Upload Resume
                                    </span>
                                @else
                                    <span class="btn resume-btn btn-lg rounded-pill px-4">
                                        <i class="uil uil-file-upload me-1"></i> Upload Your Resume
                                    </span>
                                @endauth
                            </div>
                        </div>
                        <div class="col-lg-5 text-center">
                            <img src="{{ $siteImg('Image 3.PNG') }}" alt="Skill insights and job matches tailored to your profile" class="img-fluid hirevo-site-illustration mx-auto" style="max-height: 240px;" loading="lazy" width="400" height="260">
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="section pt-2 pb-0">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <h2 class="h3 fw-bold mb-2">How Hirevoo works</h2>
                    <p class="text-muted mb-0">Clarity first. Then direction. Then smarter applications.</p>
                </div>
            </div>
            <div class="row justify-content-center mb-4">
                <div class="col-lg-10 text-center">
                    <img src="{{ $siteImg('Image 5.PNG') }}" alt="From your profile to the right role: search, match, and succeed" class="img-fluid hirevo-site-illustration hirevo-how-it-works-img" loading="lazy" width="920" height="320">
                </div>
            </div>
            <div class="row g-3 g-lg-4">
                <div class="col-md-4">
                    <div class="why-hirevo-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <strong>1</strong>
                            </div>
                            <h3 class="h6 fw-700 mb-0">Understand your profile</h3>
                        </div>
                        <p class="text-muted mb-0">Upload your resume and see where you stand.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-hirevo-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <strong>2</strong>
                            </div>
                            <h3 class="h6 fw-700 mb-0">Identify what’s missing</h3>
                        </div>
                        <p class="text-muted mb-0">Get clarity on skills, roles, and direction.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-hirevo-card p-4 h-100">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                <strong>3</strong>
                            </div>
                            <h3 class="h6 fw-700 mb-0">Apply smarter</h3>
                        </div>
                        <p class="text-muted mb-0">Focus on opportunities that actually match you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DIFFERENTIATION + VALUE -->
    <section class="section pt-4">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100">
                        <h2 class="h4 fw-bold mb-2">Not just another job platform</h2>
                        <p class="text-muted mb-0">
                            Most platforms focus on quantity. <span class="fw-semibold">We focus on clarity.</span>
                            Instead of pushing you to apply more, we help you understand what actually works for your profile and where you should focus.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100">
                        <h2 class="h4 fw-bold mb-3">What you actually get</h2>
                        <ul class="text-muted mb-0 ps-3">
                            <li>Better visibility for your profile</li>
                            <li>Clear direction on what to improve</li>
                            <li>Access to relevant opportunities</li>
                            <li>Support in becoming job-ready</li>
                        </ul>
                        <p class="small text-muted mt-3 mb-0">Focus on quality, not quantity.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SHAPE -->
    <div class="position-relative">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="1440" height="150" preserveaspectratio="none" viewbox="0 0 1440 220">
                <path d="M 0,213 C 288,186.4 1152,106.6 1440,80L1440 250L0 250z" fill="rgba(255, 255, 255, 1)"></path>
            </svg>
        </div>
    </div>

    <!-- POPULAR JOB GOALS - Apna trending style -->
    <!-- <section class="section pt-0">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 text-center">
                    <h2 class="h3 fw-bold mb-2">Popular job goals</h2>
                    <p class="text-muted mb-0">Pick a role — we'll show your skill match and gaps to fill.</p>
                </div>
            </div>
            <div class="row g-3 g-lg-4">
                @forelse(($jobRoles ?? []) as $role)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('job-goal.show', $role) }}" class="text-decoration-none text-dark">
                        <div class="trending-role-card p-4 h-100 text-center">
                            <div class="hirevo-role-icon rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="uim uim-bag fs-20"></i>
                            </div>
                            <h6 class="fw-600 mb-1">{{ $role->title }}</h6>
                            <span class="small text-muted">View skills →</span>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('job-list') }}" class="text-decoration-none text-dark">
                        <div class="trending-role-card p-4 h-100 text-center">
                            <div class="hirevo-role-icon rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="uim uim-layers-alt fs-20"></i>
                            </div>
                            <h6 class="fw-600 mb-1">Data Analyst</h6>
                            <span class="small text-muted">View skills →</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('job-list') }}" class="text-decoration-none text-dark">
                        <div class="trending-role-card p-4 h-100 text-center">
                            <div class="hirevo-role-icon rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="uim uim-airplay fs-20"></i>
                            </div>
                            <h6 class="fw-600 mb-1">Software Engineer</h6>
                            <span class="small text-muted">View skills →</span>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ route('job-list') }}" class="text-decoration-none text-dark">
                        <div class="trending-role-card p-4 h-100 text-center">
                            <div class="hirevo-role-icon rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                <i class="uim uim-bag fs-20"></i>
                            </div>
                            <h6 class="fw-600 mb-1">Product Manager</h6>
                            <span class="small text-muted">View skills →</span>
                        </div>
                    </a>
                </div>
                @endforelse
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('job-list') }}" class="btn btn-outline-primary rounded-pill px-4">View all job goals <i class="uil uil-arrow-right ms-1"></i></a>
            </div>
        </div>
    </section> -->
    <style>
.job-goals-section {
    background: #fff;
    position: relative;
}

.job-goals-section .section-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #6366f1;
    background: #eef2ff;
    padding: 4px 12px;
    border-radius: 100px;
    margin-bottom: 10px;
}

.job-goals-section .section-title {
    font-size: 1.6rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #0f172a;
    line-height: 1.2;
}

.job-goals-section .section-title span {
    color: #6366f1;
}

.job-goals-section .section-subtitle {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 6px;
}

/* Card */
.trending-role-card {
    background: #fff;
    border: 1.5px solid #e8eaf0;
    border-radius: 16px;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.trending-role-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    opacity: 0;
    transition: opacity 0.25s ease;
    z-index: 0;
}

.trending-role-card:hover {
    border-color: transparent;
    transform: translateY(-4px);
    box-shadow: 0 12px 32px -4px rgba(99, 102, 241, 0.25);
}

.trending-role-card:hover::before {
    opacity: 1;
}

.trending-role-card > * {
    position: relative;
    z-index: 1;
}

/* Icon */
.hirevo-role-icon {
    width: 56px !important;
    height: 56px !important;
    min-width: 56px;
    min-height: 56px;
    background: #eef2ff !important;
    border-radius: 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: background 0.25s;
    flex-shrink: 0;
    margin-left: auto;
    margin-right: auto;
}

.hirevo-role-icon svg {
    width: 26px !important;
    height: 26px !important;
    min-width: 26px;
    min-height: 26px;
    color: #6366f1;
    stroke: #6366f1;
    transition: color 0.25s, stroke 0.25s;
    display: block;
}

.trending-role-card:hover .hirevo-role-icon {
    background: rgba(255,255,255,0.22) !important;
}

.trending-role-card:hover .hirevo-role-icon svg {
    color: #fff !important;
    stroke: #fff !important;
}

/* Text */
.trending-role-card h6 {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.875rem;
    letter-spacing: -0.01em;
    transition: color 0.25s;
}

.trending-role-card:hover h6 {
    color: #fff;
}

.trending-role-card .view-skills-text {
    font-size: 0.75rem;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.25s;
}

.trending-role-card:hover .view-skills-text {
    color: rgba(255,255,255,0.8);
}

.trending-role-card .arrow-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #f1f5f9;
    border-radius: 50%;
    transition: background 0.25s, transform 0.25s;
}

.trending-role-card:hover .arrow-badge {
    background: rgba(255,255,255,0.25);
    transform: translateX(2px);
}

/* View all btn */
.btn-view-all {
    background: #0f172a;
    color: #fff;
    border: none;
    border-radius: 100px;
    padding: 10px 24px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, transform 0.2s;
    text-decoration: none;
}

.btn-view-all:hover {
    background: #6366f1;
    color: #fff;
    transform: translateY(-2px);
}

/* stagger animation */
@keyframes cardFadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

.role-col { animation: cardFadeUp 0.4s ease both; }
.role-col:nth-child(1) { animation-delay: 0.05s; }
.role-col:nth-child(2) { animation-delay: 0.10s; }
.role-col:nth-child(3) { animation-delay: 0.15s; }
.role-col:nth-child(4) { animation-delay: 0.20s; }
.role-col:nth-child(5) { animation-delay: 0.25s; }
.role-col:nth-child(6) { animation-delay: 0.30s; }
.role-col:nth-child(7) { animation-delay: 0.35s; }
.role-col:nth-child(8) { animation-delay: 0.40s; }
</style>

<section class="section pt-0 job-goals-section">
    <div class="container">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-8 text-center">
                <div class="section-eyebrow">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                    Career Paths
                </div>
                <h2 class="section-title">Popular <span>job goals</span></h2>
                <p class="section-subtitle">Pick a role — we'll show your skill match and gaps to fill.</p>
            </div>
        </div>

        <div class="row g-3 g-lg-4">
            @forelse(($jobRoles ?? []) as $role)
            <div class="col-6 col-md-4 col-lg-3 role-col">
                <a href="{{ route('job-goal.show', $role) }}" class="text-decoration-none">
                    <div class="trending-role-card p-4 h-100 text-center">
                        <div class="hirevo-role-icon mb-3">
                            <svg width="26" height="26" fill="none" stroke="#6366f1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            </svg>
                        </div>
                        <h6 class="fw-600 mb-1">{{ $role->title }}</h6>
                        <span class="view-skills-text">
                            View skills
                            <span class="arrow-badge">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-6 col-md-4 col-lg-3 role-col">
                <a href="{{ route('job-list') }}" class="text-decoration-none">
                    <div class="trending-role-card p-4 h-100 text-center">
                        <div class="hirevo-role-icon mb-3">
                            <svg width="26" height="26" fill="none" stroke="#6366f1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                            </svg>
                        </div>
                        <h6 class="fw-600 mb-1">Data Analyst</h6>
                        <span class="view-skills-text">
                            View skills
                            <span class="arrow-badge">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-3 role-col">
                <a href="{{ route('job-list') }}" class="text-decoration-none">
                    <div class="trending-role-card p-4 h-100 text-center">
                        <div class="hirevo-role-icon mb-3">
                            <svg width="26" height="26" fill="none" stroke="#6366f1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                            </svg>
                        </div>
                        <h6 class="fw-600 mb-1">Software Engineer</h6>
                        <span class="view-skills-text">
                            View skills
                            <span class="arrow-badge">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-3 role-col">
                <a href="{{ route('job-list') }}" class="text-decoration-none">
                    <div class="trending-role-card p-4 h-100 text-center">
                        <div class="hirevo-role-icon mb-3">
                            <svg width="26" height="26" fill="none" stroke="#6366f1" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                            </svg>
                        </div>
                        <h6 class="fw-600 mb-1">Product Manager</h6>
                        <span class="view-skills-text">
                            View skills
                            <span class="arrow-badge">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </div>
                </a>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('job-list') }}" class="btn-view-all">
                View all job goals
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

    <!-- JOB SEARCH -->
    <section class="section bg-light py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6 order-lg-2">
                    <h2 class="h3 fw-bold mb-2">Find opportunities that actually match you</h2>
                    <p class="text-muted mb-4">
                        Instead of applying everywhere, focus on roles that make sense for your profile.
                        We help you discover jobs that are relevant, practical, and worth your time.
                    </p>
                    <a href="{{ route('job-openings') }}" class="btn btn-primary btn-lg rounded-pill px-4 hirevo-cta-btn">
                        <i class="uil uil-search me-1"></i> Search Jobs
                    </a>
                </div>
                <div class="col-lg-6 order-lg-1 text-center">
                    <img src="{{ $siteImg('Image 2.PNG') }}" alt="Search jobs and explore roles that fit you" class="img-fluid hirevo-site-illustration" style="max-height: 280px;" loading="lazy" width="520" height="280">
                </div>
            </div>
        </div>
    </section>

    <!-- CAREER PATH GUIDANCE + COMMUNITY + HIRING -->
    <section class="section">
        <div class="container">
            <div class="row justify-content-center mb-2">
                <div class="col-lg-8 text-center">
                    <h2 class="h5 fw-bold text-uppercase text-muted mb-1" style="letter-spacing: 0.06em;">Why Hirevoo</h2>
                    <p class="text-muted mb-0 small">Four ways we help — whether you’re starting out, choosing a path, staying in the loop, or hiring.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100 d-flex flex-column">
                        <div class="hirevo-value-card__media mb-3">
                            <img src="{{ $siteImg('Image 1.PNG') }}" alt="Resume review, job search, networking, and growth" class="hirevo-value-card__img" loading="lazy" width="560" height="176">
                        </div>
                        <div class="hirevo-value-card__eyebrow">Community &amp; focus</div>
                        <h3 class="h4 fw-bold mb-2">Built for people who are serious about their careers</h3>
                        <p class="text-muted mb-3">
                            Hirevoo is for students, freshers, and job seekers who want <span class="fw-semibold text-dark">clarity and direction</span> — not endless blind applications.
                        </p>
                        <ul class="hirevo-value-list mb-3">
                            <li><strong class="text-dark">1000+</strong> candidates exploring roles and skill matches</li>
                            <li>Resume-first mindset: know your gaps before you apply</li>
                            <li>Regular job &amp; internship signals you can act on</li>
                        </ul>
                        <p class="small text-muted mb-0 mt-auto">Growing community — relevant updates, not noise.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100 d-flex flex-column">
                        <div class="hirevo-value-card__media mb-3">
                            <img src="{{ $siteImg('Image 4.PNG') }}" alt="Plan your next step with a clear career focus" class="hirevo-value-card__img" loading="lazy" width="560" height="176">
                        </div>
                        <div class="hirevo-value-card__eyebrow">Career clarity</div>
                        <h3 class="h4 fw-bold mb-2">Not sure what direction to take?</h3>
                        <p class="text-muted mb-3">
                            Choosing a path is easier when you can <span class="fw-semibold text-dark">compare roles, skills, and fit</span> instead of guessing from job titles alone.
                        </p>
                        <ul class="hirevo-value-list mb-4">
                            <li>Browse job goals and see what each role expects</li>
                            <li>Spot skill gaps before you invest weeks in the wrong role</li>
                            <li>Move from “I’m confused” to “here’s my next step”</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('job-list') }}" class="btn btn-outline-primary rounded-pill px-4">Explore Career Paths</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100 d-flex flex-column">
                        <div class="hirevo-value-card__media mb-3 p-0 overflow-hidden">
                            <img src="{{ $siteImg('headway-5QgIuuBxKwM-unsplash.jpg') }}" alt="Stay connected with opportunities and conversations that matter" class="hirevo-value-card__img hirevo-value-card__img--photo w-100" loading="lazy" width="560" height="160">
                        </div>
                        <div class="hirevo-value-card__eyebrow">Updates &amp; opportunities</div>
                        <h3 class="h4 fw-bold mb-2">Stay connected with opportunities</h3>
                        <p class="text-muted mb-3">
                            Get <span class="fw-semibold text-dark">curated openings and useful career signals</span> — not a flood of irrelevant listings.
                        </p>
                        <ul class="hirevo-value-list mb-4">
                            <li>Jobs, internships, and timely nudges in one place</li>
                            <li><span class="fw-semibold text-dark">No spam</span> — only what helps you move forward</li>
                            <li>Join to personalize what you hear about</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4">Join Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="why-hirevo-card p-4 p-lg-5 h-100 d-flex flex-column">
                        <div class="hirevo-value-card__media mb-3">
                            <img src="{{ $siteImg('Image 6.PNG') }}" alt="Source talent, review candidates, and hire with confidence" class="hirevo-value-card__img" loading="lazy" width="560" height="176">
                        </div>
                        <div class="hirevo-value-card__eyebrow">For employers</div>
                        <h3 class="h4 fw-bold mb-2">Hiring made simple</h3>
                        <p class="text-muted mb-3">
                            Post roles and reach candidates who are <span class="fw-semibold text-dark">already preparing</span> — clearer profiles, fewer mismatched applicants.
                        </p>
                        <ul class="hirevo-value-list mb-4">
                            <li>List jobs with clear requirements and work modes</li>
                            <li>Review applications in one workflow</li>
                            <li>Optional external apply link for your own ATS or site</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('employer.jobs.create') }}" class="btn btn-outline-primary rounded-pill px-4">Post a Job</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <h2 class="h3 fw-bold mb-2">Start taking your career seriously</h2>
                <p class="text-muted mb-4" style="max-width: 760px; margin: 0 auto;">
                    You don’t need to apply everywhere. You need to apply better.
                    Hirevoo helps you do exactly that.
                </p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ auth()->check() ? route('resume.upload') : route('login', ['redirect' => route('resume.upload')]) }}" class="btn btn-primary rounded-pill px-4">Get Started</a>
                    <a href="{{ route('job-openings') }}" class="btn btn-outline-primary rounded-pill px-4">Explore Opportunities</a>
                </div>
                <p class="text-muted small mt-3 mb-0">Careers don’t grow with random applications. They grow with clarity, preparation, and the right opportunities.</p>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script src="{{ asset($theme.'/assets/js/pages/index.init.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Choices !== 'undefined' && document.getElementById('choices-single-location')) {
        new Choices('#choices-single-location', { searchEnabled: false });
    }
});
</script>
@endpush
