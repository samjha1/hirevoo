<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\User;
use App\Services\CandidateLeadService;
use App\Support\EmployerVerification;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    private const OAUTH_CONTEXT_COOKIE = 'hirevo_oauth_ctx';

    private const OAUTH_CONTEXT_MINUTES = 15;

    /**
     * Redirect to Google.
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $this->persistOAuthContext($request);

        return $this->socialiteDriver('google')->redirect();
    }

    /**
     * Redirect to Microsoft (Azure).
     */
    public function redirectToMicrosoft(Request $request): RedirectResponse
    {
        $this->persistOAuthContext($request);

        return $this->socialiteDriver('azure')->redirect();
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

    protected function socialiteDriver(string $driver)
    {
        $socialite = Socialite::driver($driver)->stateless();

        if ($driver === 'google') {
            $socialite->scopes(['openid', 'email', 'profile']);
        }

        return $socialite;
    }

    protected function persistOAuthContext(Request $request): void
    {
        $role = $request->query('role');
        $redirect = $request->query('redirect');
        $from = $request->query('from') === 'register' ? 'register' : 'login';

        $context = ['from' => $from];

        if (in_array($role, ['candidate', 'referrer', 'edtech'], true)) {
            $context['role'] = $role;
            session(['oauth_intended_role' => $role]);
        }

        if ($redirect && Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//')) {
            $context['redirect'] = $redirect;
            session(['oauth_intended_redirect' => $redirect]);
        }

        Cookie::queue(cookie(
            self::OAUTH_CONTEXT_COOKIE,
            json_encode(array_merge($context, [
                'exp' => now()->addMinutes(self::OAUTH_CONTEXT_MINUTES)->timestamp,
            ])),
            self::OAUTH_CONTEXT_MINUTES,
            '/',
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        ));

        session()->save();
    }

    /**
     * @return array{from?: string, role?: string, redirect?: string}
     */
    protected function readOAuthContext(): array
    {
        $context = [];

        $raw = request()->cookie(self::OAUTH_CONTEXT_COOKIE);
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && ($decoded['exp'] ?? 0) >= now()->timestamp) {
                $context = $decoded;
            }
        }

        if (! isset($context['role']) && session()->has('oauth_intended_role')) {
            $context['role'] = session('oauth_intended_role');
        }

        if (! isset($context['redirect']) && session()->has('oauth_intended_redirect')) {
            $context['redirect'] = session('oauth_intended_redirect');
        }

        if (! isset($context['from'])) {
            $context['from'] = 'login';
        }

        return $context;
    }

    protected function handleProviderCallback(string $driver): RedirectResponse
    {
        $context = $this->readOAuthContext();

        if (request()->filled('error')) {
            $message = request('error') === 'access_denied'
                ? 'Sign in was cancelled. You can try again or use email.'
                : 'Could not sign in with this provider. Please try again or use email.';

            return $this->oauthFailureRedirect($context, $message);
        }

        if (! request()->filled('code')) {
            return $this->oauthFailureRedirect(
                $context,
                'Could not sign in with this provider. Please try again or use email.'
            );
        }

        try {
            $oauthUser = $this->socialiteDriver($driver)->user();
        } catch (InvalidStateException $e) {
            Log::warning('OAuth state mismatch', [
                'driver' => $driver,
                'message' => $e->getMessage(),
            ]);

            return $this->oauthFailureRedirect(
                $context,
                'Your sign-in session expired. Please try again.'
            );
        } catch (\Throwable $e) {
            Log::warning('OAuth callback failed', [
                'driver' => $driver,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return $this->oauthFailureRedirect(
                $context,
                'Could not sign in with this provider. Please try again or use email.'
            );
        }

        $email = strtolower(trim((string) $oauthUser->getEmail()));
        if ($email === '') {
            return $this->oauthFailureRedirect(
                $context,
                'No email received from provider. Please use email sign up.'
            );
        }

        $intendedRole = $context['role'] ?? session('oauth_intended_role', 'candidate');
        if (! in_array($intendedRole, ['candidate', 'referrer', 'edtech'], true)) {
            $intendedRole = 'candidate';
        }

        $user = User::where('email', $email)->first();

        if ($user) {
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

            return $this->redirectAfterLogin($context);
        }

        try {
            $user = User::create([
                'name' => $this->oauthDisplayName($oauthUser, $email),
                'email' => $email,
                'phone' => null,
                'password' => bcrypt(Str::random(32)),
                'role' => $intendedRole,
            ]);
        } catch (QueryException $e) {
            if (! $this->isUniqueEmailViolation($e)) {
                throw $e;
            }

            $user = User::where('email', $email)->first();
            if (! $user) {
                return $this->oauthFailureRedirect(
                    $context,
                    'Could not sign in with this provider. Please try again or use email.'
                );
            }

            Auth::login($user, true);

            return $this->redirectAfterLogin($context);
        }

        if ($intendedRole === 'candidate') {
            CandidateProfile::firstOrCreate(
                ['user_id' => $user->id],
                []
            );

            app(CandidateLeadService::class)->applyPendingReferralIntent($user->id);
        }

        Auth::login($user, true);

        return $this->redirectAfterLogin($context);
    }

    protected function isUniqueEmailViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';
        $driverCode = (int) ($e->errorInfo[1] ?? 0);

        return in_array($sqlState, ['23000', '23505'], true)
            || $driverCode === 1062;
    }

    /**
     * @param  array{from?: string, role?: string, redirect?: string}  $context
     */
    protected function oauthFailureRedirect(array $context, string $message): RedirectResponse
    {
        $this->clearOAuthSession();

        if (($context['from'] ?? 'login') === 'register') {
            $params = [];
            if (! empty($context['role'])) {
                $params['role'] = $context['role'];
            }
            if (! empty($context['redirect'])) {
                $params['redirect'] = $context['redirect'];
            }

            return redirect()->route('register', $params)
                ->withErrors(['email' => $message]);
        }

        $params = [];
        if (($context['role'] ?? null) === 'referrer') {
            $params['role'] = 'referrer';
        }
        if (! empty($context['redirect'])) {
            $params['redirect'] = $context['redirect'];
        }

        return redirect()->route('login', $params)
            ->withErrors(['email' => $message]);
    }

    protected function clearOAuthSession(): void
    {
        session()->forget(['oauth_intended_role', 'oauth_intended_redirect']);
        Cookie::queue(Cookie::forget(self::OAUTH_CONTEXT_COOKIE));
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

    /**
     * @param  array{from?: string, role?: string, redirect?: string}  $context
     */
    protected function redirectAfterLogin(array $context = []): RedirectResponse
    {
        $user = Auth::user();
        $redirect = $context['redirect'] ?? session('oauth_intended_redirect');
        $this->clearOAuthSession();

        if ($user && ($pending = EmployerVerification::redirectIfPending($user))) {
            return $pending;
        }

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

        if ($user && $user->isReferrer()) {
            return redirect()->intended(route('employer.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
