<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    /**
     * Show the set-password form (linked from the welcome email).
     */
    public function show(Request $request): View|RedirectResponse
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (! $token || ! $email) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or missing password setup link.']);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $record || ! Hash::check($token, $record->token)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'This password setup link is invalid or has already been used.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('login')
                ->withErrors(['email' => 'This link has expired. Please upload your resume again to get a new one.']);
        }

        return view('hirevo.auth.set-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Set the password and log the user in.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Invalid or already-used link.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('login')
                ->withErrors(['email' => 'This link has expired. Please upload your resume again.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Account not found.']);
        }

        $user->forceFill(['password' => Hash::make($request->password)])->save();
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Auth::login($user);

        // Redirect to the most recent resume results if available
        $latestResume = $user->resumes()->orderByDesc('created_at')->first();
        if ($latestResume) {
            return redirect()->route('resume.results', $latestResume)
                ->with('success', 'Password set! Welcome to Hirevo — here are your resume results.');
        }

        return redirect()->route('resume.upload')
            ->with('success', 'Password set! Welcome to Hirevo.');
    }
}
