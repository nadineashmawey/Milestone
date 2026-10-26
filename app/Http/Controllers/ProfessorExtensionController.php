<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfessorExtensionController extends Controller
{
    public function index(): View
    {
        // Illustrative records; replace with course-scoped database data.
        $milestones = [
            ['id' => 'm1', 'name' => 'M1 · Proposal', 'short' => 'M1', 'date' => '2026-10-11', 'time' => '17:00', 'weight' => 20, 'submissions' => 6],
            ['id' => 'm2', 'name' => 'M2 · Design Document', 'short' => 'M2', 'date' => '2026-10-18', 'time' => '17:00', 'weight' => 35, 'submissions' => 3],
            ['id' => 'm3', 'name' => 'M3 · Final Demo', 'short' => 'M3', 'date' => '2026-11-08', 'time' => '17:00', 'weight' => 45, 'submissions' => 0],
        ];
        $teams = [
            ['id' => 'team-1', 'name' => 'Team 1', 'members' => [['name' => 'Aya', 'score' => 78], ['name' => 'Malek', 'score' => 74], ['name' => 'Nour', 'score' => 70]], 'leader' => 'Aya', 'progress' => 72, 'risk' => 'low', 'extensions' => 1, 'submitted' => false],
            ['id' => 'team-2', 'name' => 'Team 2', 'members' => [['name' => 'Omar', 'score' => 68], ['name' => 'Salma', 'score' => 55], ['name' => 'Karim', 'score' => 46]], 'leader' => 'Omar', 'progress' => 52, 'risk' => 'medium', 'extensions' => 0, 'submitted' => true],
            ['id' => 'team-3', 'name' => 'Team 3', 'members' => [['name' => 'Aya', 'score' => 72], ['name' => 'Malek', 'score' => 48], ['name' => 'Nour', 'score' => 31]], 'leader' => 'Aya', 'progress' => 35, 'risk' => 'high', 'extensions' => 1, 'submitted' => false],
            ['id' => 'team-4', 'name' => 'Team 4', 'members' => [['name' => 'Mariam', 'score' => 65], ['name' => 'Adam', 'score' => 43], ['name' => 'Lina', 'score' => 50]], 'leader' => 'Mariam', 'progress' => 68, 'risk' => 'medium', 'extensions' => 0, 'submitted' => false],
            ['id' => 'team-5', 'name' => 'Team 5', 'members' => [['name' => 'Farah', 'score' => 81], ['name' => 'Ali', 'score' => 76], ['name' => 'Ziad', 'score' => 72]], 'leader' => 'Farah', 'progress' => 82, 'risk' => 'low', 'extensions' => 1, 'submitted' => false],
            ['id' => 'team-6', 'name' => 'Team 6', 'members' => [['name' => 'Noor', 'score' => 67], ['name' => 'Omar', 'score' => 61], ['name' => 'Dina', 'score' => 56]], 'leader' => 'Noor', 'progress' => 63, 'risk' => 'medium', 'extensions' => 0, 'submitted' => false],
            ['id' => 'team-7', 'name' => 'Team 7', 'members' => [['name' => 'Yara', 'score' => 76], ['name' => 'Tarek', 'score' => 71], ['name' => 'Laila', 'score' => 68]], 'leader' => 'Yara', 'progress' => 75, 'risk' => 'low', 'extensions' => 0, 'submitted' => false],
            ['id' => 'team-8', 'name' => 'Team 8', 'members' => [['name' => 'Hana', 'score' => 64], ['name' => 'Mostafa', 'score' => 58], ['name' => 'Rana', 'score' => 52]], 'leader' => 'Hana', 'progress' => 54, 'risk' => 'medium', 'extensions' => 0, 'submitted' => false],
        ];
        $requests = [
            ['id' => 'request-3', 'team' => 'Team 3', 'milestone' => 'M2', 'date' => 'Oct 14', 'note' => 'Two members were ill during the design sprint.', 'status' => 'Pending', 'tone' => 'warning', 'sender' => 'hana.youssef@university.edu', 'subject' => 'Team 3 request for M2 extension'],
            ['id' => 'request-1', 'team' => 'Team 1', 'milestone' => 'M2', 'date' => 'Oct 9', 'note' => 'Accessibility accommodation confirmed by email.', 'status' => 'Approved', 'tone' => 'success', 'sender' => 'aya.hassan@university.edu', 'subject' => 'Team 1 accessibility request'],
            ['id' => 'request-5', 'team' => 'Team 5', 'milestone' => 'M1', 'date' => 'Oct 7', 'note' => 'Requested more time without supporting evidence.', 'status' => 'Declined', 'tone' => 'danger', 'sender' => 'farah@university.edu', 'subject' => 'Team 5 extension request'],
        ];
        $history = [
            ['id' => 'extension-1', 'team' => 'Team 1', 'teamId' => 'team-1', 'milestone' => 'M2', 'milestoneId' => 'm2', 'originalDate' => '2026-10-18', 'newDate' => '2026-10-21', 'reasonType' => 'Accommodation', 'reason' => 'Accessibility accommodation confirmed by university email.', 'approvedBy' => 'Professor Lee', 'recorded' => 'Oct 9', 'status' => 'Active', 'tone' => 'review', 'sender' => 'aya.hassan@university.edu', 'received' => 'Oct 9'],
            ['id' => 'extension-4', 'team' => 'Team 4', 'teamId' => 'team-4', 'milestone' => 'M1', 'milestoneId' => 'm1', 'originalDate' => '2026-10-11', 'newDate' => '2026-10-13', 'reasonType' => 'Technical problem', 'reason' => 'A documented platform outage prevented submission.', 'approvedBy' => 'Professor Lee', 'recorded' => 'Oct 8', 'status' => 'Expired', 'tone' => 'neutral', 'sender' => 'mariam@university.edu', 'received' => 'Oct 8'],
        ];
        $context = ['project' => 'Software Engineering Capstone', 'sampleAsOf' => '2026-10-15', 'professor' => 'Professor Lee', 'ta' => 'Sara Ahmed', 'taEmail' => 'sara.ahmed@university.edu', 'teamCount' => 8, 'studentCount' => 24, 'activeExtensionCount' => 4];

        return view('extensions.index', compact('milestones', 'teams', 'requests', 'history', 'context'));
    }
}
