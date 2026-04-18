@extends('layouts.app')

@section('body_class', 'page-about')

@section('title', 'About')

@push('styles')
<style>
    .about-page {
        --ab-ink: #0f172a;
        --ab-muted: #64748b;
        --ab-line: rgba(15, 23, 42, 0.08);
        --ab-mint: #10b981;
        --ab-violet: #6366f1;
        min-height: 100vh;
        background: #f8fafc;
        position: relative;
    }
    .about-page .container { position: relative; z-index: 1; }

    .about-hero-band {
        position: relative;
        overflow: hidden;
        border-radius: 0 0 clamp(18px, 3vw, 28px) clamp(18px, 3vw, 28px);
        background:
            radial-gradient(ellipse 80% 120% at 90% 0%, rgba(99, 102, 241, 0.35), transparent 50%),
            radial-gradient(ellipse 70% 100% at -5% 100%, rgba(16, 185, 129, 0.22), transparent 48%),
            linear-gradient(125deg, #0a1628 0%, #0f2744 48%, #0d3d38 100%);
        color: #f1f5f9;
        padding: clamp(2rem, 5vw, 3.25rem) 0;
        margin-bottom: 2.5rem;
        box-shadow: 0 24px 60px rgba(10, 22, 40, 0.35);
    }
    .about-hero-inner { position: relative; z-index: 1; max-width: 920px; margin: 0 auto; padding: 0 12px; text-align: center; }
    .about-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.16);
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    .about-kicker-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--ab-mint);
        box-shadow: 0 0 14px rgba(16, 185, 129, 0.75);
        animation: abPulse 2.4s ease-in-out infinite;
    }
    @keyframes abPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.75; transform: scale(0.9); }
    }
    @media (prefers-reduced-motion: reduce) {
        .about-kicker-dot { animation: none; }
    }
    .about-title {
        font-size: clamp(1.65rem, 4vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.15;
        margin-bottom: 0.85rem;
        color: #fff;
        text-shadow: 0 2px 3px rgba(0, 0, 0, 0.25);
    }
    .about-title span {
        background: linear-gradient(105deg, #ecfdf5 0%, #5eead4 45%, #93c5fd 95%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        -webkit-text-fill-color: transparent;
    }
    .about-lead {
        font-size: 1rem;
        color: #cbd5e1;
        max-width: 38rem;
        margin: 0 auto 1.35rem;
        line-height: 1.55;
        font-weight: 500;
    }
    .about-chip-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
    }
    .about-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: #e2e8f0;
        transition: background 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
    }
    .about-chip:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(16, 185, 129, 0.45);
        transform: translateY(-2px);
    }
    .about-chip .mdi { font-size: 1rem; color: #6ee7b7; }

    .about-section { padding: 2.5rem 0; }
    .about-section-head {
        margin-bottom: 1.5rem;
    }
    .about-eyebrow {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--hirevo-secondary, #10b981);
        margin-bottom: 0.35rem;
    }
    .about-h2 {
        font-size: clamp(1.25rem, 2.2vw, 1.5rem);
        font-weight: 800;
        color: var(--ab-ink);
        letter-spacing: -0.02em;
        margin: 0 0 0.5rem;
    }
    .about-sub {
        color: var(--ab-muted);
        font-size: 0.95rem;
        max-width: 40rem;
        margin: 0;
        line-height: 1.55;
    }

    .about-pillar {
        height: 100%;
        background: #fff;
        border: 1px solid var(--ab-line);
        border-radius: 18px;
        padding: 1.5rem 1.35rem;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.35s ease, border-color 0.25s ease;
        position: relative;
        overflow: hidden;
    }
    .about-pillar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--ab-violet), var(--ab-mint));
        opacity: 0.85;
        transform: scaleX(0.35);
        transform-origin: left;
        transition: transform 0.4s ease;
    }
    .about-pillar:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.1);
        border-color: rgba(99, 102, 241, 0.18);
    }
    .about-pillar:hover::before { transform: scaleX(1); }
    .about-pillar-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        margin-bottom: 0.85rem;
        font-size: 1.35rem;
        background: linear-gradient(145deg, rgba(99, 102, 241, 0.12), rgba(16, 185, 129, 0.1));
        color: #4338ca;
    }
    .about-pillar-icon--mint { color: #047857; background: linear-gradient(145deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.06)); }
    .about-pillar-icon--navy { color: var(--hirevo-primary, #0b1f3b); background: rgba(11, 31, 59, 0.08); }
    .about-pillar h3 {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--ab-ink);
        margin: 0 0 0.5rem;
        letter-spacing: -0.02em;
    }
    .about-pillar p { font-size: 0.875rem; color: var(--ab-muted); margin: 0; line-height: 1.55; }

    .about-audience-card {
        height: 100%;
        background: #fff;
        border: 1px solid var(--ab-line);
        border-radius: 18px;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.3s ease, border-color 0.25s ease;
        border-top: 3px solid transparent;
    }
    .about-audience-card:nth-child(1) { border-top-color: #6366f1; }
    .about-audience-card:nth-child(2) { border-top-color: #10b981; }
    .about-audience-card:nth-child(3) { border-top-color: #f59e0b; }
    .about-audience-card:hover {
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        border-color: rgba(15, 23, 42, 0.1);
    }
    .about-audience-card h3 {
        font-size: 0.95rem;
        font-weight: 800;
        color: var(--ab-ink);
        margin: 0 0 1rem;
    }
    .about-audience-card ul {
        margin: 0;
        padding-left: 1.1rem;
        font-size: 0.875rem;
        color: var(--ab-muted);
        line-height: 1.65;
    }
    .about-audience-card li { margin-bottom: 0.35rem; }

    .about-quote-card {
        height: 100%;
        background: linear-gradient(145deg, #fff 0%, #f8fafc 100%);
        border: 1px solid var(--ab-line);
        border-radius: 18px;
        padding: 1.65rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .about-quote-card::after {
        content: '"';
        position: absolute;
        top: 0.5rem;
        right: 1rem;
        font-size: 4rem;
        line-height: 1;
        font-family: Georgia, serif;
        color: rgba(99, 102, 241, 0.12);
        pointer-events: none;
    }
    .about-quote-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
    }
    .about-quote-card h2 { font-size: 1.05rem; font-weight: 800; margin: 0 0 0.65rem; color: var(--ab-ink); }
    .about-quote-card p { font-size: 0.9rem; color: var(--ab-muted); margin: 0; line-height: 1.55; }

    .about-step-card {
        height: 100%;
        background: #fff;
        border: 1px solid var(--ab-line);
        border-radius: 16px;
        padding: 1.35rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }
    .about-step-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    }
    .about-step-num {
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(135deg, #6366f1, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        line-height: 1;
        margin-bottom: 0.65rem;
        letter-spacing: -0.03em;
    }
    .about-step-card h3 { font-size: 0.9rem; font-weight: 800; margin: 0 0 0.4rem; color: var(--ab-ink); }
    .about-step-card p { font-size: 0.82rem; color: var(--ab-muted); margin: 0; line-height: 1.5; }
    .about-step-line {
        border-left: 3px solid rgba(99, 102, 241, 0.35);
        padding-left: 0.75rem;
        margin-top: 0.15rem;
    }

    .about-stat {
        text-align: center;
        padding: 1.75rem 1rem;
        background: #fff;
        border: 1px solid var(--ab-line);
        border-radius: 18px;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .about-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.09);
    }
    .about-stat-val {
        font-size: clamp(1.75rem, 3vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        background: linear-gradient(120deg, #0b1f3b, #10b981);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 0.5rem;
        line-height: 1.1;
    }
    .about-stat p { font-size: 0.85rem; color: var(--ab-muted); margin: 0; line-height: 1.45; }

    .about-diff-card {
        height: 100%;
        background: #fff;
        border: 1px solid var(--ab-line);
        border-radius: 14px;
        padding: 1.25rem 1.2rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .about-diff-card:hover {
        border-color: rgba(16, 185, 129, 0.35);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .about-diff-card h3 { font-size: 0.88rem; font-weight: 800; margin: 0 0 0.45rem; color: var(--ab-ink); }
    .about-diff-card p { font-size: 0.82rem; color: var(--ab-muted); margin: 0; line-height: 1.5; }

    .about-trust-card {
        height: 100%;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(8px);
        border: 1px solid var(--ab-line);
        border-radius: 16px;
        padding: 1.35rem;
        transition: transform 0.25s ease;
    }
    .about-trust-card:hover { transform: translateY(-3px); }
    .about-trust-card h3 { font-size: 0.88rem; font-weight: 800; margin: 0 0 0.45rem; color: var(--ab-ink); }
    .about-trust-card p { font-size: 0.82rem; color: var(--ab-muted); margin: 0; line-height: 1.55; }

    .about-page .accordion-item {
        border: 1px solid var(--ab-line);
        border-radius: 14px !important;
        margin-bottom: 0.65rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
    }
    .about-page .accordion-button {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--ab-ink);
        padding: 1rem 1.15rem;
        box-shadow: none !important;
    }
    .about-page .accordion-button:not(.collapsed) {
        background: linear-gradient(90deg, rgba(99, 102, 241, 0.06), rgba(16, 185, 129, 0.05));
        color: var(--hirevo-primary, #0b1f3b);
    }
    .about-page .accordion-body { font-size: 0.875rem; line-height: 1.6; padding: 0 1.15rem 1.1rem; }

    .about-cta {
        border-radius: 22px;
        padding: 2.25rem 1.5rem;
        text-align: center;
        background:
            radial-gradient(ellipse 70% 80% at 80% 20%, rgba(99, 102, 241, 0.2), transparent 55%),
            linear-gradient(135deg, #0b1f3b 0%, #132a45 55%, #0d2844 100%);
        color: #e2e8f0;
        box-shadow: 0 24px 56px rgba(11, 31, 59, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.08);
        margin-top: 1rem;
        margin-bottom: 2.5rem;
    }
    .about-cta h2 {
        font-size: clamp(1.2rem, 2.5vw, 1.45rem);
        font-weight: 800;
        color: #fff;
        margin-bottom: 0.5rem;
        letter-spacing: -0.02em;
    }
    .about-cta > p { color: #94a3b8; font-size: 0.92rem; max-width: 32rem; margin: 0 auto 1.25rem; line-height: 1.5; }
    .about-cta .btn-primary {
        background: var(--ab-mint);
        border: none;
        font-weight: 700;
        padding: 0.55rem 1.35rem;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .about-cta .btn-primary:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.45);
    }
    .about-cta .btn-outline-light {
        border-width: 1.5px;
        font-weight: 700;
        color: #f1f5f9;
        transition: background 0.2s ease, transform 0.2s ease;
    }
    .about-cta .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateY(-2px);
    }
    .about-cta small { font-size: 0.72rem; color: #94a3b8; max-width: 36rem; margin: 1rem auto 0; line-height: 1.45; }

    .about-animate {
        animation: abFadeUp 0.65s cubic-bezier(0.22, 1, 0.36, 1) backwards;
    }
    @keyframes abFadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @media (prefers-reduced-motion: reduce) {
        .about-animate { animation: none; opacity: 1; transform: none; }
        .about-pillar:hover, .about-stat:hover, .about-quote-card:hover, .about-step-card:hover { transform: none; }
    }
    .about-stagger-1 { animation-delay: 0.06s; }
    .about-stagger-2 { animation-delay: 0.12s; }
    .about-stagger-3 { animation-delay: 0.18s; }
</style>
@endpush

@section('content')
    <div class="about-page">
        <section class="about-hero-band">
            <div class="about-hero-inner">
                <div class="about-kicker">
                    <span class="about-kicker-dot" aria-hidden="true"></span>
                    Hirevo
                </div>
                <h1 class="about-title">Career acceleration: <span>built for real outcomes</span></h1>
                <p class="about-lead">
                    We built a platform that improves how you get hired: AI match scores, employee referrals, and career insights that actually move the needle.
                </p>
                <div class="about-chip-row">
                    <span class="about-chip"><i class="mdi mdi-web me-1" aria-hidden="true"></i> hirevoo.in</span>
                    <span class="about-chip"><i class="mdi mdi-flag-variant-outline me-1" aria-hidden="true"></i> India&apos;s career acceleration platform</span>
                    <span class="about-chip"><i class="mdi mdi-copyright me-1" aria-hidden="true"></i> 2026 Hirevoo Pvt. Ltd.</span>
                </div>
            </div>
        </section>

        <div class="container" style="max-width: 1080px;">
            <section class="about-section about-animate">
                <div class="about-section-head">
                    <p class="about-eyebrow">Who we are</p>
                    <h2 class="about-h2">The hiring system is broken. We&apos;re fixing it.</h2>
                    <p class="about-sub">
                        Most job seekers apply to dozens of roles and hear nothing, not because they&apos;re unqualified, but because they&apos;re invisible. No referral, no match data, no edge.
                    </p>
                </div>
                <p class="text-muted mb-2" style="font-size: 0.95rem; line-height: 1.6;">
                    Hirevo is <strong>not</strong> a job portal. Job portals list vacancies. That&apos;s it.
                </p>
                <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.65;">
                    Hirevo is a career acceleration platform. We analyse your profile, score your match against real roles, connect you with employees who can refer you, and give you tools to close skill gaps before you apply.
                    We don&apos;t just show you jobs. We help you increase your probability of getting hired.
                </p>
            </section>

            <section class="about-section">
                <div class="about-section-head">
                    <p class="about-eyebrow">Why it matters</p>
                    <h2 class="about-h2">What you get</h2>
                </div>
                <div class="row g-3 g-lg-4">
                    <div class="col-md-4 about-animate about-stagger-1">
                        <div class="about-pillar">
                            <div class="about-pillar-icon"><i class="mdi mdi-chart-line" aria-hidden="true"></i></div>
                            <h3>Match before you apply</h3>
                            <p>See AI-powered compatibility for every role before you click apply. Stop guessing. Start targeting.</p>
                        </div>
                    </div>
                    <div class="col-md-4 about-animate about-stagger-2">
                        <div class="about-pillar">
                            <div class="about-pillar-icon about-pillar-icon--mint"><i class="mdi mdi-account-group-outline" aria-hidden="true"></i></div>
                            <h3>Referrals from real employees</h3>
                            <p>Connect with people at target companies who can voluntarily refer your profile, the way strong candidates have always been hired.</p>
                        </div>
                    </div>
                    <div class="col-md-4 about-animate about-stagger-3">
                        <div class="about-pillar">
                            <div class="about-pillar-icon about-pillar-icon--navy"><i class="mdi mdi-chart-box-outline" aria-hidden="true"></i></div>
                            <h3>Data-driven career decisions</h3>
                            <p>See what&apos;s missing between your profile and your goal role, and how to close the gap.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="about-section-head">
                    <p class="about-eyebrow">Product</p>
                    <h2 class="about-h2">What we do</h2>
                    <p class="about-sub mb-0">One platform. Three kinds of value.</p>
                </div>
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="about-audience-card about-animate">
                            <h3>For candidates: accelerate your career</h3>
                            <ul>
                                <li>Discover roles curated to your profile</li>
                                <li>See AI match scores before applying</li>
                                <li>Request referrals from target company employees</li>
                                <li>Track applications in one place</li>
                                <li>Identify skill gaps and upskill paths</li>
                                <li>Career consultations with experts</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="about-audience-card about-animate about-stagger-1">
                            <h3>For employees: help talent, get rewarded</h3>
                            <ul>
                                <li>Voluntarily refer qualified candidates</li>
                                <li>Earn incentives on successful referrals</li>
                                <li>Build your reputation as a connector</li>
                                <li>Full control of your referral activity</li>
                                <li>Help deserving professionals get hired</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="about-audience-card about-animate about-stagger-2">
                            <h3>For employers: hire smarter, faster</h3>
                            <ul>
                                <li>Post roles and reach pre-qualified candidates</li>
                                <li>Leverage employee networks for referrals</li>
                                <li>AI-matched candidate profiles</li>
                                <li>Track hiring pipeline in real time</li>
                                <li>Strengthen employer brand with top talent</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="about-quote-card about-animate">
                            <h2>Our mission</h2>
                            <p class="mb-2 fst-italic" style="color: #475569;">&ldquo;Make every job seeker&apos;s effort count, with match data, referrals, and insights to get hired.&rdquo;</p>
                            <p class="mb-0">We believe talent is everywhere. Opportunity isn&apos;t. We exist to close that gap and make career acceleration accessible to every professional.</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-quote-card about-animate about-stagger-1">
                            <h2>Our vision</h2>
                            <p class="mb-2 fst-italic" style="color: #475569;">&ldquo;A world where the best candidate for a role actually gets it, regardless of who they know.&rdquo;</p>
                            <p class="mb-0">We&apos;re building infrastructure for merit-based career mobility in India, where profile, skills, and potential matter more than luck.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="about-section-head">
                    <p class="about-eyebrow">Journey</p>
                    <h2 class="about-h2">How it works</h2>
                    <p class="about-sub mb-0">Five steps from profile to hired.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="about-step-card about-animate">
                            <div class="about-step-num">01</div>
                            <div class="about-step-line">
                                <h3>Discover</h3>
                                <p>Upload your resume. AI surfaces the most relevant jobs for your profile.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="about-step-card about-animate about-stagger-1">
                            <div class="about-step-num">02</div>
                            <div class="about-step-line">
                                <h3>Match</h3>
                                <p>See compatibility for each role before applying. Know where you stand.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="about-step-card about-animate about-stagger-2">
                            <div class="about-step-num">03</div>
                            <div class="about-step-line">
                                <h3>Refer</h3>
                                <p>Request a referral from a real employee at your target company.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="about-step-card about-animate about-stagger-3">
                            <div class="about-step-num">04</div>
                            <div class="about-step-line">
                                <h3>Improve</h3>
                                <p>Identify skill gaps and get precise upskill recommendations.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="about-step-card about-animate">
                            <div class="about-step-num">05</div>
                            <div class="about-step-line">
                                <h3>Get hired</h3>
                                <p>Apply stronger, track smarter, and enter every interview with confidence.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="about-stat about-animate">
                            <div class="about-stat-val">70%</div>
                            <p>of jobs are filled through referrals or networks</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-stat about-animate about-stagger-1">
                            <div class="about-stat-val">250+</div>
                            <p>applications per job posting on average</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-stat about-animate about-stagger-2">
                            <div class="about-stat-val">6 sec</div>
                            <p>average recruiter time spent on a resume</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="about-section-head">
                    <p class="about-eyebrow">Differentiators</p>
                    <h2 class="about-h2">What makes us different</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="about-diff-card">
                            <h3>01. Referral-first model</h3>
                            <p>We put employee referrals at the centre because referred candidates are more likely to be hired.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-diff-card">
                            <h3>02. AI match scoring</h3>
                            <p>Before you apply, see compatibility scores so effort goes to high-match roles.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-diff-card">
                            <h3>03. Career acceleration, not job listing</h3>
                            <p>Resume analysis, skill gaps, and upskill guidance make Hirevo your continuous career co-pilot.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-diff-card">
                            <h3>04. Data-driven decisions</h3>
                            <p>From role targeting to skill building and referrals, each move is guided by data.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section">
                <div class="about-section-head">
                    <p class="about-eyebrow">Trust</p>
                    <h2 class="about-h2">Trust &amp; transparency</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="about-trust-card">
                            <h3>We&apos;re a platform facilitator</h3>
                            <p>We connect candidates, employees, and employers. We are not an employer, recruitment agency, or hiring representative.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-trust-card">
                            <h3>We don&apos;t guarantee outcomes</h3>
                            <p>Referrals are voluntary and hiring decisions belong to employers. We provide access, insights, and tools.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-trust-card">
                            <h3>Your data, handled with care</h3>
                            <p>We follow DPDP Act 2023 principles and never sell personal data for third-party marketing.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-section pb-0">
                <div class="about-section-head">
                    <p class="about-eyebrow">FAQ</p>
                    <h2 class="about-h2">Frequently asked questions</h2>
                </div>
                <div class="accordion" id="aboutFaq">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                What is Hirevo and how is it different from a job portal?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Hirevo is a career acceleration platform, not a job portal. Along with jobs, you get AI match scores, employee referrals, skill gap insights, and tools to continuously improve your candidacy.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                How does the job referral system work on Hirevo?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Candidates can send referral requests to verified employees. Employees voluntarily choose whether to refer. Outcomes depend on employee discretion and employer decisions.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Does Hirevo guarantee a job or referral?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                No. Hirevo provides tools, insights, and connections. It does not guarantee job placement, interviews, or referral acceptance.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                What is an AI match score on Hirevo?
                            </button>
                        </h3>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="faqFour" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                An AI match score is a compatibility percentage based on your skills, experience, and qualifications for a role. It helps prioritise applications but does not guarantee outcomes.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                Is Hirevo available outside India?
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Hirevo is currently focused on the Indian job market, with global expansion as a long-term vision.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="about-cta about-animate">
                <h2>Ready to accelerate your career?</h2>
                <p>Apply smarter with match scores, referrals, and real career insights.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-1">
                    <a href="{{ route('resume.upload') }}" class="btn btn-primary rounded-pill px-4">Upload your resume free</a>
                    <a href="{{ route('job-openings') }}" class="btn btn-outline-light rounded-pill px-4">Explore job openings</a>
                </div>
                <small>Hirevo provides tools, insights, and referral access. We do not guarantee job placement, interviews, or hiring outcomes. All hiring decisions rest with employers.</small>
            </section>
        </div>
    </div>
@endsection
