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
                                    @if($errors->has('email'))
                                        <div class="alert alert-danger py-2 mb-3">{{ $errors->first('email') }}</div>
                                    @endif
                                    <div class="d-grid gap-2 mb-3">
                                        <a href="{{ route('auth.google.redirect') }}?role={{ $roleVal }}{{ request()->has('redirect') ? '&redirect=' . urlencode(request('redirect')) : '' }}" class="btn btn-light btn-hover d-flex align-items-center justify-content-center gap-2">
                                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.29 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                                            Sign up with Google
                                        </a>
                                        <a href="{{ route('auth.microsoft.redirect') }}?role={{ $roleVal }}{{ request()->has('redirect') ? '&redirect=' . urlencode(request('redirect')) : '' }}" class="btn btn-light btn-hover d-flex align-items-center justify-content-center gap-2">
                                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23"><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>
                                            Sign up with Microsoft
                                        </a>
                                    </div>
                                    <div class="position-relative mb-3">
                                        <hr class="text-white-50">
                                        <span class="position-absolute top-50 start-50 translate-middle bg-auth px-2 text-white-50 small">or</span>
                                    </div>
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
                                                <option value="candidate" {{ $roleVal === 'candidate' ? 'selected' : '' }}>candidate</option>
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
