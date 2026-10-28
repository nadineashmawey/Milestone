<?php

namespace App\Http\Controllers;

use App\Support\ZinaDemoData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function show(Request $request, ZinaDemoData $demo): View
    {
        $state = $demo->state($request);
        $dashboard = $demo->studentDashboard(empty: $state === 'empty');

        // A real account with no projects yet starts on the empty state.
        if ($state === 'populated' && $dashboard['projects'] === []) {
            $state = 'empty';
        }

        return view('zina.student-dashboard.show', [
            'state' => $state,
            'dashboard' => $dashboard,
            'independent' => $demo->isIndependent(),
            'joinHref' => $demo->link('student.projects.index'),
            // Only independent accounts can create projects (ProjectController@create); visitors still see the button.
            'createHref' => ! $request->user() || $demo->isIndependent() ? $demo->link('student.projects.create') : null,
        ]);
    }
}
