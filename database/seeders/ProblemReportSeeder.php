<?php

namespace Database\Seeders;

use App\Models\ProblemReport;
use Illuminate\Database\Seeder;

class ProblemReportSeeder extends Seeder
{
    /**
     * Three fictional reports for demos of the Help center backend.
     */
    public function run(): void
    {
        $reports = [
            [
                'reference' => 'MS-1042',
                'name' => 'Aya Hassan',
                'email' => 'aya.hassan@university.edu',
                'category' => 'submission_upload',
                'description' => 'The M2 design document upload stops at 90% every time I try to submit it before the deadline.',
                'status' => 'open',
            ],
            [
                'reference' => 'MS-2317',
                'name' => 'Omar Khaled',
                'email' => 'omar.khaled@university.edu',
                'category' => 'team_membership',
                'description' => 'I joined Team 2 before the deadline but the team page still lists me as unassigned.',
                'status' => 'in_progress',
            ],
            [
                'reference' => 'MS-3580',
                'name' => 'Salma Nabil',
                'email' => 'salma.nabil@university.edu',
                'category' => 'cant_sign_in',
                'description' => 'The password reset email never arrives at my university address, even after waiting an hour.',
                'status' => 'resolved',
            ],
        ];

        foreach ($reports as $report) {
            ProblemReport::updateOrCreate(['reference' => $report['reference']], $report);
        }
    }
}
