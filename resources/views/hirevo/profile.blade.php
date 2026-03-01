@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <section class="section pt-4">
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

            {{-- Apna-style profile header: cover + avatar + name, headline, contact --}}
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
                <div class="profile-cover position-relative" style="height: 140px; background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);"></div>
                <div class="card-body position-relative pt-0 pb-4">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4">
                        <div class="profile-avatar-wrap position-relative" style="margin-top: -50px;">
                            <div class="profile-avatar-circle rounded-circle border border-4 border-white shadow-sm bg-white d-flex align-items-center justify-content-center overflow-hidden" style="width: 100px; height: 100px;">
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-primary">
                                    <i class="uil uil-user mdi-48px" style="font-size: 48px;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 mt-2 mt-md-0">
                            <h4 class="fw-600 text-dark mb-1">{{ $user->name }}</h4>
                            @if($profile?->headline)
                                <p class="text-primary fw-500 mb-1">{{ $profile->headline }}</p>
                            @else
                                <p class="text-muted small mb-1">Add a headline in the form below</p>
                            @endif
                            <p class="text-muted small mb-1"><i class="uil uil-envelope me-1"></i> {{ $user->email }}</p>
                            @if($user->phone)
                                <p class="text-muted small mb-2"><i class="uil uil-phone me-1"></i> {{ $user->phone }}</p>
                            @endif
                            @if($profile?->location)
                                <p class="text-muted small mb-0"><i class="uil uil-map-marker me-1"></i> {{ $profile->location }}</p>
                            @endif
                            <span class="badge bg-primary mt-2">{{ ucfirst($user->role) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($user->isCandidate())
                {{-- Upload CV on profile: form stays here, data fills columns after redirect --}}
                <div class="card border shadow-none rounded-3 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-600 mb-2">Upload CV</h5>
                        <p class="text-muted small mb-3">Upload a PDF and we'll extract data into your profile (skills, headline, and more). You'll stay on this page and the fields below will update.</p>
                        <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-auto flex-grow-1">
                                <input type="file" name="resume" id="profile_resume" class="form-control form-control-sm" accept=".pdf" required>
                                @error('resume')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="uil uil-upload me-1"></i> Upload and fill profile
                                </button>
                            </div>
                        </form>
                        @if($latestResume ?? null)
                            <p class="text-muted small mb-0 mt-2">
                                <a href="{{ route('resume.results', $latestResume) }}">View last resume analysis (ATS score)</a>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Re-fill from existing resume (AI) --}}
                @if($hasResume ?? false)
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <form method="POST" action="{{ route('profile.fill-from-resume') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="uil uil-sync me-1"></i> Re-fill profile from my resume (AI)
                                </button>
                            </form>
                            <span class="text-muted small ms-2">Re-extract headline, education, skills, location using ChatGPT.</span>
                        </div>
                    </div>
                @endif

                {{-- Edit profile form (Apna-style sections) --}}
                <div class="card border shadow-none rounded-3">
                    <div class="card-body p-4">
                        <h5 class="fw-600 mb-4">Edit profile</h5>
                        <p class="text-muted small mb-4">Your skills and details are used for job goal matching and applications.</p>

                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="headline" class="form-label fw-500">Professional headline</label>
                                    <input type="text" class="form-control @error('headline') is-invalid @enderror" id="headline" name="headline"
                                           value="{{ old('headline', $profile?->headline ?? '') }}" placeholder="e.g. Data Analyst, Full Stack Developer">
                                    @error('headline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-500">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
                                           value="{{ old('phone', $user->phone ?? '') }}" placeholder="e.g. +91 98765 43210">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="education" class="form-label fw-500">Education</label>
                                    <input type="text" class="form-control @error('education') is-invalid @enderror" id="education" name="education"
                                           value="{{ old('education', $profile?->education ?? '') }}" placeholder="e.g. B.Tech Computer Science, ABC University">
                                    @error('education')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="experience_years" class="form-label fw-500">Years of experience</label>
                                    <input type="number" class="form-control @error('experience_years') is-invalid @enderror" id="experience_years" name="experience_years"
                                           value="{{ old('experience_years', $profile?->experience_years ?? '') }}" min="0" max="50" placeholder="e.g. 3">
                                    @error('experience_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label fw-500">Location</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location"
                                           value="{{ old('location', $profile?->location ?? '') }}" placeholder="e.g. Mumbai, India">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="expected_salary" class="form-label fw-500">Expected salary (optional)</label>
                                    <input type="text" class="form-control @error('expected_salary') is-invalid @enderror" id="expected_salary" name="expected_salary"
                                           value="{{ old('expected_salary', $profile?->expected_salary ?? '') }}" placeholder="e.g. 8 LPA, ₹10-12 L">
                                    @error('expected_salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label for="skills" class="form-label fw-500">Skills</label>
                                    <textarea class="form-control @error('skills') is-invalid @enderror" id="skills" name="skills" rows="4"
                                              placeholder="e.g. SQL, Excel, Python, Data Visualization, Communication (comma-separated)">{{ old('skills', $profile?->skills ?? '') }}</textarea>
                                    <p class="form-text small text-muted mb-0">Used for skill match on Job Goals. Separate with commas.</p>
                                    @error('skills')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Save profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                {{-- Non-candidate: show basic info only --}}
                <div class="card border shadow-none rounded-3">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><strong class="text-dark">Name</strong></p>
                                <p class="mb-3">{{ $user->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><strong class="text-dark">Email</strong></p>
                                <p class="mb-3">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><strong class="text-dark">Role</strong></p>
                                <p class="mb-3">{{ ucfirst($user->role) }}</p>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Profile editing is available for candidate accounts.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
