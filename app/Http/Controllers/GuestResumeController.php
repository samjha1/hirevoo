<?php

namespace App\Http\Controllers;

use App\Models\CandidateProfile;
use App\Models\User;
use App\Notifications\WelcomeSetPasswordNotification;
use App\Services\CandidateProfileFillerFromResume;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestResumeController extends Controller
{
    public function __construct(
        protected ResumeAnalysisService $resumeAnalysis,
        protected CandidateProfileFillerFromResume $profileFiller
    ) {}

    /**
     * Handle resume upload for unauthenticated users.
     * Creates account from data extracted from the PDF, analyses resume, sends set-password email, logs them in.
     */
    public function upload(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('resume.upload');
        }

        $request->validate([
            'resume' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ], [
            'resume.required' => 'Please select a PDF file.',
            'resume.mimes' => 'Only PDF files are supported.',
            'resume.max' => 'File must not exceed 10 MB.',
        ]);

        set_time_limit((int) config('hirevo.resume_analysis_time_limit', 180));

        $file = $request->file('resume');
        $path = $file->store('resumes', 'local');

        if ($path === false) {
            return back()->withErrors(['resume' => 'Failed to store file. Please try again.']);
        }

        $fullPath = storage_path('app/' . $path);
        $text = $this->resumeAnalysis->extractTextFromFile($fullPath, $file->getMimeType() ?? 'application/pdf');
        $identity = $this->resumeAnalysis->extractContactIdentityFromText($text);

        if (! $this->resumeAnalysis->isRecognizedRegistrationEmail($identity['email'])) {
            $fallback = strtolower(trim((string) $request->input('contact_email', '')));
            if ($this->resumeAnalysis->isRecognizedRegistrationEmail($fallback)) {
                $identity['email'] = $fallback;
            }
        }

        if (! $this->resumeAnalysis->isRecognizedRegistrationEmail($identity['email'])) {
            Storage::disk('local')->delete($path);

            return back()
                ->withErrors([
                    'resume' => 'We could not detect an email in this PDF (common with scanned images). Add your email in the field below and try again, use a text-based PDF, or sign in and upload while logged in.',
                ])
                ->withInput($request->only('contact_email'));
        }

        $existingUser = User::where('email', $identity['email'])->first();
        if ($existingUser !== null) {
            if (! $existingUser->isCandidate()) {
                Storage::disk('local')->delete($path);

                return back()
                    ->withErrors([
                        'resume' => 'This email is already registered to a non-candidate account. Sign in with that account to continue.',
                    ])
                    ->withInput($request->only('contact_email'));
            }

            CandidateProfile::firstOrCreate(
                ['user_id' => $existingUser->id],
                []
            );

            $existingUser->resumes()->update(['is_primary' => false]);

            $resume = $existingUser->resumes()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'is_primary' => true,
            ]);

            $this->resumeAnalysis->analyzeResume($resume);
            $this->profileFiller->fill($existingUser);

            $existingUser->refresh();
            $existingUser->syncCandidateProfileCompletion();

            Auth::login($existingUser);

            return redirect()->route('resume.results', $resume)
                ->with('success', 'Welcome back! Your resume has been analyzed — your score and recommended jobs are below.');
        }

        $displayName = $identity['name'];
        if ($displayName === null || trim($displayName) === '') {
            $local = strstr($identity['email'], '@', true) ?: 'candidate';
            $local = str_replace(['.', '_', '-'], ' ', $local);
            $local = trim(preg_replace('/\d+/', '', $local) ?? '');
            $displayName = $local !== '' ? Str::title($local) : 'Candidate';
        }
        $displayName = Str::limit(trim($displayName), 255, '');

        $user = User::create([
            'name' => $displayName,
            'email' => $identity['email'],
            'phone' => $identity['phone'],
            'password' => Hash::make(Str::random(40)),
            'role' => 'candidate',
            'candidate_profile_completed_at' => now(),
        ]);

        CandidateProfile::firstOrCreate(
            ['user_id' => $user->id],
            []
        );

        $resume = $user->resumes()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'is_primary' => true,
        ]);

        $this->resumeAnalysis->analyzeResume($resume);
        $this->profileFiller->fill($user);

        $user->refresh();
        $user->syncCandidateProfileCompletion();

        Auth::login($user);

        $plainToken = Str::random(64);
        DB::table('password_reset_tokens')->upsert(
            [
                'email' => $user->email,
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ],
            ['email'],
            ['token', 'created_at']
        );

        $setupUrl = route('auth.set-password', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        try {
            $user->notify(new WelcomeSetPasswordNotification($setupUrl, $file->getClientOriginalName()));
        } catch (\Throwable $e) {
            Log::error('Guest resume: welcome email failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('resume.results', $resume)
            ->with('success', 'Your resume has been analysed! Check your email to set a password and secure your account.');
    }
}
