@extends('layouts.app')

@section('title', 'About')

@section('content')
    <style>
        .about-hero {
            background: linear-gradient(135deg, #f7faff 0%, #edf4ff 100%);
            border: 1px solid #e7edfb;
            border-radius: 1rem;
            padding: 2rem;
        }

        .about-chip {
            display: inline-block;
            border: 1px solid rgba(13, 110, 253, 0.22);
            background: rgba(13, 110, 253, 0.08);
            color: #0b5ed7;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.83rem;
            font-weight: 600;
            margin-right: 0.45rem;
            margin-bottom: 0.45rem;
        }

        .about-card {
            border: 1px solid #edf1f7;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(16, 24, 40, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .about-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(16, 24, 40, 0.08);
        }

        .about-step {
            border-left: 3px solid #0d6efd;
            padding-left: 0.85rem;
        }

        .about-number {
            font-size: 1.9rem;
            font-weight: 800;
            color: #0d6efd;
            line-height: 1;
        }
    </style>
    <section class="section py-5">
        <div class="container" style="max-width: 1080px;">
            <div class="about-hero mb-4 mb-lg-5">
                <p class="text-uppercase small text-muted mb-1">Hirevoo</p>
                <h1 class="h2 fw-bold mb-2">Career Acceleration Platform  About Us</h1>
                <p class="text-muted mb-3">
                    We built the platform that increases your chances of getting hired  with AI match scores, real employee referrals, and career insights that actually move the needle.
                </p>
                <div class="mb-0">
                    <span class="about-chip">www.hirevoo.in</span>
                    <span class="about-chip">India's Career Acceleration Platform</span>
                    <span class="about-chip">Copyright 2025 Hirevoo Pvt. Ltd.</span>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-2">Who We Are</h2>
                <p class="text-muted mb-3 fw-semibold">The hiring system is broken. We're fixing it.</p>
                <p class="text-muted mb-2">
                    Most job seekers apply to dozens of roles and hear nothing  not because they're unqualified, but because they're invisible. No referral, no match data, no edge.
                </p>
                <p class="text-muted mb-2">
                    Hirevoo is <strong>not</strong> a job portal. Job portals list vacancies. That's it.
                </p>
                <p class="text-muted mb-0">
                    Hirevoo is a career acceleration platform  we analyse your profile, score your match against real roles, connect you with employees who can refer you, and give you the tools to close skill gaps before you apply.
                    We don't just show you jobs. We help you increase your probability of getting hired.
                </p>
            </div>

            <div class="row g-3 mb-4 mb-lg-5">
                <div class="col-md-4">
                    <div class="about-card p-4">
                        <h3 class="h6 fw-bold mb-2">Match Before You Apply</h3>
                        <p class="text-muted mb-0">See your AI-powered compatibility score for every role before you click apply. Stop guessing. Start targeting.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card p-4">
                        <h3 class="h6 fw-bold mb-2">Referrals from Real Employees</h3>
                        <p class="text-muted mb-0">Connect with employees at your target companies who can voluntarily refer your profile the way top candidates have always gotten hired.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card p-4">
                        <h3 class="h6 fw-bold mb-2">Data-Driven Career Decisions</h3>
                        <p class="text-muted mb-0">Understand exactly what's missing between your profile and your dream role and how to close that gap.</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-3">What We Do</h2>
                <p class="text-muted mb-3">One platform. Three kinds of value.</p>
                <div class="row g-3">
                    <div class="col-lg-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-3">For Candidates  Accelerate your career</h3>
                            <ul class="text-muted ps-3 mb-0">
                                <li>Discover relevant jobs curated to your profile</li>
                                <li>See AI match scores before applying</li>
                                <li>Request referrals from target company employees</li>
                                <li>Track all applications in one place</li>
                                <li>Identify skill gaps and get upskill recommendations</li>
                                <li>Access career consultations with experts</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-3">For Employees  Help great talent, get rewarded</h3>
                            <ul class="text-muted ps-3 mb-0">
                                <li>Voluntarily refer qualified candidates</li>
                                <li>Earn incentives on successful referrals</li>
                                <li>Build your reputation as a connector</li>
                                <li>Full control of your referral activity</li>
                                <li>Help deserving professionals get hired</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-3">For Employers  Hire smarter, faster</h3>
                            <ul class="text-muted ps-3 mb-0">
                                <li>Post roles and reach pre-qualified candidates</li>
                                <li>Leverage employee networks for referrals</li>
                                <li>Access AI matched candidate profiles</li>
                                <li>Track hiring pipeline in real time</li>
                                <li>Strengthen employer brand with top talent</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4 mb-lg-5">
                <div class="col-lg-6">
                    <div class="about-card p-4">
                        <h2 class="h5 fw-bold mb-2">Our Mission</h2>
                        <p class="text-muted mb-2">"Make every job seeker's effort count  by giving them the match data, the referrals, and the insights to get hired."</p>
                        <p class="text-muted mb-0">We believe talent is everywhere. Opportunity isn't. We exist to close that gap and make career acceleration accessible to every professional.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-card p-4">
                        <h2 class="h5 fw-bold mb-2">Our Vision</h2>
                        <p class="text-muted mb-2">"A world where the best candidate for a role actually gets it  regardless of who they know."</p>
                        <p class="text-muted mb-0">We're building infrastructure for merit based career mobility in India where profile, skills, and potential matter more than luck.</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-3">How It Works</h2>
                <p class="text-muted mb-3">Five steps from profile to hired.</p>
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <div class="about-card p-4">
                            <div class="about-number mb-2">01</div>
                            <div class="about-step">
                                <h3 class="h6 fw-bold mb-1">Discover</h3>
                                <p class="text-muted mb-0">Upload your resume. AI immediately surfaces the most relevant jobs for your profile.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="about-card p-4">
                            <div class="about-number mb-2">02</div>
                            <div class="about-step">
                                <h3 class="h6 fw-bold mb-1">Match</h3>
                                <p class="text-muted mb-0">See your compatibility score for each role before applying. Know where you stand.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="about-card p-4">
                            <div class="about-number mb-2">03</div>
                            <div class="about-step">
                                <h3 class="h6 fw-bold mb-1">Refer</h3>
                                <p class="text-muted mb-0">Request a referral from a real employee at your target company.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="about-card p-4">
                            <div class="about-number mb-2">04</div>
                            <div class="about-step">
                                <h3 class="h6 fw-bold mb-1">Improve</h3>
                                <p class="text-muted mb-0">Identify skill gaps and get precise upskill recommendations to strengthen your candidacy.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6">
                        <div class="about-card p-4">
                            <div class="about-number mb-2">05</div>
                            <div class="about-step">
                                <h3 class="h6 fw-bold mb-1">Get Hired</h3>
                                <p class="text-muted mb-0">Apply stronger, track smarter, and enter every interview with confidence.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4 mb-lg-5">
                <div class="col-md-4">
                    <div class="about-card p-4 text-center">
                        <div class="about-number mb-2">70%</div>
                        <p class="text-muted mb-0">of jobs are filled through referrals or networks</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card p-4 text-center">
                        <div class="about-number mb-2">250+</div>
                        <p class="text-muted mb-0">applications per job posting on average</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="about-card p-4 text-center">
                        <div class="about-number mb-2">6 sec</div>
                        <p class="text-muted mb-0">average recruiter time spent on a resume</p>
                    </div>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-3">What Makes Us Different</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">01 Referral First Model</h3>
                            <p class="text-muted mb-0">We put employee referrals at the center because referred candidates are significantly more likely to be hired.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">02 AI Match Scoring</h3>
                            <p class="text-muted mb-0">Before you apply, see compatibility scores so your effort goes to high-match roles only.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">03 Career Acceleration, Not Job Listing</h3>
                            <p class="text-muted mb-0">Resume analysis, skill gap visibility, and upskill guidance make Hirevoo your continuous career co-pilot.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">04 Data Driven Decisions</h3>
                            <p class="text-muted mb-0">From role targeting to skill building and referrals, each decision is guided by data.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-3">Trust & Transparency</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">We are a platform facilitator</h3>
                            <p class="text-muted mb-0">We connect candidates, employees, and employers. We are not an employer, recruitment agency, or hiring representative.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">We don't guarantee outcomes</h3>
                            <p class="text-muted mb-0">Referrals are voluntary and hiring decisions belong to employers. We provide access, insights, and tools.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="about-card p-4">
                            <h3 class="h6 fw-bold mb-2">Your data, handled with care</h3>
                            <p class="text-muted mb-0">We follow DPDP Act 2023 principles and never sell personal data for independent third party marketing.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 mb-lg-5">
                <h2 class="h4 fw-bold mb-3">Frequently Asked Questions</h2>
                <div class="accordion" id="aboutFaq">
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Q1. What is Hirevoo and how is it different from a job portal?
                            </button>
                        </h3>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="faqOne" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Hirevoo is a career acceleration platform  not a job portal. Along with jobs, you get AI match scores, employee referrals, skill gap insights, and tools to continuously improve your candidacy.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Q2. How does the job referral system work on Hirevoo?
                            </button>
                        </h3>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="faqTwo" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Candidates can send referral requests to verified employees. Employees voluntarily choose whether to refer. Referral outcomes depend on employee discretion and employer decisions.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Q3. Does Hirevoo guarantee a job or referral?
                            </button>
                        </h3>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="faqThree" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                No. Hirevoo provides tools, insights, and connections. It does not guarantee job placement, interviews, or referral acceptance.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="faqFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Q4. What is an AI match score on Hirevoo?
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
                                Q5. Is Hirevoo available outside India?
                            </button>
                        </h3>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="faqFive" data-bs-parent="#aboutFaq">
                            <div class="accordion-body text-muted">
                                Hirevoo is currently focused on the Indian job market, with global expansion as a long-term vision.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about-hero text-center">
                <h2 class="h4 fw-bold mb-2">Ready to accelerate your career?</h2>
                <p class="text-muted mb-3">Join thousands of professionals applying smarter with match scores, referrals, and real career insights.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-2">
                    <a href="{{ route('resume.upload') }}" class="btn btn-primary rounded-pill px-4">Upload Your Resume Free</a>
                    <a href="{{ route('job-openings') }}" class="btn btn-outline-primary rounded-pill px-4">Explore Job Openings</a>
                </div>
                <small class="text-muted d-block">Hirevoo provides tools, insights, and referral access. We do not guarantee job placement, interviews, or hiring outcomes. All hiring decisions rest with employers.</small>
            </div>
        </div>
    </section>
@endsection
