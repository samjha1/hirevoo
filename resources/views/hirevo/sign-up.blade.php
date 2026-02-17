@extends('layouts.app')

@section('title', 'Sign Up')

@section('content')
@php
    $roleVal = old('role', $defaultRole ?? request('role', 'candidate'));
    $isEmployer = $roleVal === 'referrer';
    $isCandidate = $roleVal === 'candidate';
@endphp
<section class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12">
                <div class="card auth-box">
                    <div class="row align-items-center">
                        <div class="col-lg-6 text-center">
                            <div class="card-body p-4">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset($theme.'/assets/images/logo-light.png') }}" alt="" class="logo-light">
                                    <img src="{{ asset($theme.'/assets/images/logo-dark.png') }}" alt="" class="logo-dark">
                                </a>
                                <div class="mt-5">
                                    <img src="{{ asset($theme.'/assets/images/auth/sign-up.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="auth-content card-body p-5 text-white">
                                <div class="w-100">
                                    @if($isEmployer)
                                    <div class="text-center mb-3">
                                        <h5>Employer Sign up</h5>
                                        <p class="text-white-70 mb-0">Use your company work email to register</p>
                                    </div>
                                    @elseif($isCandidate)
                                    <div class="text-center mb-3">
                                        <h5>Candidate Sign up</h5>
                                        <p class="text-white-70 mb-0">Create your account and get started</p>
                                    </div>
                                    @else
                                    <div class="text-center mb-3">
                                        <h5>Let's Get Started</h5>
                                        <p class="text-white-70 mb-0">Sign up as EdTech Partner</p>
                                    </div>
                                    @endif
                                    <form method="POST" action="{{ route('register') }}" class="auth-form">
                                        @csrf
                                        @if(request()->has('role'))<input type="hidden" name="role" value="{{ $roleVal }}">@endif
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your name" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="contact" class="form-label">Contact</label>
                                            <input type="text" class="form-control @error('contact') is-invalid @enderror" id="contact" name="contact" value="{{ old('contact') }}" placeholder="Phone number" required>
                                            @error('contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">{{ $isEmployer ? 'Work Email' : 'Email' }}</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ $isEmployer ? 'yourname@company.com' : 'Enter your email' }}" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            @if($isEmployer)
                                            <small class="text-white-50">Use your company email. Gmail, Yahoo, etc. are not allowed.</small>
                                            @endif
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Create Password</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Create password" required>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                        </div>
                                        @if(!request()->has('role'))
                                        <div class="mb-3">
                                            <label for="role_select" class="form-label">I am a</label>
                                            <select class="form-select @error('role') is-invalid @enderror" id="role_select" name="role">
                                                <option value="candidate" {{ $roleVal === 'candidate' ? 'selected' : '' }}>Student</option>
                                                <option value="referrer" {{ $roleVal === 'referrer' ? 'selected' : '' }}>Employer</option>
                                                <option value="edtech" {{ $roleVal === 'edtech' ? 'selected' : '' }}>EdTech Partner</option>
                                            </select>
                                            <small class="text-white-50">Changing this will reload the form.</small>
                                        </div>
                                        @endif
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                                <label class="form-check-label" for="terms">I agree to the <a href="javascript:void(0)" class="text-white text-decoration-underline">Terms and conditions</a></label>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-white btn-hover w-100">Sign Up</button>
                                        </div>
                                    </form>
                                    <div class="mt-3 text-center">
                                        <p class="mb-0">Already a member? <a href="{{ route('login') }}" class="fw-medium text-white text-decoration-underline">Sign In</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@if(!request()->has('role'))
@push('scripts')
<script>
document.getElementById('role_select')?.addEventListener('change', function() {
    var role = this.value;
    window.location.href = '{{ route("register") }}?role=' + role;
});
</script>
@endpush
@endif
@endsection
