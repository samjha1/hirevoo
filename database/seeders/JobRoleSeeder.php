<?php

namespace Database\Seeders;

use App\Models\JobRole;
use App\Models\JobRequiredSkill;
use Illuminate\Database\Seeder;

class JobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'title' => 'Data Analyst',
                'slug' => 'data-analyst',
                'description' => 'Analyze data, build reports, and drive insights for business decisions.',
                'skills' => ['SQL', 'Excel', 'Python', 'Data Analysis', 'Data Visualization', 'Power BI', 'Statistics'],
            ],
            [
                'title' => 'Software Engineer',
                'slug' => 'software-engineer',
                'description' => 'Design, develop, and maintain software applications.',
                'skills' => ['Java', 'Python', 'SQL', 'Git', 'Problem Solving', 'Data Structures', 'REST API', 'Agile'],
            ],
            [
                'title' => 'Product Manager',
                'slug' => 'product-manager',
                'description' => 'Own product roadmap, prioritization, and execution with cross-functional teams.',
                'skills' => ['Agile', 'Communication', 'Leadership', 'Analytics', 'Roadmap', 'Jira', 'Project Management'],
            ],
            [
                'title' => 'Full Stack Developer',
                'slug' => 'full-stack-developer',
                'description' => 'Build end-to-end web applications from frontend to backend.',
                'skills' => ['PHP', 'Laravel', 'JavaScript', 'React', 'SQL', 'Git', 'REST API', 'HTML', 'CSS'],
            ],
            [
                'title' => 'Backend Developer',
                'slug' => 'backend-developer',
                'description' => 'Develop server-side logic, APIs, and databases.',
                'skills' => ['Java', 'Python', 'SQL', 'REST API', 'Git', 'Node.js', 'MySQL', 'Problem Solving'],
            ],
            [
                'title' => 'Frontend Developer',
                'slug' => 'frontend-developer',
                'description' => 'Build user interfaces and client-side web applications.',
                'skills' => ['JavaScript', 'React', 'HTML', 'CSS', 'Git', 'Bootstrap', 'TypeScript'],
            ],
            [
                'title' => 'DevOps Engineer',
                'slug' => 'devops-engineer',
                'description' => 'Automate deployment, manage infrastructure, and improve CI/CD pipelines.',
                'skills' => ['Linux', 'Docker', 'CI/CD', 'AWS', 'Git', 'Kubernetes', 'Jenkins'],
            ],
            [
                'title' => 'Data Engineer',
                'slug' => 'data-engineer',
                'description' => 'Design and build data pipelines, warehouses, and ETL processes.',
                'skills' => ['SQL', 'Python', 'ETL', 'Data Modeling', 'AWS', 'Data Analysis', 'Git'],
            ],
            [
                'title' => 'Mobile Developer',
                'slug' => 'mobile-developer',
                'description' => 'Develop native or cross-platform mobile applications.',
                'skills' => ['JavaScript', 'React Native', 'Flutter', 'Git', 'REST API', 'Problem Solving'],
            ],
            [
                'title' => 'QA Engineer',
                'slug' => 'qa-engineer',
                'description' => 'Ensure quality through testing, automation, and process improvement.',
                'skills' => ['Problem Solving', 'Communication', 'Agile', 'Jira', 'Manual Testing', 'Automation'],
            ],
            [
                'title' => 'Business Analyst',
                'slug' => 'business-analyst',
                'description' => 'Bridge business needs and IT solutions through analysis and requirements.',
                'skills' => ['SQL', 'Excel', 'Communication', 'Analytics', 'Project Management', 'Agile'],
            ],
            [
                'title' => 'Machine Learning Engineer',
                'slug' => 'machine-learning-engineer',
                'description' => 'Build and deploy ML models and data-driven systems.',
                'skills' => ['Python', 'Machine Learning', 'Data Analysis', 'SQL', 'Git', 'Statistics'],
            ],
        ];

        foreach ($roles as $r) {
            $skillList = $r['skills'] ?? [];
            unset($r['skills']);

            $role = JobRole::firstOrCreate(
                ['slug' => $r['slug']],
                [
                    'title' => $r['title'],
                    'description' => $r['description'] ?? null,
                    'is_active' => true,
                ]
            );

            foreach ($skillList as $priority => $skillName) {
                JobRequiredSkill::firstOrCreate(
                    [
                        'job_role_id' => $role->id,
                        'skill_name' => trim($skillName),
                    ],
                    ['priority' => $priority + 1]
                );
            }
        }
    }
}
