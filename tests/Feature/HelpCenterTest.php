<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_center_lists_articles_and_the_report_form(): void
    {
        $this->get('/help')
            ->assertOk()
            ->assertSee('How can we help?')
            ->assertSee('Which email should I use?')
            ->assertSee('Report a problem')
            ->assertSee('name="_token"', false);
    }

    public function test_search_without_javascript_hides_articles_that_do_not_match(): void
    {
        $html = $this->get('/help?q=leave')->assertOk()->getContent();

        // One match ("Can I leave my team?"); every other article is rendered but hidden.
        $total = substr_count($html, 'data-hp-article');
        $hidden = preg_match_all('/data-hp-article\s+hidden/', $html);
        $this->assertSame(1, $total - $hidden);
        $this->assertMatchesRegularExpression('/class="ms-empty"[^>]*hidden/', $html);
    }

    public function test_search_with_no_results_shows_the_empty_state(): void
    {
        $html = $this->get('/help?q=zzzz')->assertOk()->getContent();

        $this->assertStringContainsString('No articles match “zzzz”.', $html);
        $this->assertDoesNotMatchRegularExpression('/class="ms-empty"[^>]*hidden/', $html);
    }

    public function test_search_matches_straight_and_curly_apostrophes(): void
    {
        $html = $this->get("/help?q=can't I change")->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'data-hp-article') - preg_match_all('/data-hp-article\s+hidden/', $html));
    }

    public function test_signed_in_users_get_their_name_and_email_prefilled(): void
    {
        $user = User::factory()->create(['name' => 'Aya Hassan', 'email' => 'aya.hassan@university.edu']);

        $this->actingAs($user)
            ->get('/help')
            ->assertOk()
            ->assertSee('value="Aya Hassan"', false)
            ->assertSee('value="aya.hassan@university.edu"', false);
    }
}
