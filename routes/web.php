<?php

use App\Http\Controllers\Admin\EmployerController as AdminEmployerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Employer\ApplicationController as EmployerApplicationController;
use App\Http\Controllers\Employer\CreditsController as EmployerCreditsController;
use App\Http\Controllers\Employer\DashboardController as EmployerDashboardController;
use App\Http\Controllers\Employer\JobController as EmployerJobController;
use App\Http\Controllers\Employer\ProfileController as EmployerProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sign-in', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/sign-in', [LoginController::class, 'login']);
Route::post('/sign-out', [LoginController::class, 'logout'])->name('logout');
Route::get('/sign-up', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/sign-up', [RegisterController::class, 'register']);

Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/microsoft/redirect', [SocialAuthController::class, 'redirectToMicrosoft'])->name('auth.microsoft.redirect');
Route::get('/auth/microsoft/callback', [SocialAuthController::class, 'handleMicrosoftCallback'])->name('auth.microsoft.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/fill-from-resume', [ProfileController::class, 'fillFromResume'])->name('profile.fill-from-resume');
    Route::get('/resume/upload', [ResumeController::class, 'showUploadForm'])->name('resume.upload');
    Route::post('/resume/upload', [ResumeController::class, 'upload']);
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
        Route::get('/applications/{application}/resume/view', [EmployerApplicationController::class, 'viewResume'])->name('applications.resume.view')->scopeBindings();
        Route::get('/applications/{application}/resume', [EmployerApplicationController::class, 'downloadResume'])->name('applications.resume')->scopeBindings();
        Route::post('/jobs/{job}/duplicate', [EmployerJobController::class, 'duplicate'])->name('jobs.duplicate')->scopeBindings();
        Route::post('/jobs/{job}/repost', [EmployerJobController::class, 'repost'])->name('jobs.repost')->scopeBindings();
        Route::resource('jobs', EmployerJobController::class)->names('jobs')->except(['show']);
        Route::get('/credits', [EmployerCreditsController::class, 'index'])->name('credits.index');
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/employers', [AdminEmployerController::class, 'index'])->name('employers.index');
        Route::post('/employers/{employer}/approve', [AdminEmployerController::class, 'approve'])->name('employers.approve');
        Route::post('/employers/{employer}/reject', [AdminEmployerController::class, 'reject'])->name('employers.reject');
    });
});

Route::get('/job-list', [HomeController::class, 'jobList'])->name('job-list');
Route::get('/job-openings', [HomeController::class, 'jobOpenings'])->name('job-openings');
Route::get('/job-openings/{job}/apply', [HomeController::class, 'showEmployerJobApply'])->name('job-openings.apply');
Route::post('/job-openings/{job}/apply', [HomeController::class, 'storeEmployerJobApply'])->middleware('auth')->name('job-openings.apply.store');
Route::get('/job-goals/{jobRole}', [HomeController::class, 'skillMatch'])->name('job-goal.show');
Route::get('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'showApplyForm'])->name('job-goal.apply');
Route::get('/job-goals/{jobRole}/match-score', [JobApplicationController::class, 'matchScore'])->middleware('auth')->name('job-goal.match-score');
Route::post('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'store'])->middleware('auth')->name('job-goal.apply.store');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/about', fn () => view('hirevo.about'))->name('about');
Route::get('/contact', fn () => view('hirevo.contact'))->name('contact');
