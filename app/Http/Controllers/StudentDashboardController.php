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

        return view('zina.student-dashboard.show', [
            'state' => $state,
            'dashboard' => $demo->studentDashboard(empty: $state === 'empty'),
            'context' => $demo->context(),
            'joinHref' => $demo->link('projects.index'),
            'createHref' => $demo->link('projects.create'),
        ]);
    }
}
