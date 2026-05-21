<?php

return [
    'site_name' => env('SEO_SITE_NAME', 'Hirevo'),
    'brand_tagline' => 'Own Your Next Career Move',
    'title_suffix' => 'Hirevo — Own Your Next Career Move',

    'default_description' => 'Hirevo helps candidates get clarity on their profile, improve ATS readiness, match skills to job goals, and apply to verified openings. Employers post jobs and manage hiring in one place.',

    'default_og_image' => 'images/20260419_104749_0000ss.png',

    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),

    'organization' => [
        'name' => env('SEO_ORG_NAME', 'Hirevo'),
        'url' => env('SEO_ORG_URL', null),
        'logo' => env('SEO_ORG_LOGO', 'images/20260419_104749_0000ss.png'),
        'email' => env('SEO_CONTACT_EMAIL', null),
        'same_as' => array_values(array_filter(array_map('trim', explode(',', (string) env('SEO_SOCIAL_PROFILES', ''))))),
    ],

    'sitemap_cache_minutes' => (int) env('SEO_SITEMAP_CACHE_MINUTES', 60),

    'noindex_route_prefixes' => [
        'login',
        'register',
        'password.',
        'auth.',
        'logout',
        'verify-email',
        'send-otp',
        'verify-email-otp',
        'resend-otp',
        'candidate.dashboard',
        'profile',
        'profile.',
        'notifications.',
        'employer.',
        'resume.upload.store',
        'resume.file',
        'resume.results',
        'resume.guest-upload',
        'resume.lead',
        'resume.referral',
        'leads.',
        'career-consultation.',
        'job-openings.apply',
        'job-openings.apply.external-redirect',
        'job-openings.apply.store',
        'job-goal.apply',
        'job-goal.apply.store',
        'job-goal.match-score',
        'contact.submit',
        'referral-signup.store',
    ],

    'static_pages' => [
        'home' => [
            'title' => 'AI Career Intelligence & Job Matching',
            'description' => 'Get resume ATS scoring, skill-gap insights, curated job goals, and live job openings. Hirevo helps students and professionals own their next career move.',
        ],
        'job-list' => [
            'title' => 'Job Goals — Skill-Based Career Paths',
            'description' => 'Explore curated job goals with required skills, match scores, and upskilling paths tailored to your profile on Hirevo.',
        ],
        'job-openings' => [
            'title' => 'Job Openings — Apply to Active Roles',
            'description' => 'Browse active employer job openings, filter by location and work type, and apply with your Hirevo profile and resume.',
        ],
        'pricing' => [
            'title' => 'Pricing — Plans for Candidates & Employers',
            'description' => 'Transparent Hirevo pricing for resume intelligence, job matching, referrals, and employer hiring tools.',
        ],
        'about' => [
            'title' => 'About Hirevo',
            'description' => 'Learn how Hirevo combines AI career intelligence, referrals, and job marketplace features to help candidates and employers hire smarter.',
        ],
        'contact' => [
            'title' => 'Contact Hirevo',
            'description' => 'Contact the Hirevo team for support, partnerships, or product questions. We respond to candidate and employer inquiries.',
        ],
        'faq' => [
            'title' => 'FAQ — Hirevo Help & Answers',
            'description' => 'Answers about Hirevo resume analysis, job goals, referrals, privacy, and how candidates and companies use the platform.',
        ],
        'help' => [
            'title' => 'Help Center',
            'description' => 'Guides and help articles for using Hirevo: profiles, applications, resume upload, and employer tools.',
        ],
        'terms' => [
            'title' => 'Terms & Conditions',
            'description' => 'Hirevo terms and conditions governing use of the career intelligence and job marketplace platform.',
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'description' => 'How Hirevo collects, uses, and protects your personal data when you use our career and hiring services.',
        ],
        'cookies' => [
            'title' => 'Cookie Policy',
            'description' => 'Information about cookies and similar technologies used on the Hirevo website.',
        ],
        'disclaimer' => [
            'title' => 'Disclaimer',
            'description' => 'Legal disclaimers for information, job listings, and AI-generated insights provided on Hirevo.',
        ],
        'resume.upload' => [
            'title' => 'Free Resume ATS Score & Analysis',
            'description' => 'Upload your resume for a free ATS-style score, skill insights, and matched job goals on Hirevo.',
        ],
    ],

    'faq_schema' => [
        ['question' => 'What is Hirevoo?', 'answer' => 'Hirevoo helps you understand your profile, improve where it matters, and connect with opportunities that actually match you.'],
        ['question' => 'How is Hirevoo different from other job platforms?', 'answer' => 'Most platforms focus on showing more jobs. Hirevoo focuses on helping you understand what’s missing in your profile and how to improve before applying.'],
        ['question' => 'Is Hirevoo only for freshers?', 'answer' => 'No. While we’re especially helpful for students and freshers, anyone looking for better direction and relevant opportunities can use Hirevoo.'],
        ['question' => 'How do I get started?', 'answer' => 'Start by uploading your resume, exploring job roles, and browsing opportunities that match your profile.'],
        ['question' => 'Does Hirevoo guarantee a job?', 'answer' => 'No platform can guarantee a job. We help you improve your chances by giving clarity, direction, and access to relevant opportunities.'],
        ['question' => 'How does resume analysis help me?', 'answer' => 'It helps you understand whether your resume is strong enough, what skills you might be missing, and what to improve before applying.'],
        ['question' => 'Are the job opportunities verified?', 'answer' => 'We aim to share relevant and genuine opportunities. However, we recommend users verify details before applying.'],
        ['question' => 'How does the referral system work?', 'answer' => 'If you work in a company and know open roles, you can refer candidates through Hirevoo. Rewards depend on successful hiring and verification.'],
        ['question' => 'Is my data safe?', 'answer' => 'We take reasonable measures to protect your data and use it only to improve your experience and provide relevant opportunities.'],
    ],
];
