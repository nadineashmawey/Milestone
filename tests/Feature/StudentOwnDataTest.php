<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jana's student pages, Milestones and Team: a real account sees only its own data.
 * The demo accounts (config/demo.php) keep the sample projects, tasks and team.
 */
class StudentOwnDataTest extends TestCase
{
    use RefreshDatabase;

    private function account(array $attributes = []): User
    {
        return User::factory()->create([
            'name' => 'Zina Shady', 'email' => 'zina.shady@gmail.com', 'role' => 'Student', 'project_type' => 'independent', 'status' => 'active', ...$attributes,
        ]);
    }

    public function test_creating_an_independent_project_saves_it_for_good(): void
    {
        $user = $this->account();

        $response = $this->actingAs($user)->post(route('student.projects.store'), ['title' => 'Recipe Planner', 'description' => 'Plan meals for the week.']);

        $project = Project::where('title', 'Recipe Planner')->first();
        $this->assertNotNull($project);
        $this->assertSame('independent', $project->type);
        $this->assertSame($user->id, $project->owner_id);
        $this->assertNull($project->professor_id);
        $response->assertRedirect(route('student.projects.show', $project->id));

        // Still there after signing out and back in (it used to live in the session only).
        $this->post(route('logout'));
        $this->actingAs($user->fresh())->get(route('student.projects.index'))->assertSee('Recipe Planner');
    }

    public function test_my_projects_shows_only_the_accounts_own_projects(): void
    {
        $user = $this->account();
        Project::create(['type' => 'independent', 'owner_id' => $user->id, 'title' => 'Recipe Planner', 'status' => 'active']);
        Project::create(['type' => 'independent', 'owner_id' => $this->account(['email' => 'someone@gmail.com'])->id, 'title' => 'Not Mine', 'status' => 'active']);

        $this->actingAs($user)->get(route('student.projects.index'))
            ->assertOk()
            ->assertSee('Recipe Planner')
            ->assertDontSee('Not Mine')
            ->assertDontSee('Software Engineering Capstone')
            ->assertDontSee('Campus Navigation App');
    }

    public function test_a_new_account_gets_empty_states_instead_of_samples(): void
    {
        $this->actingAs($this->account())->get(route('student.projects.index'))
            ->assertSee('No projects yet')->assertSee('Create an independent project');

        $this->get(route('student.tasks.index'))
            ->assertOk()->assertSee('No tasks yet')->assertDontSee('Upload design document');
        $this->get(route('student.tasks.show', 'design-document'))->assertNotFound();
        $this->get(route('student.projects.show', 'software-engineering-capstone'))->assertNotFound();
    }

    public function test_the_project_page_shows_the_accounts_project_not_the_sample_board(): void
    {
        $user = $this->account();
        $project = Project::create(['type' => 'independent', 'owner_id' => $user->id, 'title' => 'Recipe Planner', 'description' => 'Plan meals.', 'status' => 'active']);

        $this->actingAs($user)->get(route('student.projects.show', $project->id))
            ->assertOk()
            ->assertSee('Recipe Planner')
            ->assertSee('Plan meals.')
            ->assertSee('Zina Shady')
            ->assertSee('No tasks yet')
            ->assertDontSee('Aya')
            ->assertDontSee('student-project-board', false);

        // Someone else's project doesn't open.
        $this->actingAs($this->account(['email' => 'other@gmail.com']))->get(route('student.projects.show', $project->id))->assertNotFound();
    }

    public function test_milestones_and_team_explain_there_is_no_course_team_yet(): void
    {
        $this->actingAs($this->account(['project_type' => 'miu', 'email' => 'zina@miuegypt.edu.eg']));

        foreach (['student.milestones.index', 'student.team.show'] as $route) {
            $this->get(route($route, 'software-engineering-capstone'))
                ->assertOk()
                ->assertSee('You’re not on a course team yet.')
                ->assertDontSee('Aya Hassan');
        }
    }

    public function test_demo_accounts_keep_the_sample_data(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'student@demo.milestone.test')->first());

        $this->get(route('student.projects.index'))->assertSee('Software Engineering Capstone');
        $this->get(route('student.tasks.index'))->assertSee('Upload design document');
        $this->get(route('student.team.show', 'software-engineering-capstone'))->assertSee('Aya Hassan');
    }
}
