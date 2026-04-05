@extends('layouts.app')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hirevo-marketing-system.css') }}">
<style>
.hirevo-marketing-inner { min-height: auto; background: linear-gradient(165deg, #f4f4f5 0%, #fafafa 40%, #fff 100%); }
.cp-wrap { max-width: 920px; margin: 0 auto; }
.cp-hero-card { border-radius: 20px; border: 1px solid rgba(24,24,27,0.08); overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.06); }
.cp-cover { height: 112px; background: linear-gradient(135deg, #18181b 0%, #3f3f46 50%, #0d9488 100%); }
.cp-hero-photo-col { width: 100%; max-width: 132px; margin-left: auto; margin-right: auto; }
@media (min-width: 768px) {
    .cp-hero-photo-col { margin-left: 0; margin-right: 0; }
    .cp-hero-info-col { min-width: 0; }
    .cp-hero-info-inner { padding-top: 0.35rem; }
}
.cp-avatar { width: 96px; height: 96px; margin-top: -48px; border: 4px solid #fff; border-radius: 50%; object-fit: cover; background: #f4f4f5; display: block; margin-left: auto; margin-right: auto; }
@media (min-width: 768px) {
    .cp-avatar { margin-left: 0; margin-right: 0; }
}
.cp-hero-hint { font-size: 0.72rem; line-height: 1.35; color: #71717a; max-width: 132px; margin-left: auto; margin-right: auto; }
@media (min-width: 768px) {
    .cp-hero-hint { margin-left: 0; margin-right: 0; text-align: left; }
}
.cp-section-title { font-family: 'Outfit', system-ui, sans-serif; font-weight: 700; font-size: 1rem; }
.cp-repeat-block { border: 1px dashed rgba(24,24,27,0.12); border-radius: 14px; padding: 1rem; margin-bottom: 0.75rem; background: rgba(255,255,255,0.6); }
.accordion-button:not(.collapsed) { background: rgba(99,102,241,0.06); color: #18181b; }
.cp-acc-header .accordion-button { align-items: center; }
.cp-acc-header .cp-acc-title { flex: 1 1 auto; min-width: 0; text-align: left; padding-right: 0.35rem; }
.cp-acc-header .cp-sec-check { flex-shrink: 0; display: inline-flex; align-items: center; margin-right: 0.35rem; line-height: 1; color: #0d9488 !important; }
.cp-acc-header .cp-sec-check-svg { display: block; flex-shrink: 0; }
.cp-progress { height: 10px; border-radius: 999px; background: rgba(24,24,27,0.08); overflow: hidden; }
.cp-progress-bar { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #0d9488, #6366f1); transition: width 0.35s ease; }
.cp-avatar-upload-label { cursor: pointer; border-radius: 50%; }
.cp-avatar-upload-label:hover .cp-avatar, .cp-avatar-upload-label:hover .cp-avatar-fallback { box-shadow: 0 0 0 3px rgba(13,148,136,0.35); }
.cp-avatar-fallback { width: 96px; height: 96px; margin-top: -48px; border: 4px solid #fff; border-radius: 50%; background: #f4f4f5; display: flex; align-items: center; justify-content: center; margin-left: auto; margin-right: auto; }
@media (min-width: 768px) {
    .cp-avatar-fallback { margin-left: 0; margin-right: 0; }
}
.cp-avatar-upload-label { display: block; width: fit-content; margin-left: auto; margin-right: auto; }
@media (min-width: 768px) {
    .cp-avatar-upload-label { margin-left: 0; margin-right: 0; }
}
.cp-photo-zone.cp-photo-zone-active { border-color: #0d9488 !important; background: rgba(13,148,136,0.06) !important; }
#cp-hero-photo-zone.cp-hero-photo-drag .cp-avatar,
#cp-hero-photo-zone.cp-hero-photo-drag .cp-avatar-fallback { box-shadow: 0 0 0 4px rgba(13,148,136,0.45); outline: 2px dashed #0d9488; outline-offset: 4px; }
</style>
@endpush

@section('content')
@php
    $p = $profile;
    $workRows = old('work_experience', \App\Models\CandidateProfile::jsonRepeaterToArray($p?->work_experience));
    while (count($workRows) < 1) { $workRows[] = ['company'=>'','role'=>'','start_date'=>'','end_date'=>'','current'=>false,'description'=>'']; }
    $eduRows = old('education_history', \App\Models\CandidateProfile::jsonRepeaterToArray($p?->education_history));
    while (count($eduRows) < 1) { $eduRows[] = ['degree'=>'','institution'=>'','field'=>'','start_year'=>'','end_year'=>'','grade'=>'']; }
    $projRows = old('projects', \App\Models\CandidateProfile::jsonRepeaterToArray($p?->projects));
    while (count($projRows) < 1) { $projRows[] = ['title'=>'','description'=>'','technologies'=>'','link'=>'']; }
    $certRows = old('certifications', \App\Models\CandidateProfile::jsonRepeaterToArray($p?->certifications));
    while (count($certRows) < 1) { $certRows[] = ['name'=>'','issued_by'=>'','year'=>'','link'=>'']; }
    $secDone = $profileSectionsDone ?? [];
@endphp

<div class="hirevo-marketing-inner py-4">
    <div class="container cp-wrap">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($user->isCandidate())
            @if(!($hasResume ?? false))
                <div class="alert alert-dark border-0 shadow-sm rounded-3 mb-4" role="status">
                    <div class="fw-bold mb-1"><i class="uil uil-upload me-1"></i> Step 1 — Upload your resume first</div>
                    <p class="small mb-0 opacity-90">We read your PDF and fill the sections below (AI when available). Then review, edit, and save.</p>
                </div>
            @elseif(!($profileOnboardingComplete ?? false))
                <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4" role="status">
                    <div class="fw-bold mb-1"><i class="uil uil-edit me-1"></i> Step 2 — Review &amp; save</div>
                    <p class="small mb-0">Check every section, fix anything that looks wrong, then click <strong>Save profile</strong> at the bottom.</p>
                </div>
            @else
                <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4" role="status">
                    <div class="fw-bold mb-1"><i class="uil uil-check-circle me-1"></i> Profile ready</div>
                    <p class="small mb-0">Update any time. Re-upload a resume or use <strong>Fill from resume</strong> to refresh fields.</p>
                </div>
            @endif
        @endif

        {{-- Header card --}}
        <div class="cp-hero-card bg-white mb-4">
            <div class="cp-cover"></div>
            <div class="px-3 px-md-4 pb-4">
                <div class="row cp-hero-inner g-3 gx-md-2 gx-lg-3 align-items-start">
                    <div class="col-12 col-md-auto text-center text-md-start cp-hero-photo-col" id="cp-hero-photo-zone">
                        @if($user->isCandidate())
                            <label for="cp-profile-photo-input" class="cp-avatar-upload-label mb-0" title="Upload or drop a profile photo">
                                <img src="{{ $p?->profilePhotoUrl() ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}" alt="" class="cp-avatar shadow-sm {{ $p?->profile_photo_path ? '' : 'd-none' }}" id="cp-hero-avatar-img">
                                <div class="cp-avatar-fallback shadow-sm text-secondary {{ $p?->profile_photo_path ? 'd-none' : '' }}" id="cp-hero-avatar-fallback">
                                    <i class="uil uil-camera-plus" style="font-size:2rem;"></i>
                                </div>
                            </label>
                            <p class="cp-hero-hint mt-2 mb-0">Click or drop a photo — JPG, PNG, GIF or WebP, max 2&nbsp;MB. Use <strong>Save profile</strong> below to save.</p>
                        @else
                            @if($p?->profile_photo_path)
                                <img src="{{ $p->profilePhotoUrl() }}" alt="" class="cp-avatar shadow-sm">
                            @else
                                <div class="cp-avatar shadow-sm d-flex align-items-center justify-content-center text-secondary">
                                    <i class="uil uil-user" style="font-size:2.5rem;"></i>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="col-12 col-md cp-hero-info-col">
                        <div class="cp-hero-info-inner text-start">
                            <h1 class="h4 fw-bold mb-1">{{ $user->name }}</h1>
                            @if($p?->headline)<p class="text-primary fw-600 mb-1">{{ $p->headline }}</p>@endif
                            <p class="small text-muted mb-1"><i class="uil uil-envelope me-1"></i>{{ $user->email }}</p>
                            @if($user->phone)<p class="small text-muted mb-0"><i class="uil uil-phone me-1"></i>{{ $user->phone }}</p>@endif
                            @if($user->isCandidate())
                                @php $pc = $profileCompletion ?? ['percent' => 0, 'filled' => 0, 'total' => 0]; @endphp
                                <div class="mt-3 pt-2 border-top border-light">
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span class="text-muted fw-600">Profile completion</span>
                                        <span class="fw-bold text-primary">{{ $pc['percent'] }}%</span>
                                    </div>
                                    <div class="cp-progress" role="progressbar" aria-valuenow="{{ $pc['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion">
                                        <div class="cp-progress-bar" style="width: {{ $pc['percent'] }}%"></div>
                                    </div>
                                    <p class="small text-muted mb-0 mt-1">{{ $pc['filled'] }} of {{ $pc['total'] }} sections filled — keep going to strengthen your applications.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($user->isCandidate())

            {{-- Resume first --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">
                    <h2 class="cp-section-title mb-2"><i class="uil uil-file-upload text-primary me-1"></i> Resume</h2>
                    <p class="text-muted small mb-3">PDF only. Upload sends you back here with fields filled from the file.</p>
                    <form action="{{ route('resume.upload.store') }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="return_to" value="profile">
                        <div class="col-md-8">
                            <input type="file" name="resume" class="form-control" accept=".pdf,application/pdf" required>
                            @error('resume')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Upload &amp; fill profile</button>
                        </div>
                    </form>
                    @if($latestResume ?? null)
                        <p class="small text-muted mt-3 mb-0">
                            <a href="{{ route('resume.results', $latestResume) }}">View ATS analysis</a>
                            @if($hasResume ?? false)
                                ·
                                <form method="POST" action="{{ route('profile.fill-from-resume') }}" class="d-inline">@csrf<button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Re-fill from resume</button></form>
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="cp-main-form" novalidate>
                @csrf
                <input type="file" name="profile_photo" id="cp-profile-photo-input" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp,image/*" aria-label="Profile photo upload">

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3" role="alert">
                        <div class="fw-bold mb-2">We could not save your profile. Please fix the following:</div>
                        <ul class="small mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="accordion shadow-sm rounded-3 overflow-hidden border" id="cpAccordion">

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cp1"><span class="cp-acc-title">1. Basic information</span>@if(($secDone[1] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp1" class="accordion-collapse collapse show" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Full name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Job title / headline @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                        <input type="text" name="headline" class="form-control @error('headline') is-invalid @enderror" value="{{ old('headline', $p?->headline) }}" placeholder="e.g. Flutter Developer" @if(!($profileOnboardingComplete ?? false)) required @endif>
                                        @error('headline')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Email</label>
                                        <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                        <span class="small text-muted">Managed on your account</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Phone @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" @if(!($profileOnboardingComplete ?? false)) required @endif>
                                        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Location @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $p?->location) }}" placeholder="City, State, Country" @if(!($profileOnboardingComplete ?? false)) required @endif>
                                        @error('location')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Date of birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', filled($p?->date_of_birth) ? \Illuminate\Support\Carbon::parse($p->date_of_birth)->format('Y-m-d') : '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">—</option>
                                            @foreach(['Male','Female','Other','Prefer not to say'] as $g)
                                                <option value="{{ $g }}" @selected(old('gender', $p?->gender) === $g)>{{ $g }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp2"><span class="cp-acc-title">2. Professional summary</span>@if(($secDone[2] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp2" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="mb-3">
                                    <label class="form-label fw-600">Short bio / About me</label>
                                    <textarea name="bio_summary" class="form-control" rows="4" placeholder="3–5 lines about your background">{{ old('bio_summary', $p?->bio_summary) }}</textarea>
                                </div>
                                <div>
                                    <label class="form-label fw-600">Career objective</label>
                                    <textarea name="career_objective" class="form-control" rows="3" placeholder="What roles or impact are you aiming for?">{{ old('career_objective', $p?->career_objective) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp3"><span class="cp-acc-title">3. Work experience</span>@if(($secDone[3] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp3" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white" id="work-wrap">
                                @foreach($workRows as $i => $row)
                                    <div class="cp-repeat-block" data-repeat="work">
                                        <div class="row g-2">
                                            <div class="col-md-6"><label class="form-label small">Company</label><input type="text" name="work_experience[{{ $i }}][company]" class="form-control form-control-sm" value="{{ $row['company'] ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label small">Role</label><input type="text" name="work_experience[{{ $i }}][role]" class="form-control form-control-sm" value="{{ $row['role'] ?? '' }}"></div>
                                            <div class="col-md-4"><label class="form-label small">Start</label><input type="text" name="work_experience[{{ $i }}][start_date]" class="form-control form-control-sm" value="{{ $row['start_date'] ?? '' }}" placeholder="Jan 2022"></div>
                                            <div class="col-md-4"><label class="form-label small">End</label><input type="text" name="work_experience[{{ $i }}][end_date]" class="form-control form-control-sm" value="{{ $row['end_date'] ?? '' }}" placeholder="Present"></div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="hidden" name="work_experience[{{ $i }}][current]" value="0">
                                                    <input class="form-check-input" type="checkbox" name="work_experience[{{ $i }}][current]" value="1" id="wcur{{ $i }}" @checked(!empty($row['current']))>
                                                    <label class="form-check-label small" for="wcur{{ $i }}">Current job</label>
                                                </div>
                                            </div>
                                            <div class="col-12"><label class="form-label small">Description</label><textarea name="work_experience[{{ $i }}][description]" class="form-control form-control-sm" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-work">+ Add job</button>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp4"><span class="cp-acc-title">4. Education</span>@if(($secDone[4] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp4" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="mb-3">
                                    <label class="form-label fw-600">Summary line @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                    <input type="text" name="education" class="form-control @error('education') is-invalid @enderror" value="{{ old('education', $p?->education) }}" placeholder="e.g. B.Tech CSE, XYZ University" @if(!($profileOnboardingComplete ?? false)) required @endif>
                                    @error('education')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <p class="small text-muted">Detailed rows (optional):</p>
                                <div id="edu-wrap">
                                    @foreach($eduRows as $i => $row)
                                        <div class="cp-repeat-block" data-repeat="edu">
                                            <div class="row g-2">
                                                <div class="col-md-4"><label class="form-label small">Degree</label><input type="text" name="education_history[{{ $i }}][degree]" class="form-control form-control-sm" value="{{ $row['degree'] ?? '' }}"></div>
                                                <div class="col-md-8"><label class="form-label small">College / University</label><input type="text" name="education_history[{{ $i }}][institution]" class="form-control form-control-sm" value="{{ $row['institution'] ?? '' }}"></div>
                                                <div class="col-md-6"><label class="form-label small">Field of study</label><input type="text" name="education_history[{{ $i }}][field]" class="form-control form-control-sm" value="{{ $row['field'] ?? '' }}"></div>
                                                <div class="col-md-3"><label class="form-label small">Start year</label><input type="text" name="education_history[{{ $i }}][start_year]" class="form-control form-control-sm" value="{{ $row['start_year'] ?? '' }}"></div>
                                                <div class="col-md-3"><label class="form-label small">End year</label><input type="text" name="education_history[{{ $i }}][end_year]" class="form-control form-control-sm" value="{{ $row['end_year'] ?? '' }}"></div>
                                                <div class="col-12"><label class="form-label small">CGPA / %</label><input type="text" name="education_history[{{ $i }}][grade]" class="form-control form-control-sm" value="{{ $row['grade'] ?? '' }}"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-edu">+ Add education</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp5"><span class="cp-acc-title">5. Skills &amp; tools</span>@if(($secDone[5] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp5" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="mb-3">
                                    <label class="form-label fw-600">Technical skills @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                    <textarea name="skills" class="form-control @error('skills') is-invalid @enderror" rows="3" placeholder="Comma-separated" @if(!($profileOnboardingComplete ?? false)) required @endif>{{ old('skills', $p?->skills) }}</textarea>
                                    @error('skills')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-600">Tools (Git, Firebase, …)</label>
                                    <textarea name="tools" class="form-control" rows="2" placeholder="Comma-separated">{{ old('tools', $p?->tools) }}</textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Overall skill level</label>
                                        <select name="technical_skill_level" class="form-select">
                                            <option value="">—</option>
                                            @foreach(['Beginner','Intermediate','Expert'] as $lvl)
                                                <option value="{{ $lvl }}" @selected(old('technical_skill_level', $p?->technical_skill_level) === $lvl)>{{ $lvl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Years of experience @if(!($profileOnboardingComplete ?? false))<span class="text-danger">*</span>@endif</label>
                                        <input type="number" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror" min="0" max="50" value="{{ old('experience_years', $p?->experience_years) }}" @if(!($profileOnboardingComplete ?? false)) required @endif>
                                        @error('experience_years')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Extra months (0–11)</label>
                                        <input type="number" name="experience_months" class="form-control" min="0" max="11" value="{{ old('experience_months', $p?->experience_months) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Current company</label>
                                        <input type="text" name="current_company" class="form-control" value="{{ old('current_company', $p?->current_company) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp6"><span class="cp-acc-title">6. Projects</span>@if(($secDone[6] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp6" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white" id="proj-wrap">
                                @foreach($projRows as $i => $row)
                                    <div class="cp-repeat-block" data-repeat="proj">
                                        <div class="row g-2">
                                            <div class="col-12"><label class="form-label small">Title</label><input type="text" name="projects[{{ $i }}][title]" class="form-control form-control-sm" value="{{ $row['title'] ?? '' }}"></div>
                                            <div class="col-12"><label class="form-label small">Description</label><textarea name="projects[{{ $i }}][description]" class="form-control form-control-sm" rows="2">{{ $row['description'] ?? '' }}</textarea></div>
                                            <div class="col-md-6"><label class="form-label small">Technologies</label><input type="text" name="projects[{{ $i }}][technologies]" class="form-control form-control-sm" value="{{ $row['technologies'] ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label small">Link</label><input type="text" name="projects[{{ $i }}][link]" class="form-control form-control-sm" value="{{ $row['link'] ?? '' }}" placeholder="https://"></div>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-proj">+ Add project</button>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp7"><span class="cp-acc-title">7. Certifications</span>@if(($secDone[7] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp7" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white" id="cert-wrap">
                                @foreach($certRows as $i => $row)
                                    <div class="cp-repeat-block" data-repeat="cert">
                                        <div class="row g-2">
                                            <div class="col-md-6"><label class="form-label small">Certificate name</label><input type="text" name="certifications[{{ $i }}][name]" class="form-control form-control-sm" value="{{ $row['name'] ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label small">Issued by</label><input type="text" name="certifications[{{ $i }}][issued_by]" class="form-control form-control-sm" value="{{ $row['issued_by'] ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label small">Year</label><input type="text" name="certifications[{{ $i }}][year]" class="form-control form-control-sm" value="{{ $row['year'] ?? '' }}"></div>
                                            <div class="col-md-6"><label class="form-label small">Link</label><input type="text" name="certifications[{{ $i }}][link]" class="form-control form-control-sm" value="{{ $row['link'] ?? '' }}" placeholder="https://"></div>
                                        </div>
                                    </div>
                                @endforeach
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="add-cert">+ Add certification</button>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp8"><span class="cp-acc-title">8. Social links</span>@if(($secDone[8] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp8" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="row g-3">
                                    <div class="col-12"><label class="form-label fw-600">LinkedIn</label><input type="text" name="linkedin_url" class="form-control" value="{{ old('linkedin_url', $p?->linkedin_url) }}" placeholder="https://linkedin.com/in/..."></div>
                                    <div class="col-12"><label class="form-label fw-600">GitHub</label><input type="text" name="github_url" class="form-control" value="{{ old('github_url', $p?->github_url) }}" placeholder="https://github.com/..."></div>
                                    <div class="col-12"><label class="form-label fw-600">Portfolio</label><input type="text" name="portfolio_url" class="form-control" value="{{ old('portfolio_url', $p?->portfolio_url) }}" placeholder="https://..."></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0">
                        <h2 class="accordion-header cp-acc-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cp9"><span class="cp-acc-title">9. Job preferences &amp; salary</span>@if(($secDone[9] ?? false))<span class="cp-sec-check" role="img" aria-label="Section complete"><svg class="cp-sec-check-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" focusable="false" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg></span>@endif</button>
                        </h2>
                        <div id="cp9" class="accordion-collapse collapse" data-bs-parent="#cpAccordion">
                            <div class="accordion-body bg-white">
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-600">Preferred role</label><input type="text" name="preferred_job_role" class="form-control" value="{{ old('preferred_job_role', $p?->preferred_job_role) }}"></div>
                                    <div class="col-md-6"><label class="form-label fw-600">Preferred location</label><input type="text" name="preferred_job_location" class="form-control" value="{{ old('preferred_job_location', $p?->preferred_job_location) }}"></div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">Job type</label>
                                        <select name="job_type" class="form-select">
                                            <option value="">—</option>
                                            @foreach(['Full-time','Part-time','Remote','Hybrid','Contract','Internship'] as $jt)
                                                <option value="{{ $jt }}" @selected(old('job_type', $p?->job_type) === $jt)>{{ $jt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6"><label class="form-label fw-600">Notice period</label><input type="text" name="notice_period" class="form-control" value="{{ old('notice_period', $p?->notice_period) }}" placeholder="e.g. 30 days"></div>
                                    <div class="col-md-4"><label class="form-label fw-600">Expected CTC</label><input type="text" name="expected_salary" class="form-control" value="{{ old('expected_salary', $p?->expected_salary) }}"></div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">Currency</label>
                                        <input type="text" name="expected_salary_currency" class="form-control" value="{{ old('expected_salary_currency', $p?->expected_salary_currency ?? 'INR') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">Period</label>
                                        <select name="expected_salary_period" class="form-select">
                                            <option value="per_annum" @selected(old('expected_salary_period', $p?->expected_salary_period ?? 'per_annum') === 'per_annum')>Per annum</option>
                                            <option value="per_month" @selected(old('expected_salary_period', $p?->expected_salary_period) === 'per_month')>Per month</option>
                                        </select>
                                    </div>
                                    <div class="col-12"><label class="form-label fw-600">Current salary (optional)</label><input type="text" name="current_salary" class="form-control" value="{{ old('current_salary', $p?->current_salary) }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mt-4 mb-5">
                    <button type="submit" class="btn btn-primary btn-lg px-4">Save profile</button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Back to home</a>
                </div>
            </form>

            <template id="tpl-work">
                <div class="cp-repeat-block" data-repeat="work">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label small">Company</label><input type="text" name="work_experience[__I__][company]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Role</label><input type="text" name="work_experience[__I__][role]" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Start</label><input type="text" name="work_experience[__I__][start_date]" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">End</label><input type="text" name="work_experience[__I__][end_date]" class="form-control form-control-sm"></div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input type="hidden" name="work_experience[__I__][current]" value="0">
                                <input class="form-check-input" type="checkbox" name="work_experience[__I__][current]" value="1" id="wcur__I__">
                                <label class="form-check-label small" for="wcur__I__">Current job</label>
                            </div>
                        </div>
                        <div class="col-12"><label class="form-label small">Description</label><textarea name="work_experience[__I__][description]" class="form-control form-control-sm" rows="2"></textarea></div>
                    </div>
                </div>
            </template>
            <template id="tpl-edu">
                <div class="cp-repeat-block" data-repeat="edu">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label small">Degree</label><input type="text" name="education_history[__I__][degree]" class="form-control form-control-sm"></div>
                        <div class="col-md-8"><label class="form-label small">College / University</label><input type="text" name="education_history[__I__][institution]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Field</label><input type="text" name="education_history[__I__][field]" class="form-control form-control-sm"></div>
                        <div class="col-md-3"><label class="form-label small">Start year</label><input type="text" name="education_history[__I__][start_year]" class="form-control form-control-sm"></div>
                        <div class="col-md-3"><label class="form-label small">End year</label><input type="text" name="education_history[__I__][end_year]" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label small">CGPA / %</label><input type="text" name="education_history[__I__][grade]" class="form-control form-control-sm"></div>
                    </div>
                </div>
            </template>
            <template id="tpl-proj">
                <div class="cp-repeat-block" data-repeat="proj">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label small">Title</label><input type="text" name="projects[__I__][title]" class="form-control form-control-sm"></div>
                        <div class="col-12"><label class="form-label small">Description</label><textarea name="projects[__I__][description]" class="form-control form-control-sm" rows="2"></textarea></div>
                        <div class="col-md-6"><label class="form-label small">Technologies</label><input type="text" name="projects[__I__][technologies]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Link</label><input type="text" name="projects[__I__][link]" class="form-control form-control-sm"></div>
                    </div>
                </div>
            </template>
            <template id="tpl-cert">
                <div class="cp-repeat-block" data-repeat="cert">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label small">Certificate name</label><input type="text" name="certifications[__I__][name]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Issued by</label><input type="text" name="certifications[__I__][issued_by]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Year</label><input type="text" name="certifications[__I__][year]" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Link</label><input type="text" name="certifications[__I__][link]" class="form-control form-control-sm"></div>
                    </div>
                </div>
            </template>

        @else
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <p class="mb-1"><strong>Name</strong> {{ $user->name }}</p>
                    <p class="mb-1"><strong>Email</strong> {{ $user->email }}</p>
                    <p class="text-muted small mb-0">Full profile editor is for candidate accounts.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
@if($user->isCandidate())
<script>
(function () {
    var photoInput = document.getElementById('cp-profile-photo-input');
    var heroImg = document.getElementById('cp-hero-avatar-img');
    var heroPh = document.getElementById('cp-hero-avatar-fallback');
    var heroZone = document.getElementById('cp-hero-photo-zone');
    var lastPreviewUrl = null;

    function setPreviews(file) {
        if (!file || !file.type || file.type.indexOf('image/') !== 0) return;
        if (lastPreviewUrl) {
            URL.revokeObjectURL(lastPreviewUrl);
            lastPreviewUrl = null;
        }
        lastPreviewUrl = URL.createObjectURL(file);
        if (heroImg) {
            heroImg.src = lastPreviewUrl;
            heroImg.classList.remove('d-none');
        }
        if (heroPh) heroPh.classList.add('d-none');
    }

    function assignFile(file) {
        if (!photoInput || !file || !file.type || file.type.indexOf('image/') !== 0) return;
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            photoInput.files = dt.files;
            setPreviews(file);
        } catch (e) {}
    }

    if (heroImg) {
        heroImg.addEventListener('error', function () {
            if (lastPreviewUrl && heroImg.src && heroImg.src.indexOf(lastPreviewUrl) === 0) return;
            heroImg.classList.add('d-none');
            if (heroPh) heroPh.classList.remove('d-none');
        });
    }
    if (photoInput) {
        photoInput.addEventListener('change', function () {
            if (photoInput.files && photoInput.files[0]) setPreviews(photoInput.files[0]);
        });
    }
    if (heroZone && photoInput) {
        ['dragenter', 'dragover'].forEach(function (ev) {
            heroZone.addEventListener(ev, function (e) {
                e.preventDefault();
                e.stopPropagation();
                heroZone.classList.add('cp-hero-photo-drag');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            heroZone.addEventListener(ev, function (e) {
                e.preventDefault();
                e.stopPropagation();
                heroZone.classList.remove('cp-hero-photo-drag');
            });
        });
        heroZone.addEventListener('drop', function (e) {
            var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (f) assignFile(f);
        });
    }

    function bindAdd(btnId, wrapId, tplId, namePrefix) {
        var btn = document.getElementById(btnId);
        var wrap = document.getElementById(wrapId);
        var tpl = document.getElementById(tplId);
        if (!btn || !wrap || !tpl) return;
        btn.addEventListener('click', function () {
            var n = wrap.querySelectorAll('[data-repeat]').length;
            var html = tpl.innerHTML.replace(/__I__/g, n);
            var div = document.createElement('div');
            div.innerHTML = html.trim();
            wrap.insertBefore(div.firstElementChild, btn);
        });
    }
    bindAdd('add-work', 'work-wrap', 'tpl-work');
    bindAdd('add-edu', 'edu-wrap', 'tpl-edu');
    bindAdd('add-proj', 'proj-wrap', 'tpl-proj');
    bindAdd('add-cert', 'cert-wrap', 'tpl-cert');
})();
</script>
@endif
@endpush
