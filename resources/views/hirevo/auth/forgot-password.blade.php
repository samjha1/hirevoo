@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<section class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card auth-box">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h5>Forgot your password?</h5>
                            <p class="text-muted mb-0">Enter your email and we will send you a reset link.</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success py-2 mb-3">{{ session('status') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            @if($role === 'referrer')
                                <input type="hidden" name="role" value="referrer">
                            @endif

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Send password reset link</button>
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
@endsection
