<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorTeamController extends Controller
{
    private const INITIAL = [
        ['id' => 'team-1', 'name' => 'Team 1', 'members' => ['Aya', 'Malek', 'Nour'], 'leader' => 'Aya', 'progress' => 78, 'tone' => 'success'],
        ['id' => 'team-2', 'name' => 'Team 2', 'members' => ['Omar', 'Salma'], 'leader' => 'Omar', 'progress' => 61, 'tone' => 'warning'],
        ['id' => 'team-3', 'name' => 'Team 3', 'members' => ['Hana', 'Youssef', 'Layla'], 'leader' => 'Hana', 'progress' => 35, 'tone' => 'danger'],
    ];

    private function data(Request $request): array
    {
        return $request->session()->get('professor_teams', ['teams' => self::INITIAL, 'unassigned' => ['Karim', 'Dina'], 'leave_requests' => [['id' => 1, 'student' => 'Omar Said', 'team' => 'Team 2', 'reason' => 'My schedule no longer overlaps with the team’s working hours.']]]);
    }

    public function index(Request $request): View
    {
        return view('professor.teams', $this->data($request));
    }

    public function autoPlace(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $remaining = [];
        foreach ($data['unassigned'] as $name) {
            $index = collect($data['teams'])->search(fn ($team) => count($team['members']) < 4);
            if ($index === false) { $remaining[] = $name; continue; }
            $data['teams'][$index]['members'][] = $name;
        }
        $data['unassigned'] = $remaining;
        $request->session()->put('professor_teams', $data);
        return back()->with('status', 'Unassigned students were placed in teams with available space.');
    }

    public function addMember(string $team, Request $request): RedirectResponse
    {
        $request->validate(['student' => ['required', 'string', 'max:80']]);
        $data = $this->data($request);
        $index = collect($data['teams'])->search(fn ($record) => $record['id'] === $team);
        abort_if($index === false, 404);
        $student = trim($request->input('student'));
        if (count($data['teams'][$index]['members']) >= 4 || !in_array($student, $data['unassigned'], true)) return back()->withErrors(['team' => 'Select an unassigned student and a team with an available seat.']);
        $data['teams'][$index]['members'][] = $student;
        $data['unassigned'] = array_values(array_diff($data['unassigned'], [$student]));
        $request->session()->put('professor_teams', $data);
        return back()->with('status', $student.' was added to '.$data['teams'][$index]['name'].'.');
    }

    public function addSelectedMember(Request $request): RedirectResponse
    {
        $request->validate(['team_id' => ['required', 'string'], 'student' => ['required', 'string', 'max:80']]);
        return $this->addMember($request->input('team_id'), $request);
    }

    public function removeMember(string $team, string $member, Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $index = collect($data['teams'])->search(fn ($record) => $record['id'] === $team);
        abort_if($index === false || !in_array($member, $data['teams'][$index]['members'], true), 404);
        if (($data['teams'][$index]['leader'] ?? null) === $member) return back()->withErrors(['team' => 'Change the team leader before removing this member.']);
        $data['teams'][$index]['members'] = array_values(array_diff($data['teams'][$index]['members'], [$member]));
        $data['unassigned'][] = $member;
        $request->session()->put('professor_teams', $data);
        return back()->with('status', $member.' was removed from the team and returned to unassigned students.');
    }

    public function changeLeader(string $team, Request $request): RedirectResponse
    {
        $request->validate(['leader' => ['required', 'string', 'max:80']]);
        $data = $this->data($request);
        abort_unless(collect($data['teams'])->contains(fn ($record) => $record['id'] === $team && in_array($request->input('leader'), $record['members'], true)), 422);
        foreach ($data['teams'] as &$record) {
            if ($record['id'] === $team) $record['leader'] = $request->input('leader');
        }
        unset($record);
        $request->session()->put('professor_teams', $data);
        return back()->with('status', 'Team leader updated.');
    }

    public function resolveLeaveRequest(int $requestId, Request $request): RedirectResponse
    {
        $request->validate(['decision' => ['required', 'in,approve,reject']]);
        $data = $this->data($request);
        $leave = collect($data['leave_requests'])->firstWhere('id', $requestId);
        abort_if($leave === null, 404);
        if ($request->input('decision') === 'approve') {
            $teamIndex = collect($data['teams'])->search(fn ($record) => $record['name'] === $leave['team']);
            if ($teamIndex !== false && in_array($leave['student'], $data['teams'][$teamIndex]['members'], true)) {
                $data['teams'][$teamIndex]['members'] = array_values(array_diff($data['teams'][$teamIndex]['members'], [$leave['student']]));
                $data['unassigned'][] = $leave['student'];
            }
        }
        $data['leave_requests'] = array_values(array_filter($data['leave_requests'], fn ($record) => $record['id'] !== $requestId));
        $request->session()->put('professor_teams', $data);
        return back()->with('status', 'Leave request '.$request->input('decision').'ed.');
    }
}
