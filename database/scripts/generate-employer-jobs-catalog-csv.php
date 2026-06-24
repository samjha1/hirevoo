<?php

/**
 * One-off generator for employer_jobs_catalog_500.csv
 * Run: php database/scripts/generate-employer-jobs-catalog-csv.php
 */

$outPath = __DIR__.'/../csv/employer_jobs_catalog_500.csv';
$dir = dirname($outPath);
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$headers = [
    'company_name', 'title', 'job_department', 'job_type', 'work_location_type', 'pay_type',
    'location_city', 'location_state', 'location_country', 'salary_min', 'salary_max',
    'experience_years', 'description', 'required_skills', 'perks', 'apply_link',
    'joining_fee_required', 'is_night_shift', 'status', 'display_applications_count', 'posted_days_ago',
];

$companies = [
    ['TCS', 'https://www.tcs.com/careers'],
    ['Infosys', 'https://www.infosys.com/careers'],
    ['Wipro', 'https://careers.wipro.com'],
    ['HCL Technologies', 'https://www.hcltech.com/careers'],
    ['Tech Mahindra', 'https://careers.techmahindra.com'],
    ['Accenture India', 'https://www.accenture.com/in-en/careers'],
    ['Capgemini India', 'https://www.capgemini.com/careers'],
    ['IBM India', 'https://www.ibm.com/careers'],
    ['Amazon India', 'https://www.amazon.jobs/en/locations/india'],
    ['Flipkart', 'https://www.flipkartcareers.com'],
    ['Swiggy', 'https://careers.swiggy.com'],
    ['Zomato', 'https://www.zomato.com/careers'],
    ['Paytm', 'https://paytm.com/careers'],
    ['PhonePe', 'https://www.phonepe.com/careers'],
    ['Razorpay', 'https://razorpay.com/jobs'],
    ['Freshworks', 'https://www.freshworks.com/company/careers'],
    ['Zoho', 'https://www.zoho.com/careers'],
    ['Mindtree', 'https://www.mindtree.com/careers'],
    ['LTIMindtree', 'https://www.ltimindtree.com/careers'],
    ['Cognizant', 'https://careers.cognizant.com'],
    ['Deloitte India', 'https://www2.deloitte.com/in/en/careers'],
    ['EY India', 'https://careers.ey.com'],
    ['KPMG India', 'https://home.kpmg/in/en/home/careers'],
    ['Genpact', 'https://www.genpact.com/careers'],
];

$titles = [
    ['Senior Java Developer', 'Engineering', 5, 900000, 1800000, 'Java|Spring Boot|Microservices|SQL|AWS', 'Design and build scalable backend services for enterprise clients.'],
    ['React Frontend Engineer', 'Engineering', 2, 700000, 1300000, 'React|TypeScript|REST APIs|CSS|Git', 'Develop responsive web applications with modern frontend stacks.'],
    ['Full Stack Developer', 'Engineering', 3, 800000, 1500000, 'Node.js|React|MongoDB|PostgreSQL|Docker', 'Own features end-to-end across API and UI layers.'],
    ['DevOps Engineer', 'Engineering', 4, 1000000, 2000000, 'AWS|Kubernetes|Terraform|CI/CD|Linux', 'Automate deployments and maintain cloud infrastructure.'],
    ['Data Analyst', 'Operations', 1, 500000, 900000, 'SQL|Excel|Python|Power BI|Tableau', 'Transform business data into dashboards and actionable insights.'],
    ['Data Scientist', 'Engineering', 3, 1100000, 2200000, 'Python|Machine Learning|Statistics|Pandas|SQL', 'Build predictive models and analytics solutions for product teams.'],
    ['QA Automation Engineer', 'Engineering', 2, 600000, 1100000, 'Selenium|Java|API Testing|Jira|Agile', 'Design automated test suites and improve release quality.'],
    ['Product Manager', 'Operations', 5, 1200000, 2500000, 'Product Strategy|Roadmaps|Analytics|Stakeholder Management', 'Define product vision and drive cross-functional delivery.'],
    ['UI/UX Designer', 'Engineering', 2, 650000, 1200000, 'Figma|User Research|Prototyping|Design Systems', 'Create intuitive interfaces and user-centered product experiences.'],
    ['Android Developer', 'Engineering', 3, 750000, 1400000, 'Kotlin|Android SDK|REST APIs|Firebase', 'Build and ship high-quality Android applications.'],
    ['iOS Developer', 'Engineering', 3, 800000, 1500000, 'Swift|UIKit|SwiftUI|REST APIs', 'Develop native iOS apps for consumer and enterprise users.'],
    ['Flutter Developer', 'Engineering', 2, 700000, 1300000, 'Flutter|Dart|Firebase|REST APIs', 'Deliver cross-platform mobile apps from a single codebase.'],
    ['Cloud Architect', 'Engineering', 8, 1800000, 3500000, 'AWS|Azure|Solution Design|Security|Networking', 'Lead cloud migration and architecture for large programs.'],
    ['Business Analyst', 'Operations', 3, 700000, 1200000, 'Requirements|SQL|Jira|Documentation|Stakeholder Management', 'Bridge business needs with technical delivery teams.'],
    ['Sales Executive', 'Sales', 2, 400000, 800000, 'B2B Sales|CRM|Negotiation|Communication', 'Drive revenue growth through enterprise client relationships.'],
    ['Digital Marketing Manager', 'Marketing', 4, 600000, 1100000, 'SEO|Google Ads|Analytics|Content Strategy', 'Plan and execute digital campaigns across channels.'],
    ['HR Business Partner', 'Human Resources', 5, 700000, 1300000, 'Talent Management|Employee Relations|HR Policies', 'Partner with leaders on hiring, retention, and culture.'],
    ['Financial Analyst', 'Finance', 2, 550000, 1000000, 'Excel|Financial Modeling|SAP|Reporting', 'Support budgeting, forecasting, and financial reporting.'],
    ['Customer Support Lead', 'Customer Support', 3, 450000, 850000, 'Customer Service|Team Leadership|CRM|Communication', 'Lead support operations and improve customer satisfaction.'],
    ['Cybersecurity Analyst', 'Engineering', 3, 900000, 1700000, 'SIEM|Network Security|Incident Response|Compliance', 'Monitor threats and strengthen organizational security posture.'],
    ['Python Developer', 'Engineering', 2, 650000, 1200000, 'Python|Django|Flask|PostgreSQL|REST APIs', 'Build APIs and backend services using Python frameworks.'],
    ['SAP Consultant', 'Operations', 4, 1000000, 1900000, 'SAP FICO|SAP MM|Implementation|Configuration', 'Implement and support SAP modules for enterprise clients.'],
    ['Network Engineer', 'Engineering', 3, 600000, 1100000, 'Cisco|Routing|Switching|Firewalls|LAN/WAN', 'Maintain network infrastructure and troubleshoot connectivity.'],
    ['Content Writer', 'Marketing', 1, 350000, 650000, 'Content Writing|SEO|Editing|Research', 'Create engaging content for web, blogs, and marketing campaigns.'],
    ['Operations Manager', 'Operations', 6, 900000, 1600000, 'Operations|Process Improvement|Team Management|KPIs', 'Optimize daily operations and cross-team workflows.'],
];

$cities = [
    ['Bangalore', 'Karnataka'],
    ['Hyderabad', 'Telangana'],
    ['Pune', 'Maharashtra'],
    ['Mumbai', 'Maharashtra'],
    ['Chennai', 'Tamil Nadu'],
    ['Delhi NCR', 'Delhi'],
    ['Gurgaon', 'Haryana'],
    ['Noida', 'Uttar Pradesh'],
    ['Kolkata', 'West Bengal'],
    ['Ahmedabad', 'Gujarat'],
    ['Remote', 'India'],
];

$jobTypes = ['full_time', 'full_time', 'full_time', 'contract', 'part_time'];
$workTypes = ['hybrid', 'remote', 'office', 'hybrid', 'remote'];
$perksList = [
    'Health insurance, Paid time off, Learning budget',
    'WFH, Health insurance, Annual bonus',
    'Health insurance, Transport allowance, Flexible hours',
    'ESOP, Health insurance, Gym membership',
    'Paid time off, Health insurance, Parental leave',
];

$handle = fopen($outPath, 'w');
if ($handle === false) {
    fwrite(STDERR, "Cannot write to {$outPath}\n");
    exit(1);
}

fputcsv($handle, $headers);

for ($i = 0; $i < 500; $i++) {
    $company = $companies[$i % count($companies)];
    $title = $titles[$i % count($titles)];
    $city = $cities[$i % count($cities)];

    $companyName = $company[0];
    $applyLink = $company[1];
    [$roleTitle, $dept, $exp, $salMin, $salMax, $skills, $descSnippet] = $title;

    $description = sprintf(
        '%s is hiring a %s. %s Strong communication skills and relevant experience preferred.',
        $companyName,
        $roleTitle,
        $descSnippet
    );

    fputcsv($handle, [
        $companyName,
        $roleTitle.($i > count($titles) ? ' II' : ''),
        $dept,
        $jobTypes[$i % count($jobTypes)],
        $workTypes[$i % count($workTypes)],
        'fixed',
        $city[0],
        $city[1],
        'India',
        $salMin,
        $salMax,
        $exp,
        $description,
        $skills,
        $perksList[$i % count($perksList)],
        $applyLink,
        0,
        0,
        'active',
        random_int(45, 2400),
        random_int(0, 30),
    ]);
}

fclose($handle);

// Template file (headers only)
$templatePath = __DIR__.'/../csv/employer_jobs_template.csv';
$tpl = fopen($templatePath, 'w');
if ($tpl !== false) {
    fputcsv($tpl, $headers);
    fclose($tpl);
}

echo "Wrote {$outPath} (500 rows)\n";
echo "Wrote {$templatePath}\n";
