<?php

namespace App\Providers;

use App\Services\LeadsmanagerAdService;
use App\Support\SeoMetaResolver;
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

        $seoResolver = fn ($view) => $view->with('seo', app(SeoMetaResolver::class)->resolve(request()));

        View::composer(['layouts.app', 'layouts.employer'], $seoResolver);

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

        $sponsoredViews = array_keys(config('hirevo_sponsored_ads.views', []));
        if ($sponsoredViews === []) {
            $sponsoredViews = [
                'hirevo.index',
                'hirevo.job-openings',
                'hirevo.job-list',
                'hirevo.candidate.dashboard',
                'hirevo.skill-match',
                'hirevo.resume-results',
                'hirevo.pricing',
            ];
        }

        View::composer($sponsoredViews, function ($view) {
            $map = config('hirevo_sponsored_ads.views', []);
            $config = $map[$view->name()] ?? match ($view->name()) {
                'hirevo.index' => ['placement' => 'hirevo_homepage', 'variant' => 'home'],
                'hirevo.job-openings' => ['placement' => 'hirevo_jobs', 'variant' => 'sidebar'],
                'hirevo.job-list' => ['placement' => 'hirevo_jobs', 'variant' => 'inline'],
                'hirevo.candidate.dashboard' => ['placement' => 'hirevo_dashboard', 'variant' => 'dashboard'],
                'hirevo.skill-match' => ['placement' => 'hirevo_sidebar', 'variant' => 'sidebar'],
                'hirevo.resume-results' => ['placement' => 'hirevo_sidebar', 'variant' => 'inline'],
                'hirevo.pricing' => ['placement' => 'hirevo_homepage', 'variant' => 'strip'],
                default => null,
            };
            if (! $config) {
                return;
            }

            $service = app(LeadsmanagerAdService::class);

            $view->with('sponsoredAd', $service->forPlacement($config['placement']))
                ->with('sponsoredAdVariant', $config['variant'] ?? 'default');
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
