<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\StrictEmail;
use App\Support\EmployerVerification;
use App\Support\RoleEmailGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('hirevo.sign-in');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255', new StrictEmail],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            $intendedRole = $request->input('role');

            // Employer login (role=referrer): only referrers allowed
            if ($intendedRole === 'referrer') {
                if (! $user->isReferrer()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $message = RoleEmailGuard::crossRoleConflict('referrer', $user)
                        ?? 'This account is not an employer account. Please use the regular Sign In.';

                    return redirect()->route('login', ['role' => 'referrer'])->withErrors([
                        'email' => $message,
                    ])->onlyInput('email');
                }
            } else {
                // Candidate / normal sign-in: referrers must use employer login
                if ($user->isReferrer()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $message = RoleEmailGuard::crossRoleConflict('candidate', $user)
                        ?? 'This is an employer account. Please use For Employers → Log in as employer.';

                    return redirect()->route('login')->withErrors([
                        'email' => $message,
                    ])->onlyInput('email');
                }
            }

            $request->session()->regenerate();

            if ($pendingVerification = EmployerVerification::redirectIfPending($user)) {
                return $pendingVerification;
            }

            $redirect = $request->query('redirect');
            if ($redirect && \Illuminate\Support\Str::startsWith($redirect, '/') && ! \Illuminate\Support\Str::startsWith($redirect, '//')) {
                return redirect()->to($redirect);
            }
            if ($user->isReferrer()) {
                return redirect()->intended(route('employer.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('home'));
    }
}
