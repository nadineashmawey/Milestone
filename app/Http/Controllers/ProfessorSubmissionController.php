<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfessorSubmissionController extends Controller
{
    public function index(): View
    {
        // Prototype data. Replace with authorized course submissions during backend integration.
        $submissions = [
            ['id' => 'team-3', 'team' => 'Team 3', 'milestone' => 'M2 · Design Document', 'document' => 'System Design Document', 'status' => 'Submitted', 'tone' => 'info', 'extended' => true, 'updated' => 'Submitted 42 min ago', 'versions' => [1, 2, 3], 'scores' => [8, 9, 15, 8], 'feedback' => 'Your architecture is well considered. Before approval, add cardinality labels to the ERD and assign an owner to each testing phase.', 'taFeedback' => 'Strong architecture. Add cardinality labels to the ERD and identify testing owners.', 'members' => [['Hana', 8, 5, 14, 82], ['Youssef', 6, 3, 9, 61], ['Layla', 4, 6, 5, 48]], 'activity' => [['10:42 AM', 'Hana submitted version 3'], ['9:18 AM', 'Layla replaced the presentation'], ['Yesterday', 'Youssef linked commit 8d2f4a1']]],
            ['id' => 'team-1', 'team' => 'Team 1', 'milestone' => 'M2 · Design Document', 'document' => 'System Design Document', 'status' => 'TA reviewed', 'tone' => 'review', 'extended' => false, 'updated' => 'Updated yesterday', 'versions' => [1, 2], 'scores' => [7, 12, 12, 7], 'feedback' => '', 'taFeedback' => 'Clear design rationale. Add evidence for the performance requirements.', 'members' => [['Aya', 7, 4, 10, 70], ['Malek', 6, 3, 8, 60], ['Nour', 5, 4, 7, 55]], 'activity' => [['Yesterday', 'Aya submitted version 2'], ['Yesterday', 'Sara Ahmed reviewed the submission']]],
            ['id' => 'team-2', 'team' => 'Team 2', 'milestone' => 'M1 · Proposal', 'document' => 'Project Proposal', 'status' => 'Late', 'tone' => 'danger', 'extended' => false, 'updated' => 'Late · Oct 10', 'versions' => [1], 'scores' => [0, 0, 0, 0], 'feedback' => '', 'taFeedback' => null, 'members' => [['Omar', 3, 2, 4, 40], ['Salma', 4, 2, 6, 50]], 'activity' => [['Oct 10', 'Omar submitted version 1 after the deadline']]],
        ];

        return view('submissions.index', compact('submissions'));
    }
}

