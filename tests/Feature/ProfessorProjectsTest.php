<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * My projects (GET /professor/projects) and the project page.
 */
class ProfessorProjectsTest extends TestCase
{
    use RefreshDatabase;

    private User $lee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->lee = User::where('email', 'professor@demo.milestone.test')->first();
    }

    private function otherProfessor(): User
    {
        $other = User::factory()->create(['role' => 'Professor', 'name' => 'Dr. Other']);
        $course = Course::create(['professor_id' => $other->id, 'code' => 'CS 999', 'name' => 'Other Course', 'semester' => 'Fall 2026', 'starts_on' => '2026-08-30', 'ends_on' => '2026-12-24']);
        Project::create(['professor_id' => $other->id, 'course_id' => $course->id, 'title' => 'Someone Else’s Project', 'status' => 'active', 'published_at' => now()]);

        return $other;
    }

    public function test_it_lists_only_the_signed_in_professors_projects(): void
    {
        $this->otherProfessor();

        $this->actingAs($this->lee)->get(route('professor.projects.index'))
            ->assertOk()
            ->assertSee('My projects')
            ->assertSee('Software Engineering Capstone')
            ->assertSee('Database Systems')
            ->assertSee('Web Development Workshop')
            ->assertDontSee('Someone Else’s Project')
            ->assertSee('Showing 3 of 3 projects')
            ->assertSee('8 teams')
            ->assertSee('Academic')
            ->assertSee('Active')
            ->assertSee('Sep 1, 2026');
    }

    public function test_search_filter_and_sort(): void
    {
        Project::create(['professor_id' => $this->lee->id, 'course_id' => Course::where('code', 'CS 490')->value('id'), 'title' => 'Capstone Draft', 'status' => 'draft']);

        $this->actingAs($this->lee)->get(route('professor.projects.index', ['q' => 'capstone']))
            ->assertSee('Software Engineering Capstone')->assertSee('Capstone Draft')->assertDontSee('Web Development Workshop');

        $this->get(route('professor.projects.index', ['status' => 'draft']))
            ->assertSee('Capstone Draft')->assertSee('Edit and publish')->assertDontSee('Database Systems</a>', false);

        $this->get(route('professor.projects.index', ['course' => Course::where('code', 'CS 342')->value('id')]))
            ->assertSee('Database Systems</a>', false)->assertDontSee('Software Engineering Capstone</a>', false);

        // Next deadline: Database Systems (Oct 14), Capstone (Oct 18), Web Development (Oct 22), then the draft with none.
        $this->get(route('professor.projects.index', ['sort' => 'deadline']))
            ->assertSeeInOrder(['<h2><a href="'.route('professor.projects.show', Project::where('title', 'Database Systems')->first()), 'Software Engineering Capstone', 'Web Development Workshop', 'Capstone Draft'], false);

        $this->get(route('professor.projects.index', ['q' => 'nothing like this']))
            ->assertSee('No projects match these filters.')->assertSee('Clear filters');
    }

    public function test_a_professor_with_no_projects_sees_the_empty_state(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'Professor']))->get(route('professor.projects.index'))
            ->assertOk()
            ->assertSee('You haven’t published a project yet.')
            ->assertSee(route('professor.projects.create'));
    }

    public function test_loading_and_error_states_render(): void
    {
        $this->actingAs($this->lee)->get(route('professor.projects.index', ['state' => 'loading']))->assertOk()->assertSee('Loading your projects');
        $this->get(route('professor.projects.index', ['state' => 'error']))->assertOk()->assertSee('We couldn’t load your projects.');
    }

    public function test_the_project_page_shows_milestones_team_rules_and_rubric(): void
    {
        $capstone = Project::where('title', 'Software Engineering Capstone')->first();

        $this->actingAs($this->lee)->get(route('professor.projects.show', $capstone))
            ->assertOk()
            ->assertSee('Software Engineering Capstone')
            ->assertSeeInOrder(['M1', 'Proposal', 'M2', 'Design Document', 'M3', 'Final Demo'])
            ->assertSee('File, Slides')
            ->assertSee('2–4 students')
            ->assertSee('Professor decides')
            ->assertSee('Technical quality')
            ->assertDontSee('Edit and publish');
    }

    public function test_another_professor_gets_403_on_the_project_page(): void
    {
        $other = $this->otherProfessor();
        $capstone = Project::where('title', 'Software Engineering Capstone')->first();

        $this->actingAs($other)->get(route('professor.projects.show', $capstone))->assertForbidden();
    }

    public function test_my_projects_is_in_the_professor_navbar_next_to_create_project(): void
    {
        $html = $this->actingAs($this->lee)->get(route('professor.projects.index'))->getContent();

        $this->assertMatchesRegularExpression('#aria-current="page"[^>]*>.*?<span class="ms-navbar__label">My projects</span>#s', $html);
        $this->assertStringContainsString('<span class="ms-navbar__label">Create project</span>', $html);
    }
}
