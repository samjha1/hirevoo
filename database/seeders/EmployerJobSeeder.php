<?php

namespace Database\Seeders;

use App\Models\EmployerJob;
use App\Models\ReferrerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployerJobSeeder extends Seeder
{
    public function run(): void
    {
        $employer = User::firstOrCreate(
            ['email' => 'employer@hirevo.com'],
            [
                'name' => 'Hirevo Demo Employer',
                'password' => Hash::make('password'),
                'role' => 'referrer',
                'status' => 'active',
            ]
        );

        ReferrerProfile::firstOrCreate(
            ['user_id' => $employer->id],
            [
                'company_name' => 'Hirevo Tech',
                'company_email' => 'employer@hirevo.com',
                'is_approved' => true,
                'credits' => 10,
            ]
        );

        $jobs = [
            [
                'title' => 'Flutter Developer',
                'description' => 'We are looking for a Flutter Developer to build cross-platform mobile apps. You will work with Dart, REST APIs, and state management (Provider/Riverpod). Experience with Firebase and CI/CD is a plus.',
                'location' => 'Remote',
                'job_type' => 'full_time',
                'work_location_type' => 'remote',
                'company_name' => 'Hirevo Tech',
            ],
            [
                'title' => 'Senior Flutter Developer',
                'description' => 'Join our mobile team to design and develop Flutter applications. Strong Dart skills, clean architecture, and experience with GetX or Bloc required. You will mentor junior developers.',
                'location' => 'Bangalore',
                'job_type' => 'full_time',
                'work_location_type' => 'hybrid',
                'company_name' => 'Hirevo Tech',
            ],
            [
                'title' => 'Flutter / Mobile Developer',
                'description' => 'Build beautiful mobile apps with Flutter. Knowledge of Dart, REST APIs, and state management. We work on iOS and Android from a single codebase.',
                'location' => 'Mumbai',
                'job_type' => 'full_time',
                'work_location_type' => 'office',
                'company_name' => 'Hirevo Tech',
            ],
            [
                'title' => 'Data Analyst',
                'description' => 'Analyze data and build reports using SQL, Excel, and Python. Create dashboards and drive insights for business decisions. Power BI or similar experience preferred.',
                'location' => 'Remote',
                'job_type' => 'full_time',
                'work_location_type' => 'remote',
                'company_name' => 'Hirevo Tech',
            ],
            [
                'title' => 'Software Engineer',
                'description' => 'Design and develop software applications. Strong in Java or Python, SQL, and problem solving. Experience with REST APIs and Agile teams.',
                'location' => 'Delhi NCR',
                'job_type' => 'full_time',
                'work_location_type' => 'hybrid',
                'company_name' => 'Hirevo Tech',
            ],
            // QA Engineer jobs (for job-goals/11)
            [
                'title' => 'QA Engineer',
                'description' => 'Ensure quality through manual and automation testing. Work with Agile teams, Jira, and improve testing processes. Strong problem solving and communication.',
                'location' => 'Remote',
                'job_type' => 'full_time',
                'work_location_type' => 'remote',
                'company_name' => 'Hirevo Tech',
            ],
            [
                'title' => 'Senior QA Engineer',
                'description' => 'Lead QA efforts for our product. Experience with Jira, Agile, manual testing, and test automation. Communication and leadership skills required.',
                'location' => 'Bangalore',
                'job_type' => 'full_time',
                'work_location_type' => 'hybrid',
                'company_name' => 'Hirevo Tech',
            ],
            // Extra Flutter developer jobs
            [
                'title' => 'Flutter Developer – Mobile Apps',
                'description' => 'Build cross-platform mobile applications with Flutter and Dart. REST API integration, state management (Provider/Bloc), and Firebase experience preferred.',
                'location' => 'Pune',
                'job_type' => 'full_time',
                'work_location_type' => 'office',
                'company_name' => 'Hirevo Tech',
            ],
        ];

        foreach ($jobs as $data) {
            EmployerJob::firstOrCreate(
                [
                    'user_id' => $employer->id,
                    'title' => $data['title'],
                ],
                [
                    'company_name' => $data['company_name'],
                    'description' => $data['description'],
                    'location' => $data['location'] ?? null,
                    'job_type' => $data['job_type'] ?? 'full_time',
                    'work_location_type' => $data['work_location_type'] ?? null,
                    'status' => 'active',
                ]
            );
        }
    }
}
