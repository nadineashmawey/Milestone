<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfessorRiskRadarController extends Controller
{
    public function index(): View
    {
        // Sample response contract. Replace with an authorized ML prediction service later.
        $teams = [
            ['id' => '1', 'name' => 'Team 1', 'risk' => 'low', 'progress' => 72, 'activity' => 78, 'openTasks' => 4, 'inactiveDays' => 0, 'delayDays' => 0, 'action' => 'Keep the current plan and check progress at the next scheduled review.', 'members' => [['Aya', 78], ['Malek', 74], ['Nour', 70]]],
            ['id' => '2', 'name' => 'Team 2', 'risk' => 'medium', 'progress' => 52, 'activity' => 52, 'openTasks' => 6, 'inactiveDays' => 2, 'delayDays' => 1, 'action' => 'Check the remaining deliverables and help the team resolve blockers before the milestone.', 'members' => [['Omar', 68], ['Salma', 55], ['Karim', 46]]],
            ['id' => '3', 'name' => 'Team 3', 'risk' => 'high', 'progress' => 35, 'activity' => 30, 'openTasks' => 8, 'inactiveDays' => 4, 'delayDays' => 3, 'action' => 'Confirm owners for the remaining M2 tasks and schedule a short team check-in.', 'members' => [['Hana', 72], ['Youssef', 48], ['Layla', 31]]],
            ['id' => '4', 'name' => 'Team 4', 'risk' => 'medium', 'progress' => 68, 'activity' => 38, 'openTasks' => 3, 'inactiveDays' => 2, 'delayDays' => 2, 'action' => 'Check whether recent work is recorded and redistribute any blocked tasks.', 'members' => [['Mariam', 65], ['Adam', 43], ['Lina', 50]]],
            ['id' => '5', 'name' => 'Team 5', 'risk' => 'low', 'progress' => 82, 'activity' => 66, 'openTasks' => 3, 'inactiveDays' => 1, 'delayDays' => 0, 'action' => 'Review the final deliverables and maintain the current pace.', 'members' => [['Farah', 81], ['Ali', 76], ['Ziad', 72]]],
        ];
        $context = ['project' => 'Software Engineering Capstone', 'milestone' => 'M2 · Design Document', 'deadline' => '2026-10-18', 'source' => 'sample', 'modelVersion' => null, 'generatedAt' => null];

        return view('risk-radar.index', compact('teams', 'context'));
    }
}
