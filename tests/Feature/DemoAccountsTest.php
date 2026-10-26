<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_the_seeder_creates_every_demo_account(): void
    {
        foreach (config('demo.accounts') as $account) {
            $this->assertDatabaseHas('users', [
                'email' => $account['email'],
                'name' => $account['name'],
                'role' => $account['role'],
                'project_type' => $account['project_type'],
                'status' => 'active',
            ]);
        }
    }

    public function test_each_demo_account_lands_on_its_own_dashboard(): void
    {
        $expected = [
            'student@demo.milestone.test' => '/student/dashboard',
            'independent@demo.milestone.test' => '/student/dashboard',
            'ta@demo.milestone.test' => '/ta/dashboard',
            'professor@demo.milestone.test' => '/professor/dashboard',
            'admin@demo.milestone.test' => '/admin/dashboard',
        ];

        foreach ($expected as $email => $path) {
            $this->post('/sign-in', ['email' => $email, 'password' => config('demo.password')])->assertRedirect($path);
            $this->assertAuthenticatedAs(User::where('email', $email)->first());
            $this->get($path)->assertOk();
            $this->post('/sign-out')->assertRedirect('/sign-in');
            $this->assertGuest();
        }
    }

    public function test_a_wrong_password_is_refused_with_a_generic_message(): void
    {
        $this->from('/sign-in')
            ->post('/sign-in', ['email' => 'student@demo.milestone.test', 'password' => 'wrong-password'])
            ->assertRedirect('/sign-in')
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
        $this->assertGuest();
    }

    public function test_the_sign_in_page_lists_the_demo_accounts(): void
    {
        $response = $this->get('/sign-in')->assertOk()->assertSee('Demo accounts for testing')->assertSee(config('demo.password'));

        foreach (config('demo.accounts') as $account) {
            $response->assertSee($account['name'])->assertSee($account['email']);
        }
    }

    public function test_the_demo_list_can_be_turned_off(): void
    {
        config(['demo.enabled' => false]);

        $this->get('/sign-in')->assertOk()->assertDontSee('Demo accounts for testing')->assertDontSee(config('demo.password'));
    }

    public function test_signed_in_users_can_reach_their_dashboard_and_sign_out_from_home(): void
    {
        $this->actingAs(User::where('email', 'professor@demo.milestone.test')->first())
            ->get('/')
            ->assertOk()
            ->assertSee('href="'.route('professor.dashboard').'"', false)
            ->assertSee('Sign out');
    }

    public function test_the_signed_in_shell_has_a_sign_out_button(): void
    {
        $this->actingAs(User::where('email', 'student@demo.milestone.test')->first())
            ->get('/student/dashboard')
            ->assertOk()
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Sign out Aya Hassan');
    }
}
