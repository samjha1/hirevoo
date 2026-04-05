@extends('layouts.employer')

@section('title', 'Company Profile')
@section('header_title', 'Company Profile')

@section('content')
    @php
        $profile = $profile ?? null;
        $user = $user ?? auth()->user();
        $jobsCount = $jobsCount ?? 0;
        $activeJobsCount = $activeJobsCount ?? 0;
        $credits = $profile ? (int) $profile->credits : 0;
        $photoUrl = null;
        if ($profile && $profile->profile_photo) {
            $photoUrl = str_starts_with($profile->profile_photo, 'uploads/') ? asset($profile->profile_photo) : asset('storage/' . $profile->profile_photo);
        }
    @endphp

    {{-- Profile header (Apna-style) --}}
    <div class="card employer-card mb-4 overflow-hidden">
        <div class="profile-cover position-relative" style="height: 120px; background: linear-gradient(135deg, var(--hirevo-primary) 0%, var(--hirevo-secondary) 100%);"></div>
        <div class="card-body position-relative pt-0">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-4">
                <div class="profile-avatar-wrap position-relative">
                    <div class="profile-avatar-circle rounded-circle border border-4 border-white shadow-sm bg-white d-flex align-items-center justify-content-center overflow-hidden">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Profile" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            <div class="d-none w-100 h-100 d-flex align-items-center justify-content-center bg-light text-primary">
                                <i class="mdi mdi-domain mdi-48px"></i>
                            </div>
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-primary">
                                <i class="mdi mdi-domain mdi-48px"></i>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex-grow-1 mt-2 mt-md-0">
                    <h4 class="fw-600 text-dark mb-1">{{ $profile ? $profile->company_name : 'Company' }}</h4>
                    <p class="text-muted mb-1">{{ $user->name }}</p>
                    @if($profile)
                        <p class="text-muted small mb-2">{{ $profile->company_email }}</p>
                        @if($profile->designation || $profile->department)
                            <p class="small text-muted mb-0">
                                @if($profile->designation){{ $profile->designation }}@endif
                                @if($profile->designation && $profile->department) · @endif
                                @if($profile->department){{ $profile->department }}@endif
                            </p>
                        @endif
                    @else
                        <p class="text-muted small mb-2">Complete your profile below.</p>
                    @endif
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        @if($profile)
                            @if($profile->is_approved)
                                <span class="badge bg-success"><i class="mdi mdi-check-circle-outline me-1"></i>Verified</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="mdi mdi-clock-outline me-1"></i>Pending verification</span>
                            @endif
                            @if($profile->approved_at)
                                <span class="text-muted small">Member since {{ $profile->approved_at->format('M Y') }}</span>
                            @elseif($profile->created_at)
                                <span class="text-muted small">Joined {{ $profile->created_at->format('M Y') }}</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Incomplete</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats row (Apna-style) --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card employer-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="mdi mdi-briefcase-outline mdi-24px text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total jobs</p>
                        <h5 class="fw-600 mb-0">{{ $jobsCount }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card employer-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="mdi mdi-briefcase-check-outline mdi-24px text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Active jobs</p>
                        <h5 class="fw-600 mb-0">{{ $activeJobsCount }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card employer-card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="mdi mdi-coin-outline mdi-24px text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Available credits</p>
                        <h5 class="fw-600 mb-0">{{ $credits }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Company details card + Edit form --}}
    <div class="card employer-card">
        <div class="card-body p-4">
            <h5 class="fw-600 mb-1">Edit</h5>
            <p class="text-muted small mb-4">Update your profile and company information.</p>

            <form method="POST" action="{{ route('employer.profile.update') }}" enctype="multipart/form-data">
                @csrf

                {{-- Basic Details --}}
                <h6 class="fw-600 text-dark mb-3">Basic Details</h6>
                <div class="row align-items-start">
                    <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                        <label class="form-label fw-500">Profile / Company photo</label>
                        <div class="d-inline-block position-relative">
                            <div class="rounded-circle border overflow-hidden bg-light d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                @if($photoUrl)
                                    <img id="profile-photo-preview" src="{{ $photoUrl }}" alt="Profile" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; document.getElementById('profile-photo-placeholder').classList.remove('d-none');">
                                    <div id="profile-photo-placeholder" class="d-none w-100 h-100 d-flex align-items-center justify-content-center text-primary"><i class="mdi mdi-domain mdi-48px"></i></div>
                                @else
                                    <div id="profile-photo-placeholder" class="w-100 h-100 d-flex align-items-center justify-content-center text-primary"><i class="mdi mdi-domain mdi-48px"></i></div>
                                    <img id="profile-photo-preview" src="" alt="" class="w-100 h-100 object-fit-cover d-none">
                                @endif
                            </div>
                            <label for="profile_photo" class="btn btn-sm btn-outline-primary mt-2 rounded-pill cursor-pointer">
                                <i class="mdi mdi-camera me-1"></i>Change photo
                            </label>
                            <input type="file" class="d-none" id="profile_photo" name="profile_photo" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-500">Your full name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="e.g. Shivam Jha" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="company_email" class="form-label fw-500">Email</label>
                            <input type="email" class="form-control @error('company_email') is-invalid @enderror" id="company_email" name="company_email" value="{{ old('company_email', $profile ? $profile->company_email : $user->email) }}" placeholder="company@example.com" required>
                            <small class="text-muted">Use a work/company email address.</small>
                            @error('company_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-500">Mobile</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 8130734125">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="company_name" class="form-label fw-500">Company name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $profile ? $profile->company_name : '') }}" placeholder="Your company name" required>
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="designation" class="form-label fw-500">Your designation</label>
                                <input type="text" class="form-control @error('designation') is-invalid @enderror" id="designation" name="designation" value="{{ old('designation', $profile ? $profile->designation : '') }}" placeholder="e.g. HR Manager">
                                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="department" class="form-label fw-500">Department</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department" value="{{ old('department', $profile ? $profile->department : '') }}" placeholder="e.g. Human Resources">
                                @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GST / ISD-GST Details --}}
                <hr class="my-4">
                <h6 class="fw-600 text-dark mb-3">GST / ISD-GST Details</h6>
                <div class="mb-3">
                    <label for="gstin" class="form-label fw-500">GST / ISD-GST No.</label>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <input type="text" class="form-control @error('gstin') is-invalid @enderror" id="gstin" name="gstin" value="{{ old('gstin', $profile ? $profile->gstin : '') }}" placeholder="e.g. 09AAHCI5163N1Z0" maxlength="20">
                        @if($profile && $profile->gst_verified)
                            <span class="badge bg-success"><i class="mdi mdi-check-circle-outline me-1"></i>Verified</span>
                        @endif
                    </div>
                    @error('gstin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                @if($profile && ($profile->company_legal_name || $profile->company_address))
                    <div class="alert alert-light border mb-3">
                        <p class="fw-500 small mb-2">We found following company details</p>
                        @if($profile->company_legal_name)
                            <p class="small mb-1"><strong>Company name:</strong> {{ $profile->company_legal_name }}</p>
                        @endif
                        @if($profile->company_address)
                            <p class="small mb-0"><strong>Address:</strong> {{ $profile->company_address }}</p>
                        @endif
                    </div>
                @endif
                <div class="mb-3">
                    <label for="company_legal_name" class="form-label fw-500">Legal company name</label>
                    <input type="text" class="form-control @error('company_legal_name') is-invalid @enderror" id="company_legal_name" name="company_legal_name" value="{{ old('company_legal_name', $profile ? $profile->company_legal_name : '') }}" placeholder="As per GST registration">
                    @error('company_legal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="company_address" class="form-label fw-500">Registered address</label>
                    <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address" name="company_address" rows="3" placeholder="Full address as per GST">{{ old('company_address', $profile ? $profile->company_address : '') }}</textarea>
                    @error('company_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="invoice_consent" value="1" id="invoice_consent" {{ old('invoice_consent', $profile && $profile->invoice_consent) ? 'checked' : '' }}>
                    <label class="form-check-label" for="invoice_consent">
                        I verify my company details and understand that the invoices would be generated using the same information.
                    </label>
                </div>

                <hr class="my-4">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Save changes</button>
                    <a href="{{ route('employer.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('profile_photo') && document.getElementById('profile_photo').addEventListener('change', function (e) {
            var f = e.target.files[0];
            if (!f || !f.type.match('image.*')) return;
            var r = new FileReader();
            r.onload = function () {
                var preview = document.getElementById('profile-photo-preview');
                var placeholder = document.getElementById('profile-photo-placeholder');
                if (preview) { preview.src = r.result; preview.classList.remove('d-none'); }
                if (placeholder) placeholder.classList.add('d-none');
            };
            r.readAsDataURL(f);
        });
    </script>
    @endpush
@endsection
