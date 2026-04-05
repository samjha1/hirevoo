<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google.
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $this->storeIntendedInSession($request);
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /**
     * Redirect to Microsoft (Azure).
     */
    public function redirectToMicrosoft(Request $request): RedirectResponse
    {
        $this->storeIntendedInSession($request);
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback(): RedirectResponse
    {
        return $this->handleProviderCallback('google');
    }

    /**
     * Handle Microsoft callback.
     */
    public function handleMicrosoftCallback(): RedirectResponse
    {
        return $this->handleProviderCallback('azure');
    }

    protected function storeIntendedInSession(Request $request): void
    {
        $role = $request->query('role');
        if (in_array($role, ['candidate', 'referrer', 'edtech'], true)) {
            session(['oauth_intended_role' => $role]);
        }
        $redirect = $request->query('redirect');
        if ($redirect && Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//')) {
            session(['oauth_intended_redirect' => $redirect]);
        }
    }

    protected function handleProviderCallback(string $driver): RedirectResponse
    {
        try {
            $oauthUser = Socialite::driver($driver)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Could not sign in with this provider. Please try again or use email.']);
        }

        $email = $oauthUser->getEmail();
        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No email received from provider. Please use email sign up.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $intendedRole = session('oauth_intended_role', 'candidate');
            if ($intendedRole === 'referrer' && ! $user->isReferrer()) {
                $this->clearOAuthSession();
                return redirect()->route('login', ['role' => 'referrer'])
                    ->withErrors(['email' => 'This account is not an employer account. Please use the regular Sign In.']);
            }
            if ($intendedRole !== 'referrer' && $user->isReferrer()) {
                $this->clearOAuthSession();
                return redirect()->route('login')
                    ->withErrors(['email' => 'This is an employer account. Please use For Employers → Log in as employer.']);
            }
            Auth::login($user, true);
            return $this->redirectAfterLogin();
        }

        $intendedRole = session('oauth_intended_role', 'candidate');
        if (! in_array($intendedRole, ['candidate', 'referrer', 'edtech'], true)) {
            $intendedRole = 'candidate';
        }

        $user = User::create([
            'name' => $this->oauthDisplayName($oauthUser, $email),
            'email' => $email,
            'phone' => null,
            'password' => bcrypt(Str::random(32)),
            'role' => $intendedRole,
        ]);

        if ($intendedRole === 'candidate') {
            CandidateProfile::firstOrCreate(
                ['user_id' => $user->id],
                []
            );
        }

        Auth::login($user, true);
        return $this->redirectAfterLogin();
    }

    protected function clearOAuthSession(): void
    {
        session()->forget(['oauth_intended_role', 'oauth_intended_redirect']);
    }

    /**
     * Prefer given + family name from the provider (e.g. Microsoft Graph) over a short displayName.
     */
    protected function oauthDisplayName($oauthUser, string $email): string
    {
        $raw = $oauthUser->user ?? [];
        $given = $raw['givenName'] ?? $raw['given_name'] ?? null;
        $family = $raw['surname'] ?? $raw['family_name'] ?? null;
        if (is_string($given)) {
            $given = trim($given);
        } else {
            $given = '';
        }
        if (is_string($family)) {
            $family = trim($family);
        } else {
            $family = '';
        }
        $fromParts = trim($given.' '.$family);
        if ($fromParts !== '') {
            return $fromParts;
        }

        $name = trim((string) $oauthUser->getName());
        if ($name !== '') {
            return $name;
        }

        return Str::before($email, '@') ?: 'User';
    }

    protected function redirectAfterLogin(): RedirectResponse
    {
        $user = Auth::user();
        $redirect = session('oauth_intended_redirect');
        $this->clearOAuthSession();

        if ($user && $user->isCandidate()) {
            if (! $user->candidate_profile_completed_at) {
                return redirect()
                    ->route('profile')
                    ->with('info', 'Complete your profile first — then upload your resume to continue.');
            }
            if (! $user->resumes()->exists()) {
                return redirect()
                    ->route('resume.upload')
                    ->with('info', 'Upload your resume so we can analyse it and match you to roles.');
            }
        }

        if ($redirect) {
            return redirect()->to($redirect);
        }

        return redirect()->intended($user && $user->isCandidate() ? route('candidate.dashboard') : route('home'));
    }
}
