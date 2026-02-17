@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <!-- Start page title -->
    <section class="page-title-box">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="text-center text-white">
                        <h3 class="mb-4">My Profile</h3>
                        <div class="page-next">
                            <nav class="d-inline-block" aria-label="breadcrumb text-center">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
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

    <section class="section">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card border shadow-none rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ asset($theme.'/assets/images/profile.jpg') }}" alt="" class="rounded-circle" width="80" height="80">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1">{{ auth()->user()->name }}</h5>
                                    <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
                                    <span class="badge bg-primary mt-2">{{ auth()->user()->role }}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong class="text-dark">Name</strong></p>
                                    <p class="mb-3">{{ auth()->user()->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong class="text-dark">Email</strong></p>
                                    <p class="mb-3">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong class="text-dark">Role</strong></p>
                                    <p class="mb-3">{{ ucfirst(auth()->user()->role) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong class="text-dark">Status</strong></p>
                                    <p class="mb-3">{{ ucfirst(auth()->user()->status) }}</p>
                                </div>
                            </div>

                            @if(auth()->user()->isCandidate())
                            <hr>
                            <h6 class="mb-3">Your skills (for skill match)</h6>
                            <p class="text-muted small mb-3">Add skills separated by commas (e.g. SQL, Excel, Python). These are used to calculate your match with job goals.</p>
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="headline" class="form-label">Headline</label>
                                    <input type="text" class="form-control" id="headline" name="headline" value="{{ old('headline', $profile->headline ?? '') }}" placeholder="e.g. Data Analyst">
                                </div>
                                <div class="mb-3">
                                    <label for="skills" class="form-label">Skills</label>
                                    <textarea class="form-control" id="skills" name="skills" rows="3" placeholder="e.g. SQL, Excel, Python, Data Visualization">{{ old('skills', $profile->skills ?? '') }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Save profile</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
