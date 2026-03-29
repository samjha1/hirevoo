<div class="card employer-card employer-job-card mb-3">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0 flex-grow-1">
                        <h6 class="mb-2 fw-600 text-dark employer-job-card-title text-break">{{ $job->title }}</h6>
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge job-card-status {{ $job->status === 'active' ? 'bg-success' : ($job->status === 'closed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($job->status) }}</span>
                            @if(!empty($job->job_department))
                                <span class="badge bg-light text-dark border">{{ $job->job_department }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm employer-job-card-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Job actions">
                            <i class="mdi mdi-dots-vertical employer-job-card-menu-icon" aria-hidden="true"></i>
                            <span class="employer-job-card-menu-fallback" aria-hidden="true">&#8942;</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('employer.jobs.edit', $job) }}"><i class="mdi mdi-pencil-outline me-2"></i>Edit job</a></li>
                            <li>
                                <form method="POST" action="{{ route('employer.jobs.duplicate', $job) }}" class="d-inline">@csrf<button type="submit" class="dropdown-item"><i class="mdi mdi-content-copy me-2"></i>Duplicate</button></form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job?');">@csrf @method('DELETE')<button type="submit" class="dropdown-item text-danger"><i class="mdi mdi-delete-outline me-2"></i>Delete job</button></form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="employer-job-card-meta-stack">
                    <div class="employer-job-meta-row">
                        <i class="mdi mdi-map-marker-outline employer-job-meta-icon" aria-hidden="true"></i>
                        <div class="employer-job-meta-body">
                            @forelse($job->location_display_lines as $idx => $line)
                                <div class="employer-job-meta-line @if($idx === 0) employer-job-meta-line--primary @endif">{{ $line }}</div>
                            @empty
                                <div class="employer-job-meta-line text-muted">—</div>
                            @endforelse
                        </div>
                    </div>
                    @if($job->formatted_salary_summary)
                        <div class="employer-job-meta-row">
                            <i class="mdi mdi-cash-multiple employer-job-meta-icon" aria-hidden="true"></i>
                            <div class="employer-job-meta-body">
                                <div class="employer-job-meta-line employer-job-meta-line--primary">{{ $job->formatted_salary_summary }}</div>
                            </div>
                        </div>
                    @endif
                    @if(!is_null($job->experience_years))
                        <div class="employer-job-meta-row">
                            <i class="mdi mdi-briefcase-clock-outline employer-job-meta-icon" aria-hidden="true"></i>
                            <div class="employer-job-meta-body">
                                <div class="employer-job-meta-line">
                                    {{ $job->experience_years }} {{ $job->experience_years === 1 ? 'year' : 'years' }} experience
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="employer-job-meta-row employer-job-meta-row--muted">
                        <i class="mdi mdi-calendar-clock employer-job-meta-icon" aria-hidden="true"></i>
                        <div class="employer-job-meta-body">
                            <div class="employer-job-meta-line">Posted {{ $job->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-auto">
                <div class="employer-job-card-actions">
                    <div class="text-lg-end mb-2">
                        <span class="employer-job-applied-label">Applications</span>
                        <strong class="employer-job-applied-count d-block d-lg-inline">{{ $job->applications_count }}</strong>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                        <a href="{{ route('employer.jobs.applications', $job) }}" class="btn btn-primary btn-sm">View applications</a>
                        @if($job->status === 'closed')
                            <form method="POST" action="{{ route('employer.jobs.repost', $job) }}" class="d-inline">@csrf<button type="submit" class="btn btn-success btn-sm">Repost now</button></form>
                        @endif
                        <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                    </div>
                    @if($job->status === 'closed')
                        <p class="employer-job-card-hint small mb-0 mt-2 text-lg-end">Repost to receive new candidates.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
