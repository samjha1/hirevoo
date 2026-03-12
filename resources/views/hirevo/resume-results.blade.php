@extends('layouts.app')

@section('title', 'Resume Results - ATS Score')

@push('styles')
<style>
    .resume-score-ring { width: 140px; height: 140px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; background: var(--score-bg); border: 6px solid var(--score-border); }
    .resume-score-ring.score-high { --score-bg: rgba(16, 185, 129, 0.12); --score-border: rgba(16, 185, 129, 0.4); color: #059669; }
    .resume-score-ring.score-mid { --score-bg: rgba(245, 158, 11, 0.12); --score-border: rgba(245, 158, 11, 0.4); color: #b45309; }
    .resume-score-ring.score-low { --score-bg: rgba(239, 68, 68, 0.12); --score-border: rgba(239, 68, 68, 0.4); color: #dc2626; }
    .job-goal-card { transition: box-shadow 0.2s ease; }
    .job-goal-card:hover { box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08); }
    .match-bar { height: 6px; border-radius: 3px; background: var(--bs-light); overflow: hidden; }
    .match-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
    .resume-section-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; }
    .upskill-card { border-left: 4px solid var(--hirevo-primary, #0b1f3b); }
</style>
@endpush

@section('content')
    <section class="section pb-5">
        <div class="container">
            <nav class="mb-3" aria-label="breadcrumb">
                <!-- <ol class="breadcrumb mb-0 fs-14">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('resume.upload') }}">Submit CV</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Results</li>
                </ol> -->
            </nav>
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row g-4">
                {{-- Left column: ATS, summary, skills, matching jobs --}}
                <div class="col-lg-6">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 p-3 rounded-3 bg-light">
                        <i class="uil uil-file-alt text-primary fs-20"></i>
                        <span class="fw-medium">{{ $resume->file_name ?? 'Resume' }}</span>
                        @if($resume->created_at)
                            <span class="text-muted small">· Uploaded {{ $resume->created_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    @php
                        $score = $resume->ai_score ?? 0;
                        $scoreClass = $score >= 70 ? 'score-high' : ($score >= 50 ? 'score-mid' : 'score-low');
                        $band = $score >= 70 ? 'Good' : ($score >= 50 ? 'Fair' : 'Needs work');
                        $bandBg = $score >= 70 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-auto text-center mb-3 mb-md-0">
                                    <div class="resume-score-ring {{ $scoreClass }} mx-auto">{{ $score }}<span class="fs-18 opacity-75">%</span></div>
                                    <span class="badge bg-{{ $bandBg }} mt-2">{{ $band }}</span>
                                </div>
                                <div class="col">
                                    <h4 class="mb-2">ATS Compatibility Score</h4>
                                    <p class="text-muted small mb-2">How well your resume is likely to parse in applicant tracking systems.</p>
                                    <div class="match-bar mb-2"><div class="match-bar-fill bg-{{ $bandBg }}" style="width: {{ $score }}%;"></div></div>
                                    @if($resume->ai_score_explanation)
                                        <p class="mb-0 text-dark small">{{ $resume->ai_score_explanation }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($resume->ai_summary)
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-2">Resume Summary</h5>
                            <p class="text-muted mb-0 lh-lg small">{{ $resume->ai_summary }}</p>
                        </div>
                    </div>
                    @endif

                    @php $skills = is_array($resume->extracted_skills) ? $resume->extracted_skills : []; @endphp
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-2">Extracted Skills</h5>
                            <p class="text-muted small mb-3">Used to match you with job goals below.</p>
                            @if(count($skills) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach(array_slice($skills, 0, 20) as $skill)
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{{ is_string($skill) ? $skill : '' }}</span>
                                    @endforeach
                                    @if(count($skills) > 20)<span class="badge bg-light text-muted">+{{ count($skills) - 20 }} more</span>@endif
                                </div>
                            @else
                                <p class="text-muted mb-0 small">No skills detected. Add a clear Skills section.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Recommended employer jobs --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Recommended jobs for you</h5>
                            <p class="text-muted small mb-3">Posted jobs that match your resume.</p>
                            @if(!empty($recommendedEmployerJobs))
                                <div class="row g-2">
                                    @foreach(array_slice($recommendedEmployerJobs, 0, 5) as $item)
                                        @php $job = $item['job']; $matchPct = $item['match_percentage']; $companyName = $job->user->referrerProfile?->company_name ?? $job->company_name ?? 'Company'; @endphp
                                        <div class="col-12">
                                            <div class="job-goal-card card border rounded-3">
                                                <div class="card-body p-3">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                        <div>
                                                            <a href="{{ route('job-openings.apply', $job) }}" class="fw-semibold text-dark text-decoration-none">{{ $job->title }}</a>
                                                            <p class="text-muted small mb-0">{{ $companyName }}</p>
                                                            <div class="match-bar mt-1" style="max-width: 100px;"><div class="match-bar-fill {{ $matchPct >= 50 ? 'bg-success' : ($matchPct >= 25 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $matchPct }}%;"></div></div>
                                                            <span class="small text-muted">{{ $matchPct }}% match</span>
                                                        </div>
                                                        @if(in_array($job->id, $appliedEmployerJobIds ?? []))
                                                            <span class="badge bg-success">Applied</span>
                                                        @else
                                                            <a href="{{ route('job-openings.apply', $job) }}" class="btn btn-primary btn-sm">Apply</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('job-openings') }}" class="btn btn-soft-success btn-sm mt-2">View all job openings <i class="uil uil-arrow-right ms-1"></i></a>
                            @else
                                <p class="text-muted small mb-0">No posted jobs right now. <a href="{{ route('job-openings') }}">Browse job openings</a>.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Recommended job goals --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="mb-3">Resume matching job goals</h5>
                            <p class="text-muted small mb-3">Match % = required skills found in your resume.</p>
                            @if(!empty($recommendedJobGoals))
                                <div class="row g-2">
                                    @foreach($recommendedJobGoals as $item)
                                        @php $role = $item['job_role']; $match = $item['match_percentage']; @endphp
                                        <div class="col-12">
                                            <div class="job-goal-card card border rounded-3">
                                                <div class="card-body p-3">
                                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                        <div>
                                                            <a href="{{ route('job-goal.show', $role) }}" class="fw-semibold text-dark text-decoration-none">{{ $role->title }}</a>
                                                            <div class="match-bar mt-1" style="max-width: 100px;"><div class="match-bar-fill {{ $match >= 70 ? 'bg-success' : ($match >= 40 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $match }}%;"></div></div>
                                                            <span class="small text-muted">{{ $match }}% match</span>
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            <a href="{{ route('job-goal.show', $role) }}" class="btn btn-soft-primary btn-sm">View</a>
                                                            @if(in_array($role->id, $appliedJobIds ?? []))
                                                                <span class="badge bg-success">Applied</span>
                                                            @else
                                                                <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm">Apply</a>
                                                            @endif
                                                            <form action="{{ route('resume.lead') }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="resume_id" value="{{ $resume->id }}">
                                                                <input type="hidden" name="job_role_id" value="{{ $role->id }}">
                                                                <button type="submit" class="btn btn-soft-success btn-sm">Get help to learn</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">No job roles to match. <a href="{{ route('job-list') }}">Browse Job Goals</a>.</p>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('resume.upload') }}" class="btn btn-primary btn-sm"><i class="uil uil-file-upload me-1"></i> Upload another CV</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">Back to Home</a>
                    </div>
                </div>

                {{-- Right column: Upskill opportunities (same as job-goals) --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 1rem;">
                        <div class="card-body p-4">
                            <h5 class="mb-3"><i class="uil uil-chart-growth text-primary me-2"></i> Upskill for these skills</h5>
                            <p class="text-muted small mb-4">You can get the <strong class="text-dark">20 LPA package</strong>. Higher package roles from top companies. Interested? Contact us and we’ll store your interest.</p>
                            @if(!empty($upskillOpportunities))
                                <div class="d-flex flex-column gap-3">
                                    @foreach($upskillOpportunities as $opp)
                                        <div class="upskill-card card border rounded-3">
                                            <div class="card-body p-3">
                                                <h6 class="mb-1">{{ $opp->title }}</h6>
                                                @if($opp->company_name)
                                                    <p class="text-primary small fw-medium mb-2">{{ $opp->company_name }}</p>
                                                @endif
                                                @if($opp->description)
                                                    <p class="text-muted small mb-2">{{ Str::limit($opp->description, 120) }}</p>
                                                @endif
                                                @php
                                                    $oppSkills = $opp->getSkillsList();
                                                    if (empty($oppSkills) && $opp->title) {
                                                        $defaults = [
                                                            'SDE 1 (Software Development Engineer)' => ['Data Structures', 'Algorithms', 'System Design', 'C++ / Java', 'Problem Solving'],
                                                            'Senior Software Engineer' => ['System Design', 'Azure', 'Distributed Systems', 'Leadership', 'Cloud Architecture'],
                                                            'Data Scientist' => ['Python', 'Machine Learning', 'Statistics', 'SQL', 'Data Visualization'],
                                                            'Product Manager' => ['Analytics', 'Roadmap', 'Stakeholder Management', 'Agile', 'Product Strategy'],
                                                        ];
                                                        $oppSkills = $defaults[$opp->title] ?? [];
                                                    }
                                                    $userSkillsLower = $userSkillsForUpskill ?? [];
                                                    $missingForYou = array_values(array_filter($oppSkills, fn($s) => !in_array(strtolower(trim($s)), $userSkillsLower)));
                                                @endphp
                                                @if(count($oppSkills) > 0)
                                                    <p class="small mb-1 fw-medium text-danger">Missing for you:</p>
                                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                                        @if(count($missingForYou) > 0)
                                                            @foreach($missingForYou as $skill)
                                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger">{{ $skill }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="small text-muted">You have these skills.</span>
                                                        @endif
                                                    </div>
                                                    <p class="small mb-1 fw-medium text-dark">Upskill in (for this role):</p>
                                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                                        @foreach($oppSkills as $skill)
                                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning">{{ $skill }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <form action="{{ route('leads.upskill-contact') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="upskill_opportunity_id" value="{{ $opp->id }}">
                                                    <button type="submit" class="btn btn-primary btn-sm">Contact</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">Upskill opportunities will appear here. <a href="{{ route('contact') }}">Contact us</a> for guidance.</p>
                            @endif
                            <div class="mt-4 pt-3 border-top">
                                <a href="{{ route('pricing') }}" class="btn btn-primary w-100">View Premium – Get Referrals</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
