@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<section class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card auth-box">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h5>Reset password</h5>
                            <p class="text-muted mb-0">Create a new password for your account.</p>
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
                                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email" inputmode="email" spellcheck="false" autocapitalize="none" title="Enter a valid email like name@company.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm password</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Reset password</button>
                        </form>

                        <div class="mt-3 text-center">
                            <a href="{{ route('login', $role === 'referrer' ? ['role' => 'referrer'] : []) }}" class="text-decoration-underline">Back to sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('hirevo.partials.auth-email-validation')
@endsection
