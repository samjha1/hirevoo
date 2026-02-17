@extends('layouts.app')

@section('title', 'Job Goals')

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">Job Goals</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Job Goals</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end page title -->

    <!-- START SHAPE -->
    <div class="position-relative" style="z-index: 1">
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewbox="0 0 1440 250">
                <path fill="" fill-opacity="1" d="M0,192L120,202.7C240,213,480,235,720,234.7C960,235,1200,213,1320,202.7L1440,192L1440,320L1320,320C1200,320,960,320,720,320C480,320,240,320,120,320L0,320Z"></path>
            </svg>
        </div>
    </div>
    <!-- END SHAPE -->

    <!-- START JOB-LIST -->
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title text-center mb-4 pb-2">
                        <h4 class="title">Select your target role</h4>
                        <p class="text-muted mb-1">Choose a job goal to see skill match and missing skills. AI will analyse and suggest learning path.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @forelse($jobRoles ?? [] as $role)
                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="card border shadow-none rounded-3 mb-3 job-card">
                        <div class="card-body p-4">
                            <h5 class="mb-2"><a href="{{ route('job-goal.show', $role) }}" class="text-dark">{{ $role->title }}</a></h5>
                            <p class="text-muted mb-0 fs-14">{{ Str::limit($role->description, 80) }}</p>
                            <div class="mt-3">
                                <a href="{{ route('job-goal.show', $role) }}" class="btn btn-soft-primary btn-sm">View skill match</a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card border shadow-none rounded-3">
                        <div class="card-body p-5 text-center">
                            <p class="text-muted mb-0">Job goals will appear here. Add roles from admin or run seeders.</p>
                            <a href="{{ route('home') }}" class="btn btn-primary mt-3">Back to Home</a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
