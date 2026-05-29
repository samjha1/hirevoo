<?php

/**
 * Maps Hirevo Blade views to Ads Manager placements and card layout variants.
 */
return [
    'views' => [
        'hirevo.index' => [
            'placement' => 'hirevo_homepage',
            'variant' => 'home',
        ],
        'hirevo.job-openings' => [
            'placement' => 'hirevo_jobs',
            'variant' => 'sidebar',
        ],
        'hirevo.job-list' => [
            'placement' => 'hirevo_jobs',
            'variant' => 'inline',
        ],
        'hirevo.candidate.dashboard' => [
            'placement' => 'hirevo_dashboard',
            'variant' => 'dashboard',
        ],
        'hirevo.skill-match' => [
            'placement' => 'hirevo_sidebar',
            'variant' => 'sidebar',
        ],
        'hirevo.resume-results' => [
            'placement' => 'hirevo_sidebar',
            'variant' => 'inline',
        ],
        'hirevo.pricing' => [
            'placement' => 'hirevo_homepage',
            'variant' => 'strip',
        ],
    ],
];
