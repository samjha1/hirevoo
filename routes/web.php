<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Employer\ApplicationController as EmployerApplicationController;
use App\Http\Controllers\Employer\CreditsController as EmployerCreditsController;
use App\Http\Controllers\Employer\DashboardController as EmployerDashboardController;
use App\Http\Controllers\Employer\JobController as EmployerJobController;
use App\Http\Controllers\Employer\ProfileController as EmployerProfileController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\GuestResumeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\CandidateDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralSignupController;
use App\Http\Controllers\CareerConsultationController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sign-in', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/sign-in', [LoginController::class, 'login']);
Route::post('/sign-out', [LoginController::class, 'logout'])->name('logout');
Route::get('/sign-up', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/sign-up', [RegisterController::class, 'register']);

// Guest resume upload (no login required)
Route::get('/resume/upload', [ResumeController::class, 'showUploadForm'])->name('resume.upload');
Route::post('/resume/guest-upload', [GuestResumeController::class, 'upload'])->name('resume.guest-upload');

// Password setup (from welcome email link)
Route::get('/set-password', [SetPasswordController::class, 'show'])->name('auth.set-password');
Route::post('/set-password', [SetPasswordController::class, 'store'])->name('auth.set-password.store');

Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/microsoft/redirect', [SocialAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft.redirect');
Route::get('/auth/microsoft/callback', [SocialAuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');

Route::middleware(['auth', 'candidate.onboarding'])->group(function () {
    Route::get('/dashboard', [CandidateDashboardController::class, 'index'])->name('candidate.dashboard');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/fill-from-resume', [ProfileController::class, 'fillFromResume'])->name('profile.fill-from-resume');
    Route::post('/career-consultation', [CareerConsultationController::class, 'store'])->name('career-consultation.store');
    Route::post('/resume/upload', [ResumeController::class, 'upload'])->name('resume.upload.store');
    Route::get('/resume/{resume}/file', [ResumeController::class, 'serveFile'])->name('resume.file');
    Route::get('/resume/{resume}/results', [ResumeController::class, 'results'])->name('resume.results');
    Route::post('/resume/lead', [ResumeController::class, 'createLead'])->name('resume.lead');
    Route::post('/leads/upskill-contact', [LeadController::class, 'storeUpskillContact'])->name('leads.upskill-contact');

    // Employer routes (role: referrer)
    Route::middleware('role:referrer')->prefix('employer')->name('employer.')->group(function () {
        Route::get('/profile', [EmployerProfileController::class, 'show'])->name('profile');
        Route::post('/profile', [EmployerProfileController::class, 'update'])->name('profile.update');
        Route::get('/dashboard', [EmployerDashboardController::class, 'index'])
            ->middleware('employer.profile.complete')
            ->name('dashboard');
        Route::post('/jobs/generate-description', [EmployerJobController::class, 'generateDescription'])->name('jobs.generate-description');
        Route::get('/jobs/{job}/applications', [EmployerApplicationController::class, 'index'])->name('jobs.applications')->scopeBindings();
        Route::patch('/applications/{application}/status', [EmployerApplicationController::class, 'updateStatus'])->name('applications.status')->scopeBindings();
        // ATS / Pipeline tracking (Kanban-style stages)
        Route::get('/jobs/{job}/pipeline', [EmployerApplicationController::class, 'pipeline'])->name('jobs.pipeline')->scopeBindings();
        // Application detail view (candidate + resume + scores)
        Route::get('/applications/{application}', [EmployerApplicationController::class, 'show'])->name('applications.show')->scopeBindings();
        Route::post('/applications/{application}/calculate-match', [EmployerApplicationController::class, 'calculateMatch'])->name('applications.calculate-match')->scopeBindings();
        Route::get('/applications/{application}/resume/view', [EmployerApplicationController::class, 'viewResume'])->name('applications.resume.view')->scopeBindings();
        Route::get('/applications/{application}/resume', [EmployerApplicationController::class, 'downloadResume'])->name('applications.resume')->scopeBindings();
        // Interview scheduling
        Route::post('/applications/{application}/interviews', [EmployerApplicationController::class, 'storeInterview'])
            ->name('applications.interviews.store')
            ->scopeBindings();
        Route::patch('/interviews/{interview}/cancel', [EmployerApplicationController::class, 'cancelInterview'])
            ->name('interviews.cancel')
            ->scopeBindings();
        Route::get('/interviews/{interview}/calendar', [EmployerApplicationController::class, 'calendarInvite'])
            ->name('interviews.calendar')
            ->scopeBindings();
        Route::post('/jobs/{job}/duplicate', [EmployerJobController::class, 'duplicate'])->name('jobs.duplicate')->scopeBindings();
        Route::post('/jobs/{job}/repost', [EmployerJobController::class, 'repost'])->name('jobs.repost')->scopeBindings();
        Route::resource('jobs', EmployerJobController::class)->names('jobs')->except(['show']);
        Route::get('/credits', [EmployerCreditsController::class, 'index'])->name('credits.index');
    });

});

Route::get('/job-list', [HomeController::class, 'jobList'])->name('job-list');
Route::get('/job-openings', [HomeController::class, 'jobOpenings'])->name('job-openings');
Route::get('/job-openings/{job}/apply', [HomeController::class, 'showEmployerJobApply'])->name('job-openings.apply');
Route::post('/job-openings/{job}/apply', [HomeController::class, 'storeEmployerJobApply'])->middleware('auth')->name('job-openings.apply.store');
Route::get('/job-openings/{job}/apply/external', [HomeController::class, 'externalEmployerJobApplyRedirect'])
    ->name('job-openings.apply.external-redirect');
Route::get('/job-goals/{jobRole}', [HomeController::class, 'skillMatch'])->name('job-goal.show');
Route::get('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'showApplyForm'])->name('job-goal.apply');
Route::get('/job-goals/{jobRole}/match-score', [JobApplicationController::class, 'matchScore'])->middleware('auth')->name('job-goal.match-score');
Route::post('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'store'])->middleware('auth')->name('job-goal.apply.store');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/about', fn () => view('hirevo.about'))->name('about');
Route::get('/help', fn () => view('hirevo.help'))->name('help');
Route::get('/faq', fn () => view('hirevo.faq'))->name('faq');
Route::get('/terms', fn () => view('hirevo.legal.terms'))->name('terms');
Route::get('/privacy', fn () => view('hirevo.legal.privacy'))->name('privacy');
Route::get('/cookies', fn () => view('hirevo.legal.cookies'))->name('cookies');
Route::get('/disclaimer', fn () => view('hirevo.legal.disclaimer'))->name('disclaimer');
Route::get('/contact', fn () => view('hirevo.contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::post('/referral-signup', [ReferralSignupController::class, 'store'])->name('referral-signup.store');
