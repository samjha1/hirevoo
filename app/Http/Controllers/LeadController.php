<?php

namespace App\Http\Controllers;

use App\Models\UpskillOpportunity;
use App\Services\CandidateLeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Store a lead when candidate clicks "Contact" on an upskill opportunity.
     */
    public function storeUpskillContact(Request $request, CandidateLeadService $candidateLeads): RedirectResponse
    {
        $request->validate([
            'upskill_opportunity_id' => ['required', 'integer', 'exists:upskill_opportunities,id'],
        ]);

        if (! auth()->check() || ! auth()->user()->isCandidate()) {
            return redirect()->route('login', ['redirect' => $request->url()])
                ->with('info', 'Please sign in as a candidate to submit.');
        }

        $opportunity = UpskillOpportunity::where('is_active', true)->findOrFail($request->upskill_opportunity_id);

        $candidateLeads->recordUpskillLead(auth()->id(), $opportunity);

        return redirect()->route('contact')
            ->with('success', 'Thanks for your interest in '.$opportunity->title.'. We will contact you soon.');
    }
}
