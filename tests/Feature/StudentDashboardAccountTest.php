<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Real accounts see their own dashboard; only the demo accounts (config/demo.php) see the sample people.
 */
class StudentDashboardAccountTest extends TestCase
{
    use RefreshDatabase;

    private function account(array $attributes = []): User
    {
        return User::factory()->create([
            'name' => 'Zina Shady', 'first_name' => 'Zina', 'last_name' => 'Shady', 'email' => 'zina.shady@gmail.com',
            'role' => 'Student', 'project_type' => 'independent', 'status' => 'active', ...$attributes,
        ]);
    }

    public function test_a_new_independent_account_sees_its_own_empty_dashboard(): void
    {
        $this->actingAs($this->account())
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Good morning, Zina')
            ->assertSee('You’re not in any projects yet.')
            ->assertSee('Create an independent project')
            ->assertDontSee('Aya')
            ->assertDontSee('Malek')
            ->assertDontSee('Viewing as');
    }

    public function test_it_lists_the_projects_the_account_created(): void
    {
        $user = $this->account();

        $this->actingAs($user)
            ->withSession(['independent_projects' => [['id' => 'p-1', 'title' => 'Recipe Planner', 'description' => '', 'owner_id' => $user->id]]])
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Recipe Planner')
            ->assertSee('Add your first task')
            ->assertDontSee('Software Engineering Capstone')
            ->assertDontSee('Campus Navigation App');
    }

    public function test_a_university_account_is_not_offered_create(): void
    {
        $this->actingAs($this->account(['email' => 'zina@miuegypt.edu.eg', 'project_type' => 'miu']))
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Join with a code')
            ->assertDontSee('Create an independent project');
    }

    public function test_the_profile_shows_the_account_not_the_sample_student(): void
    {
        $this->actingAs($this->account())
            ->get('/profile')
            ->assertOk()
            ->assertSee('zina.shady@gmail.com')
            ->assertDontSee('aya.hassan@university.edu');
    }

    public function test_the_independent_demo_account_sees_only_independent_work(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'independent@demo.milestone.test')->first())
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('Good morning, Omar')
            ->assertSee('Campus Navigation App')
            ->assertDontSee('Software Engineering Capstone')
            ->assertDontSee('Viewing as');
    }
}
