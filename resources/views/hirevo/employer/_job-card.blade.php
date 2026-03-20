<div class="card employer-card employer-job-card mb-3">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <h6 class="mb-1 fw-600 text-dark">{{ $job->title }}</h6>
                        <span class="badge job-card-status {{ $job->status === 'active' ? 'bg-success' : ($job->status === 'closed' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ ucfirst($job->status) }}</span>
                    </div>
                    <div class="dropdown">
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
                <p class="job-card-meta mb-0 mt-2"><i class="mdi mdi-map-marker-outline me-1"></i>{{ $job->location ?? '—' }}</p>
                @if(!empty($job->salary_amount) && ($job->pay_type ?? '') !== 'not_disclosed')
                    <p class="job-card-meta mb-0 mt-1"><i class="mdi mdi-cash-multiple me-1"></i>{{ $job->salary_amount }}</p>
                @endif
                <p class="job-card-meta mb-0">Posted on {{ $job->created_at->format('d M Y') }}</p>
            </div>
            <div class="col-12 col-lg-auto">
                <div class="job-card-meta mb-1">Applied to job: <strong>{{ $job->applications_count }}</strong></div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('employer.jobs.applications', $job) }}" class="btn btn-primary btn-sm">View applications</a>
                    @if($job->status === 'closed')
                        <form method="POST" action="{{ route('employer.jobs.repost', $job) }}" class="d-inline">@csrf<button type="submit" class="btn btn-success btn-sm">Repost now</button></form>
                    @endif
                    <a href="{{ route('employer.jobs.edit', $job) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                </div>
                @if($job->status === 'closed')
                    <p class="job-card-meta small mb-0 mt-1">Repost now to receive new candidates.</p>
                @endif
            </div>
        </div>
    </div>
</div>
