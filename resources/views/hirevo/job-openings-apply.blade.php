@extends('layouts.app')

@section('title', $job->title . ' - Apply')

@section('content')
    <section class="section py-4">
        <div class="container">
            {{-- Compact breadcrumb only --}}
            <nav class="mb-3" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-openings') }}">Job openings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Apply</li>
                </ol>
            </nav>

            <div class="row">
                {{-- Left: Job details (Apna-style) --}}
                <div class="col-lg-5 order-lg-2 mb-4 mb-lg-0">
                    <div class="card border shadow-none rounded-3 sticky-top" style="top: 90px;">
                        <div class="card-body p-4">
                            <h1 class="h4 fw-600 mb-2">{{ $job->title }}</h1>
                            <p class="text-muted mb-2">{{ $job->user->referrerProfile?->company_name ?? 'Company' }}</p>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @if($job->location)
                                    <span class="text-muted small"><i class="uil uil-map-marker me-1"></i>{{ $job->location }}</span>
                                @endif
                                @if($job->job_type)
                                    @php
                                        $jobTypeLabels = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship', 'temporary' => 'Temporary', 'volunteer' => 'Volunteer', 'other' => 'Other'];
                                    @endphp
                                    <span class="badge bg-soft-primary">{{ $jobTypeLabels[$job->job_type] ?? $job->job_type }}</span>
                                @endif
                                @if($job->work_location_type)
                                    @php
                                        $workLabels = ['office' => 'Work from Office', 'remote' => 'Work from Home', 'hybrid' => 'Hybrid'];
                                    @endphp
                                    <span class="badge bg-soft-success">{{ $workLabels[$job->work_location_type] ?? $job->work_location_type }}</span>
                                @endif
                            </div>
                            <hr>
                            <h6 class="fw-600 mb-2">Job details</h6>
                            @if($job->description)
                                <div class="text-muted small mb-0 job-description-apply" style="max-height: 280px; overflow-y: auto; white-space: pre-wrap;">{{ $job->description }}</div>
                            @else
                                <p class="text-muted small mb-0">—</p>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('job-openings') }}" class="btn btn-outline-secondary btn-sm">Back to jobs</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Application form --}}
                <div class="col-lg-7 order-lg-1">
                    <div class="card border shadow-none rounded-3">
                        <div class="card-body p-4 p-lg-5">
                            <h5 class="fw-600 mb-1">Apply for this job</h5>
                            <p class="text-muted small mb-4">Fill in your details. Add a resume so the employer can view your CV.</p>

                            <form action="{{ route('job-openings.apply.store', $job) }}" method="POST">
                                @csrf
                                @if($errors->any())
                                    <div class="alert alert-danger mb-4">
                                        <ul class="mb-0 list-unstyled">
                                            @foreach($errors->all() as $err)
                                                <li>{{ $err }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <h6 class="text-muted small text-uppercase mb-3">Profile details</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="10-digit mobile">
                                        </div>
                                        <div class="col-12">
                                            <label for="headline" class="form-label">Current role / Headline</label>
                                            <input type="text" name="headline" id="headline" class="form-control" value="{{ old('headline', $profile?->headline ?? '') }}" placeholder="e.g. Business Intelligence Analyst">
                                        </div>
                                        <div class="col-12">
                                            <label for="education" class="form-label">Education</label>
                                            <input type="text" name="education" id="education" class="form-control" value="{{ old('education', $profile?->education ?? '') }}" placeholder="e.g. BE/B.Tech, College Name">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="experience_years" class="form-label">Experience (years)</label>
                                            <input type="number" name="experience_years" id="experience_years" class="form-control" value="{{ old('experience_years', $profile?->experience_years ?? '') }}" min="0" max="50" placeholder="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" name="location" id="location" class="form-control" value="{{ old('location', $profile?->location ?? '') }}" placeholder="e.g. Gurgaon">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="expected_salary" class="form-label">Expected salary</label>
                                            <input type="text" name="expected_salary" id="expected_salary" class="form-control" value="{{ old('expected_salary', $profile?->expected_salary ?? '') }}" placeholder="e.g. 4.7 L">
                                        </div>
                                        <div class="col-12">
                                            <label for="skills" class="form-label">Skills (comma-separated)</label>
                                            <textarea name="skills" id="skills" class="form-control" rows="2" placeholder="e.g. SQL, Excel, Python">{{ old('skills', $profile?->skills ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-muted small text-uppercase mb-3">Resume & message</h6>
                                    @if($resumes->count() > 0)
                                        <div class="mb-3">
                                            <label for="resume_id" class="form-label">Attach resume</label>
                                            <select name="resume_id" id="resume_id" class="form-select">
                                                <option value="">No resume</option>
                                                @foreach($resumes as $r)
                                                    <option value="{{ $r->id }}" {{ old('resume_id', $resumes->first()?->id) == $r->id ? 'selected' : '' }}>
                                                        {{ $r->file_name ?? 'Resume #' . $r->id }}{{ $r->ai_score ? ' (' . $r->ai_score . '% ATS)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @else
                                        <div class="alert alert-light border mb-3 py-2">
                                            <a href="{{ route('resume.upload') }}" class="btn btn-sm btn-outline-primary">Upload resume</a>
                                        </div>
                                    @endif
                                    <div>
                                        <label for="cover_message" class="form-label">Cover message (optional)</label>
                                        <textarea name="cover_message" id="cover_message" class="form-control" rows="3" placeholder="Why are you a good fit?">{{ old('cover_message') }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">Submit application</button>
                                    <a href="{{ route('job-openings') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
