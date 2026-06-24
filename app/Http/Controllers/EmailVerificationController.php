<?php

namespace App\Http\Controllers;

use App\Models\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification view
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user->isReferrer() && $user->referrerProfile) {
            $profile = $user->referrerProfile;
            if ($profile->is_approved) {
                return redirect()->route('employer.dashboard');
            }
        }

        $pendingOtp = EmailOtp::getLatestPendingOtp($user->id);

        if (
            $user->isReferrer()
            && $user->referrerProfile
            && ! $user->referrerProfile->is_approved
            && session()->pull('verify_email_auto_send')
        ) {
            return $this->sendOtp($request);
        }

        return view('hirevo.verify-email', [
            'email' => $user->email,
            'otpSent' => $pendingOtp !== null && !$pendingOtp->isExpired(),
            'otpId' => $pendingOtp?->id,
        ]);
    }

    /**
     * Send OTP to user's email
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Delete any existing pending OTPs
        EmailOtp::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record
        $emailOtp = EmailOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10), // OTP valid for 10 minutes
        ]);

        // Send OTP via email
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return redirect()->route('verify-email')
            ->with('success', 'OTP sent to your email. Please check your inbox.');
    }

    /**
     * Verify the OTP
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $user = auth()->user();

        // Get the latest pending OTP
        $emailOtp = EmailOtp::getLatestPendingOtp($user->id);

        if (!$emailOtp) {
            return redirect()->route('verify-email')
                ->with('error', 'No OTP found. Please request a new one.');
        }

        if ($emailOtp->isExpired()) {
            $emailOtp->delete();
            return redirect()->route('verify-email')
                ->with('error', 'OTP has expired. Please request a new one.');
        }

        if ($emailOtp->hasExceededAttempts()) {
            $emailOtp->delete();
            return redirect()->route('verify-email')
                ->with('error', 'Too many failed attempts. Please request a new OTP.');
        }

        if ($emailOtp->otp !== $request->otp) {
            $emailOtp->increment('attempts');
            $remaining = 5 - $emailOtp->attempts;
            
            return redirect()->route('verify-email')
                ->with('error', "Invalid OTP. {$remaining} attempts remaining.");
        }

        // Mark OTP as verified
        $emailOtp->update(['verified_at' => now()]);

        // Approve employer and grant welcome job posting credits
        if ($user->isReferrer() && $user->referrerProfile) {
            $welcomeCredits = max(0, (int) config('hirevo_plans.employer_approval_credits', 1));
            $profile = $user->referrerProfile;
            $user->referrerProfile->update([
                'is_approved' => true,
                'approved_at' => now(),
                'company_email_verified' => true,
                'credits' => max((int) $profile->credits, $welcomeCredits),
            ]);
        }

        $welcomeCredits = max(0, (int) config('hirevo_plans.employer_approval_credits', 1));
        $creditNote = $welcomeCredits === 1
            ? ' You received 1 free job posting credit.'
            : ($welcomeCredits > 0 ? " You received {$welcomeCredits} free job posting credits." : '');

        return redirect()->route('employer.dashboard')
            ->with('success', 'Email verified successfully! Your account has been approved.'.$creditNote);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Check if there's a recent OTP
        $recentOtp = EmailOtp::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('created_at', '>', now()->subMinutes(1))
            ->first();

        if ($recentOtp) {
            return redirect()->route('verify-email')
                ->with('error', 'Please wait before requesting a new OTP.');
        }

        // Delete existing pending OTPs
        EmailOtp::where('user_id', $user->id)
            ->whereNull('verified_at')
            ->delete();

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $emailOtp = EmailOtp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP
        Mail::to($user->email)->send(new OtpMail($user, $otp));

        return redirect()->route('verify-email')
            ->with('success', 'New OTP sent to your email.');
    }
}
