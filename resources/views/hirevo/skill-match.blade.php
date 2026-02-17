@extends('layouts.app')

@section('title', 'Skill Match - ' . $jobRole->title)

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">Skill Match</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('job-list') }}">Job Goals</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ $jobRole->title }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end page title -->

    <div class="position-relative" style="z-index: 1">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 1440 250">
                <path fill="" fill-opacity="1" d="M0,192L120,202.7C240,213,480,235,720,234.7C960,235,1200,213,1320,202.7L1440,192L1440,320L1320,320C1200,320,960,320,720,320C480,320,240,320,120,320L0,320Z"></path>
            </svg>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Job role header -->
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h4 class="mb-2">{{ $jobRole->title }}</h4>
                            @if($jobRole->description)
                                <p class="text-muted mb-0">{{ $jobRole->description }}</p>
                            @endif
                        </div>
                    </div>

                    @auth
                    <!-- Match result for logged-in candidate -->
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Your skill match</h5>
                            @if($hasProfile && !empty($candidateSkills))
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 70px; height: 70px;">
                                            <span class="fs-24 fw-bold text-primary">{{ $matchPercentage }}%</span>
                                        </div>
                                        <div>
                                            <p class="mb-0 fw-medium">Match with {{ $jobRole->title }}</p>
                                            <p class="text-muted mb-0 small">Based on your profile skills vs required skills</p>
                                        </div>
                                    </div>
                                    @if($matchPercentage >= 100)
                                        <p class="text-success mb-0">You have all the required skills for this role.</p>
                                    @else
                                        <p class="text-muted mb-0">You have {{ count($matchedSkills) }} of {{ count($requiredSkills) }} required skills.</p>
                                    @endif
                                </div>
                                @if(count($matchedSkills) > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-2">Skills you have</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($matchedSkills as $skill)
                                                <span class="badge bg-success-subtle text-success">{{ ucfirst($skill) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if(count($missingSkills) > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-2">Skills to learn</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($missingSkills as $skill)
                                                <span class="badge bg-warning-subtle text-warning">{{ ucfirst($skill) }}</span>
                                            @endforeach
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">Focus on these to improve your match for {{ $jobRole->title }}.</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-muted mb-3">Add your skills in your profile to see how you match with this role.</p>
                                <a href="{{ route('profile') }}" class="btn btn-soft-primary">Complete your profile</a>
                            @endif
                        </div>
                    </div>
                    @else
                    <!-- Guest: show required skills + CTA -->
                    <div class="card border shadow-none rounded-3 mb-4">
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">Sign in or create an account to see your skill match for this role. We’ll compare your skills with the requirements below.</p>
                            <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary me-2">Sign In</a>
                            <a href="{{ route('register') }}?role=candidate" class="btn btn-soft-primary">Sign Up</a>
                        </div>
                    </div>
                    @endauth

                    <!-- Required skills (always shown) -->
                    <div class="card border shadow-none rounded-3">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Required skills for {{ $jobRole->title }}</h5>
                            @if($requiredSkills->count() > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($requiredSkills as $skill)
                                        <li class="d-flex align-items-center mb-2">
                                            <i class="uil uil-check-circle text-primary me-2"></i>
                                            <span>{{ $skill->skill_name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No specific skills defined yet for this role. Check back later or contact us.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Next steps (for candidates with match) -->
                    @auth
                    @if($hasProfile && !empty($candidateSkills) && count($missingSkills) > 0)
                    <div class="card border shadow-none rounded-3 mt-4 border-primary">
                        <div class="card-body p-4">
                            <h6 class="mb-2">Get help to learn these skills</h6>
                            <p class="text-muted small mb-3">Opt in to get matched with EdTech partners who can help you upskill. Partners may reach out with relevant courses.</p>
                            <button type="button" class="btn btn-soft-primary btn-sm">I want to learn these skills</button>
                        </div>
                    </div>
                    @endif
                    @if($hasProfile && $matchPercentage >= 70)
                    <div class="card border shadow-none rounded-3 mt-4 border-success">
                        <div class="card-body p-4">
                            <h6 class="mb-2">Request a referral</h6>
                            <p class="text-muted small mb-3">Premium members can request referrals from verified employees. Upgrade to get 3 referral requests per month.</p>
                            <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm">View Premium</a>
                        </div>
                    </div>
                    @endif
                    @endauth

                    <div class="text-center mt-4">
                        <a href="{{ route('job-list') }}" class="btn btn-soft-primary"><i class="uil uil-arrow-left me-1"></i> Back to Job Goals</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
