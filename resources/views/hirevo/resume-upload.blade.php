@extends('layouts.app')

@section('title', 'Upload Your Resume — Get ATS Score Free')

@push('styles')
<style>
/* ── Page shell ──────────────────────────────────── */
.ru-page {
    background: linear-gradient(160deg, #eef2ff 0%, #f8fafc 22%, #fff 65%);
    min-height: 100vh;
}

/* ── Hero banner ─────────────────────────────────── */
.ru-hero {
    border-radius: 1.5rem;
    background: linear-gradient(135deg, #0b1f3b 0%, #1e3a5f 50%, #0f5242 100%);
    color: #fff;
    padding: 2rem 1.75rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(11,31,59,0.22);
}
.ru-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16,185,129,0.22) 0%, transparent 70%);
    pointer-events: none;
}
.ru-hero-inner { position: relative; z-index: 1; }
.ru-hero-kicker {
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #34d399;
    margin-bottom: 0.35rem;
}
.ru-hero-title {
    font-size: 1.45rem;
    font-weight: 800;
    line-height: 1.2;
}
@media (min-width: 768px) { .ru-hero-title { font-size: 1.75rem; } }
.ru-hero-sub {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.65);
    margin-top: 0.35rem;
    line-height: 1.5;
}
.ru-benefit-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1.1rem;
}
.ru-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.28rem 0.75rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    color: rgba(255,255,255,0.88);
    backdrop-filter: blur(4px);
}

/* ── Main card ───────────────────────────────────── */
.ru-card {
    background: #fff;
    border-radius: 1.35rem;
    border: 1px solid rgba(15,23,42,0.08);
    box-shadow: 0 8px 40px rgba(15,23,42,0.07);
    padding: 2rem 1.5rem;
}
@media (min-width: 576px) { .ru-card { padding: 2.25rem 2rem; } }

/* ── Dropzone ────────────────────────────────────── */
.ru-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 1.1rem;
    padding: 2.25rem 1.5rem;
    text-align: center;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.ru-dropzone:hover {
    border-color: #10b981;
    background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 100%);
    box-shadow: 0 4px 20px rgba(16,185,129,0.12);
}
.ru-dropzone.dragover {
    border-color: #6366f1;
    background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
    box-shadow: 0 4px 24px rgba(99,102,241,0.15);
}
.ru-dropzone.has-file {
    border-style: solid;
    border-color: #10b981;
    background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 100%);
}
.ru-dropzone-file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.ru-dz-icon {
    width: 60px; height: 60px;
    border-radius: 1rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem;
    margin-bottom: 0.85rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    box-shadow: 0 4px 16px rgba(16,185,129,0.35);
    transition: background 0.25s;
}
.ru-dropzone.has-file .ru-dz-icon {
    background: linear-gradient(135deg, #0b1f3b, #1e3a5f);
    box-shadow: 0 4px 16px rgba(11,31,59,0.3);
}
.ru-dz-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.25rem;
}
.ru-dz-sub { font-size: 0.82rem; color: #64748b; }
.ru-dz-note { font-size: 0.72rem; color: #94a3b8; margin-top: 0.5rem; }
.ru-browse-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.4rem 0.9rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.82rem;
    background: #0b1f3b;
    color: #fff;
    border: none;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    pointer-events: auto;
    position: relative;
    z-index: 2;
}
.ru-browse-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(11,31,59,0.25); color: #fff; }
.ru-file-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.9rem;
    border-radius: 0.65rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 0.75rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    position: relative;
    z-index: 2;
}
.ru-remove-file {
    color: #94a3b8;
    cursor: pointer;
    border-radius: 4px;
    transition: color 0.15s, background 0.15s;
    padding: 0 3px;
    font-size: 1rem;
    line-height: 1;
    position: relative;
    z-index: 3;
}
.ru-remove-file:hover { color: #dc2626; background: #fef2f2; }

/* ── Guest fields ────────────────────────────────── */
.ru-guest-section {
    border: 1.5px solid rgba(99,102,241,0.2);
    border-radius: 1rem;
    padding: 1.25rem 1.25rem 1rem;
    background: linear-gradient(135deg, rgba(99,102,241,0.04) 0%, #fff 100%);
    margin-top: 1.25rem;
}
.ru-guest-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #4338ca;
    background: rgba(99,102,241,0.1);
    border: 1px solid rgba(99,102,241,0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    margin-bottom: 0.75rem;
}
.ru-field-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.35rem;
    display: block;
}
.ru-field {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.9rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    background: #fff;
    color: #0f172a;
}
.ru-field:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}
.ru-field.is-invalid { border-color: #ef4444; }
.ru-guest-note {
    font-size: 0.72rem;
    color: #64748b;
    margin-top: 0.75rem;
    line-height: 1.5;
    display: flex;
    gap: 0.4rem;
}

/* ── Already-exists alert ────────────────────────── */
.ru-exists-alert {
    background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(251,191,36,0.05));
    border: 1.5px solid rgba(245,158,11,0.3);
    border-radius: 0.85rem;
    padding: 0.85rem 1rem;
    font-size: 0.82rem;
    color: #92400e;
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
}

/* ── Submit button ───────────────────────────────── */
.ru-submit-btn {
    width: 100%;
    padding: 0.9rem;
    border-radius: 0.9rem;
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 4px 18px rgba(16,185,129,0.4);
    margin-top: 1.35rem;
}
.ru-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(16,185,129,0.5); color: #fff; }
.ru-submit-btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; box-shadow: none; }

/* ── AI Loading Overlay ──────────────────────────── */
.ru-ai-overlay {
    position: fixed;
    inset: 0;
    z-index: 3000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background:
        radial-gradient(circle at 20% 10%, rgba(16, 185, 129, 0.2), transparent 40%),
        radial-gradient(circle at 85% 90%, rgba(99, 102, 241, 0.22), transparent 45%),
        rgba(2, 6, 23, 0.84);
    backdrop-filter: blur(8px);
}
.ru-ai-overlay.is-visible { display: flex; }
.ru-ai-card {
    width: min(560px, 100%);
    border-radius: 1.1rem;
    padding: 1.2rem 1.1rem 1rem;
    color: #e2e8f0;
    border: 1px solid rgba(148, 163, 184, 0.22);
    background: linear-gradient(145deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.82));
    box-shadow: 0 24px 70px rgba(2, 8, 23, 0.55);
}
@media (min-width: 576px) {
    .ru-ai-card { padding: 1.5rem 1.45rem 1.25rem; border-radius: 1.25rem; }
}
.ru-ai-head {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}
.ru-ai-spinner {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: 3px solid rgba(148, 163, 184, 0.28);
    border-top-color: #34d399;
    border-right-color: #60a5fa;
    animation: ruSpin 0.85s linear infinite;
}
@keyframes ruSpin { to { transform: rotate(360deg); } }
.ru-ai-title {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 800;
    color: #f8fafc;
}
.ru-ai-sub {
    margin: 0.2rem 0 0;
    font-size: 0.82rem;
    color: #94a3b8;
}
.ru-ai-progress {
    height: 8px;
    margin-top: 1rem;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(148, 163, 184, 0.18);
}
.ru-ai-progress-bar {
    width: 20%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #34d399, #60a5fa, #818cf8);
    box-shadow: 0 0 18px rgba(96, 165, 250, 0.5);
    transition: width 0.45s ease;
}
.ru-ai-steps {
    margin: 0.85rem 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.38rem;
}
.ru-ai-step {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    border-radius: 0.7rem;
    padding: 0.43rem 0.6rem;
    font-size: 0.79rem;
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(15, 23, 42, 0.4);
}
.ru-ai-step i { color: #475569; transition: color 0.2s ease; }
.ru-ai-step.is-active {
    color: #dbeafe;
    border-color: rgba(96, 165, 250, 0.38);
    background: linear-gradient(100deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.9));
}
.ru-ai-step.is-active i { color: #60a5fa; }
.ru-ai-step.is-done {
    color: #a7f3d0;
    border-color: rgba(52, 211, 153, 0.35);
    background: rgba(6, 78, 59, 0.3);
}
.ru-ai-step.is-done i { color: #34d399; }
.ru-ai-note {
    margin: 0.7rem 0 0;
    color: #cbd5e1;
    font-size: 0.75rem;
}

/* ── Benefit cards ───────────────────────────────── */
.ru-benefit {
    background: #fff;
    border: 1px solid rgba(15,23,42,0.07);
    border-radius: 1rem;
    padding: 1.25rem;
    height: 100%;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.ru-benefit:hover {
    border-color: rgba(16,185,129,0.25);
    box-shadow: 0 6px 20px rgba(16,185,129,0.08);
}
.ru-benefit-icon {
    width: 44px; height: 44px;
    border-radius: 0.75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.75rem;
}
</style>
@endpush

@section('content')
<div class="ru-page">
    <section class="section pb-5 pt-4">
        <div class="container">

            {{-- Hero --}}
            <div class="ru-hero">
                <div class="ru-hero-inner">
                    <p class="ru-hero-kicker"><i class="uil uil-bolt-alt me-1"></i>Free resume intelligence</p>
                    <h1 class="ru-hero-title">Get your resume scored in 2 minutes</h1>
                    <p class="ru-hero-sub">Upload your PDF and increase your chances of getting hired by up to <strong>+{{ random_int(72, 88) }}%</strong>. We read your contact details from the file when needed, score your resume, and match you to roles. New here? We’ll email you a link to set your password.</p>
                    <div class="ru-benefit-chips">
                        <span class="ru-chip"><i class="uil uil-check-circle"></i>ATS score</span>
                        <span class="ru-chip"><i class="uil uil-check-circle"></i>Skill gap analysis</span>
                        <span class="ru-chip"><i class="uil uil-check-circle"></i>Job recommendations</span>
                        <span class="ru-chip"><i class="uil uil-check-circle"></i>100% free</span>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-3 border-0 shadow-sm">
                    <i class="uil uil-exclamation-triangle me-2"></i>
                    <ul class="mb-0 list-unstyled d-inline">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row align-items-start g-4">

                {{-- Upload Form --}}
                <div class="col-lg-7">
                    <div class="ru-card">

                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <h2 class="h5 fw-bold mb-1">Upload your resume</h2>
                                <p class="text-muted small mb-0">PDF only · max 10 MB · analysed instantly. Your email and name are taken from the resume when you’re not signed in.</p>
                            </div>
                            @guest
                            <a href="{{ route('login', ['redirect' => route('resume.upload', [], false)]) }}" class="btn btn-outline-secondary btn-sm rounded-pill" style="font-size:0.75rem;white-space:nowrap;">
                                Already have an account? Sign in
                            </a>
                            @endguest
                        </div>

                        <form action="{{ auth()->check() ? route('resume.upload.store') : route('resume.guest-upload') }}" method="POST" enctype="multipart/form-data" id="resumeForm">
                            @csrf
                            @include('hirevo.partials.resume-dropzone')

                            @guest
                            <div class="mt-3">
                                <label class="ru-field-label mb-1" for="contact_email">Email <span class="text-muted fw-normal">(optional if it’s already in your PDF)</span></label>
                                <input type="email" name="contact_email" id="contact_email"
                                       class="ru-field @error('contact_email') is-invalid @enderror"
                                       value="{{ old('contact_email') }}"
                                       placeholder="you@example.com  use if your CV is scanned or email wasn’t detected"
                                       autocomplete="email">
                                @error('contact_email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <p class="text-muted small mb-0 mt-2" style="font-size:0.78rem;">
                                    <i class="uil uil-info-circle text-primary me-1"></i>
                                    We read your email from the resume when possible. If the PDF has no selectable text (image scan), enter your email here the same upload button.
                                </p>
                            </div>
                            @endguest

                            <button type="submit" class="ru-submit-btn" id="submitBtn" disabled>
                                <i class="uil uil-chart-line me-2"></i>Analyse my resume
                            </button>
                        </form>

                    </div>
                </div>

                {{-- Benefits sidebar --}}
                <div class="col-lg-5">
                    <p class="fw-bold text-dark small text-uppercase mb-3" style="letter-spacing:0.07em;">What you'll get</p>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-12 col-xl-6">
                            <div class="ru-benefit">
                                <div class="ru-benefit-icon" style="background:rgba(16,185,129,0.1);color:#059669;">
                                    <i class="uil uil-chart-line"></i>
                                </div>
                                <h6 class="fw-700 mb-1" style="font-size:0.88rem;">ATS Score</h6>
                                <p class="text-muted mb-0" style="font-size:0.78rem;">See how well recruiters' systems parse your resume.</p>
                            </div>
                        </div>
                        <div class="col-6 col-lg-12 col-xl-6">
                            <div class="ru-benefit">
                                <div class="ru-benefit-icon" style="background:rgba(99,102,241,0.1);color:#4338ca;">
                                    <i class="uil uil-lightbulb-alt"></i>
                                </div>
                                <h6 class="fw-700 mb-1" style="font-size:0.88rem;">AI Summary</h6>
                                <p class="text-muted mb-0" style="font-size:0.78rem;">Extracted skills and highlights from your CV.</p>
                            </div>
                        </div>
                        <div class="col-6 col-lg-12 col-xl-6">
                            <div class="ru-benefit">
                                <div class="ru-benefit-icon" style="background:rgba(245,158,11,0.1);color:#b45309;">
                                    <i class="uil uil-briefcase-alt"></i>
                                </div>
                                <h6 class="fw-700 mb-1" style="font-size:0.88rem;">Job Matches</h6>
                                <p class="text-muted mb-0" style="font-size:0.78rem;">Live openings and goals ranked by your skill match.</p>
                            </div>
                        </div>
                        <div class="col-6 col-lg-12 col-xl-6">
                            <div class="ru-benefit">
                                <div class="ru-benefit-icon" style="background:rgba(239,68,68,0.08);color:#b91c1c;">
                                    <i class="uil uil-exclamation-octagon"></i>
                                </div>
                                <h6 class="fw-700 mb-1" style="font-size:0.88rem;">Skill Gaps</h6>
                                <p class="text-muted mb-0" style="font-size:0.78rem;">Know exactly what to add to unlock better roles.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3 p-3" style="background:rgba(16,185,129,0.07);border:1px solid rgba(16,185,129,0.2);">
                        <p class="small fw-600 text-success mb-1"><i class="uil uil-check-circle me-1"></i>Your data is safe</p>
                        <p class="small text-muted mb-0" style="font-size:0.75rem;">We only use your resume to generate your score and matches. No data is shared with third parties without your consent.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<div id="ruAiOverlay" class="ru-ai-overlay" aria-hidden="true" role="status" aria-live="polite">
    <div class="ru-ai-card">
        <div class="ru-ai-head">
            <span class="ru-ai-spinner" aria-hidden="true"></span>
            <div>
                <p class="ru-ai-title">AI is analyzing your resume</p>
                <p class="ru-ai-sub" id="ruAiStatusText">Preparing secure upload...</p>
            </div>
        </div>
        <div class="ru-ai-progress" aria-hidden="true">
            <div id="ruAiProgressBar" class="ru-ai-progress-bar"></div>
        </div>
        <ul class="ru-ai-steps">
            <li class="ru-ai-step" data-step="0"><span><i class="uil uil-cloud-upload"></i> Uploading your PDF</span><small>Queued</small></li>
            <li class="ru-ai-step" data-step="1"><span><i class="uil uil-file-search-alt"></i> Reading and parsing content</span><small>Queued</small></li>
            <li class="ru-ai-step" data-step="2"><span><i class="mdi mdi-robot-outline"></i> Running AI scoring and insights</span><small>Queued</small></li>
            <li class="ru-ai-step" data-step="3"><span><i class="uil uil-check-circle"></i> Preparing your report</span><small>Queued</small></li>
        </ul>
        <p class="ru-ai-note mb-0"><i class="uil uil-shield-check me-1"></i>Please keep this tab open. This usually takes a few seconds.</p>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var zone       = document.getElementById('uploadZone');
    var input      = document.getElementById('resumeFileInput');
    var fileChip   = document.getElementById('ru-file-chip');
    var fileText   = document.getElementById('ru-file-text');
    var dzTitle    = document.getElementById('ruDzTitle');
    var dzSub      = document.getElementById('ruDzSub');
    var dzIcon     = document.getElementById('ruDzIcon');
    var removeBtn  = document.getElementById('ruRemoveFile');
    var browseBtn  = document.getElementById('ruBrowseBtn');
    var submitBtn  = document.getElementById('submitBtn');
    var form       = document.getElementById('resumeForm');
    var aiOverlay  = document.getElementById('ruAiOverlay');
    var aiStatus   = document.getElementById('ruAiStatusText');
    var aiBar      = document.getElementById('ruAiProgressBar');
    var aiSteps    = Array.prototype.slice.call(document.querySelectorAll('.ru-ai-step'));
    var isSubmitting = false;
    if (!zone || !input) return;

    function setFile(name) {
        var has = !!name;
        zone.classList.toggle('has-file', has);
        if (dzTitle) dzTitle.textContent = has ? 'Resume selected!' : 'Drop your resume here';
        if (dzSub)   dzSub.style.display = has ? 'none' : '';
        if (dzIcon)  dzIcon.className = 'uil ' + (has ? 'uil-file-check-alt' : 'uil-file-upload-alt');
        if (fileChip) fileChip.classList.toggle('d-none', !has);
        if (fileText && has) fileText.textContent = name;
        if (submitBtn) submitBtn.disabled = !has;
    }

    browseBtn && browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.click(); });
    removeBtn && removeBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.value = ''; setFile(''); });
    zone.addEventListener('click', function(e) {
        if (e.target === browseBtn || e.target === removeBtn || e.target.closest('#ruRemoveFile') || e.target.closest('#ruBrowseBtn')) return;
        input.click();
    });
    zone.addEventListener('dragover',  function(e) { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', function()  { zone.classList.remove('dragover'); });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            var f = e.dataTransfer.files[0];
            if (f.type !== 'application/pdf') {
                alert('Only PDF files are supported.');
                return;
            }
            input.files = e.dataTransfer.files;
            setFile(f.name);
        }
    });
    input.addEventListener('change', function() {
        setFile(input.files.length ? input.files[0].name : '');
    });

    function setAiStep(index) {
        for (var i = 0; i < aiSteps.length; i++) {
            aiSteps[i].classList.remove('is-active');
            aiSteps[i].classList.remove('is-done');
            aiSteps[i].lastElementChild.textContent = 'Queued';
            if (i < index) {
                aiSteps[i].classList.add('is-done');
                aiSteps[i].lastElementChild.textContent = 'Done';
            } else if (i === index) {
                aiSteps[i].classList.add('is-active');
                aiSteps[i].lastElementChild.textContent = 'Running';
            }
        }
    }

    function startAiLoadingUI() {
        if (!aiOverlay || !aiBar || !aiStatus) return;
        aiOverlay.classList.add('is-visible');
        aiOverlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        var labels = [
            'Uploading and validating your file...',
            'Extracting text and resume structure...',
            'Generating AI score and suggestions...',
            'Finalizing your personalized report...'
        ];
        var widths = ['22%', '48%', '76%', '94%'];
        var step = 0;

        setAiStep(step);
        aiStatus.textContent = labels[step];
        aiBar.style.width = widths[step];

        window.setInterval(function() {
            if (step < labels.length - 1) step++;
            setAiStep(step);
            aiStatus.textContent = labels[step];
            aiBar.style.width = widths[step];
        }, 1300);
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="uil uil-spin uil-spinner me-2"></i>Processing...';
            }
            startAiLoadingUI();
        });
    }

    setFile(input.files.length ? input.files[0].name : '');
})();
</script>
@endpush
@endsection
