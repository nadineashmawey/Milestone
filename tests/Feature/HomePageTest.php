<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_the_headline_and_every_section(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Group projects that don’t fall apart.')
            ->assertSee('id="how"', false)
            ->assertSee('id="types"', false)
            ->assertSee('id="roles"', false)
            ->assertSee('id="faq"', false)
            ->assertSee('Evidence, not guesswork');
    }

    public function test_home_page_content_comes_from_config(): void
    {
        config(['milestone_home.faqs' => [['A question only this test uses?', 'An answer.']]]);

        $this->get('/')->assertOk()->assertSee('A question only this test uses?');
    }

    public function test_guests_see_sign_in_and_get_started(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee('Get started')
            ->assertDontSee('Go to dashboard');
    }

    public function test_signed_in_users_see_go_to_dashboard_instead(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Go to dashboard')
            ->assertDontSee('Get started');
    }

    public function test_faq_accordion_starts_with_only_the_first_answer_open(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertSame(1, substr_count($html, 'aria-expanded="true"'), 'Only the first FAQ question should be expanded.');
        $this->assertSame(
            count(config('milestone_home.faqs')) - 1,
            preg_match_all('/class="hp-faq-answer"[^>]*\shidden/', $html),
            'Every other answer should start hidden.',
        );
    }
}
