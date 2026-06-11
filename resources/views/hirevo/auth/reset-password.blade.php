@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<section class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12">
                <div class="card auth-box">
                    <div class="row g-0">
                        <div class="col-lg-6 text-center">
                            <div class="card-body p-4">
                                <a href="{{ route('home') }}">
                                    <img src="{{ asset('images/12575-2.svg') }}" alt="Hirevo" class="hirevo-logo">
                                </a>
                                <div class="mt-5">
                                    <img src="{{ asset($theme.'/assets/images/auth/reset-password.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="auth-content card-body p-5 h-100 text-white">
                                <div class="w-100">
                                    <div class="text-center mb-4">
                                        <h5>Reset password</h5>
                                        <p class="text-white-70 mb-0">Create a new password for your account.</p>
                                    </div>

                                    @if($errors->any())
                                        <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
                                    @endif

                                    <form method="POST" action="{{ route('password.update') }}" class="auth-form" novalidate>
                                        @csrf
                                        <input type="hidden" name="token" value="{{ $token }}">
                                        @if($role === 'referrer')
                                            <input type="hidden" name="role" value="referrer">
                                        @endif

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" placeholder="name@company.com" required autocomplete="email" inputmode="email" spellcheck="false" autocapitalize="none" title="Enter a valid email like name@company.com">
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 auth-password-wrap">
                                            <label for="password" class="form-label">New password</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter your new password" required autofocus autocomplete="new-password">
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="mb-3 auth-password-wrap">
                                            <label for="password_confirmation" class="form-label">Confirm password</label>
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Re-enter your new password" required autocomplete="new-password">
                                        </div>

                                        <button type="submit" class="btn btn-signin-submit w-100">Reset password</button>
                                    </form>

                                    <div class="mt-4 text-center">
                                        <p class="mb-0">Remembered it? <a href="{{ route('login', $role === 'referrer' ? ['role' => 'referrer'] : []) }}" class="fw-medium text-white text-decoration-underline">Back to sign in</a></p>
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
@include('hirevo.partials.auth-email-validation')
@endsection
