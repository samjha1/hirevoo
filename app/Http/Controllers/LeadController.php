<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\UpskillOpportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Store a lead when candidate clicks "Contact" on an upskill opportunity.
     * Saves to leads table for follow-up.
     */
    public function storeUpskillContact(Request $request): RedirectResponse
    {
        $request->validate([
            'upskill_opportunity_id' => ['required', 'integer', 'exists:upskill_opportunities,id'],
        ]);

        if (! auth()->check() || ! auth()->user()->isCandidate()) {
            return redirect()->route('login', ['redirect' => $request->url()])
                ->with('info', 'Please sign in as a candidate to submit.');
        }

        $opportunity = UpskillOpportunity::where('is_active', true)->findOrFail($request->upskill_opportunity_id);

        Lead::firstOrCreate(
            [
                'candidate_id' => auth()->id(),
                'upskill_opportunity_id' => $opportunity->id,
            ],
            [
                'skill_analysis_id' => null,
                'job_role_id' => null,
                'match_percentage' => null,
                'missing_skills' => null,
                'status' => 'available',
            ]
        );

        return redirect()->route('contact')
            ->with('success', 'Thanks for your interest in ' . $opportunity->title . '. We will contact you soon.');
    }
}
