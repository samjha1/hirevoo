@extends('layouts.app')

@section('title', 'Pricing')

@section('content')
    <style>
        .pricing-hero {
            background: linear-gradient(135deg, #f7faff 0%, #edf4ff 100%);
            border: 1px solid #e7edfb;
            border-radius: 1rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .pricing-hero::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            right: -80px;
            top: -80px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.16) 0%, rgba(13, 110, 253, 0) 70%);
            pointer-events: none;
        }

        .pricing-chip {
            display: inline-block;
            border: 1px solid rgba(13, 110, 253, 0.25);
            background: rgba(13, 110, 253, 0.08);
            color: #0b5ed7;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.83rem;
            font-weight: 600;
            margin-right: 0.45rem;
            margin-bottom: 0.45rem;
        }

        .pricing-card {
            border: 1px solid #edf1f7;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(16, 24, 40, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            background: #fff;
        }

        .pricing-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(16, 24, 40, 0.08);
        }

        .pricing-popular {
            border: 2px solid #0d6efd;
            position: relative;
        }

        .pricing-badge {
            position: absolute;
            top: -12px;
            right: 18px;
            background: #0d6efd;
            color: #fff;
            border-radius: 999px;
            padding: 0.3rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            z-index: 1;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 0.35rem;
        }

        .section-title {
            position: relative;
            padding-left: 0.9rem;
        }

        .section-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0.35rem;
            width: 4px;
            height: 1.2rem;
            border-radius: 999px;
            background: #0d6efd;
        }

        .metric-card {
            background: #fff;
            border: 1px solid #edf1f7;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(16, 24, 40, 0.05);
            text-align: center;
            padding: 1.15rem;
            height: 100%;
        }

        .metric-value {
            font-size: 1.9rem;
            line-height: 1;
            font-weight: 800;
            color: #0d6efd;
        }

        .compare-card {
            border: 1px solid #edf1f7;
            border-radius: 1rem;
            background: #fff;
            padding: 1.25rem;
            height: 100%;
        }

        .check-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .check-list li {
            position: relative;
            padding-left: 1.4rem;
            margin-bottom: 0.55rem;
            color: #6c757d;
        }

        .check-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 0;
            color: #198754;
            font-weight: 700;
        }

        .pricing-table th {
            background: #f8fbff;
            font-weight: 700;
            white-space: nowrap;
        }

        .pricing-table td,
        .pricing-table th {
            padding: 0.8rem 0.7rem;
            font-size: 0.92rem;
        }

        .section-block {
            margin-bottom: 3rem;
        }

        @media (max-width: 991.98px) {
            .pricing-hero {
                padding: 1.5rem;
            }

            .section-block {
                margin-bottom: 2.25rem;
            }
        }

        @media (max-width: 767.98px) {
            .section {
                padding-top: 2rem !important;
                padding-bottom: 2.25rem !important;
            }

            .pricing-hero {
                padding: 1.25rem;
                border-radius: 0.85rem;
            }

            .pricing-hero h1 {
                font-size: 1.5rem;
                line-height: 1.3;
            }

            .pricing-card,
            .metric-card,
            .compare-card {
                border-radius: 0.85rem;
            }

            .pricing-card {
                padding: 1rem !important;
            }

            .pricing-popular {
                border-width: 1px;
                box-shadow: 0 12px 30px rgba(13, 110, 253, 0.15);
            }

            .pricing-badge {
                top: 10px;
                right: 10px;
                font-size: 0.68rem;
                padding: 0.25rem 0.55rem;
            }

            .section-title {
                font-size: 1.2rem;
                padding-left: 0.75rem;
            }

            .section-title::before {
                height: 1rem;
                top: 0.3rem;
            }

            .metric-value {
                font-size: 1.6rem;
            }

            .table-responsive.pricing-card {
                padding: 0.65rem !important;
                border-radius: 0.85rem;
            }

            .pricing-table {
                min-width: 700px;
            }

            .pricing-table td,
            .pricing-table th {
                padding: 0.65rem 0.6rem;
                font-size: 0.84rem;
            }

            .accordion-button {
                font-size: 0.95rem;
                line-height: 1.45;
                padding: 0.9rem 0.95rem;
            }

            .accordion-body {
                font-size: 0.9rem;
                padding: 0.9rem 0.95rem;
            }

            .pricing-hero.text-center .d-flex {
                flex-direction: column;
            }

            .pricing-hero.text-center .btn {
                width: 100%;
            }
        }
    </style>
    <section class="section py-5">
        <div class="container" style="max-width: 1120px;">
            <div class="pricing-hero mb-4 mb-lg-5">
                <p class="text-uppercase small text-muted mb-1">Hirevoo</p>
                <h1 class="h2 fw-bold mb-2">Get Hired Faster with Hirevoo</h1>
                <p class="text-muted mb-2">Increase Your Chances with Smart Referrals and AI Insights.</p>
                <div class="mb-3">
                    <span class="pricing-chip">AI Match Scoring</span>
                    <span class="pricing-chip">Real Referrals</span>
                    <span class="pricing-chip">Career Insights</span>
                </div>
                <small class="text-muted">www.hirevoo.in</small>
            </div>
            <div class="section-block">
                <span class="section-eyebrow">Plans</span>
                <h2 class="h4 fw-bold mb-3 section-title">Hiring Advantage Plans</h2>
                <p class="text-muted mb-3">Choose your level of advantage.</p>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-1">Access</h3>
                            <div class="h3 fw-bold mb-2">₹149</div>
                            <p class="text-muted small mb-3">Your first step in</p>
                            <ul class="text-muted ps-3 mb-4">
                                <li>1 Referral Request</li>
                                <li>Basic Match Score</li>
                                <li>Job Insights</li>
                                <li>Application Tracker</li>
                            </ul>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-outline-primary w-100">Choose Access</a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-card pricing-popular p-4">
                            <span class="pricing-badge">Most Popular</span>
                            <h3 class="h6 fw-bold mb-1">Advantage</h3>
                            <div class="h3 fw-bold mb-2">₹499</div>
                            <p class="text-muted small mb-3">Most job seekers choose this</p>
                            <ul class="text-muted ps-3 mb-4">
                                <li>5 Referral Requests</li>
                                <li>Detailed Match Score</li>
                                <li>Priority Profile Visibility</li>
                                <li>Smart Job Recommendations</li>
                                <li>Skill Gap Report</li>
                            </ul>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-primary w-100">Choose Advantage</a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-1">Accelerator</h3>
                            <div class="h3 fw-bold mb-2">₹999</div>
                            <p class="text-muted small mb-3">Serious about getting hired</p>
                            <ul class="text-muted ps-3 mb-4">
                                <li>15 Referral Requests</li>
                                <li>Advanced Insights</li>
                                <li>Priority Processing</li>
                                <li>High-Quality Job Access</li>
                                <li>Resume Analysis</li>
                            </ul>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-outline-primary w-100">Choose Accelerator</a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-1">Elite</h3>
                            <div class="h3 fw-bold mb-2">₹2,499</div>
                            <p class="text-muted small mb-3">For top-tier career moves</p>
                            <ul class="text-muted ps-3 mb-4">
                                <li>Unlimited Referrals*</li>
                                <li>Dedicated Support</li>
                                <li>Profile Optimisation</li>
                                <li>Interview Guidance</li>
                                <li>All Premium Features</li>
                            </ul>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-outline-primary w-100">Choose Elite</a>
                        </div>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">* Unlimited referrals subject to fair usage policy. All plans provide access to tools and features  Hirevoo does not guarantee job placement, interviews, or referral responses.</p>
            </div>

            <div class="mb-4 mb-lg-5">
                <span class="section-eyebrow">Comparison</span>
                <h2 class="h4 fw-bold mb-3 section-title">Plan comparison at a glance</h2>
                <div class="table-responsive pricing-card p-3">
                    <table class="table align-middle mb-0 pricing-table">
                        <thead>
                            <tr>
                                <th>Feature Access</th>
                                <th>₹149 Access</th>
                                <th>₹499 Advantage</th>
                                <th>₹999 Accelerator</th>
                                <th>₹2,499 Elite</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Referral Requests</td><td>1</td><td>5</td><td>15</td><td>Unlimited*</td></tr>
                            <tr><td>Match Score</td><td>Basic</td><td>Detailed</td><td>Advanced</td><td>Advanced</td></tr>
                            <tr><td>Job Recommendations</td><td>Smart</td><td>Priority</td><td>Premium</td><td>Premium</td></tr>
                            <tr><td>Skill Gap Report</td><td>-</td><td>Yes</td><td>Yes</td><td>Yes</td></tr>
                            <tr><td>Profile Visibility Boost</td><td>-</td><td>Priority</td><td>Priority</td><td>Priority</td></tr>
                            <tr><td>Resume Analysis</td><td>-</td><td>-</td><td>Yes</td><td>Yes</td></tr>
                            <tr><td>Interview Guidance</td><td>-</td><td>-</td><td>-</td><td>Yes</td></tr>
                            <tr><td>Dedicated Support</td><td>-</td><td>-</td><td>-</td><td>Yes</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="section-block">
                <span class="section-eyebrow">The Problem</span>
                <h2 class="h4 fw-bold mb-3 section-title">Does this sound familiar?</h2>
                <ul class="text-muted ps-3 mb-3">
                    <li>You apply to 30+ jobs and get 1 response  or zero.</li>
                    <li>Your resume disappears into a pile of hundreds. No feedback, no visibility.</li>
                    <li>You have no idea if you're even a good fit for the role.</li>
                    <li>You don't know anyone at the company you're applying to.</li>
                    <li>You keep applying the same way and expecting different results.</li>
                </ul>
                <p class="text-muted mb-0">The problem isn't you. It's how you're applying. Hirevoo was built to change that.</p>
            </div>

            <div class="section-block">
                <span class="section-eyebrow">The Solution</span>
                <h2 class="h4 fw-bold mb-3 section-title">Meet Hirevoo your career advantage</h2>
                <p class="text-muted mb-3">Hirevoo is not a job portal. It's a career acceleration platform that gives you data, referrals, and insights to apply smarter and get hired faster.</p>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-2">See Your Chances</h3>
                            <p class="text-muted mb-0">Know your match score before applying. No more guessing.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-2">Get Referrals</h3>
                            <p class="text-muted mb-0">Request referrals from real employees at your target companies.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-2">Improve Your Profile</h3>
                            <p class="text-muted mb-0">Identify skill gaps and get precise recommendations.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="pricing-card p-4">
                            <h3 class="h6 fw-bold mb-2">Apply Strategically</h3>
                            <p class="text-muted mb-0">Target roles where you have a real shot instead of blind applying.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-block">
                <span class="section-eyebrow">How It Works</span>
                <h2 class="h4 fw-bold mb-3 section-title">5 simple steps to go from applicant to hired</h2>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">1. Upload Your Resume</h3><p class="text-muted mb-0">Our AI analyses your profile and surfaces relevant jobs.</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">2. See Your Match Score</h3><p class="text-muted mb-0">Get compatibility scores for each role before applying.</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">3. Request a Referral</h3><p class="text-muted mb-0">Send referral requests to real employees at target companies.</p></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">4. Improve Your Profile</h3><p class="text-muted mb-0">Use skill gap analysis and upskill recommendations.</p></div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">5. Get Hired</h3><p class="text-muted mb-0">Apply with confidence  referred, matched, and prepared.</p></div>
                    </div>
                </div>
            </div>



            <div class="section-block">
                <span class="section-eyebrow">Why It Matters</span>
                <h2 class="h4 fw-bold mb-3 section-title">Same candidate, better strategy, better outcomes</h2>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value">4x</div>
                            <p class="text-muted mb-0">more likely to be hired with a referral</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value">70%</div>
                            <p class="text-muted mb-0">of jobs filled through networks and referrals</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card">
                            <div class="metric-value">6 sec</div>
                            <p class="text-muted mb-0">average recruiter time spent on a resume</p>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="compare-card">
                            <h3 class="h6 fw-bold mb-3">Without Hirevoo</h3>
                            <ul class="check-list">
                                <li>Applying to any job that looks okay</li>
                                <li>No idea of your match percentage</li>
                                <li>No employee contact at the company</li>
                                <li>No feedback, no direction, no strategy</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="compare-card">
                            <h3 class="h6 fw-bold mb-3">With Hirevoo</h3>
                            <ul class="check-list">
                                <li>Target high match roles (70%+ score)</li>
                                <li>Know exactly where your profile stands</li>
                                <li>Request referrals from real insiders</li>
                                <li>Close skill gaps and apply with confidence</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>



            <div class="section-block">
                <span class="section-eyebrow">Why Hirevoo</span>
                <h2 class="h4 fw-bold mb-3 section-title">Built different. For a reason.</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">Real Employee Referrals</h3><p class="text-muted mb-0">Real employees at real companies who can move your application forward.</p></div>
                    </div>
                    <div class="col-md-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">Data-Driven Approach</h3><p class="text-muted mb-0">Match scores, skill gaps, and job insights power every decision.</p></div>
                    </div>
                    <div class="col-md-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">Higher Hiring Probability</h3><p class="text-muted mb-0">Referred candidates with strong match scores stand out immediately.</p></div>
                    </div>
                    <div class="col-md-6">
                        <div class="pricing-card p-4"><h3 class="h6 fw-bold mb-2">Transparent System</h3><p class="text-muted mb-0">Clear guidance on referrals, scores, and what we can or cannot guarantee.</p></div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 mb-lg-5">
                <strong>Transparency and Trust:</strong> We don't guarantee jobs. We improve your chances with better data, better connections, and a stronger candidacy.
            </div>

            <div class="section-block">
                <span class="section-eyebrow">FAQ</span>
                <h2 class="h4 fw-bold mb-3 section-title">Honest answers to common questions</h2>
                <div class="accordion" id="pricingFaq">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="q1">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1" aria-expanded="true" aria-controls="a1">Is a job guaranteed if I subscribe?</button>
                        </h3>
                        <div id="a1" class="accordion-collapse collapse show" aria-labelledby="q1" data-bs-parent="#pricingFaq">
                            <div class="accordion-body text-muted">No. Hirevoo provides tools, insights, and referral access. Hiring decisions are made by employers.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="q2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2" aria-expanded="false" aria-controls="a2">How exactly do referrals work?</button>
                        </h3>
                        <div id="a2" class="accordion-collapse collapse" aria-labelledby="q2" data-bs-parent="#pricingFaq">
                            <div class="accordion-body text-muted">You send a request to an eligible employee. They may voluntarily refer you. Referral acceptance is at their discretion.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="q3">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3" aria-expanded="false" aria-controls="a3">What if I don't get selected after a referral?</button>
                        </h3>
                        <div id="a3" class="accordion-collapse collapse" aria-labelledby="q3" data-bs-parent="#pricingFaq">
                            <div class="accordion-body text-muted">That can happen. Final selection is always the employer's decision. Hirevoo helps improve visibility and profile quality.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="q4">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a4" aria-expanded="false" aria-controls="a4">How fast will I receive a referral response?</button>
                        </h3>
                        <div id="a4" class="accordion-collapse collapse" aria-labelledby="q4" data-bs-parent="#pricingFaq">
                            <div class="accordion-body text-muted">Response times vary by employee availability. Hirevoo does not guarantee a specific timeline.</div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="q5">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a5" aria-expanded="false" aria-controls="a5">Can I upgrade my plan later?</button>
                        </h3>
                        <div id="a5" class="accordion-collapse collapse" aria-labelledby="q5" data-bs-parent="#pricingFaq">
                            <div class="accordion-body text-muted">Yes. You can start with Access and upgrade anytime. Additional benefits are added on upgrade.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pricing-hero text-center">
                <h2 class="h4 fw-bold mb-2">Stop applying blindly. Start applying smartly.</h2>
                <p class="text-muted mb-3">Upload your resume, see your match score, and request your first referral.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                    <a href="{{ route('resume.upload') }}" class="btn btn-primary rounded-pill px-4">Get Started with Hirevoo</a>
                    <a href="{{ route('register') }}?role=candidate" class="btn btn-outline-primary rounded-pill px-4">Create Candidate Account</a>
                </div>
                <small class="text-muted d-block">Starting at just ₹149 · No hidden fees · Cancel anytime</small>
                <small class="text-muted d-block mt-1">Hirevoo provides platform access, tools, and referral facilitation. Job placement, interviews, and referral outcomes are not guaranteed. All hiring decisions are made exclusively by employers.</small>
            </div>
        </div>
    </section>
@endsection
