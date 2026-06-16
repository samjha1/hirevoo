<?php

return [

    /** Cache career insights per candidate (seconds). */
    'insights_cache_ttl' => max(300, (int) env('CANDIDATE_INSIGHTS_CACHE_TTL', 1800)),

    /** Minimum match % for “strong” highlights (dashboard widget). */
    'job_match_min_pct' => max(30, (int) env('CANDIDATE_JOB_MATCH_MIN_PCT', 45)),

    /** Lowest match % still listed on Job Matches page (includes stretch roles). */
    'job_match_include_min_pct' => max(5, (int) env('CANDIDATE_JOB_MATCH_INCLUDE_MIN_PCT', 15)),

    /** Max jobs on the Job Matches page. */
    'job_match_page_limit' => max(10, (int) env('CANDIDATE_JOB_MATCH_PAGE_LIMIT', 30)),

    /** How many job goals to score against the resume. */
    'job_match_goal_scan' => max(10, (int) env('CANDIDATE_JOB_MATCH_GOAL_SCAN', 35)),

    /** Employer jobs to score; top matches are merged into results. */
    'job_match_employer_scan' => max(20, (int) env('CANDIDATE_JOB_MATCH_EMPLOYER_SCAN', 150)),
    'job_match_employer_take' => max(5, (int) env('CANDIDATE_JOB_MATCH_EMPLOYER_TAKE', 25)),

    /** Lighter limits for dashboard first paint (full scan runs on Job Matches page). */
    'job_match_dashboard_goal_scan' => max(5, (int) env('CANDIDATE_JOB_MATCH_DASHBOARD_GOAL_SCAN', 12)),
    'job_match_dashboard_employer_scan' => max(10, (int) env('CANDIDATE_JOB_MATCH_DASHBOARD_EMPLOYER_SCAN', 40)),
    'job_match_dashboard_employer_take' => max(4, (int) env('CANDIDATE_JOB_MATCH_DASHBOARD_EMPLOYER_TAKE', 8)),
    'job_match_dashboard_page_limit' => max(4, (int) env('CANDIDATE_JOB_MATCH_DASHBOARD_PAGE_LIMIT', 8)),

    /**
     * Annual CTC bands (₹ LPA) by role keyword — used for salary insights heuristics.
     *
     * @var list<array{keywords: list<string>, fresher: array{min: int, max: int}, mid: array{min: int, max: int}, senior: array{min: int, max: int}}>
     */
    'salary_bands' => [
        [
            'keywords' => ['software', 'developer', 'engineer', 'full stack', 'backend', 'frontend', 'devops'],
            'fresher' => ['min' => 3, 'max' => 8],
            'mid' => ['min' => 8, 'max' => 18],
            'senior' => ['min' => 18, 'max' => 40],
        ],
        [
            'keywords' => ['data', 'analyst', 'scientist', 'machine learning', 'ml', 'ai'],
            'fresher' => ['min' => 4, 'max' => 10],
            'mid' => ['min' => 10, 'max' => 22],
            'senior' => ['min' => 22, 'max' => 45],
        ],
        [
            'keywords' => ['recruiter', 'hr', 'talent', 'staffing', 'bench'],
            'fresher' => ['min' => 2, 'max' => 5],
            'mid' => ['min' => 5, 'max' => 12],
            'senior' => ['min' => 12, 'max' => 22],
        ],
        [
            'keywords' => ['sales', 'business development', 'account'],
            'fresher' => ['min' => 3, 'max' => 7],
            'mid' => ['min' => 7, 'max' => 15],
            'senior' => ['min' => 15, 'max' => 30],
        ],
        [
            'keywords' => ['marketing', 'digital', 'seo', 'content'],
            'fresher' => ['min' => 2, 'max' => 6],
            'mid' => ['min' => 6, 'max' => 14],
            'senior' => ['min' => 14, 'max' => 28],
        ],
        [
            'keywords' => ['sap', 'salesforce', 'erp', 'consultant'],
            'fresher' => ['min' => 4, 'max' => 9],
            'mid' => ['min' => 9, 'max' => 20],
            'senior' => ['min' => 20, 'max' => 38],
        ],
    ],

    'default_salary_band' => [
        'fresher' => ['min' => 2, 'max' => 6],
        'mid' => ['min' => 6, 'max' => 14],
        'senior' => ['min' => 14, 'max' => 28],
    ],

    /**
     * Skill assessment question pools (MCQ). Matched to resume / role skills.
     *
     * @var array<string, list<array{question: string, options: list<string>, answer: int}>>
     */
    'assessment_questions' => [
        'PHP' => [
            ['question' => 'Which PHP feature helps enforce type safety on function arguments?', 'options' => ['declare(strict_types=1)', 'error_reporting(0)', 'session_start()', 'header()'], 'answer' => 0],
            ['question' => 'What does PSR-4 define?', 'options' => ['Autoloading standard', 'HTTP caching', 'Database migrations', 'Unit testing'], 'answer' => 0],
            ['question' => 'Which Laravel component handles HTTP routing?', 'options' => ['Router', 'Eloquent', 'Blade', 'Queue'], 'answer' => 0],
        ],
        'Laravel' => [
            ['question' => 'Where are route definitions typically stored in Laravel?', 'options' => ['routes/web.php', 'config/app.php', 'storage/logs', 'public/index.php'], 'answer' => 0],
            ['question' => 'Eloquent is Laravel\'s…', 'options' => ['ORM', 'Cache driver', 'Mailer', 'Scheduler'], 'answer' => 0],
            ['question' => 'Which artisan command clears config cache?', 'options' => ['config:clear', 'route:list', 'migrate:fresh', 'db:seed'], 'answer' => 0],
        ],
        'JavaScript' => [
            ['question' => 'Which keyword declares a block-scoped variable?', 'options' => ['let', 'var', 'function', 'static'], 'answer' => 0],
            ['question' => 'JSON.parse() converts…', 'options' => ['JSON string to object', 'Object to JSON string', 'Array to Set', 'Number to string'], 'answer' => 0],
            ['question' => 'Promises help with…', 'options' => ['Async operations', 'DOM styling', 'Memory leaks', 'Regex matching'], 'answer' => 0],
        ],
        'Python' => [
            ['question' => 'Which data structure is ordered and mutable?', 'options' => ['list', 'tuple', 'frozenset', 'str (immutable)'], 'answer' => 0],
            ['question' => 'pip is used to…', 'options' => ['Install packages', 'Compile Python', 'Run tests', 'Format code'], 'answer' => 0],
            ['question' => 'List comprehension syntax is…', 'options' => ['[x for x in items]', '{x: x}', '(x)', '<x>'], 'answer' => 0],
        ],
        'SQL' => [
            ['question' => 'Which clause filters rows before grouping?', 'options' => ['WHERE', 'HAVING', 'ORDER BY', 'LIMIT'], 'answer' => 0],
            ['question' => 'INNER JOIN returns…', 'options' => ['Matching rows from both tables', 'All left rows', 'All right rows', 'Cartesian product only'], 'answer' => 0],
            ['question' => 'PRIMARY KEY ensures…', 'options' => ['Unique row identifier', 'Nullable column', 'Default value', 'Index disable'], 'answer' => 0],
        ],
        'React' => [
            ['question' => 'useState returns…', 'options' => ['State value and setter', 'Ref object', 'Context only', 'Router'], 'answer' => 0],
            ['question' => 'Virtual DOM helps…', 'options' => ['Efficient UI updates', 'Database queries', 'File uploads', 'SEO only'], 'answer' => 0],
            ['question' => 'Props are…', 'options' => ['Read-only inputs to components', 'Global state', 'CSS classes', 'API keys'], 'answer' => 0],
        ],
        'AWS' => [
            ['question' => 'S3 is primarily used for…', 'options' => ['Object storage', 'Relational DB', 'Load balancing', 'DNS'], 'answer' => 0],
            ['question' => 'EC2 provides…', 'options' => ['Virtual servers', 'Email service', 'CDN only', 'NoSQL DB'], 'answer' => 0],
            ['question' => 'IAM controls…', 'options' => ['Access permissions', 'Billing only', 'VPC routing', 'Lambda runtime'], 'answer' => 0],
        ],
        'Communication' => [
            ['question' => 'In STAR method, R stands for…', 'options' => ['Result', 'Role', 'Resume', 'Review'], 'answer' => 0],
            ['question' => 'Active listening includes…', 'options' => ['Paraphrasing and confirming', 'Interrupting often', 'Avoiding eye contact', 'Multitasking'], 'answer' => 0],
            ['question' => 'Best email subject lines are…', 'options' => ['Specific and concise', 'ALL CAPS', 'Empty', 'Only emojis'], 'answer' => 0],
        ],
        'Recruitment' => [
            ['question' => 'ATS primarily helps…', 'options' => ['Track applicants', 'Payroll', 'Office seating', 'Travel booking'], 'answer' => 0],
            ['question' => 'Boolean search in recruiting uses…', 'options' => ['AND/OR/NOT operators', 'Only exact names', 'Image search', 'Video calls'], 'answer' => 0],
            ['question' => 'Time-to-fill measures…', 'options' => ['Days to close a requisition', 'Interview length', 'Offer amount', 'Notice period'], 'answer' => 0],
        ],
    ],

    /**
     * Mock interview question packs (behavioral + technical + HR).
     *
     * @var array<string, list<array{question: string, tip: string, sample: string}>>
     */
    'mock_interview_packs' => [
        'behavioral' => [
            ['question' => 'Tell me about yourself.', 'tip' => 'Keep it 60–90 seconds: present role, key wins, why this opportunity.', 'sample' => 'I\'m a [role] with [X] years in [domain]. Recently I [achievement with metric]. I\'m excited about this role because [link to company/role].'],
            ['question' => 'Describe a challenge you overcame at work.', 'tip' => 'Use STAR: Situation, Task, Action, Result with numbers.', 'sample' => 'Our team faced [problem]. I owned [task], did [actions], and we achieved [result % or time saved].'],
            ['question' => 'Tell me about a time you handled conflict.', 'tip' => 'Show empathy, facts, and resolution — not blame.', 'sample' => 'Two stakeholders disagreed on [X]. I facilitated a short sync, aligned on criteria, and we shipped on time.'],
            ['question' => 'Why are you leaving your current role?', 'tip' => 'Stay positive: growth, impact, alignment — never bad-mouth.', 'sample' => 'I\'ve learned a lot, and I\'m looking for [specific growth] that this role offers.'],
            ['question' => 'Where do you see yourself in 3 years?', 'tip' => 'Tie ambition to skills and contribution at this company.', 'sample' => 'Deepening expertise in [skill], leading [scope], and delivering measurable impact.'],
        ],
        'technical' => [
            ['question' => 'Walk me through a recent project you built.', 'tip' => 'Cover problem, architecture, your contribution, trade-offs, outcome.', 'sample' => 'We needed [goal]. I designed [components], chose [stack] because [reason], and improved [metric].'],
            ['question' => 'How do you debug a production issue?', 'tip' => 'Mention logs, monitoring, reproduce, isolate, fix, postmortem.', 'sample' => 'Check alerts → reproduce in staging → bisect commits → patch → document root cause.'],
            ['question' => 'Explain a concept from your resume in simple terms.', 'tip' => 'Imagine explaining to a non-technical stakeholder.', 'sample' => 'Pick your strongest listed skill and use an analogy plus one real example.'],
            ['question' => 'How do you prioritize when deadlines conflict?', 'tip' => 'Impact, urgency, dependencies, communicate early.', 'sample' => 'I rank by business impact, flag risks to stakeholders, and negotiate scope if needed.'],
            ['question' => 'What would you improve in our product/process?', 'tip' => 'Research the company; be constructive and specific.', 'sample' => 'Based on [research], I\'d explore [idea] because [user/business benefit].'],
        ],
        'hr_screening' => [
            ['question' => 'What are your salary expectations?', 'tip' => 'Give a researched range; align with market data below.', 'sample' => 'Based on my experience and market for [role] in [city], I\'m looking at ₹X–Y LPA, flexible on overall package.'],
            ['question' => 'What is your notice period?', 'tip' => 'Be honest; mention if negotiable or buyout possible.', 'sample' => 'My notice period is [N] days. I can discuss an early release with my manager if needed.'],
            ['question' => 'Are you open to relocation / hybrid?', 'tip' => 'Match the job location mode honestly.', 'sample' => 'I\'m open to [hybrid/on-site] in [city] as required by the role.'],
            ['question' => 'Why should we hire you?', 'tip' => '3 bullets: skills match, proof, culture fit.', 'sample' => 'I bring [top skills], proven results in [area], and I thrive in [team style].'],
            ['question' => 'Do you have any questions for us?', 'tip' => 'Ask about team, success metrics, growth — never only leave/CTC.', 'sample' => 'What does success look like in the first 90 days? How does the team collaborate?'],
        ],
    ],

];
