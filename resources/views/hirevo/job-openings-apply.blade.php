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

                            @if(!empty($job->apply_link))
                                <div class="alert alert-info d-flex align-items-start gap-2 mb-4" role="alert">
                                    <i class="uil uil-info-circle fs-18 mt-1"></i>
                                    <div class="small">
                                        <div class="fw-600">You’ll be redirected after submission</div>
                                        <div class="opacity-75">After you submit, we’ll save your application on Hirevo and redirect you to the employer’s website to finish the application.</div>
                                    </div>
                                </div>
                            @endif

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
                                    <h6 class="text-muted small text-uppercase mb-3">Basic details</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="full_name" class="form-label">Full name <span class="text-danger">*</span></label>
                                            <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', auth()->user()->name ?? '') }}" autocomplete="name" required>
                                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <p class="text-muted small mt-1 mb-0">Prefilled from your profile; you can edit if needed.</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="apply_email" class="form-label">Email address</label>
                                            <input type="email" class="form-control bg-light" id="apply_email" value="{{ auth()->user()->email ?? '' }}" readonly autocomplete="email">
                                            <p class="text-muted small mt-1 mb-0">Shown for confirmation. To change your email, update your account settings.</p>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="10-digit mobile" required>
                                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="linkedin_url" class="form-label">LinkedIn profile URL</label>
                                            <input type="text" name="linkedin_url" id="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url', $profile?->linkedin_url ?? '') }}" placeholder="https://www.linkedin.com/in/your-profile">
                                            @error('linkedin_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label for="headline" class="form-label">Current role / Headline <span class="text-danger">*</span></label>
                                            <input type="text" name="headline" id="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline', $profile?->headline ?? '') }}" placeholder="e.g. Business Intelligence Analyst" required>
                                            @error('headline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="current_company" class="form-label">Current company / organisation</label>
                                            <input type="text" name="current_company" id="current_company" class="form-control @error('current_company') is-invalid @enderror" value="{{ old('current_company', $profile?->current_company ?? '') }}" placeholder="e.g. Acme Corp">
                                            @error('current_company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="education" class="form-label">Education <span class="text-danger">*</span></label>
                                            @php $eduVal = old('education', $profile?->education); @endphp
                                            <select name="education" id="education" class="form-select @error('education') is-invalid @enderror" required>
                                                <option value="" disabled {{ $eduVal === null || $eduVal === '' || ! in_array($eduVal, $educationDegrees, true) ? 'selected' : '' }}>Select qualification</option>
                                                @foreach($educationDegrees as $deg)
                                                    <option value="{{ $deg }}" {{ (string) $eduVal === (string) $deg ? 'selected' : '' }}>{{ $deg }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-muted small mt-1 mb-0">If your exact degree is not listed, choose the closest option or &ldquo;Other&rdquo;.</p>
                                            @error('education')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="experience_years" class="form-label">Experience (years) <span class="text-danger">*</span></label>
                                            <input type="number" name="experience_years" id="experience_years" class="form-control @error('experience_years') is-invalid @enderror" value="{{ old('experience_years', $profile?->experience_years ?? '0') }}" min="0" max="50" required>
                                            @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label for="experience_months" class="form-label">Experience (months) <span class="text-danger">*</span></label>
                                            <input type="number" name="experience_months" id="experience_months" class="form-control @error('experience_months') is-invalid @enderror" value="{{ old('experience_months', $profile?->experience_months ?? '0') }}" min="0" max="11" required>
                                            @error('experience_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <p class="text-muted small mt-1 mb-0">e.g. 2 years + 6 months</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="notice_period" class="form-label">Notice period <span class="text-danger">*</span></label>
                                            <select name="notice_period" id="notice_period" class="form-select @error('notice_period') is-invalid @enderror" required>
                                                <option value="" disabled {{ old('notice_period') ? '' : 'selected' }}>Select notice period</option>
                                                @foreach($noticePeriods as $val => $label)
                                                    <option value="{{ $val }}" {{ old('notice_period') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('notice_period')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="current_salary" class="form-label">Current salary / CTC</label>
                                            <input type="text" name="current_salary" id="current_salary" class="form-control @error('current_salary') is-invalid @enderror" value="{{ old('current_salary', $profile?->current_salary ?? '') }}" placeholder="e.g. 8 LPA (optional)">
                                            @error('current_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Expected salary <span class="text-danger">*</span></label>
                                            <div class="row g-2">
                                                <div class="col-5">
                                                    <select name="expected_salary_currency" class="form-select @error('expected_salary_currency') is-invalid @enderror" aria-label="Currency" required>
                                                        @foreach($salaryCurrencies as $cur)
                                                            <option value="{{ $cur }}" {{ old('expected_salary_currency', $profile?->expected_salary_currency ?? 'INR') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('expected_salary_currency')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-7">
                                                    <input type="text" name="expected_salary" id="expected_salary" class="form-control @error('expected_salary') is-invalid @enderror" value="{{ old('expected_salary', $profile?->expected_salary ?? '') }}" placeholder="Amount" required>
                                                    @error('expected_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-12">
                                                    <div class="btn-group" role="group" aria-label="Expected salary period">
                                                        <input type="radio" class="btn-check" name="expected_salary_period" id="exp_per_annum" value="per_annum" {{ old('expected_salary_period', $profile?->expected_salary_period ?? 'per_annum') === 'per_annum' ? 'checked' : '' }} required>
                                                        <label class="btn btn-outline-secondary btn-sm" for="exp_per_annum">Per annum</label>
                                                        <input type="radio" class="btn-check" name="expected_salary_period" id="exp_per_month" value="per_month" {{ old('expected_salary_period', $profile?->expected_salary_period ?? 'per_annum') === 'per_month' ? 'checked' : '' }}>
                                                        <label class="btn btn-outline-secondary btn-sm" for="exp_per_month">Per month</label>
                                                    </div>
                                                    @error('expected_salary_period')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="location" class="form-label">Location (current) <span class="text-danger">*</span></label>
                                            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $profile?->location ?? '') }}" placeholder="e.g. Gurgaon" required>
                                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label for="preferred_job_location" class="form-label">Preferred job location <span class="text-danger">*</span></label>
                                            <input type="text" name="preferred_job_location" id="preferred_job_location" class="form-control @error('preferred_job_location') is-invalid @enderror" value="{{ old('preferred_job_location', $profile?->preferred_job_location ?? $profile?->location ?? '') }}" placeholder="Where you are open to working" required>
                                            @error('preferred_job_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

                                <div class="mb-4 pt-2 border-top">
                                    <h6 class="text-muted small text-uppercase mb-3">Consent &amp; submit</h6>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input @error('info_accurate') is-invalid @enderror" type="checkbox" name="info_accurate" id="info_accurate" value="1" {{ old('info_accurate') ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="info_accurate">
                                            I confirm the information provided is accurate to the best of my knowledge. <span class="text-danger">*</span>
                                        </label>
                                        @error('info_accurate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="alert alert-light border small mb-0" role="note">
                                        <p class="mb-2 fw-600 text-dark">How we use your data for this application</p>
                                        <p class="mb-0 text-muted">Hirevo shares the details on this form and your attached resume <strong>only with the employer who posted this job</strong>, so they can review and process your application. We do not sell your personal data for marketing. Your account credentials and general platform use remain covered by our usual privacy terms.</p>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        {{ !empty($job->apply_link) ? 'Submit & Continue' : 'Submit application' }}
                                    </button>
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
