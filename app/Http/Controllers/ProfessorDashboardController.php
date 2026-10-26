<?php

namespace App\Http\Controllers;

use App\Support\ZinaDemoData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorDashboardController extends Controller
{
    public function show(Request $request, ZinaDemoData $demo): View
    {
        $state = $demo->state($request);

        return view('zina.professor-dashboard.show', [
            'state' => $state,
            'dashboard' => $demo->professorDashboard(empty: $state === 'empty'),
        ]);
    }
}
