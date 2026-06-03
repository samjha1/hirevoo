<?php

namespace App\Http\Controllers;

use App\Models\CompanyReferrerSignup;
use App\Rules\StrictEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralSignupController extends Controller
{
    /**
     * Store a company referrer signup from the home page (refer in your company & earn).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'max:255', new StrictEmail],
            'phone'          => ['nullable', 'string', 'max:20'],
            'max_candidates' => ['required', 'integer', 'min:1', 'max:100'],
            'message'        => ['nullable', 'string', 'max:1000'],
        ]);

        CompanyReferrerSignup::create([
            'company_name'   => $request->company_name,
            'name'           => $request->name,
            'email'          => strtolower($request->email),
            'phone'          => $request->phone ?: null,
            'max_candidates' => (int) $request->max_candidates,
            'message'        => $request->message ? trim($request->message) : null,
            'source'         => 'home',
        ]);

        return redirect()->back()->with('referral_success', 'Thanks! We’ll get in touch with you soon to help you refer candidates and earn.');
    }
}
