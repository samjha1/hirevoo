<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
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
    Route::get('/resume/upload', [ResumeController::class, 'showUploadForm'])->name('resume.upload');
    Route::post('/resume/upload', [ResumeController::class, 'upload']);
    Route::get('/resume/{resume}/results', [ResumeController::class, 'results'])->name('resume.results');
    Route::post('/resume/lead', [ResumeController::class, 'createLead'])->name('resume.lead');
});

Route::get('/job-list', [HomeController::class, 'jobList'])->name('job-list');
Route::get('/job-goals/{jobRole}', [HomeController::class, 'skillMatch'])->name('job-goal.show');
Route::get('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'showApplyForm'])->name('job-goal.apply');
Route::post('/job-goals/{jobRole}/apply', [JobApplicationController::class, 'store'])->middleware('auth')->name('job-goal.apply.store');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');
Route::get('/about', fn () => view('hirevo.about'))->name('about');
Route::get('/contact', fn () => view('hirevo.contact'))->name('contact');
