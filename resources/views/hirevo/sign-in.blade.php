@extends('layouts.app')

@section('title', 'Sign In')

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
                                    <img src="{{ asset($theme.'/assets/images/logo-light.png') }}" alt="" class="logo-light">
                                    <img src="{{ asset($theme.'/assets/images/logo-dark.png') }}" alt="" class="logo-dark">
                                </a>
                                <div class="mt-5">
                                    <img src="{{ asset($theme.'/assets/images/auth/sign-in.png') }}" alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="auth-content card-body p-5 h-100 text-white">
                                <div class="w-100">
                                    <div class="text-center mb-4">
                                        <h5>Welcome Back!</h5>
                                        <p class="text-white-70">Sign in to continue to Hirevo.</p>
                                    </div>
                                    @if($errors->has('email'))
                                        <div class="alert alert-danger py-2 mb-3">{{ $errors->first('email') }}</div>
                                    @endif
                                    <div class="d-grid gap-2 mb-3">
                                        <a href="{{ route('auth.google.redirect') }}{{ request()->has('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}" class="btn btn-light btn-hover d-flex align-items-center justify-content-center gap-2">
                                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.16 7.09-10.29 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                                            Sign in with Google
                                        </a>
                                        <a href="{{ route('auth.microsoft.redirect') }}{{ request()->has('redirect') ? '?redirect=' . urlencode(request('redirect')) : '' }}" class="btn btn-light btn-hover d-flex align-items-center justify-content-center gap-2">
                                            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23"><path fill="#f35325" d="M1 1h10v10H1z"/><path fill="#81bc06" d="M12 1h10v10H12z"/><path fill="#05a6f0" d="M1 12h10v10H1z"/><path fill="#ffba08" d="M12 12h10v10H12z"/></svg>
                                            Sign in with Microsoft
                                        </a>
                                    </div>
                                    <div class="position-relative mb-3">
                                        <hr class="text-white-50">
                                        <span class="position-absolute top-50 start-50 translate-middle bg-auth px-2 text-white-50 small">or</span>
                                    </div>
                                    <form method="POST" action="{{ route('login') }}" class="auth-form">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Enter your password" required>
                                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                                <label class="form-check-label" for="remember">Remember me</label>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-white btn-hover w-100">Sign In</button>
                                        </div>
                                    </form>
                                    <div class="mt-4 text-center">
                                        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="fw-medium text-white text-decoration-underline">Sign Up</a></p>
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
@endsection
