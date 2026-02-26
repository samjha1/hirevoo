@extends('layouts.app')

@section('title', 'Submit CV - Get ATS Score')

@push('styles')
<style>
    .resume-upload-page .upload-hero-title { font-size: 1.75rem; font-weight: 700; }
    .resume-upload-page .upload-hero-sub { color: #64748b; font-size: 1rem; }
    .resume-upload-page .dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 20px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        transition: all 0.25s ease;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 220px;
        position: relative;
        cursor: pointer;
    }
    .resume-upload-page .dropzone:hover {
        border-color: var(--hirevo-secondary);
        background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 30%);
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);
    }
    .resume-upload-page .dropzone.dragover {
        border-color: var(--hirevo-primary);
        background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 30%);
        box-shadow: 0 4px 24px rgba(11, 31, 59, 0.12);
    }
    .resume-upload-page .dropzone.has-file {
        border-style: solid;
        border-color: var(--hirevo-secondary);
        background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 100%);
    }
    .resume-upload-page .dropzone-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, var(--hirevo-secondary), #059669);
        color: #fff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }
    .resume-upload-page .dropzone.has-file .dropzone-icon {
        background: linear-gradient(135deg, var(--hirevo-primary), #162d4d);
    }
    .resume-upload-page .browse-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        background: var(--hirevo-primary);
        color: #fff;
        border: none;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .resume-upload-page .browse-btn:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(11, 31, 59, 0.3); }
    .resume-upload-page .file-name-display {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        font-weight: 500;
        font-size: 0.95rem;
        margin-top: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .resume-upload-page .file-name-display .remove-file {
        color: #94a3b8;
        cursor: pointer;
        padding: 0 0.25rem;
        border-radius: 4px;
        transition: color 0.15s, background 0.15s;
    }
    .resume-upload-page .file-name-display .remove-file:hover { color: #dc2626; background: #fef2f2; }
    .resume-upload-page .analyze-btn {
        padding: 0.85rem 2rem;
        font-weight: 600;
        border-radius: 12px;
        font-size: 1rem;
        background: linear-gradient(135deg, var(--hirevo-secondary), #059669);
        border: none;
        color: #fff;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .resume-upload-page .analyze-btn:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5); }
    .resume-upload-page .analyze-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .resume-upload-page .benefit-card {
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem;
        transition: all 0.2s ease;
        background: #fff;
        height: 100%;
    }
    .resume-upload-page .benefit-card:hover {
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.08);
    }
    .resume-upload-page .benefit-card .benefit-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }
    .resume-upload-page .benefit-card:nth-child(1) .benefit-icon { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .resume-upload-page .benefit-card:nth-child(2) .benefit-icon { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
    .resume-upload-page .benefit-card:nth-child(3) .benefit-icon { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
    .resume-upload-page .illustration-wrap {
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%);
        padding: 2rem;
    }
    .resume-upload-page .illustration-wrap img { max-height: 260px; width: auto; margin: 0 auto; display: block; }
    .resume-upload-page .accept-note { font-size: 0.8rem; color: #94a3b8; }
</style>
@endpush

@section('content')
    <div class="resume-upload-page">
        <section class="section">
            <div class="container">
                <nav class="mb-3" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-14">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Submit CV</li>
                    </ol>
                </nav>

                <div class="row align-items-start">
                    {{-- Left: Illustration (desktop) --}}
                    <div class="col-lg-4 d-none d-lg-block">
                        <div class="illustration-wrap sticky-top" style="top: 100px;">
                            @if(file_exists(public_path('images/resume-upload.svg')))
                                <img src="{{ asset('images/resume-upload.svg') }}" alt="Upload your resume" class="img-fluid">
                            @else
                                <div class="text-center text-success py-5">
                                    <i class="uil uil-file-upload-alt" style="font-size: 6rem; opacity: 0.4;"></i>
                                    <p class="mt-2 text-muted small">Get your resume scored</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Right: Upload form + benefits --}}
                    <div class="col-lg-8">
                        <div class="mb-4">
                            <h1 class="upload-hero-title mb-2">Get your resume scored</h1>
                            <p class="upload-hero-sub mb-0">Upload your CV and get your ATS score, AI summary, and job recommendations in one go.</p>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="card-body p-4 p-lg-5">
                                <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data" id="resumeForm">
                                    @csrf
                                    @if($errors->any())
                                        <div class="alert alert-danger rounded-3 mb-4 d-flex align-items-start">
                                            <i class="uil uil-exclamation-triangle me-2 mt-1"></i>
                                            <ul class="mb-0 list-unstyled">
                                                @foreach($errors->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="mb-4 position-relative">
                                        <div class="dropzone position-relative" id="uploadZone">
                                            <input type="file" name="resume" id="resume" class="position-absolute @error('resume') is-invalid @enderror" accept=".pdf,application/pdf" required style="cursor: pointer; width: 100%; height: 100%; top: 0; left: 0; opacity: 0;">
                                            <div class="dropzone-icon">
                                                <i class="uil uil-file-upload-alt" id="zoneIcon"></i>
                                            </div>
                                            <p class="mb-2 fw-600 text-dark" id="zoneTitle">Drop your resume here</p>
                                            <p class="text-muted small mb-3" id="zoneSub">or <span class="browse-btn" id="browseBtn">Browse files</span></p>
                                            <p class="accept-note mb-0">PDF only · Max 10 MB</p>
                                            <div class="file-name-display d-none" id="fileNameDisplay">
                                                <i class="uil uil-file-alt text-primary"></i>
                                                <span id="fileNameText"></span>
                                                <span class="remove-file ms-2" id="removeFile" title="Remove">×</span>
                                            </div>
                                        </div>
                                        @error('resume')
                                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn analyze-btn px-4" id="submitBtn" disabled>
                                            <i class="uil uil-analyze me-2"></i>Analyze my resume
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <p class="text-center text-muted small mb-4">What you'll get after uploading</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon"><i class="uil uil-chart-line"></i></div>
                                    <h6 class="fw-600 mb-1">ATS score</h6>
                                    <p class="text-muted small mb-0">See how well your resume parses in recruiter systems.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon"><i class="uil uil-document-layout-left"></i></div>
                                    <h6 class="fw-600 mb-1">Summary & skills</h6>
                                    <p class="text-muted small mb-0">AI-generated summary and extracted skills.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="benefit-card">
                                    <div class="benefit-icon"><i class="uil uil-briefcase-alt"></i></div>
                                    <h6 class="fw-600 mb-1">Job recommendations</h6>
                                    <p class="text-muted small mb-0">Posted jobs and job goals that match your profile.</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4"><i class="uil uil-arrow-left me-1"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
    (function() {
        var zone = document.getElementById('uploadZone');
        var input = document.getElementById('resume');
        var fileNameDisplay = document.getElementById('fileNameDisplay');
        var fileNameText = document.getElementById('fileNameText');
        var zoneTitle = document.getElementById('zoneTitle');
        var zoneSub = document.getElementById('zoneSub');
        var zoneIcon = document.getElementById('zoneIcon');
        var browseBtn = document.getElementById('browseBtn');
        var removeFile = document.getElementById('removeFile');
        var submitBtn = document.getElementById('submitBtn');
        if (!zone || !input) return;

        function setFile(name) {
            var hasFile = !!name;
            if (fileNameDisplay) {
                fileNameDisplay.classList.toggle('d-none', !hasFile);
                if (fileNameText) fileNameText.textContent = name || '';
            }
            if (zone) {
                zone.classList.toggle('has-file', hasFile);
                if (zoneTitle) zoneTitle.textContent = hasFile ? 'Resume selected' : 'Drop your resume here';
                if (zoneSub) zoneSub.style.display = hasFile ? 'none' : 'block';
                if (zoneIcon) {
                    zoneIcon.className = hasFile ? 'uil uil-file-alt' : 'uil uil-file-upload-alt';
                }
            }
            if (submitBtn) submitBtn.disabled = !hasFile;
        }

        if (browseBtn) browseBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.click(); });
        if (removeFile) removeFile.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); input.value = ''; setFile(''); });
        zone.addEventListener('click', function(e) { if (e.target !== browseBtn && e.target !== removeFile) input.click(); });
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                setFile(e.dataTransfer.files[0].name);
            }
        });
        input.addEventListener('change', function() {
            setFile(input.files.length ? input.files[0].name : '');
        });
        setFile(input.files.length ? input.files[0].name : '');
    })();
    </script>
    @endpush
@endsection
