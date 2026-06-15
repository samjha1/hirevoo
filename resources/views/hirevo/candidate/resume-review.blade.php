@extends('layouts.candidate')

@section('title', 'Resume Review')

@section('body_class', 'candidate-resume-review-page')

@php
    $user = auth()->user();
    $displayName = trim($user->name) !== '' ? \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower(trim($user->name))) : 'Account';
    $profilePct = \App\Models\CandidateProfile::completionStats($user->candidateProfile, $user)['percent'] ?? 0;
@endphp

@section('header_greeting')
    <div class="cp-greeting">
        <h1 class="cp-greeting-title">Resume Review</h1>
        <p class="cp-greeting-sub">Your saved resume analysis — ATS score, summary, and skills.</p>
    </div>
@endsection

@section('header_actions')
    <a href="{{ route('resume.upload') }}" class="cp-btn cp-btn--outline">
        <i class="mdi mdi-upload-outline"></i>
        <span>Upload New</span>
    </a>

    <a href="{{ route('profile') }}" class="cp-user-chip">
        <span class="cp-user-avatar">{{ $user->initials() }}</span>
        <span class="cp-user-meta">
            <span class="cp-user-name">{{ $displayName }}</span>
            <span class="cp-user-progress-label">Profile: {{ $profilePct }}%</span>
            <span class="cp-user-progress-bar"><span style="width: {{ $profilePct }}%"></span></span>
        </span>
    </a>
@endsection

@section('content')
<div class="crr-page">

    @if(! $resume)
        <div class="cd-card crr-empty">
            <div class="crr-empty-icon" aria-hidden="true"><i class="mdi mdi-file-document-outline"></i></div>
            <h2>No resume on file yet</h2>
            <p>Upload your resume to get an ATS score, AI summary, and skill extraction saved to your account.</p>
            <a href="{{ route('resume.upload') }}" class="cd-btn cd-btn--primary">Upload Resume</a>
        </div>
    @else
        @php
            $score = (int) ($resume->ai_score ?? 0);
            $bandKey = $score >= 70 ? 'high' : ($score >= 50 ? 'mid' : 'low');
            $bandLabel = $score >= 70 ? 'Strong' : ($score >= 50 ? 'Fair' : 'Needs Work');
            $skills = is_array($resume->extracted_skills) ? $resume->extracted_skills : [];
            $scoreCirc = 2 * M_PI * 54;
            $scoreOffset = $scoreCirc - (($score / 100) * $scoreCirc);
            $fileExists = \App\Support\StoredFile::exists($resume->file_path);
            $analyzedAt = $resume->updated_at && $resume->updated_at->gt($resume->created_at)
                ? $resume->updated_at
                : $resume->created_at;
        @endphp

        {{-- File bar + version picker --}}
        <div class="crr-file-bar cd-card">
            <div class="crr-file-main">
                <span class="crr-file-icon" aria-hidden="true"><i class="mdi mdi-file-pdf-box"></i></span>
                <div>
                    <strong class="crr-file-name">{{ $resume->file_name ?? 'Resume.pdf' }}</strong>
                    <span class="crr-file-meta">
                        @if($resume->is_primary)<span class="crr-badge">Primary</span>@endif
                        Uploaded {{ $resume->created_at?->format('d M Y') ?? '—' }}
                        @if($analyzedAt)
                            · Analysed {{ $analyzedAt->diffForHumans() }}
                        @endif
                    </span>
                </div>
            </div>
            <div class="crr-file-actions">
                @if($allResumes->count() > 1)
                    <form method="get" action="{{ route('candidate.resume.review') }}" class="crr-version-form">
                        <select name="resume" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Select resume version">
                            @foreach($allResumes as $r)
                                <option value="{{ $r->id }}" @selected($r->id === $resume->id)>
                                    {{ $r->file_name ?? 'Resume' }} ({{ $r->created_at?->format('d M Y') }}){{ $r->is_primary ? ' — Primary' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @if($fileExists)
                    <a href="{{ route('resume.file', $resume) }}" target="_blank" rel="noopener" class="cd-btn cd-btn--sm crr-btn-outline">
                        <i class="mdi mdi-eye-outline"></i> View PDF
                    </a>
                    <a href="{{ route('resume.file', ['resume' => $resume, 'download' => 1]) }}" class="cd-btn cd-btn--sm crr-btn-outline">
                        <i class="mdi mdi-download-outline"></i> Download
                    </a>
                @endif
                <a href="{{ route('resume.results', $resume) }}" class="cd-btn cd-btn--primary cd-btn--sm">
                    Full report & jobs
                </a>
            </div>
        </div>

        <div class="crr-grid">
            {{-- PDF preview --}}
            <div class="cd-card crr-preview-card">
                <div class="cd-card-head">
                    <h2 class="cd-card-title">Your Resume</h2>
                </div>
                @if($fileExists)
                    <div class="crr-preview-frame-wrap">
                        <iframe
                            src="{{ route('resume.file', $resume) }}#toolbar=0"
                            title="Resume preview"
                            class="crr-preview-frame"
                            loading="lazy"></iframe>
                    </div>
                @else
                    <p class="crr-missing-file text-muted mb-0">
                        <i class="mdi mdi-alert-circle-outline"></i>
                        The file is no longer on the server. Upload a new resume to restore preview and analysis.
                    </p>
                    <a href="{{ route('resume.upload') }}" class="cd-btn cd-btn--primary cd-btn--sm mt-3">Upload again</a>
                @endif
            </div>

            {{-- Score + summary --}}
            <div class="crr-analysis-col">
                <div class="cd-card crr-score-card">
                    <div class="cd-card-head">
                        <h2 class="cd-card-title">ATS Score</h2>
                        <span class="crr-band crr-band--{{ $bandKey }}">{{ $bandLabel }}</span>
                    </div>
                    <div class="crr-score-body">
                        <div class="cd-score-ring-wrap crr-score-ring">
                            <svg class="cd-score-ring" viewBox="0 0 120 120" aria-hidden="true">
                                <circle class="cd-score-ring-track" cx="60" cy="60" r="54"/>
                                <circle class="cd-score-ring-fill crr-ring-fill--{{ $bandKey }}" cx="60" cy="60" r="54"
                                        stroke-dasharray="{{ $scoreCirc }}"
                                        stroke-dashoffset="{{ $scoreOffset }}"/>
                            </svg>
                            <div class="cd-score-ring-center">
                                <span class="cd-score-num">{{ $score }}<small>/100</small></span>
                                <span class="cd-score-badge">ATS</span>
                            </div>
                        </div>
                        <div class="crr-score-detail">
                            @if($resume->ai_score !== null)
                                <div class="cd-progress crr-score-bar"><span class="crr-bar-fill--{{ $bandKey }}" style="width: {{ min(100, $score) }}%"></span></div>
                            @else
                                <p class="text-muted small mb-2">Score not available — re-upload to run analysis.</p>
                            @endif
                            @if(filled($resume->ai_score_explanation))
                                <p class="crr-explanation">{{ $resume->ai_score_explanation }}</p>
                            @elseif($resume->ai_score !== null)
                                <p class="crr-explanation text-muted">Analysis complete. See summary and skills below.</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if(filled($resume->ai_summary))
                    <div class="cd-card">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title">AI Summary</h2>
                        </div>
                        <div class="crr-summary">{!! nl2br(e($resume->ai_summary)) !!}</div>
                    </div>
                @endif

                @if(count($skills) > 0)
                    <div class="cd-card">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title">Extracted Skills</h2>
                            <span class="cd-card-meta">{{ count($skills) }} detected</span>
                        </div>
                        <div class="crr-skills">
                            @foreach($skills as $skill)
                                @if(is_string($skill) && trim($skill) !== '')
                                    <span class="crr-skill-chip">{{ $skill }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="cd-card crr-actions-card">
                    <p class="crr-actions-title">Improve your resume</p>
                    <div class="crr-actions-row">
                        <a href="{{ route('resume.upload') }}" class="cd-btn cd-btn--primary cd-btn--sm">Upload updated PDF</a>
                        <a href="{{ route('profile') }}" class="cd-btn cd-btn--sm crr-btn-outline">Edit profile</a>
                        <a href="{{ route('resume.results', $resume) }}" class="cd-link">View matched jobs →</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
