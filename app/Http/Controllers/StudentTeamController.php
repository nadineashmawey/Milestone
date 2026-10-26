<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamActionRequest;
use App\Support\ZinaDemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentTeamController extends Controller
{
    public function show(Request $request, ZinaDemoData $demo, string $project): View
    {
        $team = $demo->team($project);
        abort_if($team === null, 404);

        $state = $demo->state($request);
        $refusals = collect(['accept', 'send', 'leave', 'withdraw-leave', 'invite', 'approve-join', 'remove-member'])
            ->mapWithKeys(fn ($action) => [$action => $demo->teamRefusal($project, $action)])
            ->all();

        return view('zina.team.show', [
            'state' => $state,
            'team' => $team,
            'refusals' => $refusals,
            'context' => $demo->context(),
            'confirm' => (string) $request->query('confirm', ''),
        ]);
    }

    /**
     * Every team action posts here. TeamActionRequest has already checked permission and validated the input.
     */
    public function act(TeamActionRequest $request, ZinaDemoData $demo, string $project, ?string $item = null): RedirectResponse
    {
        $message = $demo->applyTeamAction($project, $request->action(), $item, $request->validated());

        // TODO: notify the people involved (team, professor) once notifications exist. Email stays simulated.
        return redirect()->route('student.team.show', $project)->with('team_status', $message);
    }
}
