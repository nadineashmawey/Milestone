<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Tests\TestCase;

class ProfessorDashboardTest extends TestCase
{
    public function test_it_greets_the_professor_and_offers_create_project(): void
    {
        $this->get('/professor/dashboard')
            ->assertOk()
            ->assertSee('Good morning, Professor Lee')
            ->assertSee('Tuesday, October 13')
            ->assertSee('Create project');
    }

    public function test_it_shows_the_four_summary_figures(): void
    {
        $this->get('/professor/dashboard')
            ->assertSeeInOrder(['Active projects', '3', 'Teams', '19', 'Waiting for review', '7', 'At-risk projects', '2']);
    }

    public function test_needs_attention_is_prioritised_and_names_the_page_owner_until_it_exists(): void
    {
        $response = $this->get('/professor/dashboard')
            ->assertSeeInOrder(['Due soon', 'Waiting on you', 'Watch', 'FYI'])
            ->assertSee('Team 3 submitted M2 Design Document');

        // Each item links to its owner's page once that route exists, and says whose page it is until then.
        foreach (['professor.submissions.index' => 'Submissions is Nadine’s page', 'notifications' => 'Notifications is Raghad’s page'] as $route => $note) {
            \Illuminate\Support\Facades\Route::has($route)
                ? $response->assertSee('href="'.route($route).'"', false)->assertDontSee($note)
                : $response->assertSee($note.', coming soon');
        }
    }

    public function test_project_cards_label_risk_as_simulated_and_show_the_type(): void
    {
        $this->get('/professor/dashboard')
            ->assertSee('Low risk · Simulated')
            ->assertSee('High risk · Simulated')
            ->assertSee('1 team extended')
            ->assertSee('Academic')
            ->assertSee('Overall progress');
    }

    public function test_deadlines_and_activity_are_shown_separately_from_notifications(): void
    {
        $this->get('/professor/dashboard')
            ->assertSee('Upcoming deadlines')
            ->assertSeeInOrder(['Database Systems', 'Software Engineering Capstone', 'Web Development Workshop'])
            ->assertSee('Recent activity')
            ->assertSee('Alerts meant for you are in');
    }

    public function test_what_students_did_this_session_reaches_the_professor(): void
    {
        $this->withSession([ZinaDemoData::SESSION_KEY => ['team' => ['software-engineering-capstone' => ['leave' => [
            'reason' => 'My schedule no longer overlaps with the team.',
            'sent_at' => now(),
            'by' => 'Aya Hassan',
        ]]]]])
            ->get('/professor/dashboard')
            ->assertSee('Aya Hassan asked to leave Team 1');
    }

    public function test_each_state_renders(): void
    {
        $this->get('/professor/dashboard?state=empty')->assertOk()->assertSee('Create your first project.')->assertDontSee('Needs attention');
        $this->get('/professor/dashboard?state=loading')->assertOk()->assertSee('Loading your dashboard');
        $this->get('/professor/dashboard?state=error')->assertOk()->assertSee('We couldn’t load your dashboard.')->assertSee('Try again');
    }

    public function test_the_professor_navigation_is_shown(): void
    {
        $this->get('/professor/dashboard')
            ->assertSee('Professor navigation')
            ->assertSee('Risk Radar')
            ->assertSee('aria-current="page"', false);
    }
}
