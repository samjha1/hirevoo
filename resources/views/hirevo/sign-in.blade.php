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
