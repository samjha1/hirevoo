@extends('layouts.app')

@section('title', $primaryResume ? 'Resume Matching Jobs' : ('Skill Match - ' . $jobRole->title))

@push('styles')
<style>
    .match-bar { height: 6px; border-radius: 3px; background: var(--bs-light); overflow: hidden; }
    .match-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
    .job-goal-card { transition: box-shadow 0.2s ease; }
    .job-goal-card:hover { box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.08); }
    .upskill-card { border-left: 4px solid var(--hirevo-primary, #0b1f3b); }
</style>
@endpush

@section('content')
    <section class="section pt-3">
        <div class="container">
            <nav class="mb-3 fs-14" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('job-list') }}">Job Goals</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $primaryResume ? 'Matching jobs' : $jobRole->title }}</li>
                </ol>
            </nav>
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info mb-4">{{ session('info') }}</div>
            @endif

            @if($primaryResume ?? null)
                {{-- Case 1: Resume uploaded – two columns: matching jobs (left) + upskill (right) --}}
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border shadow-none rounded-3 mb-3">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="uil uil-file-alt text-primary me-2 fs-20"></i>
                                    <span class="fw-medium">{{ $primaryResume->file_name ?? 'Resume' }}</span>
                                </div>
                                <h5 class="mb-3">Resume matching jobs</h5>
                                <p class="text-muted small mb-0">Jobs matched from our database based on your resume skills. Apply to see your AI match score (visible to employers).</p>
                            </div>
                        </div>
                        @if(!empty($matchingJobGoals))
                            <div class="row g-3">
                                @foreach($matchingJobGoals as $item)
                                    @php
                                        $role = $item['job_role'];
                                        $matchPct = $item['match_percentage'];
                                        $applied = in_array($role->id, $appliedJobIds ?? []);
                                    @endphp
                                    <div class="col-12">
                                        <div class="job-goal-card card border rounded-3 h-100">
                                            <div class="card-body p-4">
                                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                                                    <div class="flex-grow-1 min-w-0">
                                                        <h6 class="mb-1"><a href="{{ route('job-goal.show', $role) }}" class="text-dark text-decoration-none">{{ $role->title }}</a></h6>
                                                        @if($role->description)
                                                            <p class="text-muted small mb-2">{{ Str::limit(strip_tags($role->description), 90) }}</p>
                                                        @endif
                                                        <div class="match-bar mb-1" style="max-width: 120px;">
                                                            <div class="match-bar-fill {{ $matchPct >= 70 ? 'bg-success' : ($matchPct >= 40 ? 'bg-warning' : 'bg-secondary') }}" style="width: {{ $matchPct }}%;"></div>
                                                        </div>
                                                        <span class="small text-muted">{{ $matchPct }}% match</span>
                                                    </div>
                                                    <div class="d-flex flex-column gap-2 flex-shrink-0">
                                                        @if($applied)
                                                            <span class="badge bg-success px-3 py-2"><i class="uil uil-check me-1"></i> Applied</span>
                                                            <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm"><i class="uil uil-user-plus me-1"></i> Get Referral</a>
                                                            <p class="small text-muted mb-0">Chance of selection is higher with a referral.</p>
                                                        @else
                                                            <a href="{{ route('job-goal.apply', $role) }}" class="btn btn-primary btn-sm"><i class="uil uil-import me-1"></i> Apply</a>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(!empty($item['missing_skills']) && count($item['missing_skills']) > 0)
                                                    <p class="small text-muted mb-0 mt-2 pt-2 border-top">Missing: {{ implode(', ', array_slice($item['missing_skills'], 0, 5)) }}{{ count($item['missing_skills']) > 5 ? '...' : '' }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="card border rounded-3">
                                <div class="card-body p-4 text-center text-muted">
                                    <p class="mb-0">No matching job goals yet. <a href="{{ route('job-list') }}">Browse all Job Goals</a> or upload a resume with more skills.</p>
                                </div>
                            </div>
                        @endif
                        @if(isset($relatedJobs) && $relatedJobs->isNotEmpty())
                        <div class="card border shadow-none rounded-3 mt-4">
                            <div class="card-body p-4">
                                <h5 class="mb-3"><i class="uil uil-briefcase-alt text-primary me-2"></i>Open positions for {{ $jobRole->title }}</h5>
                                <p class="text-muted small mb-3">Apply to these job openings from employers.</p>
                                @foreach($relatedJobs as $job)
                                    <div class="card border-0 shadow-sm rounded-3 mb-2">
                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="flex-grow-1 min-w-0">
                                                    <h6 class="mb-1"><a href="{{ route('job-openings.apply', $job) }}" class="text-dark text-decoration-none">{{ $job->title }}</a></h6>
                                                    <p class="text-muted small mb-0">{{ $job->company_name ?? $job->user->referrerProfile?->company_name ?? 'Company' }}</p>
                                                    @if($job->location)<span class="small text-muted"><i class="uil uil-map-marker me-1"></i>{{ $job->location }}</span>@endif
                                                </div>
                                                <div class="flex-shrink-0">
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
                                <a href="{{ route('job-openings') }}?q={{ urlencode($jobRole->title) }}" class="btn btn-soft-primary btn-sm mt-2">View all jobs</a>
                            </div>
                        </div>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('job-list') }}" class="btn btn-soft-primary btn-sm"><i class="uil uil-arrow-left me-1"></i> Back to Job Goals</a>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border shadow-none rounded-3 sticky-top" style="top: 1rem;">
                            <div class="card-body p-4">
                                <h5 class="mb-3"><i class="uil uil-chart-growth text-primary me-2"></i> Upskill for these skills</h5>
                                <p class="text-muted small mb-4">You can get the <strong class="text-dark">20 LPA package</strong>. Higher package roles from top companies. Interested? Contact us.</p>
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
                                                        <p class="text-muted small mb-2">{{ Str::limit($opp->description, 140) }}</p>
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
            @else
                {{-- No resume: single column – current job role skill match --}}
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card border shadow-none rounded-3 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <h4 class="mb-2">{{ $jobRole->title }}</h4>
                                        @if($jobRole->description)
                                            <p class="text-muted mb-0">{{ $jobRole->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0">
                                        @auth
                                            @if($hasApplied ?? false)
                                                <span class="badge bg-success fs-14 px-3 py-2"><i class="uil uil-check me-1"></i> Applied</span>
                                                <div class="mt-2">
                                                    <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm"><i class="uil uil-user-plus me-1"></i> Get Referral</a>
                                                </div>
                                            @else
                                                <a href="{{ route('job-goal.apply', $jobRole) }}" class="btn btn-primary"><i class="uil uil-import me-1"></i> Apply now</a>
                                            @endif
                                        @else
                                            <a href="{{ route('login', ['redirect' => route('job-goal.apply', $jobRole)]) }}" class="btn btn-primary"><i class="uil uil-import me-1"></i> Apply now</a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>

                        @auth
                        <div class="card border shadow-none rounded-3 mb-4">
                            <div class="card-body p-4">
                                <h5 class="mb-4">Your skill match</h5>
                                @if($hasProfile && !empty($candidateSkills))
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0 rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 70px; height: 70px;">
                                                <span class="fs-24 fw-bold text-primary">{{ $matchPercentage }}%</span>
                                            </div>
                                            <div>
                                                <p class="mb-0 fw-medium">Match with {{ $jobRole->title }}</p>
                                                <p class="text-muted mb-0 small">Based on your profile skills vs required skills</p>
                                            </div>
                                        </div>
                                    </div>
                                    @if(count($matchedSkills) > 0)
                                        <div class="mb-4">
                                            <h6 class="mb-2">Skills you have</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($matchedSkills as $skill)
                                                    <span class="badge bg-success-subtle text-success">{{ ucfirst($skill) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @if(count($missingSkills) > 0)
                                        <div class="mb-4">
                                            <h6 class="mb-2">Skills to learn</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($missingSkills as $skill)
                                                    <span class="badge bg-warning-subtle text-warning">{{ ucfirst($skill) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted mb-3">Upload a resume or add skills in your profile to see match. <a href="{{ route('resume.upload') }}">Upload resume</a> for matching jobs.</p>
                                    <a href="{{ route('profile') }}" class="btn btn-soft-primary">Complete your profile</a>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="card border shadow-none rounded-3 mb-4">
                            <div class="card-body p-4">
                                <p class="text-muted mb-4">Sign in and upload your resume to see resume-matching jobs and your match score.</p>
                                <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary me-2">Sign In</a>
                                <a href="{{ route('register') }}?role=candidate" class="btn btn-soft-primary">Sign Up</a>
                            </div>
                        </div>
                        @endauth

                        <div class="card border shadow-none rounded-3 mb-4">
                            <div class="card-body p-4">
                                <h5 class="mb-3">Required skills for {{ $jobRole->title }}</h5>
                                @if($requiredSkills->count() > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($requiredSkills as $skill)
                                            <li class="d-flex align-items-center mb-2"><i class="uil uil-check-circle text-primary me-2"></i><span>{{ $skill->skill_name }}</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted mb-0">No specific skills defined yet for this role.</p>
                                @endif
                            </div>
                        </div>

                        @if(isset($relatedJobs) && $relatedJobs->isNotEmpty())
                        <div class="card border shadow-none rounded-3 mb-4">
                            <div class="card-body p-4">
                                <h5 class="mb-3"><i class="uil uil-briefcase-alt text-primary me-2"></i>Open positions for {{ $jobRole->title }}</h5>
                                <p class="text-muted small mb-3">Apply to these job openings from employers.</p>
                                <div class="list-group list-group-flush">
                                    @foreach($relatedJobs as $job)
                                        <div class="card border-0 shadow-sm rounded-3 mb-2">
                                            <div class="card-body p-3">
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <div class="flex-grow-1 min-w-0">
                                                        <h6 class="mb-1"><a href="{{ route('job-openings.apply', $job) }}" class="text-dark text-decoration-none">{{ $job->title }}</a></h6>
                                                        <p class="text-muted small mb-0">{{ $job->company_name ?? $job->user->referrerProfile?->company_name ?? 'Company' }}</p>
                                                        @if($job->location)<span class="small text-muted"><i class="uil uil-map-marker me-1"></i>{{ $job->location }}</span>@endif
                                                    </div>
                                                    <div class="flex-shrink-0">
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
                                <a href="{{ route('job-openings') }}?q={{ urlencode($jobRole->title) }}" class="btn btn-soft-primary btn-sm mt-2">View all jobs</a>
                            </div>
                        </div>
                        @endif

                        @auth
                        @if($hasApplied ?? false)
                        <div class="card border shadow-none rounded-3 border-success">
                            <div class="card-body p-4">
                                <h6 class="mb-2">Get a referral</h6>
                                <p class="text-muted small mb-3">Chance of getting selected is higher with a referral. Upgrade to Premium to request referrals.</p>
                                <a href="{{ route('pricing') }}" class="btn btn-primary btn-sm">View Premium</a>
                            </div>
                        </div>
                        @endif
                        @endauth

                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                            @auth
                                @if(!($hasApplied ?? false))
                                    <a href="{{ route('job-goal.apply', $jobRole) }}" class="btn btn-primary"><i class="uil uil-import me-1"></i> Apply now</a>
                                @endif
                            @else
                                <a href="{{ route('login', ['redirect' => route('job-goal.apply', $jobRole)]) }}" class="btn btn-primary"><i class="uil uil-import me-1"></i> Apply now</a>
                            @endauth
                            <a href="{{ route('job-list') }}" class="btn btn-soft-primary"><i class="uil uil-arrow-left me-1"></i> Back to Job Goals</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
