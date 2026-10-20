<?php


namespace App\Support;


/** Local display data for the frontend project screens. */
final class StudentProjectCatalog
{
    public static function all(): array
    {
        return [
            [
                'id' => 'software-engineering-capstone',
                'title' => 'Software Engineering Capstone',
                'kind' => 'Academic',
                'course' => 'CS 490',
                'professor' => 'Professor Lee',
                'team' => 'Team 1',
                'progress' => 62,
                'next' => 'M2 Design · Oct 18',
                'due' => 'M2 Design Document is due in 6 days.',
                'tasks' => [
                    ['title' => 'Research summary', 'description' => 'Document research findings and confirm the proposed API resources.', 'status' => 'To do', 'priority' => 'High', 'owner' => 'Aya Hassan', 'initials' => 'AH', 'due' => '10/12', 'flag' => 'Overdue'],
                    ['title' => 'API contract', 'description' => 'Define the endpoints, payloads, and ownership for the implementation.', 'status' => 'In progress', 'priority' => 'Medium', 'owner' => 'Malek Mansour', 'initials' => 'MM', 'due' => '10/14'],
                    ['title' => 'Wireframes', 'description' => 'Complete the annotated workflow screens for the M2 review.', 'status' => 'Review', 'priority' => 'Medium', 'owner' => 'Nour Saleh', 'initials' => 'NS', 'due' => '10/15', 'flag' => 'Blocked'],
                    ['title' => 'Project brief', 'description' => 'Confirm the agreed project scope and success measures.', 'status' => 'Done', 'priority' => 'Low', 'owner' => 'Aya Hassan', 'initials' => 'AH', 'due' => '10/07'],
                ],
                'members' => 4,
                'open_tasks' => 8,
            ],
            [
                'id' => 'campus-navigation-app',
                'title' => 'Campus Navigation App',
                'kind' => 'Independent',
                'course' => 'Student-led',
                'professor' => null,
                'team' => null,
                'progress' => 38,
                'next' => '8 open tasks · Active today',
                'due' => 'Keep your campus navigation prototype moving.',
                'tasks' => [
                    ['title' => 'Map data review', 'description' => 'Check building and accessibility information for the first campus map.', 'status' => 'To do', 'priority' => 'High', 'owner' => 'Aya Hassan', 'initials' => 'AH', 'due' => '10/19'],
                    ['title' => 'Route prototype', 'description' => 'Build the first walking route flow between campus locations.', 'status' => 'In progress', 'priority' => 'Medium', 'owner' => 'Malek Mansour', 'initials' => 'MM', 'due' => '10/22'],
                ],
                'members' => 4,
                'open_tasks' => 8,
            ],
        ];
    }


    public static function find(string $id): ?array
    {
        foreach (self::all() as $project) {
            if ($project['id'] === $id) {
                return $project;
            }
        }


        return null;
    }
}
