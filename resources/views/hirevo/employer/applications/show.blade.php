@extends('layouts.employer')

@section('title', 'Application – ' . ($application->user->name ?? 'Candidate'))

@section('header_title', 'Application Detail')

@section('header_actions')
    <a href="{{ route('employer.jobs.pipeline', $application->employerJob) }}" class="btn btn-outline-primary btn-sm">
        <i class="mdi mdi-arrow-left me-1"></i>Back to pipeline
    </a>
@endsection

@section('content')
    @php
        $candidate = $application->user;
        $profile = $candidate?->candidateProfile;
        $job = $application->employerJob;

        $skills = [];
        if ($profile && $profile->skills) {
            $skills = is_string($profile->skills)
                ? array_map('trim', explode(',', $profile->skills))
                : (is_array($profile->skills) ? $profile->skills : []);
        }
        $skills = array_values(array_filter($skills));
    @endphp

    <div class="mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <h2 class="h5 mb-1 fw-800 text-dark">{{ $candidate->name ?? 'Candidate' }}</h2>
                <p class="text-muted small mb-0">
                    {{ $job->title ?? '' }}
                    @if(!empty($job->formatted_location)) · {{ $job->formatted_location }} @endif
                    · <span class="fw-600">{{ ucfirst($application->status) }}</span>
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($application->resume_id)
                    <a href="{{ route('employer.applications.resume.view', $application) }}" target="_blank" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-eye me-1"></i>View resume
                    </a>
                    <a href="{{ route('employer.applications.resume', $application) }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-download me-1"></i>Download
                    </a>
                @else
                    <span class="btn btn-outline-secondary btn-sm disabled">No resume</span>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card employer-card">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-3">Resume Viewer</h5>
                    @if($application->resume_id)
                        <div class="border rounded-3 overflow-hidden" style="background:#fff;">
                            <iframe
                                src="{{ route('employer.applications.resume.view', $application) }}"
                                style="width:100%;height:560px;border:0;"
                                title="Resume">
                            </iframe>
                        </div>
                    @else
                        <div class="text-muted">No resume attached.</div>
                    @endif
                </div>
            </div>

            <div class="card employer-card mt-3">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-3">Application Notes</h5>
                    @if($application->cover_message)
                        <p class="text-muted small mb-1"><span class="fw-600">Cover message</span></p>
                        <p class="mb-0" style="white-space:pre-wrap;">{{ $application->cover_message }}</p>
                    @else
                        <p class="text-muted mb-0">No cover message provided.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card employer-card">
                <div class="card-body p-4">
                    <h5 class="fw-700 mb-3">Candidate Profile</h5>

                    <div class="mb-2">
                        <div class="text-muted small">Email</div>
                        <div class="fw-600"><a href="mailto:{{ $candidate->email }}">{{ $candidate->email ?? '—' }}</a></div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Phone</div>
                        <div class="fw-600">{{ $candidate->phone ?? '—' }}</div>
                    </div>

                    @if($profile?->linkedin_url)
                        <div class="mb-2">
                            <div class="text-muted small">LinkedIn</div>
                            <div class="fw-600 text-break"><a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener">{{ $profile->linkedin_url }}</a></div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <div class="text-muted small">Headline</div>
                        <div class="fw-600">{{ $profile?->headline ?? '—' }}</div>
                    </div>

                    @if($profile?->current_company)
                        <div class="mb-2">
                            <div class="text-muted small">Current company</div>
                            <div class="fw-600">{{ $profile->current_company }}</div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <div class="text-muted small">Education</div>
                        <div class="fw-600">{{ $profile?->education ?? '—' }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Total experience</div>
                        <div class="fw-600">
                            {{ $profile?->formattedTotalExperience() ?? '—' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Notice period (at application)</div>
                        <div class="fw-600">
                            {{ \App\Models\EmployerJobApplication::noticePeriodOptions()[$application->notice_period] ?? ($application->notice_period ?? '—') }}
                        </div>
                    </div>

                    @if($profile?->current_salary)
                        <div class="mb-2">
                            <div class="text-muted small">Current salary / CTC</div>
                            <div class="fw-600">{{ $profile->current_salary }}</div>
                        </div>
                    @endif

                    <div class="mb-2">
                        <div class="text-muted small">Expected salary</div>
                        <div class="fw-600">{{ $profile?->formattedExpectedSalary() ?? ($profile?->expected_salary ?? '—') }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Location (current)</div>
                        <div class="fw-600">{{ $profile?->location ?? '—' }}</div>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small">Preferred job location</div>
                        <div class="fw-600">{{ $profile?->preferred_job_location ?? '—' }}</div>
                    </div>

                    @if($application->info_accurate_confirmed_at)
                        <div class="mb-2">
                            <div class="text-muted small">Candidate confirmation</div>
                            <div class="fw-600 small">Declared information accurate · {{ $application->info_accurate_confirmed_at->format('d M Y, H:i') }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="text-muted small">Skills</div>
                        @if(count($skills))
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach(array_slice($skills, 0, 12) as $s)
                                    <span class="badge bg-light text-dark border">{{ $s }}</span>
                                @endforeach
                                @if(count($skills) > 12)
                                    <span class="badge bg-light text-dark border">+{{ count($skills) - 12 }} more</span>
                                @endif
                            </div>
                        @else
                            <div class="fw-600">—</div>
                        @endif
                    </div>

                    <hr class="my-3">

                    <h5 class="fw-700 mb-2">Match Scores</h5>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-success">
                            Match {{ $application->job_match_score !== null ? $application->job_match_score . '%' : '—' }}
                        </span>
                        <span class="badge bg-secondary">
                            ATS {{ $application->ats_score !== null ? $application->ats_score . '%' : '—' }}
                        </span>
                    </div>
                    <div class="text-muted small">Match explanation</div>
                    <div style="white-space:pre-wrap;" class="mb-2">
                        {{ $application->job_match_explanation ?? '—' }}
                    </div>
                </div>
            </div>

            <div class="card employer-card mt-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-700 mb-0">Interview Scheduling</h5>
                        <span class="text-muted small">
                            Moves candidate to <strong>Interviewed</strong> stage
                        </span>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-700 mb-2">Upcoming / Past Interviews</h6>

                        @if($application->interviews->isEmpty())
                            <p class="text-muted small mb-0">No interviews scheduled yet.</p>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach($application->interviews->sortByDesc('scheduled_at') as $int)
                                    <div class="border rounded-3 p-3">
                                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                                            <div>
                                                <div class="fw-700">
                                                    {{ $int->interview_type === 'phone' ? 'Phone' : ($int->interview_type === 'video' ? 'Video' : 'In-Person') }}
                                                    @if($int->interviewer_name)
                                                        <span class="text-muted fw-600">· {{ $int->interviewer_name }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted small">
                                                    {{
                                                        $int->scheduled_at
                                                            ? ($int->scheduled_at instanceof \Carbon\CarbonInterface
                                                                ? $int->scheduled_at->format('d M Y, g:i A')
                                                                : \Carbon\Carbon::parse($int->scheduled_at)->format('d M Y, g:i A'))
                                                            : '—'
                                                    }}
                                                    · {{ $int->duration_minutes }} mins
                                                </div>
                                            </div>

                                            <div class="d-flex flex-column align-items-end gap-2">
                                                <span class="badge {{ $int->status === 'cancelled' ? 'bg-danger' : ($int->status === 'completed' ? 'bg-success' : 'bg-primary') }}">
                                                    {{ ucfirst($int->status) }}
                                                </span>

                                                @if($int->status !== 'cancelled')
                                                    <form method="POST" action="{{ route('employer.interviews.cancel', $int) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                                                onclick="return confirm('Cancel this interview?');">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        @if(!empty($int->meeting_url))
                                            <div class="text-muted small mb-2">Meeting link</div>
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <a href="{{ $int->meeting_url }}" target="_blank" rel="noopener"
                                                   class="btn btn-primary btn-sm">
                                                    <i class="mdi mdi-link-variant me-1"></i>Open
                                                </a>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        onclick="navigator.clipboard.writeText(@json($int->meeting_url))">
                                                    <i class="mdi mdi-content-copy me-1"></i>Copy
                                                </button>
                                            </div>
                                        @endif
                                        <div class="mt-2">
                                            <a href="{{ route('employer.interviews.calendar', $int) }}"
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="mdi mdi-calendar-text me-1"></i>Add to calendar (.ics)
                                            </a>
                                        </div>
                                        @if(!empty($int->notes))
                                            <div class="text-muted small mt-2">
                                                <span class="fw-600 text-dark">Notes:</span>
                                                <span style="white-space:pre-wrap;">{{ $int->notes }}</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <h6 class="fw-700 mb-2">Schedule a new interview</h6>
                    <form method="POST" action="{{ route('employer.applications.interviews.store', $application) }}" id="interview-schedule-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="interview_type" class="form-label fw-600">Interview type</label>
                                <select class="form-select form-select-sm" id="interview_type" name="interview_type" required>
                                    <option value="phone">Phone</option>
                                    <option value="video" {{ old('interview_type') === 'video' ? 'selected' : '' }}>Video</option>
                                    <option value="in_person" {{ old('interview_type') === 'in_person' ? 'selected' : '' }}>In-Person</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6" id="meeting_provider_wrap" style="display:none;">
                                <label for="meeting_provider" class="form-label fw-600">Provider</label>
                                <select class="form-select form-select-sm" id="meeting_provider" name="meeting_provider">
                                    <option value="google_meet">Google Meet</option>
                                    <option value="zoom">Zoom</option>
                                    <option value="teams">Microsoft Teams</option>
                                </select>
                            </div>

                            <div class="col-12" id="meeting_url_wrap" style="display:none;">
                                <label for="meeting_url" class="form-label fw-600">Google Meet link (or any meeting link)</label>
                                <input type="url"
                                       class="form-control form-control-sm @error('meeting_url') is-invalid @enderror"
                                       id="meeting_url" name="meeting_url"
                                       value="{{ old('meeting_url') }}"
                                       placeholder="https://meet.google.com/xxx-yyyy-zzz">
                                @error('meeting_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div class="text-muted small mt-1">If empty, we’ll auto-generate a placeholder link for video interviews.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="scheduled_at" class="form-label fw-600">Date & time</label>
                                <input type="datetime-local"
                                       class="form-control form-control-sm @error('scheduled_at') is-invalid @enderror"
                                       id="scheduled_at" name="scheduled_at"
                                       value="{{ old('scheduled_at') }}"
                                       required>
                                @error('scheduled_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="duration_minutes" class="form-label fw-600">Duration (minutes)</label>
                                <input type="number"
                                       class="form-control form-control-sm @error('duration_minutes') is-invalid @enderror"
                                       id="duration_minutes" name="duration_minutes"
                                       value="{{ old('duration_minutes', 30) }}"
                                       min="15" max="240">
                                @error('duration_minutes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="interviewer_name" class="form-label fw-600">Interviewer name</label>
                                <input type="text"
                                       class="form-control form-control-sm @error('interviewer_name') is-invalid @enderror"
                                       id="interviewer_name" name="interviewer_name"
                                       value="{{ old('interviewer_name') }}"
                                       placeholder="e.g. HR Manager">
                                @error('interviewer_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label fw-600">Notes (optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          id="notes" name="notes" rows="2"
                                          placeholder="Any internal details for the interviewers...">{{ old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-calendar-clock me-1"></i>Schedule Interview
                                </button>
                                <a href="{{ route('employer.jobs.pipeline', $application->employerJob) }}" class="btn btn-outline-secondary btn-sm">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>

                    @push('scripts')
                        <script>
                            (function () {
                                var type = document.getElementById('interview_type');
                                var wrap = document.getElementById('meeting_provider_wrap');
                                var urlWrap = document.getElementById('meeting_url_wrap');
                                function toggle() {
                                    if (!type || !wrap) return;
                                    var isVideo = type.value === 'video';
                                    wrap.style.display = isVideo ? 'block' : 'none';
                                    if (urlWrap) urlWrap.style.display = isVideo ? 'block' : 'none';
                                }
                                if (type) type.addEventListener('change', toggle);
                                toggle();
                            })();
                        </script>
                    @endpush
                </div>
            </div>
        </div>
    </div>
@endsection

