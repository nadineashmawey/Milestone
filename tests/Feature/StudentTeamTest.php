<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Tests\TestCase;

class StudentTeamTest extends TestCase
{
    private const ACADEMIC = '/student/projects/software-engineering-capstone/team';

    private const INDEPENDENT = '/student/projects/campus-navigation-app/team';

    private function withContext(array $context): static
    {
        return $this->withSession([ZinaDemoData::SESSION_KEY => ['context' => $context]]);
    }

    private function forming(): static
    {
        return $this->withContext(['phase' => 'forming']);
    }

    public function test_the_locked_team_shows_capacity_leader_and_the_lock_banner(): void
    {
        $this->get(self::ACADEMIC)
            ->assertOk()
            ->assertSee('Team 1')
            ->assertSee('3 of 4 members')
            ->assertSee('Teams are locked.')
            ->assertSee('cancelled automatically')
            ->assertSee('Assigned by Professor Lee')
            ->assertSee('Cancelled')
            ->assertSee('Academic');
    }

    public function test_requests_are_refused_once_teams_are_locked(): void
    {
        $this->post(self::ACADEMIC.'/requests/in-dina/accept', ['confirmed' => 1])->assertForbidden();
        $this->post(self::ACADEMIC.'/outgoing', ['student' => 'Tarek Hosny'])->assertForbidden();
    }

    public function test_before_the_deadline_requests_can_be_answered(): void
    {
        $this->forming()
            ->get(self::ACADEMIC)
            ->assertOk()
            ->assertSee('Team formation is open until')
            ->assertSee('2 days left')
            ->assertSee('Not assigned yet')
            ->assertSee('Withdraw')
            ->assertSee('Send request');
    }

    public function test_accepting_a_request_that_fills_the_team_needs_confirmation(): void
    {
        $this->forming()->from(self::ACADEMIC)
            ->post(self::ACADEMIC.'/requests/in-dina/accept')
            ->assertSessionHasErrors('request');

        $this->forming()
            ->post(self::ACADEMIC.'/requests/in-dina/accept', ['confirmed' => 1])
            ->assertRedirect(self::ACADEMIC)
            ->assertSessionHas('team_status', 'Dina Nasser joined Team 1.');
    }

    public function test_a_full_team_refuses_more_members(): void
    {
        $this->forming()->post(self::ACADEMIC.'/requests/in-dina/accept', ['confirmed' => 1]);

        $this->get(self::ACADEMIC)->assertSee('4 of 4 members')->assertSee('Team 1 is full.');

        $this->from(self::ACADEMIC)
            ->post(self::ACADEMIC.'/requests/in-karim/accept', ['confirmed' => 1])
            ->assertSessionHasErrors(['request' => 'Team 1 is already full (4 of 4). Decline this request instead.']);
    }

    public function test_a_student_can_have_at_most_five_pending_requests(): void
    {
        $this->forming()->post(self::ACADEMIC.'/outgoing', ['student' => 'Tarek Hosny'])->assertSessionHasNoErrors();
        $this->post(self::ACADEMIC.'/outgoing', ['student' => 'Yasmin Fathy'])->assertSessionHasNoErrors();

        $this->from(self::ACADEMIC)
            ->post(self::ACADEMIC.'/outgoing', ['student' => 'Rana Samir'])
            ->assertSessionHasErrors(['student' => 'You already have 5 pending requests, the most allowed. Withdraw one before you send another.']);
    }

    public function test_requests_must_go_to_a_listed_classmate(): void
    {
        $this->forming()->from(self::ACADEMIC)
            ->post(self::ACADEMIC.'/outgoing', ['student' => 'Malek Mansour'])
            ->assertSessionHasErrors('student');
    }

    public function test_a_sent_request_can_be_withdrawn(): void
    {
        $this->forming()
            ->post(self::ACADEMIC.'/outgoing/out-laila/withdraw')
            ->assertSessionHas('team_status', 'Your request to Laila Fawzy was withdrawn.');
    }

    public function test_a_leave_request_needs_a_reason_and_keeps_you_on_the_team(): void
    {
        $this->from(self::ACADEMIC)->post(self::ACADEMIC.'/leave', ['reason' => 'short'])->assertSessionHasErrors('reason');

        $this->post(self::ACADEMIC.'/leave', ['reason' => 'My schedule no longer overlaps with the team.'])->assertRedirect(self::ACADEMIC);

        $this->get(self::ACADEMIC)
            ->assertSee('Leave request pending')
            ->assertSee('You stay on Team 1')
            ->assertSee('My schedule no longer overlaps with the team.');

        $this->post(self::ACADEMIC.'/leave', ['reason' => 'Asking a second time, just in case.'])->assertForbidden();
    }

    public function test_the_leave_dialog_works_without_javascript(): void
    {
        $this->get(self::ACADEMIC.'?confirm=leave-dialog')
            ->assertOk()
            ->assertSee('data-as-dialog-static', false)
            ->assertSee('Professor Lee gets your request and decides.');
    }

    public function test_the_independent_team_shows_members_code_invitations_and_requests(): void
    {
        $this->get(self::INDEPENDENT)
            ->assertOk()
            ->assertSee('Campus Navigation App')
            ->assertSee('4 of 10 members')
            ->assertSee('CNA-7Q4K')
            ->assertSee('Karim Saleh')
            ->assertSeeInOrder(['Cancelled', 'Expired', 'Declined', 'Accepted', 'Pending'])
            ->assertSee('Independent');
    }

    public function test_the_leader_can_invite_and_invitations_are_validated(): void
    {
        $this->from(self::INDEPENDENT)->post(self::INDEPENDENT.'/invitations', ['identifier' => 'not an email!'])->assertSessionHasErrors('identifier');
        $this->from(self::INDEPENDENT)->post(self::INDEPENDENT.'/invitations', ['identifier' => 'malek.mansour@university.edu'])
            ->assertSessionHasErrors(['identifier' => 'That person is already a member.']);
        $this->from(self::INDEPENDENT)->post(self::INDEPENDENT.'/invitations', ['identifier' => 'hana.youssef@university.edu'])
            ->assertSessionHasErrors(['identifier' => 'You already invited them. Their invitation is still pending.']);

        $this->post(self::INDEPENDENT.'/invitations', ['identifier' => 'rana.samir'])
            ->assertSessionHas('team_status', 'Invitation sent to rana.samir. It expires in 7 days.');
    }

    public function test_the_project_stops_at_ten_members(): void
    {
        foreach (['a.one', 'b.two', 'c.three', 'd.four', 'e.five'] as $username) {
            $this->post(self::INDEPENDENT.'/invitations', ['identifier' => $username])->assertSessionHasNoErrors();
        }

        $this->from(self::INDEPENDENT)
            ->post(self::INDEPENDENT.'/invitations', ['identifier' => 'f.six'])
            ->assertSessionHasErrors(['identifier' => 'The project is at its limit of 10 members, counting pending invitations.']);
    }

    public function test_join_requests_can_be_approved_and_invitations_cancelled(): void
    {
        $this->post(self::INDEPENDENT.'/join-requests/karim/approve')->assertSessionHas('team_status', 'Karim Saleh joined the project.');
        $this->get(self::INDEPENDENT)->assertSee('5 of 10 members');

        $this->post(self::INDEPENDENT.'/invitations/inv-1/cancel')->assertSessionHasNoErrors();
        $this->from(self::INDEPENDENT)->post(self::INDEPENDENT.'/invitations/inv-4/cancel')->assertSessionHasErrors('invitation');
    }

    public function test_the_leader_cannot_be_removed(): void
    {
        $this->from(self::INDEPENDENT)->post(self::INDEPENDENT.'/members/aya/remove')->assertSessionHasErrors('member');
        $this->post(self::INDEPENDENT.'/members/salma/remove')->assertSessionHas('team_status');
    }

    public function test_members_see_a_restriction_and_the_server_refuses_leader_actions(): void
    {
        $this->withContext(['viewer' => 'malek'])
            ->get(self::INDEPENDENT)
            ->assertOk()
            ->assertSee('Only the project leader, Aya Hassan, can manage members and invitations.')
            ->assertDontSee('Send invitation');

        $this->post(self::INDEPENDENT.'/invitations', ['identifier' => 'rana.samir'])->assertForbidden();
        $this->post(self::INDEPENDENT.'/join-requests/karim/approve')->assertForbidden();
        $this->post(self::INDEPENDENT.'/members/salma/remove')->assertForbidden();
    }

    public function test_each_state_renders(): void
    {
        $this->get(self::ACADEMIC.'?state=empty')->assertOk()->assertSee('You’re not on a team yet.');
        $this->get(self::ACADEMIC.'?state=loading')->assertOk()->assertSee('Loading your team');
        $this->get(self::ACADEMIC.'?state=error')->assertOk()->assertSee('We couldn’t load your team.');
    }

    public function test_unknown_projects_are_not_found(): void
    {
        $this->get('/student/projects/no-such-project/team')->assertNotFound();
    }
}
