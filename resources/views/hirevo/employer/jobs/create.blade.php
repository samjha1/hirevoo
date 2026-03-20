@extends('layouts.employer')

@section('title', 'Post a job')
@section('header_title', 'Post a new job')

@section('content')
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

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h2 class="h5 mb-0 fw-600 text-dark">Post a new job</h2>
            <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-format-list-bulleted me-1"></i>Use Templates</a>
        </div>

        <form method="POST" action="{{ route('employer.jobs.store') }}" id="job-form">
            @csrf

            {{-- Job details --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1 fw-600">Job details</h5>
                    <p class="text-muted small mb-4">We use this information to find the best candidates for the job. <span class="text-danger">*</span> Marked fields are mandatory.</p>

                    <div class="mb-4">
                        <label class="form-label fw-500">Company you're hiring for <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-dark fw-medium">{{ $companyName ?: '—' }}</span>
                            <a href="{{ route('employer.profile') }}" class="btn btn-link btn-sm p-0 text-success">Change</a>
                        </div>
                        @if(empty($companyName))
                            <p class="small text-muted mb-0 mt-1">Set your company name in <a href="{{ route('employer.profile') }}">Company Profile</a>.</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label fw-500">Job title / Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Accountant" required>
                        <p class="small text-muted mt-1 mb-0"><i class="mdi mdi-information-outline me-1"></i>Only similar job title edits are allowed after publishing.</p>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-500">Type of job <span class="text-danger">*</span></label>
                        <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Job type">
                            <input type="radio" class="btn-check" name="job_type" id="jt_full_time" value="full_time" {{ old('job_type') === 'full_time' ? 'checked' : '' }} required>
                            <label class="btn btn-outline-primary" for="jt_full_time">Full time</label>
                            <input type="radio" class="btn-check" name="job_type" id="jt_part_time" value="part_time" {{ old('job_type') === 'part_time' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="jt_part_time">Part time</label>
                            <input type="radio" class="btn-check" name="job_type" id="jt_contract" value="contract" {{ old('job_type') === 'contract' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="jt_contract">Contract</label>
                            <input type="radio" class="btn-check" name="job_type" id="jt_internship" value="internship" {{ old('job_type') === 'internship' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="jt_internship">Internship</label>
                            <input type="radio" class="btn-check" name="job_type" id="jt_temporary" value="temporary" {{ old('job_type') === 'temporary' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="jt_temporary">Temporary</label>
                            <input type="radio" class="btn-check" name="job_type" id="jt_other" value="other" {{ old('job_type') === 'other' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary" for="jt_other">Other</label>
                        </div>
                        @error('job_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_night_shift" value="1" id="is_night_shift" {{ old('is_night_shift') ? 'checked' : '' }}>
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
                            <option value="office" {{ old('work_location_type') === 'office' ? 'selected' : '' }}>On-site</option>
                            <option value="remote" {{ old('work_location_type') === 'remote' ? 'selected' : '' }}>Remote</option>
                            <option value="hybrid" {{ old('work_location_type') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        @error('work_location_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label for="location" class="form-label fw-500">Address / Area</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location') }}" placeholder="e.g. Sector 63, Noida or Remote">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                            <option value="fixed" {{ old('pay_type') === 'fixed' ? 'selected' : '' }}>Fixed salary</option>
                            <option value="hourly" {{ old('pay_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                            <option value="negotiable" {{ old('pay_type') === 'negotiable' ? 'selected' : '' }}>Negotiable</option>
                            <option value="not_disclosed" {{ old('pay_type') === 'not_disclosed' ? 'selected' : '' }}>Not disclosed</option>
                            <option value="other" {{ old('pay_type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('pay_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="salary_amount_wrap">
                        <label for="salary_amount" class="form-label fw-500">Salary amount <small class="text-muted">(optional)</small></label>
                        <input type="text"
                               class="form-control @error('salary_amount') is-invalid @enderror"
                               id="salary_amount"
                               name="salary_amount"
                               value="{{ old('salary_amount') }}"
                               placeholder="e.g. ₹25-30 LPA, ₹40/hr, 50,000/month">
                        @error('salary_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <p class="small text-muted mt-1 mb-0">Add salary if you want candidates to see it. If you select “Not disclosed”, you can leave this blank.</p>
                    </div>

                    <div class="mb-4">
                        <label for="perks" class="form-label fw-500">Do you offer any additional perks?</label>
                        <textarea class="form-control" id="perks" name="perks" rows="3" placeholder="e.g. Health insurance, Flexible hours, Work from home">{{ old('perks') }}</textarea>
                        <p class="small text-muted mt-1 mb-0">Add perks that might attract candidates.</p>
                        @error('perks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label class="form-label fw-500">Is there any joining fee or deposit required from the candidate? <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="joining_fee_required" id="joining_no" value="0" {{ old('joining_fee_required', '0') === '0' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="joining_no">No</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="joining_fee_required" id="joining_yes" value="1" {{ old('joining_fee_required') === '1' ? 'checked' : '' }}>
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
                            <i class="mdi mdi-auto-fix me-1"></i>Generate with AI
                        </button>
                    </div>
                    <p class="text-muted small mb-3">Write the role overview, responsibilities, and requirements.</p>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="8" placeholder="About the role, key responsibilities, required skills...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div id="generate-ai-status" class="small mt-2 d-none"></div>
                </div>
            </div>

            {{-- Publish options & Submit --}}
            <div class="card employer-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <label for="status" class="form-label mb-0 fw-500 small text-muted">Publish as</label>
                        <select class="form-select form-select-sm status-select" id="status" name="status" style="max-width: 200px;">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                        <span class="small text-muted">Active = visible to candidates</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-outline-secondary"
                                onclick="document.getElementById('status').value='draft'">Save Draft</button>
                        <button type="submit" class="btn btn-primary"
                                onclick="document.getElementById('status').value='active'">Continue & post job</button>
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
            var salaryInput = document.getElementById('salary_amount');
            if (!payType || !wrap || !salaryInput) return;

            function toggleSalary() {
                var isNotDisclosed = payType.value === 'not_disclosed';
                wrap.style.display = isNotDisclosed ? 'none' : '';
                salaryInput.disabled = isNotDisclosed;
                if (isNotDisclosed) salaryInput.value = '';
            }

            payType.addEventListener('change', toggleSalary);
            toggleSalary();
        })();
    </script>
    @endpush
@endsection
