<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    public function test_it_greets_the_student_with_the_date_and_a_summary(): void
    {
        $this->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Tuesday, October 13')
            ->assertSee('Good morning, Aya')
            ->assertSee('4 tasks to focus on, 1 deadline this week.');
    }

    public function test_it_lists_what_needs_the_students_action(): void
    {
        $this->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Needs your action')
            ->assertSee('M2 Design Document needs a resubmission')
            ->assertSee('Research summary is overdue')
            ->assertSee('Karim Saleh asked to join Campus Navigation App');
    }

    public function test_every_project_shows_its_type_and_the_tasks_show_their_flags(): void
    {
        $response = $this->get('/student/dashboard')->assertOk();

        $response->assertSeeInOrder(['Software Engineering Capstone', 'Academic'], false)
            ->assertSee('Independent')
            ->assertSee('Overdue')
            ->assertSee('Blocked')
            ->assertSee('No grade')
            ->assertSee('47 / 50');
    }

    public function test_deadlines_are_listed_nearest_first_with_a_countdown(): void
    {
        $this->get('/student/dashboard')
            ->assertSeeInOrder(['M2 · Design Document', 'Prototype checkpoint', 'M3 · Final Demo'])
            ->assertSee('5 days left');
    }

    public function test_the_empty_state_offers_ways_to_join_or_create_a_project(): void
    {
        $this->get('/student/dashboard?state=empty')
            ->assertOk()
            ->assertSee('You’re not in any projects yet.')
            ->assertSee('Join with a code')
            ->assertSee('Create an independent project')
            ->assertDontSee('Needs your action');
    }

    public function test_the_loading_state_shows_a_skeleton(): void
    {
        $this->get('/student/dashboard?state=loading')
            ->assertOk()
            ->assertSee('Loading your dashboard')
            ->assertSee('as-skeleton', false)
            ->assertDontSee('My upcoming tasks');
    }

    public function test_the_error_state_shows_a_plain_message_and_retry(): void
    {
        $this->get('/student/dashboard?state=error')
            ->assertOk()
            ->assertSee('We couldn’t load your dashboard.')
            ->assertSee('Try again')
            ->assertSee('role="alert"', false);
    }

    public function test_unknown_states_fall_back_to_the_populated_page(): void
    {
        $this->get('/student/dashboard?state=bogus')->assertOk()->assertSee('My upcoming tasks');
    }

    public function test_a_team_member_sees_their_own_tasks_and_no_leader_items(): void
    {
        $this->withSession([ZinaDemoData::SESSION_KEY => ['context' => ['viewer' => 'malek']]])
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Good morning, Malek')
            ->assertSee('API contract')
            ->assertSee('See the feedback')
            ->assertDontSee('Karim Saleh asked to join');
    }

    public function test_links_to_teammates_pages_fall_back_safely_until_they_exist(): void
    {
        $this->assertSame('#', \App\Support\AppShellNavigation::href('a.route.nobody.has'));

        // Jana's task pages: linked once her routes are registered, "#" before that.
        $expected = \Illuminate\Support\Facades\Route::has('student.tasks.show')
            ? route('student.tasks.show', 'design-document')
            : '#';
        $this->get('/student/dashboard')->assertOk()->assertSee('href="'.$expected.'"', false);
    }
}
