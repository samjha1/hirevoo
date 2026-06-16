<?php

namespace App\Providers;

use App\Models\CandidateProfile;
use App\Models\EmployerJob;
use App\Models\JobRole;
use App\Models\TalentPoolCandidate;
use App\Models\User;
use App\Observers\CandidateProfileSearchObserver;
use App\Observers\CandidateUserSearchObserver;
use App\Observers\EmployerJobSearchObserver;
use App\Observers\JobRoleSearchObserver;
use App\Observers\TalentPoolCandidateSearchObserver;
use App\Services\CandidatePremiumService;
use App\Services\EmployerPlanService;
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
        if (config('elasticsearch.enabled')) {
            EmployerJob::observe(EmployerJobSearchObserver::class);
            JobRole::observe(JobRoleSearchObserver::class);
            User::observe(CandidateUserSearchObserver::class);
            CandidateProfile::observe(CandidateProfileSearchObserver::class);
            TalentPoolCandidate::observe(TalentPoolCandidateSearchObserver::class);
        }

        Paginator::useBootstrapFive();
        View::share('theme', config('hirevo.theme_path', 'theme'));

        $seoResolver = fn ($view) => $view->with('seo', app(SeoMetaResolver::class)->resolve(request()));

        View::composer(['layouts.app', 'layouts.employer', 'layouts.candidate'], $seoResolver);

        View::composer(['layouts.app', 'layouts.candidate'], function ($view) {
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

        View::composer(['layouts.app', 'layouts.candidate'], function ($view) {
            $user = Auth::user();
            $premium = app(CandidatePremiumService::class);
            $activePlan = ($user && $user->isCandidate())
                ? $premium->activeSubscriptionSummary($user)
                : null;

            $view->with([
                'candidateHasPremium' => $user && $user->isCandidate() ? $premium->hasAccess($user) : false,
                'candidateHasAiTools' => $user && $user->isCandidate() ? $premium->hasAiCareerToolsAccess($user) : false,
                'candidatePlanUrl' => $premium->planUrl(),
                'candidateActivePlan' => $activePlan,
                'candidateActivePlanName' => $activePlan['name'] ?? null,
                'candidateActivePlanKey' => $activePlan['key'] ?? null,
                'candidatePlanExpiresAt' => $activePlan['expires_at'] ?? null,
                'candidateRenewalPlanKey' => $activePlan['renewal_plan'] ?? ($user?->candidateProfile?->renewal_plan ?? null),
            ]);
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

        View::composer(
            ['layouts.app', 'layouts.employer', 'hirevo.employer.*'],
            function ($view) {
                $credits = 0;
                $profilePhotoUrl = null;
                $activePlanKey = null;
                $activePlanName = null;
                $hasActivePlan = false;
                $planExpiresAt = null;
                $planIsLaunch = false;

                if (auth()->check() && auth()->user()->isReferrer() && auth()->user()->referrerProfile) {
                    $profile = auth()->user()->referrerProfile;
                    $credits = (int) $profile->credits;
                    if ($profile->profile_photo) {
                        $profilePhotoUrl = $profile->profilePhotoUrl();
                    }

                    $planService = app(EmployerPlanService::class);
                    $activePlanKey = $planService->planKey($profile);
                    $hasActivePlan = $planService->hasActiveSubscription($profile);
                    $planExpiresAt = $profile->subscription_expires_at;

                    if ($activePlanKey !== null) {
                        $planConfig = $planService->planConfig($activePlanKey);
                        $activePlanName = $planConfig['name'] ?? ucfirst($activePlanKey);
                        $billing = $planConfig['billing_period'] ?? '';
                        $planIsLaunch = $activePlanKey === 'hiring-launch'
                            || in_array($billing, ['one_time_7d', 'launch_7d'], true);
                    }
                }

                $view->with([
                    'employerCredits' => $credits,
                    'employerProfilePhotoUrl' => $profilePhotoUrl,
                    'employerActivePlanKey' => $activePlanKey,
                    'employerActivePlanName' => $activePlanName,
                    'employerHasActivePlan' => $hasActivePlan,
                    'employerPlanExpiresAt' => $planExpiresAt,
                    'employerPlanIsLaunch' => $planIsLaunch,
                ]);
            }
        );

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        });
    }
}
