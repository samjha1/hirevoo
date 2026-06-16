<?php

return [
    'checkout' => [
        'gst_rate' => (float) env('PLAN_GST_RATE', 18),
        'success_message' => 'Payment successful! Your premium plan is now active.',
        'pending_message' => 'Your payment is being processed.',
    ],

    /** Lowest tier — referral basics only; AI career tools unlock on higher plans. */
    'base_plan_slug' => 'access',

    'plans' => [
        // Fallback catalog when `candidate_plans` table is empty. Primary source: DB + CandidatePlanSeeder.
        'access' => [
            'name' => 'Access',
            'tagline' => 'Your first step in',
            'price_inr' => 149,
            'popular' => false,
            'duration_days' => 30,
            'referral_requests_limit' => 1,
            'features' => [
                '1 Referral Request',
                'Basic Match Score',
                'Job Insights',
                'Application Tracker',
            ],
        ],
        'advantage' => [
            'name' => 'Advantage',
            'tagline' => 'Most job seekers choose this',
            'price_inr' => 499,
            'popular' => true,
            'duration_days' => 90,
            'referral_requests_limit' => 5,
            'features' => [
                '5 Referral Requests',
                'Detailed Match Score',
                'LinkedIn Profile Analysis',
                'Priority Profile Visibility',
                'Smart Job Recommendations',
                'Skill Gap Report',
            ],
        ],
        'accelerator' => [
            'name' => 'Accelerator',
            'tagline' => 'Serious about getting hired',
            'price_inr' => 999,
            'popular' => false,
            'duration_days' => 180,
            'referral_requests_limit' => 15,
            'features' => [
                '15 Referral Requests',
                'Advanced Insights',
                'LinkedIn Profile Analysis',
                'Priority Processing',
                'High-Quality Job Access',
                'Resume Analysis',
            ],
        ],
        'elite' => [
            'name' => 'Elite',
            'tagline' => 'For top-tier career moves',
            'price_inr' => 2499,
            'popular' => false,
            'duration_days' => 365,
            'referral_requests_limit' => 999,
            'features' => [
                'Unlimited Referrals*',
                'Dedicated Support',
                'LinkedIn Deep Analysis',
                'Priority Auto Apply',
                'Profile Optimisation',
                'Interview Guidance',
                'All Premium Features',
            ],
        ],
    ],
];
