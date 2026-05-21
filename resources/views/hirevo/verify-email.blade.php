@extends('layouts.app')

@section('title', 'Verify Email - Hirevo')

@section('content')
<section class="bg-auth">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card auth-box">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                <i class="mdi mdi-email-outline text-primary" style="font-size: 35px;"></i>
                            </div>
                            <h5 class="fw-600">Verify Your Email</h5>
                            <p class="text-muted small mb-0">We sent an OTP to</p>
                            <p class="text-muted small"><strong>{{ $email }}</strong></p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                                {{ $errors->first() }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Send OTP Section - Shown First -->
                        @if(!$otpSent)
                            <div class="mb-4">
                                <p class="text-muted mb-3">We'll send a One-Time Password to verify your email address.</p>
                                <form method="POST" action="{{ route('send-otp') }}" id="sendOtpForm">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-600">
                                        <i class="mdi mdi-send me-2"></i> Send OTP
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- OTP Verification Section - Shown After OTP Sent -->
                        @if($otpSent)
                            <div class="alert alert-info alert-dismissible fade show py-2 mb-3" role="alert">
                                <i class="mdi mdi-check-circle me-2"></i> OTP has been sent to your email
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <form method="POST" action="{{ route('verify-email-otp') }}" id="otpForm">
                                @csrf

                                <div class="mb-4">
                                    <label for="otp" class="form-label fw-500">Enter OTP</label>
                                    <input type="text" class="form-control form-control-lg text-center @error('otp') is-invalid @enderror" 
                                           id="otp" name="otp" placeholder="000000" 
                                           maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required 
                                           style="font-size: 24px; letter-spacing: 10px; font-weight: 600;">
                                    @error('otp')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">Enter the 6-digit OTP sent to your email</small>
                                </div>

                                <div class="mb-3">
                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-600">
                                        <i class="mdi mdi-check-circle me-2"></i> Verify Email
                                    </button>
                                </div>
                            </form>

                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="text-muted small mb-2">Didn't receive the OTP?</p>
                                
                                <button type="button" class="btn btn-link btn-sm text-decoration-none" id="resendBtn" disabled>
                                    Resend OTP <span id="resendTimer">(60s)</span>
                                </button>
                                <form method="POST" action="{{ route('resend-otp') }}" id="resendForm" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        @endif

                        <div class="mt-4 p-3 bg-light rounded">
                            <p class="text-muted small mb-2"><strong>Pro tip:</strong></p>
                            <ul class="text-muted small mb-0 ps-3">
                                <li>Check your spam/junk folder if you don't see the email</li>
                                <li>OTP is valid for 10 minutes</li>
                                <li>You have 5 attempts to enter the correct OTP</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format OTP input to numeric only
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Auto-submit after 6 digits
                // Uncomment if you want auto-submit:
                // document.getElementById('otpForm').submit();
            }
        });
    }

    // Resend timer
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');
    
    if (resendBtn && resendBtn.disabled) {
        let seconds = 60;
        const interval = setInterval(() => {
            seconds--;
            resendTimer.textContent = `(${seconds}s)`;
            
            if (seconds <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Resend OTP';
                
                resendBtn.onclick = function() {
                    document.getElementById('resendForm').submit();
                };
            }
        }, 1000);
    }
});
</script>
@endsection
