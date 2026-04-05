@php
    $user = $app->user;
    $profile = $user->candidateProfile;
    $resume = $app->resume;
    $skillsList = [];
    if ($profile && $profile->skills) {
        if (is_string($profile->skills)) {
            $skillsList = array_map('trim', explode(',', $profile->skills));
        } elseif (is_array($profile->skills)) {
            $skillsList = $profile->skills;
        }
    }
    if ($resume && $resume->extracted_skills && is_array($resume->extracted_skills)) {
        $skillsList = array_unique(array_merge($skillsList, $resume->extracted_skills));
    }
    $skillsList = array_values(array_filter($skillsList));
    $skillsShow = 6;
    $skillsVisible = array_slice($skillsList, 0, $skillsShow);
    $skillsMore = count($skillsList) - $skillsShow;
@endphp
<div class="applicant-card card employer-card mb-3">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12 col-lg">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h6 class="applicant-name mb-0 fw-600">{{ $user->name }}</h6>
                        <span class="badge bg-primary" title="Job match score (calculated when they applied)">
                            Match {{ $app->job_match_score !== null ? $app->job_match_score . '%' : '—' }}
                        </span>
                        @if($app->ats_score !== null)
                            <span class="badge bg-secondary" title="Resume ATS score">ATS {{ $app->ats_score }}%</span>
                        @endif
                    </div>
                    <form action="{{ route('employer.applications.status', $app) }}" method="POST" class="d-inline applicant-status-form">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select form-select-sm application-status-select" data-application-id="{{ $app->id }}">
                            @foreach(\App\Models\EmployerJobApplication::statusOptions() as $value => $label)
                                <option value="{{ $value }}" {{ $app->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <p class="text-muted small mb-2">
                    @if($profile && $profile->formattedTotalExperience())
                        <span>{{ $profile->formattedTotalExperience() }}</span>
                    @endif
                    @if($profile && $profile->formattedExpectedSalary())
                        <span class="ms-2">{{ $profile->formattedExpectedSalary() }}</span>
                    @elseif($profile && $profile->expected_salary)
                        <span class="ms-2">{{ $profile->expected_salary }}</span>
                    @endif
                    @if($profile && $profile->preferred_job_location)
                        <span class="ms-2">{{ $profile->preferred_job_location }}</span>
                    @elseif($profile && $profile->location)
                        <span class="ms-2">{{ $profile->location }}</span>
                    @endif
                    @if($app->notice_period)
                        <span class="ms-2">Notice: {{ \App\Models\EmployerJobApplication::noticePeriodOptions()[$app->notice_period] ?? $app->notice_period }}</span>
                    @endif
                    @if(!$profile)
                        <span>{{ $user->email }}</span>
                    @endif
                </p>

                @if($profile && $profile->headline)
                    <p class="applicant-detail mb-1"><span class="detail-label">Current / Latest</span><br>{{ $profile->headline }}</p>
                @endif

                @if($profile && $profile->education)
                    <p class="applicant-detail mb-1"><span class="detail-label">Education</span><br>{{ $profile->education }}</p>
                @endif

                @if($profile && $profile->current_company)
                    <p class="applicant-detail mb-1"><span class="detail-label">Company</span><br>{{ $profile->current_company }}</p>
                @endif

                @if($profile && $profile->location)
                    <p class="applicant-detail mb-1"><span class="detail-label">Current location</span><br>{{ $profile->location }}</p>
                @endif

                @if($profile && $profile->preferred_job_location)
                    <p class="applicant-detail mb-1"><span class="detail-label">Preferred location</span><br>{{ $profile->preferred_job_location }}</p>
                @endif

                @if($profile && $profile->linkedin_url)
                    <p class="applicant-detail mb-1"><span class="detail-label">LinkedIn</span><br><a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" class="text-break">{{ $profile->linkedin_url }}</a></p>
                @endif

                @if(count($skillsList) > 0)
                    <p class="applicant-detail mb-1">
                        <span class="detail-label">Skills</span><br>
                        <span class="applicant-skills">
                            @foreach($skillsVisible as $s)
                                <span class="skill-tag">{{ is_string($s) ? $s : '' }}</span>
                            @endforeach
                            @if($skillsMore > 0)
                                <span class="skill-tag skill-more">+{{ $skillsMore }} more</span>
                            @endif
                        </span>
                    </p>
                @endif

                <p class="applicant-detail mb-0">
                    @if($app->resume)
                        <span class="badge bg-light text-dark border">CV attached</span>
                    @endif
                    <span class="text-muted small ms-2">Active on {{ $app->created_at->format('d M\'y') }}</span>
                </p>
            </div>
            <div class="col-12 col-lg-auto">
                <div class="d-flex flex-column gap-2">
                    @if($app->resume)
                        <a href="{{ route('employer.applications.resume.view', $app) }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">View resume</a>
                        <a href="{{ route('employer.applications.resume', $app) }}" class="btn btn-outline-primary btn-sm">Download resume</a>
                    @else
                        <span class="text-muted small">No resume attached</span>
                    @endif
                    <a href="mailto:{{ $user->email }}" class="btn btn-outline-secondary btn-sm">Contact</a>
                </div>
            </div>
        </div>

        @if($app->job_match_explanation)
            <div class="applicant-cover mt-3 pt-3 border-top">
                <span class="detail-label d-block mb-1">Match summary</span>
                <p class="text-muted small mb-0">{{ $app->job_match_explanation }}</p>
            </div>
        @endif
        @if($app->cover_message)
            <div class="applicant-cover mt-3 pt-3 border-top">
                <span class="detail-label d-block mb-1">Cover message</span>
                <p class="text-muted small mb-0">{{ $app->cover_message }}</p>
            </div>
        @endif
    </div>
</div>
