@extends('layouts.app')

@section('title', 'Set Your Password — Hirevo')

@push('styles')
<style>
.sp-page {
    min-height: 100vh;
    background: linear-gradient(160deg, #eef2ff 0%, #f8fafc 30%, #fff 70%);
    display: flex;
    align-items: center;
    padding: 3rem 0;
}
.sp-card {
    background: #fff;
    border-radius: 1.5rem;
    border: 1px solid rgba(15,23,42,0.08);
    box-shadow: 0 24px 80px rgba(15,23,42,0.1);
    padding: 2.5rem 2rem;
    max-width: 440px;
    width: 100%;
    margin: 0 auto;
}
@media (min-width: 576px) { .sp-card { padding: 3rem 2.5rem; } }
.sp-icon-wrap {
    width: 64px; height: 64px;
    border-radius: 1rem;
    background: linear-gradient(135deg, #10b981, #059669);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem;
    color: #fff;
    margin: 0 auto 1.5rem;
    box-shadow: 0 8px 24px rgba(16,185,129,0.3);
}
.sp-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    text-align: center;
    margin-bottom: 0.4rem;
}
.sp-sub {
    font-size: 0.85rem;
    color: #64748b;
    text-align: center;
    margin-bottom: 2rem;
    line-height: 1.55;
}
.sp-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.4rem;
    display: block;
}
.sp-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.75rem;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    background: #fff;
}
.sp-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
}
.sp-input.is-invalid { border-color: #ef4444; }
.sp-btn {
    width: 100%;
    padding: 0.85rem;
    border-radius: 0.85rem;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 4px 16px rgba(16,185,129,0.35);
    margin-top: 1.25rem;
}
.sp-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16,185,129,0.45); }
.sp-divider { height: 1px; background: #f1f5f9; margin: 1.5rem 0; }
.sp-strength { height: 4px; border-radius: 2px; background: #e2e8f0; margin-top: 0.4rem; overflow: hidden; }
.sp-strength-fill { height: 100%; border-radius: 2px; width: 0; transition: width 0.3s, background 0.3s; }
</style>
@endpush

@section('content')
<div class="sp-page">
    <div class="container">
        <div class="sp-card">
            <div class="sp-icon-wrap">
                <i class="uil uil-lock-access"></i>
            </div>
            <h1 class="sp-title">Set your password</h1>
            <p class="sp-sub">Your resume is analysed and ready. Set a password to save your account and view results anytime.</p>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-3 small">
                    <i class="uil uil-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.set-password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label class="sp-label">Email</label>
                    <input type="email" class="sp-input" value="{{ $email }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="sp-label" for="password">New Password</label>
                    <input type="password" name="password" id="sp-password"
                           class="sp-input @error('password') is-invalid @enderror"
                           placeholder="At least 8 characters"
                           autocomplete="new-password"
                           required>
                    <div class="sp-strength">
                        <div class="sp-strength-fill" id="sp-strength-fill"></div>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-1">
                    <label class="sp-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="sp-confirm"
                           class="sp-input"
                           placeholder="Repeat your password"
                           autocomplete="new-password"
                           required>
                </div>

                <button type="submit" class="sp-btn">
                    <i class="uil uil-check-circle me-2"></i>Save password &amp; continue
                </button>
            </form>

            <div class="sp-divider"></div>
            <p class="text-center small text-muted mb-0">
                Already have a password? <a href="{{ route('login') }}" class="text-primary fw-600">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var pw   = document.getElementById('sp-password');
    var fill = document.getElementById('sp-strength-fill');
    if (!pw || !fill) return;
    pw.addEventListener('input', function () {
        var v = pw.value;
        var score = 0;
        if (v.length >= 8)  score++;
        if (/[A-Z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        var colors = ['#ef4444','#f59e0b','#10b981','#059669'];
        fill.style.width  = (score * 25) + '%';
        fill.style.background = colors[score - 1] || '#e2e8f0';
    });
})();
</script>
@endpush
