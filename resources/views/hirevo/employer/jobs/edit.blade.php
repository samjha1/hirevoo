@extends('layouts.employer')

@section('title', 'Edit job')
@section('header_title', 'Edit job')

@section('content')
    @php
        $locationDecoded = is_string($job->location) ? json_decode($job->location, true) : null;
        $locationData = is_array($locationDecoded) ? $locationDecoded : [];
    @endphp
    <div class="post-job-page">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('employer.jobs.update', $job) }}" id="job-form">
            @csrf
            @method('PUT')

            {{-- Job details --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1 fw-600">Job details</h5>
                    <p class="text-muted small mb-4">We use this information to find the best candidates for the job. <span class="text-danger">*</span> Marked fields are mandatory.</p>

                    <div class="mb-4">
                        <label class="form-label fw-500">Company you're hiring for <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-dark fw-medium">{{ $job->company_name ?: (auth()->user()->referrerProfile->company_name ?? '—') }}</span>
                            <a href="{{ route('employer.profile') }}" class="btn btn-link btn-sm p-0 text-primary">Change</a>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="job_department" class="form-label fw-500">Job department <span class="text-danger">*</span></label>
                        <select class="form-select @error('job_department') is-invalid @enderror" id="job_department" name="job_department" required>
                            <option value="">Select department</option>
                            <option value="Sales" {{ old('job_department', $job->job_department) === 'Sales' ? 'selected' : '' }}>Sales</option>
                            <option value="Marketing" {{ old('job_department', $job->job_department) === 'Marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="Human Resources" {{ old('job_department', $job->job_department) === 'Human Resources' ? 'selected' : '' }}>Human Resources</option>
                            <option value="Finance" {{ old('job_department', $job->job_department) === 'Finance' ? 'selected' : '' }}>Finance</option>
                            <option value="Engineering" {{ old('job_department', $job->job_department) === 'Engineering' ? 'selected' : '' }}>Engineering</option>
                            <option value="Operations" {{ old('job_department', $job->job_department) === 'Operations' ? 'selected' : '' }}>Operations</option>
                            <option value="Customer Support" {{ old('job_department', $job->job_department) === 'Customer Support' ? 'selected' : '' }}>Customer Support</option>
                            <option value="Legal" {{ old('job_department', $job->job_department) === 'Legal' ? 'selected' : '' }}>Legal</option>
                            <option value="Other" {{ old('job_department', $job->job_department) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('job_department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label fw-500">Job title / Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $job->title) }}" required>
                        <p class="small text-muted mt-1 mb-0">Only similar job title edits are allowed after publishing.</p>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="job_type" class="form-label fw-500">Type of job <span class="text-danger">*</span></label>
                        <select class="form-select @error('job_type') is-invalid @enderror" id="job_type" name="job_type" required>
                            <option value="">Select type</option>
                            <option value="full_time" {{ old('job_type', $job->job_type) === 'full_time' ? 'selected' : '' }}>Full-time</option>
                            <option value="part_time" {{ old('job_type', $job->job_type) === 'part_time' ? 'selected' : '' }}>Part-time</option>
                            <option value="contract" {{ old('job_type', $job->job_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                            <option value="internship" {{ old('job_type', $job->job_type) === 'internship' ? 'selected' : '' }}>Internship</option>
                            <option value="temporary" {{ old('job_type', $job->job_type) === 'temporary' ? 'selected' : '' }}>Temporary</option>
                            <option value="volunteer" {{ old('job_type', $job->job_type) === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                            <option value="other" {{ old('job_type', $job->job_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('job_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_night_shift" value="1" id="is_night_shift" {{ old('is_night_shift', $job->is_night_shift) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_night_shift">This is a night shift job</label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Work location --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1 fw-600">Location</h5>
                    <p class="text-muted small mb-4">Let candidates know where they will be working from.</p>

                    <div class="mb-4">
                        <label for="work_location_type" class="form-label fw-500">Work location type <span class="text-danger">*</span></label>
                        <select class="form-select @error('work_location_type') is-invalid @enderror" id="work_location_type" name="work_location_type" required>
                            <option value="">Select</option>
                            <option value="office" {{ old('work_location_type', $job->work_location_type) === 'office' ? 'selected' : '' }}>On-site</option>
                            <option value="remote" {{ old('work_location_type', $job->work_location_type) === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ old('work_location_type', $job->work_location_type) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        @error('work_location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="location_area" class="form-label fw-500">Area / Address</label>
                            <input type="text" class="form-control @error('location_area') is-invalid @enderror" id="location_area" name="location_area" value="{{ old('location_area', $locationData['area'] ?? '') }}" placeholder="e.g. Sector 63">
                            @error('location_area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="location_city" class="form-label fw-500">City</label>
                            <input type="text" class="form-control @error('location_city') is-invalid @enderror" id="location_city" name="location_city" value="{{ old('location_city', $locationData['city'] ?? '') }}" placeholder="e.g. Noida">
                            @error('location_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="location_state" class="form-label fw-500">State</label>
                            <input type="text" class="form-control @error('location_state') is-invalid @enderror" id="location_state" name="location_state" value="{{ old('location_state', $locationData['state'] ?? '') }}" placeholder="e.g. Uttar Pradesh">
                            @error('location_state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="location_country" class="form-label fw-500">Country</label>
                            <input type="text" class="form-control @error('location_country') is-invalid @enderror" id="location_country" name="location_country" value="{{ old('location_country', $locationData['country'] ?? '') }}" placeholder="e.g. India">
                            @error('location_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="location_pincode" class="form-label fw-500">Pincode</label>
                            <input type="text" class="form-control @error('location_pincode') is-invalid @enderror" id="location_pincode" name="location_pincode" value="{{ old('location_pincode', $locationData['pincode'] ?? '') }}" placeholder="e.g. 201301">
                            @error('location_pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compensation --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1 fw-600">Compensation</h5>
                    <p class="text-muted small mb-4">Job postings with the right salary & incentives help you find the right candidates.</p>

                    <div class="mb-4">
                        <label for="pay_type" class="form-label fw-500">What is the pay type? <span class="text-danger">*</span></label>
                        <select class="form-select @error('pay_type') is-invalid @enderror" id="pay_type" name="pay_type" required>
                            <option value="">Select</option>
                            <option value="fixed" {{ old('pay_type', $job->pay_type) === 'fixed' ? 'selected' : '' }}>Fixed salary</option>
                            <option value="hourly" {{ old('pay_type', $job->pay_type) === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="negotiable" {{ old('pay_type', $job->pay_type) === 'negotiable' ? 'selected' : '' }}>Negotiable</option>
                            <option value="not_disclosed" {{ old('pay_type', $job->pay_type) === 'not_disclosed' ? 'selected' : '' }}>Not disclosed</option>
                            <option value="other" {{ old('pay_type', $job->pay_type) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('pay_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="salary_amount_wrap">
                        <label class="form-label fw-500">Salary amount <small class="text-muted">(optional)</small></label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="number"
                                       class="form-control @error('salary_min') is-invalid @enderror"
                                       id="salary_min"
                                       name="salary_min"
                                       min="0"
                                       step="1"
                                       value="{{ old('salary_min', $job->salary_min) }}"
                                       placeholder="Minimum salary">
                                @error('salary_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <input type="number"
                                       class="form-control @error('salary_max') is-invalid @enderror"
                                       id="salary_max"
                                       name="salary_max"
                                       min="0"
                                       step="1"
                                       value="{{ old('salary_max', $job->salary_max) }}"
                                       placeholder="Maximum salary">
                                @error('salary_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <p class="small text-muted mt-1 mb-0">Add salary if you want candidates to see it. If you select “Not disclosed”, you can leave this blank.</p>
                    </div>

                    <div class="mb-4">
                        <label for="experience_years" class="form-label fw-500">Experience required (years)</label>
                        <input type="number"
                               class="form-control @error('experience_years') is-invalid @enderror"
                               id="experience_years"
                               name="experience_years"
                               min="0"
                               step="1"
                               value="{{ old('experience_years', $job->experience_years) }}"
                               placeholder="e.g. 2">
                        @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="perks" class="form-label fw-500">Do you offer any additional perks?</label>
                        <textarea class="form-control" id="perks" name="perks" rows="3" placeholder="e.g. Health insurance, Flexible hours">{{ old('perks', $job->perks) }}</textarea>
                        @error('perks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label fw-500">Is there any joining fee or deposit required from the candidate? <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="joining_fee_required" id="joining_no" value="0" {{ old('joining_fee_required', $job->joining_fee_required ? '1' : '0') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="joining_no">No</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="joining_fee_required" id="joining_yes" value="1" {{ old('joining_fee_required', $job->joining_fee_required ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="joining_yes">Yes</label>
                            </div>
                        </div>
                        @error('joining_fee_required')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Job description --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h5 class="card-title mb-0 fw-600">Job description</h5>
                        <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none" id="generate-ai-btn">
                            <i class="mdi mdi-auto-fix me-1"></i>Regenerate with AI
                        </button>
                    </div>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8">{{ old('description', $job->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div id="generate-ai-status" class="small mt-2 d-none"></div>
                </div>
            </div>

            {{-- Status & Submit --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <label for="status" class="form-label mb-0 fw-500">Status</label>
                        <select class="form-select form-select-sm status-select" id="status" name="status">
                            <option value="active" {{ old('status', $job->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status', $job->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="closed" {{ old('status', $job->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">Update job</button>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('generate-ai-btn');
            var titleInput = document.getElementById('title');
            var descInput = document.getElementById('description');
            var statusEl = document.getElementById('generate-ai-status');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (!btn || !titleInput || !descInput || !statusEl) return;

            btn.addEventListener('click', function () {
                var title = (titleInput.value || '').trim();
                if (!title) {
                    statusEl.textContent = 'Enter a job title first.';
                    statusEl.className = 'small mt-2 text-warning';
                    statusEl.classList.remove('d-none');
                    return;
                }
                btn.disabled = true;
                statusEl.textContent = 'Generating...';
                statusEl.className = 'small mt-2 text-muted';
                statusEl.classList.remove('d-none');

                var body = new FormData();
                body.append('title', title);
                body.append('_token', csrf ? csrf.getAttribute('content') : '');

                fetch('{{ route("employer.jobs.generate-description") }}', {
                    method: 'POST',
                    body: body,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                .then(function (r) {
                    return r.json().then(function (data) {
                        if (!r.ok) throw new Error(data.error || 'Could not generate description');
                        return data;
                    });
                })
                .then(function (data) {
                    if (data.description) {
                        descInput.value = data.description;
                        statusEl.textContent = 'Description generated. You can edit it below.';
                        statusEl.className = 'small mt-2 text-success';
                    } else {
                        throw new Error(data.error || 'No description returned');
                    }
                })
                .catch(function (err) {
                    var msg = err.message || 'Request failed. Try again or write the description manually.';
                    if (msg.indexOf('429') !== -1 || msg.toLowerCase().indexOf('quota') !== -1 || msg.toLowerCase().indexOf('billing') !== -1) {
                        msg = 'AI usage limit reached. Write the job description below, or check your OpenAI plan and billing.';
                    }
                    statusEl.textContent = msg;
                    statusEl.className = 'small mt-2 text-danger';
                })
                .finally(function () {
                    btn.disabled = false;
                });
            });
        })();

        // Hide/disable salary_amount when pay_type is "not_disclosed".
        (function () {
            var payType = document.getElementById('pay_type');
            var wrap = document.getElementById('salary_amount_wrap');
            var salaryMinInput = document.getElementById('salary_min');
            var salaryMaxInput = document.getElementById('salary_max');
            if (!payType || !wrap || !salaryMinInput || !salaryMaxInput) return;

            function toggleSalary() {
                var isNotDisclosed = payType.value === 'not_disclosed';
                wrap.style.display = isNotDisclosed ? 'none' : '';
                salaryMinInput.disabled = isNotDisclosed;
                salaryMaxInput.disabled = isNotDisclosed;
                if (isNotDisclosed) {
                    salaryMinInput.value = '';
                    salaryMaxInput.value = '';
                }
            }

            payType.addEventListener('change', toggleSalary);
            toggleSalary();
        })();
    </script>
    @endpush
@endsection
