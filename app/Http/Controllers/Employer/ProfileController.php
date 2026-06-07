<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\ReferrerProfile;
use App\Rules\StrictEmail;
use App\Support\StoredFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Serve the logged-in employer's profile photo (streams from S3 or legacy local file).
     */
    public function servePhoto(): StreamedResponse|BinaryFileResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            abort(403);
        }

        $profile = $user->referrerProfile;
        if (! $profile || ! filled($profile->profile_photo)) {
            abort(404);
        }

        return StoredFile::imageResponse($profile->profile_photo);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->isReferrer()) {
            return redirect()->route('home')->with('info', 'Access for employers only.');
        }

        $photoMaxKb = StoredFile::imageMaxKb();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'company_name'   => ['required', 'string', 'max:255'],
            'company_email'  => ['required', 'string', 'max:255', new StrictEmail, Rule::unique('referrer_profiles', 'company_email')->ignore($user->referrerProfile?->id)],
            'phone'          => ['nullable', 'string', 'max:20'],
            'designation'    => ['nullable', 'string', 'max:255'],
            'department'     => ['nullable', 'string', 'max:255'],
            'profile_photo'  => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:'.$photoMaxKb],
            'gstin'          => ['nullable', 'string', 'max:20'],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'company_address'    => ['nullable', 'string', 'max:1000'],
            'invoice_consent'    => ['nullable', 'boolean'],
        ], [
            'profile_photo.max' => 'Image must not be greater than 10 MB. Please choose a smaller file.',
            'profile_photo.image' => 'Please upload a valid image file (JPEG, PNG, GIF, or WebP).',
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

        if ($isNew) {
            $profile->user_id = $user->id;
            $profile->credits = 5;
        }

        $profile->save();

        if ($request->hasFile('profile_photo')) {
            if ($profile->profile_photo) {
                StoredFile::delete($profile->profile_photo);
            }
            $storedKey = StoredFile::storeUploadedFile($request->file('profile_photo'), 'employer-profiles');
            if ($storedKey === false) {
                return redirect()->route('employer.profile')
                    ->withErrors(['profile_photo' => 'Failed to upload photo to AWS S3. Please try again.'])
                    ->with('success', 'Other profile details were saved.');
            }
            $profile->profile_photo = $storedKey;
            $profile->save();
        }

        $message = $isNew
            ? 'Company profile saved. Your account is pending admin verification.'
            : 'Profile updated successfully.';

        return redirect()->route('employer.profile')->with('success', $message);
    }
}
