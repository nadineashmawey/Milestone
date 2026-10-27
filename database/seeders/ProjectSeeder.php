<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Professor Lee's courses and the three projects the professor pages used to hardcode.
 * Runs with the demo accounts only (config/demo.php), so it never touches production.
 */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $lee = User::where('email', 'professor@demo.milestone.test')->first();
        if (! $lee) {
            return;
        }

        $courses = [
            'CS 490' => ['name' => 'Software Engineering', 'schedule' => 'Monday and Wednesday', 'tone' => 'blue'],
            'CS 342' => ['name' => 'Database Systems', 'schedule' => null, 'tone' => 'coral'],
            'CS 210' => ['name' => 'Web Development', 'schedule' => null, 'tone' => 'yellow'],
        ];
        foreach ($courses as $code => $course) {
            Course::updateOrCreate(['code' => $code], [
                ...$course,
                'professor_id' => $lee->id,
                'semester' => 'Fall 2026',
                'starts_on' => '2026-08-30',
                'ends_on' => '2026-12-24',
            ]);
        }

        $projects = [
            [
                'course' => 'CS 490', 'title' => 'Software Engineering Capstone', 'teams' => 8, 'formation' => '2026-09-11', 'leftover' => 'manual',
                'description' => 'Plan, build and demo a working product in teams, using an iterative software engineering process.',
                'milestones' => [['Proposal', '2026-09-27', ['file'], 20], ['Design Document', '2026-10-18', ['file', 'slides'], 35], ['Final Demo', '2026-11-08', ['link', 'github'], 45]],
            ],
            [
                'course' => 'CS 342', 'title' => 'Database Systems', 'teams' => 6, 'formation' => '2026-09-15', 'leftover' => 'auto',
                'description' => 'Design and implement a normalised database for a real client, from ERD to tuned queries.',
                'milestones' => [['Schema Proposal', '2026-09-30', ['file'], 25], ['Design Document', '2026-10-14', ['file', 'images'], 35], ['Final Report', '2026-11-20', ['file', 'github'], 40]],
            ],
            [
                'course' => 'CS 210', 'title' => 'Web Development Workshop', 'teams' => 5, 'formation' => '2026-09-10', 'leftover' => 'auto',
                'description' => 'Build and ship an accessible, responsive website for a campus organisation.',
                'milestones' => [['Proposal', '2026-09-20', ['file'], 20], ['Prototype', '2026-10-05', ['link', 'images'], 30], ['Demo', '2026-10-22', ['link', 'github'], 50]],
            ],
        ];

        foreach ($projects as $data) {
            $project = Project::updateOrCreate(['title' => $data['title'], 'professor_id' => $lee->id], [
                'course_id' => Course::where('code', $data['course'])->value('id'),
                'type' => 'academic',
                'description' => $data['description'],
                'goals' => ['Apply an iterative software engineering process', 'Document technical decisions and evaluate the final product'],
                'total_grade' => 50,
                'team_min' => 2,
                'team_max' => 4,
                'formation_deadline' => $data['formation'],
                'leftover_mode' => $data['leftover'],
                'status' => 'active',
                'settings' => ['color' => 'Royal blue', 'formation' => 'Students choose', 'late_policy' => 'Allow with a Late flag', 'resubmit' => true, 'reminders' => true, 'ta_permission' => 'Full review'],
                'published_at' => '2026-09-01 09:00:00',
            ]);

            $project->milestones()->delete();
            foreach ($data['milestones'] as $position => [$name, $due, $types, $weight]) {
                $project->milestones()->create(['name' => $name, 'due_at' => $due.' 17:00:00', 'deliverable_types' => $types, 'weight' => $weight, 'points' => (int) round(50 * $weight / 100), 'position' => $position]);
            }

            $project->criteria()->delete();
            foreach ([['Research and rationale', 10], ['Technical quality', 20], ['Collaboration', 10], ['Presentation', 10]] as $position => [$name, $points]) {
                $project->criteria()->create(['name' => $name, 'max_points' => $points, 'position' => $position]);
            }

            $project->teams()->delete();
            foreach (range(1, $data['teams']) as $number) {
                $project->teams()->create(['name' => 'Team '.$number]);
            }
        }
    }
}
