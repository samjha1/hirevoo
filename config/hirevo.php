<?php

return [
    'theme_path' => env('THEME_PATH', 'theme'),

    /** Total HTTP timeout per LLM request (read/transfer). Raise if models are slow; keep below PHP max_execution_time. */
    'llm_http_timeout_seconds' => max(15, (int) env('LLM_HTTP_TIMEOUT', 120)),

    /** Connection phase only (DNS + TCP + TLS). */
    'llm_http_connect_timeout_seconds' => max(5, (int) env('LLM_HTTP_CONNECT_TIMEOUT', 30)),

    /** PHP time limit for resume upload + analysis + profile fill (seconds). */
    'resume_analysis_time_limit' => max(60, (int) env('RESUME_ANALYSIS_TIME_LIMIT', 180)),

    /** Candidate in-app notifications: hide unread items older than this many days (and from counts). */
    'notification_retention_days' => (int) env('NOTIFICATION_RETENTION_DAYS', 14),

    /**
     * Minimum annual CTC (₹) allowed for employer job salary_min when pay is fixed or negotiable.
     * Default 1.5 LPA = ₹1,50,000 per annum. Set EMPLOYER_SALARY_MIN_FLOOR_INR=200000 for 2 LPA.
     */
    'employer_salary_min_floor_inr' => (int) env('EMPLOYER_SALARY_MIN_FLOOR_INR', 150_000),

    /**
     * Grouped preset skills & certifications for employer job posts (multi-select checkboxes).
     */
    'employer_job_skill_presets' => [
        'Cloud & DevOps' => [
            'AWS', 'Microsoft Azure', 'Google Cloud (GCP)', 'Oracle Cloud', 'IBM Cloud', 'Alibaba Cloud',
            'Kubernetes', 'Docker', 'Terraform', 'Ansible', 'Chef', 'Puppet',
            'Jenkins', 'GitLab CI/CD', 'GitHub Actions', 'CircleCI', 'Argo CD', 'Helm',
            'Prometheus', 'Grafana', 'ELK Stack', 'Splunk', 'Nginx', 'Linux Administration',
        ],
        'Languages & backend' => [
            'Python', 'Java', 'JavaScript', 'TypeScript', 'C#', 'PHP', 'Go', 'Ruby', 'Rust', 'C++', 'Scala',
            'Node.js', 'Laravel', 'Spring Boot', 'Django', 'FastAPI', 'Flask', 'Express.js',
            '.NET', 'ASP.NET Core', 'GraphQL', 'REST API design', 'Microservices',
        ],
        'Frontend & mobile' => [
            'React', 'Vue.js', 'Angular', 'Next.js', 'Nuxt.js', 'Svelte', 'HTML/CSS', 'SASS', 'Tailwind CSS',
            'Webpack', 'Vite', 'React Native', 'Flutter', 'iOS (Swift)', 'Android (Kotlin)', 'Electron',
        ],
        'Data, AI & analytics' => [
            'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'Redis', 'Elasticsearch', 'Snowflake', 'BigQuery',
            'Data Analytics', 'Power BI', 'Tableau', 'Looker', 'Machine Learning', 'Deep Learning',
            'PyTorch', 'TensorFlow', 'Scikit-learn', 'NLP', 'Computer Vision', 'Generative AI',
            'Apache Spark', 'Databricks', 'Apache Airflow', 'dbt', 'ETL / ELT', 'Data Engineering',
        ],
        'CRM, ERP & platforms' => [
            'Salesforce', 'SAP', 'SAP FI/CO', 'SAP MM', 'Workday', 'ServiceNow', 'HubSpot', 'Zoho CRM',
            'Zendesk', 'Shopify', 'Magento', 'WordPress', 'Atlassian (Jira/Confluence)', 'Microsoft Dynamics',
        ],
        'Security & compliance' => [
            'Cybersecurity', 'Penetration Testing', 'Application Security', 'ISO 27001', 'SOC 2', 'GDPR',
            'Network Security', 'IAM', 'SIEM',
        ],
        'Marketing & growth' => [
            'Digital Marketing', 'SEO', 'SEM / Google Ads', 'Social Media Marketing', 'Content Marketing',
            'Email Marketing', 'Growth Marketing', 'Brand Management', 'Performance Marketing',
        ],
        'Sales & customer' => [
            'B2B Sales', 'B2C Sales', 'Inside Sales', 'Field Sales', 'Account Management',
            'Business Development', 'Customer Success', 'Pre-sales', 'Salesforce Administration',
        ],
        'Operations & supply chain' => [
            'Operations Management', 'Supply Chain', 'Logistics', 'Procurement', 'Inventory Management',
            'Lean Manufacturing', 'Six Sigma', 'Quality Assurance', 'Process Improvement',
        ],
        'People & workplace' => [
            'HR / Talent Acquisition', 'Recruitment', 'Payroll', 'L&D / Training', 'Employee Relations',
            'Compensation & Benefits', 'HR Analytics',
        ],
        'Finance & accounting' => [
            'Financial Accounting', 'Management Accounting', 'Taxation', 'GST', 'TDS', 'Audit',
            'Financial Modelling', 'FP&A', 'Treasury', 'CA / ICAI', 'CFA', 'Bookkeeping',
        ],
        'Professional certifications' => [
            'PMP', 'PRINCE2', 'ITIL', 'CISSP', 'CISM', 'AWS Certified', 'Azure Certified', 'GCP Professional',
            'Scrum Master (CSM)', 'Product Management', 'UX Design', 'UI Design', 'Figma',
            'Copywriting', 'Technical Writing', 'Video Editing',
        ],
    ],

    /**
     * Job department dropdown values (employer post / edit job). Used with employer_job_department_skill_groups.
     */
    'employer_job_departments' => [
        'Sales',
        'Marketing',
        'Human Resources',
        'Finance',
        'Engineering',
        'Operations',
        'Customer Support',
        'Legal',
        'Other',
    ],

    /**
     * Skill preset group titles (keys of employer_job_skill_presets) to show for each department.
     * Empty array for "Other" means: show every group. Unknown department: show all.
     */
    'employer_job_department_skill_groups' => [
        'Sales' => [
            'Sales & customer',
            'CRM, ERP & platforms',
            'Marketing & growth',
            'Professional certifications',
        ],
        'Marketing' => [
            'Marketing & growth',
            'CRM, ERP & platforms',
            'Data, AI & analytics',
            'Professional certifications',
        ],
        'Human Resources' => [
            'People & workplace',
            'Finance & accounting',
            'Professional certifications',
        ],
        'Finance' => [
            'Finance & accounting',
            'CRM, ERP & platforms',
            'Data, AI & analytics',
            'Professional certifications',
        ],
        'Engineering' => [
            'Cloud & DevOps',
            'Languages & backend',
            'Frontend & mobile',
            'Data, AI & analytics',
            'CRM, ERP & platforms',
            'Security & compliance',
            'Professional certifications',
        ],
        'Operations' => [
            'Operations & supply chain',
            'Finance & accounting',
            'CRM, ERP & platforms',
            'Professional certifications',
        ],
        'Customer Support' => [
            'Sales & customer',
            'CRM, ERP & platforms',
            'Operations & supply chain',
            'Professional certifications',
        ],
        'Legal' => [
            'Security & compliance',
            'Finance & accounting',
            'Professional certifications',
        ],
        'Other' => [],
    ],
];
