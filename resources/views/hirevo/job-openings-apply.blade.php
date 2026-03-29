@extends('layouts.app')

@section('title', $job->title . ' - Apply')

@section('content')
    <section class="section py-4">
        <div class="container">
            {{-- Compact breadcrumb only --}}
            <nav class="mb-3" aria-label="breadcrumb">
                <!-- <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-openings') }}">Job openings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Apply</li>
                </ol> -->
            </nav>

            <div class="row">
                {{-- Left: Job details (Apna-style) --}}
                <div class="col-lg-5 order-lg-2 mb-4 mb-lg-0">
                    <div class="card border shadow-none rounded-3 sticky-top" style="top: 90px;">
                        <div class="card-body p-4">
                            @php
                                $jobTypeLabels = ['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'internship' => 'Internship', 'temporary' => 'Temporary', 'volunteer' => 'Volunteer', 'other' => 'Other'];
                                $workLabels = ['office' => 'Work from Office', 'remote' => 'Work from Home', 'hybrid' => 'Hybrid'];
                                $payTypeLabels = ['fixed' => 'Fixed', 'hourly' => 'Hourly', 'negotiable' => 'Negotiable', 'not_disclosed' => 'Not disclosed', 'other' => 'Other'];
                                $companyDisplay = $job->company_name ?: ($job->user->referrerProfile?->company_name ?? 'Company');
                                $requiredSkillsList = is_array($job->required_skills) ? array_values(array_filter($job->required_skills, fn ($s) => $s !== null && $s !== '')) : [];
                                $salaryDisplay = null;
                                if (($job->pay_type ?? '') !== 'not_disclosed') {
                                    if (!is_null($job->salary_min) || !is_null($job->salary_max)) {
                                        $salaryDisplay = ($job->salary_min ?? '—') . ' – ' . ($job->salary_max ?? '—');
                                    } elseif (!empty($job->salary_amount)) {
                                        $salaryDisplay = $job->salary_amount;
                                    } elseif (($job->pay_type ?? '') === 'negotiable') {
                                        $salaryDisplay = 'Negotiable';
                                    }
                                }
                            @endphp
                            <h1 class="h4 fw-600 mb-2">{{ $job->title }}</h1>
                            <p class="text-muted mb-2">{{ $companyDisplay }}</p>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @if($job->formatted_location)
                                    <span class="text-muted small"><i class="uil uil-map-marker me-1"></i>{{ $job->formatted_location }}</span>
                                @endif
                                @if($job->job_type)
                                    <span class="badge bg-soft-primary">{{ $jobTypeLabels[$job->job_type] ?? $job->job_type }}</span>
                                @endif
                                @if($job->work_location_type)
                                    <span class="badge bg-soft-success">{{ $workLabels[$job->work_location_type] ?? $job->work_location_type }}</span>
                                @endif
                                @if($job->is_night_shift)
                                    <span class="badge bg-soft-warning text-dark">Night shift</span>
                                @endif
                            </div>

                            <dl class="row small mb-3 g-2 job-apply-meta">
                                @if(!empty($job->job_department))
                                    <dt class="col-sm-4 text-muted mb-0">Department</dt>
                                    <dd class="col-sm-8 mb-0">{{ $job->job_department }}</dd>
                                @endif
                                @if(!is_null($job->experience_years))
                                    <dt class="col-sm-4 text-muted mb-0">Experience</dt>
                                    <dd class="col-sm-8 mb-0">{{ $job->experience_years }} {{ $job->experience_years === 1 ? 'year' : 'years' }} (min.)</dd>
                                @endif
                                <dt class="col-sm-4 text-muted mb-0">Pay</dt>
                                <dd class="col-sm-8 mb-0">
                                    {{ $payTypeLabels[$job->pay_type] ?? ($job->pay_type ?? '—') }}
                                    @if($salaryDisplay)
                                        <span class="d-block text-dark mt-1">{{ $salaryDisplay }}</span>
                                    @endif
                                </dd>
                                @if($job->joining_fee_required)
                                    <dt class="col-sm-4 text-muted mb-0">Joining</dt>
                                    <dd class="col-sm-8 mb-0 text-warning">Joining fee may apply — confirm with employer.</dd>
                                @endif
                                <dt class="col-sm-4 text-muted mb-0">Posted</dt>
                                <dd class="col-sm-8 mb-0">{{ $job->created_at->format('d M Y') }}</dd>
                            </dl>

                            @if(count($requiredSkillsList) > 0)
                                <h6 class="fw-600 mb-2 small text-uppercase text-muted">Required skills</h6>
                                <div class="d-flex flex-wrap gap-1 mb-3">
                                    @foreach($requiredSkillsList as $skill)
                                        <span class="badge bg-light text-dark border fw-normal">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($job->perks))
                                <h6 class="fw-600 mb-2 small text-uppercase text-muted">Perks &amp; benefits</h6>
                                <div class="text-muted small mb-3" style="white-space: pre-wrap;">{{ $job->perks }}</div>
                            @endif

                            <hr>
                            <h6 class="fw-600 mb-2">Job description</h6>
                            @if($job->description)
                                <div class="text-muted small mb-0 job-description-apply" style="max-height: 360px; overflow-y: auto; white-space: pre-wrap;">{{ $job->description }}</div>
                            @else
                                <p class="text-muted small mb-0">No description provided.</p>
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
                                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="10-digit mobile" required>
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="headline" class="form-label">Current role / Headline <span class="text-danger">*</span></label>
                                            <input type="text" name="headline" id="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline', $profile?->headline ?? '') }}" placeholder="e.g. Business Intelligence Analyst" required>
                                            @error('headline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="education" class="form-label">Education <span class="text-danger">*</span></label>
                                            <input type="text" name="education" id="education" class="form-control @error('education') is-invalid @enderror" value="{{ old('education', $profile?->education ?? '') }}" placeholder="e.g. BE/B.Tech, College Name" required>
                                            @error('education')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="experience_years" class="form-label">Experience (years) <span class="text-danger">*</span></label>
                                            <input type="number" name="experience_years" id="experience_years" class="form-control @error('experience_years') is-invalid @enderror" value="{{ old('experience_years', $profile?->experience_years ?? '') }}" min="0" max="50" placeholder="0" required>
                                            @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $profile?->location ?? '') }}" placeholder="e.g. Gurgaon" required>
                                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="expected_salary" class="form-label">Expected salary <span class="text-danger">*</span></label>
                                            <input type="text" name="expected_salary" id="expected_salary" class="form-control @error('expected_salary') is-invalid @enderror" value="{{ old('expected_salary', $profile?->expected_salary ?? '') }}" placeholder="e.g. 4.7 L" required>
                                            @error('expected_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="skills" class="form-label">Skills (comma-separated) <span class="text-danger">*</span></label>
                                            <textarea name="skills" id="skills" class="form-control @error('skills') is-invalid @enderror" rows="2" placeholder="e.g. SQL, Excel, Python" required>{{ old('skills', $profile?->skills ?? '') }}</textarea>
                                            @error('skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-muted small text-uppercase mb-3">Resume & message</h6>
                                    @if($resumes->count() > 0)
                                        <div class="mb-3">
                                            <label for="resume_id" class="form-label">Attach resume <span class="text-danger">*</span></label>
                                            <select name="resume_id" id="resume_id" class="form-select @error('resume_id') is-invalid @enderror" required>
                                                <option value="">Select resume</option>
                                                @foreach($resumes as $r)
                                                    <option value="{{ $r->id }}" {{ old('resume_id', $resumes->first()?->id) == $r->id ? 'selected' : '' }}>
                                                        {{ $r->file_name ?? 'Resume #' . $r->id }}{{ $r->ai_score ? ' (' . $r->ai_score . '% ATS)' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('resume_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
