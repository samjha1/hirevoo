@php
    $departments = config('hirevo.employer_job_departments', []);
    $currentDept = old('job_department', $selectedDepartment ?? '');
@endphp
<div class="mb-4">
    <label for="job_department" class="form-label fw-500">Job department <span class="text-danger">*</span></label>
    <select class="form-select @error('job_department') is-invalid @enderror" id="job_department" name="job_department" required>
        <option value="">Select department</option>
        @foreach($departments as $dept)
            <option value="{{ $dept }}" @selected($currentDept === $dept)>{{ $dept }}</option>
        @endforeach
    </select>
    <p class="small text-muted mt-1 mb-0">Skill suggestions below update based on this department.</p>
    @error('job_department')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
