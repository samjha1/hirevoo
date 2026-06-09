-- Run after migration 2026_06_09_000001_create_employer_plans_table.php
-- Or use: php artisan db:seed --class=EmployerPlanSeeder

INSERT INTO `employer_plans`
(`slug`, `tier`, `name`, `tagline`, `price_inr`, `price_sub`, `cta`, `is_popular`, `is_custom_price`, `is_active`, `sort_order`, `billing_period`, `talent_pool_access`, `job_credits_included`, `unlimited_profile_unlocks`, `max_active_jobs`, `features`, `extras`, `created_at`, `updated_at`)
VALUES
('hiring-launch', 'Launch Offer', 'Hiring Launch Program', 'Launch your first hiring campaign with Hirevoo and experience a smarter way to hire.', 1999, 'one-time · 7 days access', 'Launch Now', 0, 0, 1, 0, 'one_time_7d', 'limited', 1, 0, 1, '["1 Active Job Posting","7 Days Hiring Launch Access","Hiring Health Score™ Assessment","Dedicated Hiring Consultation","Job Description Review & Optimization","Candidate Sourcing Support","Candidate Management Dashboard Access","Application Tracking","Basic Candidate Filtering","Hiring Performance Review","Hiring Recommendations Report","Priority Email & WhatsApp Support","One-Time Offer for New Companies Only","No Long-Term Commitment Required","Easy Upgrade to Hirevoo Subscription Plans"]', '{"is_launch_offer":true,"duration":"7 Days","duration_days":7,"ideal_for":["Startups","Small Businesses","First-Time Recruiters","Companies Hiring for 1–10 Positions"],"bonus":["Complimentary 30-Minute Hiring Strategy Session","Personalized Hiring Improvement Recommendations"]}', NOW(), NOW()),
('starter', 'Tier 01', 'Starter', 'Ideal for small businesses & early-stage startups testing the market.', 4999, 'per month, billed monthly', 'Get Started', 0, 0, 1, 1, 'monthly', 'limited', 0, 0, 3, '["3 Active Job Postings","Candidate Dashboard","Basic Candidate Filtering","Limited Resume Database Access","Email Support"]', NOW(), NOW()),
('growth', 'Tier 02', 'Growth', 'Built for growing startups & SMEs ready to hire with speed and precision.', 14999, 'per month, billed monthly', 'Start Hiring Smarter', 1, 0, 1, 2, 'monthly', 'full', 50, 1, 10, '["10 Active Job Postings","AI Match Scoring","Referral-Backed Candidates","Priority Candidate Visibility","Assisted Candidate Shortlisting","Full Resume Database Access","Employer Branding","Hiring Analytics Dashboard","WhatsApp Support"]', NOW(), NOW()),
('scale', 'Tier 03', 'Scale', 'Designed for high-growth companies with fast, volume hiring demands.', 39999, 'per month, billed monthly', 'Scale Your Team', 0, 0, 1, 3, 'monthly', 'full', 50, 0, 50, '["50 Active Job Postings","50 Job Posting Credits Included","Dedicated Hiring Manager","Priority Referrals","Candidate Screening","Interview Coordination","Advanced Analytics","ATS Access","Premium Employer Branding","Dedicated Account Support"]', NOW(), NOW()),
('enterprise', 'Tier 04', 'Enterprise', 'For large organizations needing full-suite recruitment infrastructure.', NULL, 'tailored to your requirements', 'Talk to Sales', 0, 1, 1, 4, 'monthly', 'full', NULL, 1, NULL, '["Unlimited Hiring","Campus & Bulk Hiring","Recruitment Process Outsourcing (RPO)","HRMS Access","Dedicated Recruitment Team","API Integrations","Custom Analytics","Dedicated Success Manager"]', NOW(), NOW())
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`price_inr` = VALUES(`price_inr`),
`job_credits_included` = VALUES(`job_credits_included`),
`features` = VALUES(`features`),
`updated_at` = NOW();
