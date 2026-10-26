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
