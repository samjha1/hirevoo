<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\ReferrerProfile;
use App\Rules\StrictEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $profile = $user->referrerProfile;
        $jobsCount = $user->employerJobs()->count();
        $activeJobsCount = $user->employerJobs()->where('status', 'active')->count();

        return view('hirevo.employer.profile', [
            'profile' => $profile,
            'user' => $user,
            'jobsCount' => $jobsCount,
            'activeJobsCount' => $activeJobsCount,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'company_name'   => ['required', 'string', 'max:255'],
            'company_email'  => ['required', 'string', 'max:255', new StrictEmail, Rule::unique('referrer_profiles', 'company_email')->ignore($user->referrerProfile?->id)],
            'phone'          => ['nullable', 'string', 'max:20'],
            'designation'    => ['nullable', 'string', 'max:255'],
            'department'     => ['nullable', 'string', 'max:255'],
            'profile_photo'  => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'gstin'          => ['nullable', 'string', 'max:20'],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'company_address'    => ['nullable', 'string', 'max:1000'],
            'invoice_consent'    => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->save();

        $isNew = ! $user->referrerProfile;
        $profile = $user->referrerProfile ?? new ReferrerProfile(['user_id' => $user->id]);
        $profile->company_name = $validated['company_name'];
        $profile->company_email = strtolower($validated['company_email']);
        $profile->designation = $validated['designation'] ?? $profile->designation;
        $profile->department = $validated['department'] ?? $profile->department;
        $profile->gstin = $validated['gstin'] ?? null;
        $profile->company_legal_name = $validated['company_legal_name'] ?? null;
        $profile->company_address = $validated['company_address'] ?? null;
        $profile->invoice_consent = ! empty($request->boolean('invoice_consent'));

        if ($request->hasFile('profile_photo')) {
            $dir = public_path('uploads/employer-profiles');
            File::isDirectory($dir) || File::makeDirectory($dir, 0755, true);
            if ($profile->profile_photo && str_starts_with($profile->profile_photo, 'uploads/employer-profiles/')) {
                $oldPath = public_path($profile->profile_photo);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            $file = $request->file('profile_photo');
            $name = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $profile->profile_photo = 'uploads/employer-profiles/' . $name;
        }

        if ($isNew) {
            $profile->credits = 5;
        }
        $profile->save();

        $message = $isNew
            ? 'Company profile saved. Your account is pending admin verification.'
            : 'Profile updated successfully.';

        return redirect()->route('employer.profile')->with('success', $message);
    }
}
