<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorCourseController extends Controller
{
    public function index(Request $request): View
    {
        // Fictional data for the phase 1 prototype.
        $courses = [
            'cs490' => [
                'code' => 'CS 490',
                'name' => 'Software Engineering',
                'tone' => 'blue',
                'studentCount' => 24,
                'taCount' => 1,
                'unassignedCount' => 2,
                'schedule' => 'Monday and Wednesday',
                'projects' => [
                    [
                        'title' => 'Software Engineering Capstone',
                        'teams' => 8,
                        'milestones' => 3,
                        'totalGrade' => 50,
                    ],
                ],
                'students' => [
                    ['name' => 'Aya Hassan', 'team' => 'Team 1'],
                    ['name' => 'Malek Mansour', 'team' => 'Team 2'],
                    ['name' => 'Nour Saleh', 'team' => 'Team 3'],
                    ['name' => 'Karim Saleh', 'team' => null],
                ],
            ],

            'cs342' => [
                'code' => 'CS 342',
                'name' => 'Database Systems',
                'tone' => 'coral',
                'studentCount' => 28,
                'taCount' => 0,
                'unassignedCount' => 28,
                'schedule' => 'Schedule not assigned',
                'projects' => [],
                'students' => [],
            ],

            'cs210' => [
                'code' => 'CS 210',
                'name' => 'Web Development',
                'tone' => 'yellow',
                'studentCount' => 31,
                'taCount' => 0,
                'unassignedCount' => 31,
                'schedule' => 'Schedule not assigned',
                'projects' => [],
                'students' => [],
            ],
        ];

        $selectedId = $request->query('course', 'cs490');

        if (! is_string($selectedId) || ! isset($courses[$selectedId])) {
            $selectedId = 'cs490';
        }

        return view('courses.index', [
            'courses' => $courses,
            'selectedId' => $selectedId,
            'selectedCourse' => $courses[$selectedId],
        ]);
    }
}