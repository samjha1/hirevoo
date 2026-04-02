<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        View::share('theme', config('hirevo.theme_path', 'theme'));

        View::composer('layouts.app', function ($view) {
            $navUnreadCount = 0;
            $navNotifications = collect();
            if (Auth::check() && Auth::user()->isCandidate()) {
                $days = config('hirevo.notification_retention_days', 14);
                $user = Auth::user();
                $navUnreadCount = $user->unreadNotifications()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->count();
                $navNotifications = $user->notifications()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->orderByDesc('created_at')
                    ->limit(12)
                    ->get();
            }
            $view->with('navUnreadCount', $navUnreadCount)->with('navNotifications', $navNotifications);
        });

        View::composer('layouts.employer', function ($view) {
            $credits = 0;
            $profilePhotoUrl = null;
            if (auth()->check() && auth()->user()->isReferrer() && auth()->user()->referrerProfile) {
                $profile = auth()->user()->referrerProfile;
                $credits = (int) $profile->credits;
                if ($profile->profile_photo) {
                    $profilePhotoUrl = str_starts_with($profile->profile_photo, 'uploads/')
                        ? asset($profile->profile_photo)
                        : asset('storage/' . $profile->profile_photo);
                }
            }
            $view->with('employerCredits', $credits)->with('employerProfilePhotoUrl', $profilePhotoUrl);
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        });
    }
}
