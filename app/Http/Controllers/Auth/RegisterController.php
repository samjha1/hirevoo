<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReferrerProfile;
use App\Rules\StrictEmail;
use App\Rules\ValidEmployerReferralCode;
use App\Services\CandidateLeadService;
use App\Services\CrmEmployerProspectBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $role = $request->query('role', 'candidate');
        if (!in_array($role, ['candidate', 'referrer', 'edtech'], true)) {
            $role = 'candidate';
        }
        return view('hirevo.sign-up', ['defaultRole' => $role]);
    }

    public function register(Request $request)
    {
        $role = $request->input('role', $request->query('role', 'candidate'));
        $employerSignup = $role === 'referrer'
            || $request->query('role') === 'referrer'
            || $request->filled('company_name');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'max:255', new StrictEmail, 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:candidate,referrer,edtech'],
        ];

        if ($employerSignup) {
            $rules['role'] = ['required', 'in:referrer'];
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['referral_code'] = ['nullable', 'string', 'max:50', new ValidEmployerReferralCode];
        }

        $validated = $request->validate($rules);

        if ($employerSignup) {
            $validated['role'] = 'referrer';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['contact'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        if ($user->isReferrer()) {
            ReferrerProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $validated['company_name'] ?? null,
                    'company_email' => $validated['email'],
                    'referral_code' => $validated['referral_code'] ?? null,
                    'company_email_verified' => false,
                    'is_approved' => false,
                    'credits' => 0,
                ]
            );

            if (! app(CrmEmployerProspectBridge::class)->syncReferrerSignup($user->fresh(['referrerProfile']))) {
                Log::warning('CRM company prospect sync did not complete for referrer signup', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
            return redirect(route('verify-email'))
                ->with('success', 'Welcome! Please verify your email to activate your account.');
        }

        app(CandidateLeadService::class)->applyPendingReferralIntent($user->id);

        return redirect()
            ->route('profile')
            ->with('info', 'Welcome! Complete your profile first — then upload your resume to unlock job matching and applications.');
    }
}
