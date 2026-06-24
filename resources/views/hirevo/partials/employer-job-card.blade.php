@php
    $jobMatchScores = $jobMatchScores ?? [];
    $companyName = $job->displayCompanyName();
    $initialRaw = trim($companyName);
    $initial = $initialRaw !== '' ? strtoupper(mb_substr($initialRaw, 0, 1)) : '?';
    $jobTypeLabels = [
        'full_time' => 'Full-time',
        'part_time' => 'Part-time',
        'contract' => 'Contract',
        'internship' => 'Internship',
        'temporary' => 'Temporary',
        'volunteer' => 'Volunteer',
        'other' => 'Other',
    ];
    $jobTypeLabel = $job->job_type ? ($jobTypeLabels[$job->job_type] ?? $job->job_type) : null;
    $workTypeLabels = ['office' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid'];
    $workTypeLabel = $job->work_location_type ? ($workTypeLabels[$job->work_location_type] ?? $job->work_location_type) : null;
@endphp
<div class="jo-job-card-wrap">
    <article class="card border-0 jo-job-card">
        <div class="card-body p-3 p-md-4">
            <div class="jo-job-card-layout">
                <div class="jo-job-card__brand">
                    <div class="jo-co-avatar" aria-hidden="true">{{ $initial }}</div>
                </div>

                <div class="jo-job-card__content">
                    <header class="jo-job-card__head">
                        <h2 class="h5 mb-1">
                            <a href="{{ route('job-openings.apply', $job) }}" class="jo-job-title d-block text-dark text-decoration-none">{{ $job->title }}</a>
                        </h2>
                        <p class="jo-job-card__company">{{ $companyName }}</p>
                    </header>

                    <div class="jo-job-card__tags">
                        @if(isset($jobMatchScores[$job->id]))
                            <span class="jo-fit-pill" title="Keyword overlap vs your resume">{{ $jobMatchScores[$job->id] }}% fit</span>
                        @endif
                        @if($job->formatted_location)
                            <span class="jo-meta-pill"><i class="uil uil-map-marker text-muted me-1"></i>{{ $job->formatted_location }}</span>
                        @endif
                        @if($jobTypeLabel)
                            <span class="jo-meta-pill">{{ $jobTypeLabel }}</span>
                        @endif
                        @if($workTypeLabel)
                            <span class="jo-meta-pill jo-meta-pill--accent">{{ $workTypeLabel }}</span>
                        @endif
                        @if($job->formatted_salary_summary)
                            <span class="jo-meta-pill"><i class="uil uil-money-stack text-muted me-1"></i>{{ $job->formatted_salary_summary }}</span>
                        @endif
                        @if($job->experience_years !== null && $job->experience_years !== '')
                            <span class="jo-meta-pill">{{ (int) $job->experience_years === 0 ? 'Fresher friendly' : ((int) $job->experience_years . '+ yrs exp.') }}</span>
                        @endif
                        @if($job->displayApplicationsCount() > 0)
                            <span class="jo-meta-pill">{{ number_format($job->displayApplicationsCount()) }} applied</span>
                        @endif
                    </div>

                    <p class="text-muted mb-0 small lh-base jo-job-desc-clamp">{{ Str::limit(strip_tags($job->description), 180) ?: '—' }}</p>
                    @if($job->created_at)
                        <p class="text-muted mb-0 mt-2 jo-job-posted"><i class="uil uil-clock me-1"></i>Posted {{ $job->created_at->diffForHumans() }}</p>
                    @endif
                </div>

                <div class="jo-job-card__actions">
                    @if(in_array($job->id, $appliedIds ?? []))
                        <span class="badge bg-success rounded-pill px-3 py-2 align-self-lg-end">Applied</span>
                    @else
                        <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm rounded-pill jo-apply-btn d-inline-flex align-items-center justify-content-center">
                            {{ $job->apply_link ? 'Apply on company site' : 'Apply now' }}
                        </a>
                        <a href="{{ route('referral.intent', ['source' => 'job_openings', 'employer_job_id' => $job->id]) }}" class="jo-referral-nudge" role="note">
                            <span class="jo-referral-nudge__icon" aria-hidden="true"><i class="uil uil-gift"></i></span>
                            <span class="jo-referral-nudge__text">
                                <span class="jo-referral-nudge__label">Get referral</span>
                                <span class="jo-referral-nudge__stat">Up to <strong>+{{ random_int(72, 88) }}%</strong> better odds to get hired</span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </article>
</div>
