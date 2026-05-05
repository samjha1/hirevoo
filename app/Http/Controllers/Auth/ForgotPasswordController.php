<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm(Request $request): View
    {
        $role = $request->query('role');
        if ($role !== 'referrer') {
            $role = null;
        }

        return view('hirevo.auth.forgot-password', [
            'role' => $role,
        ]);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['nullable', 'in:referrer'],
        ]);

        try {
            $status = Password::sendResetLink([
                'email' => $validated['email'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Password reset mail send failed', [
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return back()->withInput($request->only('email', 'role'))->withErrors([
                'email' => 'Unable to send reset email right now. Please try again shortly or contact support.',
            ]);
        }

        $redirectParams = [];
        if (($validated['role'] ?? null) === 'referrer') {
            $redirectParams['role'] = 'referrer';
        }

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('password.request', $redirectParams)
                ->with('status', __($status));
        }

        return back()->withInput($request->only('email', 'role'))->withErrors([
            'email' => __($status),
        ]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        $role = $request->query('role');
        if ($role !== 'referrer') {
            $role = null;
        }

        return view('hirevo.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
            'role' => $role,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'role' => ['nullable', 'in:referrer'],
        ]);

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $redirectParams = [];
            if (($validated['role'] ?? null) === 'referrer') {
                $redirectParams['role'] = 'referrer';
            }

            return redirect()->route('login', $redirectParams)->with('status', __($status));
        }

        return back()->withInput($request->only('email', 'role'))->withErrors([
            'email' => __($status),
        ]);
    }
}
