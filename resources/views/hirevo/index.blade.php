@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- START HOME -->
    <section class="bg-home2" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="mb-4 pb-3 me-lg-5">
                        <h6 class="sub-title">AI Career Intelligence Platform</h6>
                        <h1 class="display-5 fw-semibold mb-3">Find your dream jobs with <span class="text-primary fw-bold">Hirevo</span></h1>
                        <p class="lead text-muted mb-0">Get skill-gap analysis, job goal matching, referral access to verified employees, and upskilling leads. One platform for your career growth.</p>
                    </div>
                    <form action="{{ route('job-list') }}" method="GET">
                        <div class="registration-form">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="filter-search-form filter-border mt-3 mt-md-0">
                                        <i class="uil uil-briefcase-alt"></i>
                                        <input type="search" name="q" id="job-title" class="form-control filter-input-box" placeholder="Job goal e.g. Data Analyst...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="filter-search-form mt-3 mt-md-0">
                                        <i class="uil uil-map-marker"></i>
                                        <select class="form-select" data-trigger="" name="location" id="choices-single-location" aria-label="Location">
                                            <option value="IN">India</option>
                                            <option value="US">United States</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="AE">UAE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mt-3 mt-md-0 h-100">
                                        <button class="btn btn-primary submit-btn w-100 h-100" type="submit"><i class="uil uil-search me-1"></i> Find Job</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5">
                    <div class="mt-5 mt-md-0">
                        <img src="{{ asset($theme.'/assets/images/process-02.png') }}" alt="" class="home-img">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Home -->

    <!-- START SHAPE -->
    <div class="position-relative">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="1440" height="150" preserveaspectratio="none" viewbox="0 0 1440 220">
                <g mask="url(&quot;#SvgjsMask1004&quot;)" fill="none">
                    <path d="M 0,213 C 288,186.4 1152,106.6 1440,80L1440 250L0 250z" fill="rgba(255, 255, 255, 1)"></path>
                </g>
                <defs>
                    <mask id="SvgjsMask1004">
                        <rect width="1440" height="250" fill="#ffffff"></rect>
                    </mask>
                </defs>
            </svg>
        </div>
    </div>
    <!-- END SHAPE -->

    <!-- START CATEGORY -->
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section-title text-center">
                        <h3 class="title">Browse Job Goals</h3>
                        <p class="text-muted">Select your target role. We'll match your skills and show the gap to fill.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @forelse(($jobRoles ?? []) as $role)
                <div class="col-lg-3 col-md-6 mt-4 pt-2">
                    <div class="popu-category-box rounded text-center">
                        <div class="popu-category-icon icons-md">
                            <i class="uim uim-bag"></i>
                        </div>
                        <div class="popu-category-content mt-4">
                            <a href="{{ route('job-goal.show', $role) }}" class="text-dark stretched-link">
                                <h5 class="fs-18">{{ $role->title }}</h5>
                            </a>
                            <p class="text-muted mb-0">View skills</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-lg-3 col-md-6 mt-4 pt-2">
                    <div class="popu-category-box rounded text-center">
                        <div class="popu-category-icon icons-md"><i class="uim uim-layers-alt"></i></div>
                        <div class="popu-category-content mt-4">
                            <a href="{{ route('job-list') }}" class="text-dark stretched-link"><h5 class="fs-18">Data Analyst</h5></a>
                            <p class="text-muted mb-0">View skills</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mt-4 pt-2">
                    <div class="popu-category-box rounded text-center">
                        <div class="popu-category-icon icons-md"><i class="uim uim-airplay"></i></div>
                        <div class="popu-category-content mt-4">
                            <a href="{{ route('job-list') }}" class="text-dark stretched-link"><h5 class="fs-18">Software Engineer</h5></a>
                            <p class="text-muted mb-0">View skills</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mt-4 pt-2">
                    <div class="popu-category-box rounded text-center">
                        <div class="popu-category-icon icons-md"><i class="uim uim-bag"></i></div>
                        <div class="popu-category-content mt-4">
                            <a href="{{ route('job-list') }}" class="text-dark stretched-link"><h5 class="fs-18">Product Manager</h5></a>
                            <p class="text-muted mb-0">View skills</p>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="mt-5 text-center">
                        <a href="{{ route('job-list') }}" class="btn btn-primary btn-hover">Browse All Job Goals <i class="uil uil-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END CATEGORY -->

    <!-- START UPLOAD RESUME CTA -->
    <section class="section bg-light">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-4 col-md-5 d-none d-md-block text-center">
                    <img src="{{ asset('images/career-cta.svg') }}" alt="Career" class="img-fluid" style="max-height: 220px;">
                </div>
                <div class="col-lg-8 col-md-7">
                    <div class="card border-0 shadow rounded-3 overflow-hidden bg-primary bg-opacity-10">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <h3 class="mb-3">Get your resume scored and discover matching job goals</h3>
                            <p class="text-muted mb-4">Upload your CV and we'll show your ATS score and recommend job goals that match your skills.</p>
                            @auth
                                @if(auth()->user()->isCandidate())
                                    <a href="{{ route('resume.upload') }}" class="btn btn-primary btn-hover px-4"><i class="uil uil-file-upload me-1"></i> Upload resume</a>
                                @else
                                    <a href="{{ route('resume.upload') }}" class="btn btn-primary btn-hover px-4"><i class="uil uil-file-upload me-1"></i> Submit CV</a>
                                @endif
                            @else
                                <a href="{{ route('login', ['redirect' => '/resume/upload']) }}" class="btn btn-primary btn-hover px-4"><i class="uil uil-file-upload me-1"></i> Submit CV</a>
                                <p class="text-muted small mt-3 mb-0">Sign in or sign up as a candidate to upload your resume.</p>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END UPLOAD RESUME CTA -->

    <!-- START JOB-LIST -->
    <section class="section bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="section-title text-center mb-4 pb-2">
                        <h4 class="title">Why Hirevo</h4>
                        <p class="text-muted mb-1">AI Career Intelligence + Referral Network + Skill Monetization Engine.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-3">
                        <div class="card-body p-4">
                            <div class="avatar-md mb-3">
                                <div class="avatar-title bg-primary bg-opacity-10 rounded"><i class="uil uil-analysis text-primary fs-20"></i></div>
                            </div>
                            <h5 class="mb-2">Skill Gap Analysis</h5>
                            <p class="text-muted mb-0">Select your job goal. AI calculates match %, missing skills, and learning roadmap.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-3">
                        <div class="card-body p-4">
                            <div class="avatar-md mb-3">
                                <div class="avatar-title bg-success bg-opacity-10 rounded"><i class="uil uil-user-check text-success fs-20"></i></div>
                            </div>
                            <h5 class="mb-2">Referral Marketplace</h5>
                            <p class="text-muted mb-0">Premium candidates get referral requests to verified employees. ₹999/month.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-3">
                        <div class="card-body p-4">
                            <div class="avatar-md mb-3">
                                <div class="avatar-title bg-info bg-opacity-10 rounded"><i class="uil uil-book-open text-info fs-20"></i></div>
                            </div>
                            <h5 class="mb-2">EdTech Lead Bidding</h5>
                            <p class="text-muted mb-0">Opt for upskilling help. EdTech partners bid for your lead.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END JOB-LIST -->
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
