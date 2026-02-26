<?php

namespace Database\Seeders;

use App\Models\UpskillOpportunity;
use Illuminate\Database\Seeder;

class UpskillOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = [
            [
                'title' => 'SDE 1 (Software Development Engineer)',
                'company_name' => 'Google',
                'description' => 'Google is hiring for SDE 1 roles with higher package and growth. Upskill in Data Structures, Algorithms, and system design to increase your chances.',
                'skills' => ['Data Structures', 'Algorithms', 'System Design', 'C++ / Java', 'Problem Solving'],
                'cta_type' => 'pricing',
                'cta_label' => 'Get Referral – Increase selection chance',
                'sort_order' => 10,
            ],
            [
                'title' => 'Senior Software Engineer',
                'company_name' => 'Microsoft',
                'description' => 'Microsoft hires Senior SDEs with strong system design and leadership. Add cloud (Azure) and distributed systems to your skills to qualify.',
                'skills' => ['System Design', 'Azure', 'Distributed Systems', 'Leadership', 'Cloud Architecture'],
                'cta_type' => 'pricing',
                'cta_label' => 'View Premium & Get Referral',
                'sort_order' => 20,
            ],
            [
                'title' => 'Data Scientist',
                'company_name' => 'Top product companies',
                'description' => 'Data Scientist roles offer 2–3x package growth. We can connect you with learning partners.',
                'skills' => ['Python', 'Machine Learning', 'Statistics', 'SQL', 'Data Visualization'],
                'cta_type' => 'contact',
                'cta_label' => 'Contact for upskilling',
                'sort_order' => 30,
            ],
            [
                'title' => 'Product Manager',
                'company_name' => 'FAANG & startups',
                'description' => 'PM roles need analytics, roadmap, and stakeholder skills. If you are in tech and want to move to product, upskill and get referred.',
                'skills' => ['Analytics', 'Roadmap', 'Stakeholder Management', 'Agile', 'Product Strategy'],
                'cta_type' => 'pricing',
                'cta_label' => 'Get Referral',
                'sort_order' => 40,
            ],
        ];

        foreach ($opportunities as $i => $row) {
            UpskillOpportunity::updateOrCreate(
                ['title' => $row['title'], 'company_name' => $row['company_name']],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}
