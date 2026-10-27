<?php

namespace App\Http\Controllers;

use App\Support\CurrentUser;
use App\Support\ProfessorProjects;
use App\Support\ZinaDemoData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorDashboardController extends Controller
{
    public function show(Request $request, ZinaDemoData $demo, ProfessorProjects $projects): View
    {
        $state = $demo->state($request);
        $professor = CurrentUser::get();
        $dashboard = $demo->professorDashboard(
            empty: $state === 'empty',
            live: $professor ? $projects->dashboard($professor) : null,
            name: $professor?->name,
        );

        // A professor with no active projects starts on the empty state.
        if ($state === 'populated' && $dashboard['projects'] === []) {
            $state = 'empty';
        }

        return view('zina.professor-dashboard.show', [
            'state' => $state,
            'dashboard' => $dashboard,
        ]);
    }
}
