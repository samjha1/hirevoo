<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'referrer')
            ->with('referrerProfile')
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('referrerProfile')
                        ->orWhereHas('referrerProfile', fn ($q2) => $q2->where('is_approved', false));
                });
            }
            if ($request->status === 'approved') {
                $query->whereHas('referrerProfile', fn ($q) => $q->where('is_approved', true));
            }
        }

        $employers = $query->paginate(15)->withQueryString();

        return view('hirevo.admin.employers.index', [
            'employers' => $employers,
        ]);
    }

    public function approve(User $employer): RedirectResponse
    {
        $this->authorizeEmployer($employer);

        $profile = $employer->referrerProfile;
        if (! $profile) {
            return redirect()->route('admin.employers.index')
                ->with('error', 'Employer has no profile. They must complete profile first.');
        }

        $profile->is_approved = true;
        $profile->approved_at = now();
        $profile->credits = (int) config('hirevo_plans.employer_approval_credits', 1);
        $profile->save();

        return redirect()->route('admin.employers.index')
            ->with('success', "Employer {$employer->name} has been approved.");
    }

    public function reject(User $employer): RedirectResponse
    {
        $this->authorizeEmployer($employer);

        $profile = $employer->referrerProfile;
        if ($profile) {
            $profile->is_approved = false;
            $profile->approved_at = null;
            $profile->save();
        }

        return redirect()->route('admin.employers.index')
            ->with('success', "Employer {$employer->name} has been rejected.");
    }

    private function authorizeEmployer(User $employer): void
    {
        if ($employer->role !== 'referrer') {
            abort(404, 'Not an employer.');
        }
    }
}
