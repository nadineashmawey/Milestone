<?php

namespace Tests\Feature;

use App\Models\ProblemReport;
use App\Models\User;
use Database\Seeders\ProblemReportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProblemReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validReport(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Aya Hassan',
            'email' => 'aya.hassan@university.edu',
            'category' => 'submission_upload',
            'description' => 'The M2 upload stops at 90% every time I try.',
        ], $overrides);
    }

    public function test_a_guest_can_report_a_problem(): void
    {
        $response = $this->post('/help/reports', $this->validReport());

        $response->assertRedirect(route('help').'#report')
            ->assertSessionHas('report_sent', fn (array $sent) => preg_match('/^MS-\d{4,5}$/', $sent['reference']) === 1
                && $sent['first_name'] === 'Aya'
                && $sent['email'] === 'aya.hassan@university.edu');

        $this->assertDatabaseHas('problem_reports', [
            'user_id' => null,
            'email' => 'aya.hassan@university.edu',
            'category' => 'submission_upload',
            'status' => 'open',
        ]);
    }

    public function test_the_reference_is_shown_after_the_redirect_and_the_form_is_cleared(): void
    {
        $this->followingRedirects()
            ->post('/help/reports', $this->validReport())
            ->assertOk()
            ->assertSee('Report sent')
            ->assertSee(ProblemReport::first()->reference)
            ->assertDontSee('value="Aya Hassan"', false);
    }

    public function test_a_signed_in_user_is_linked_to_their_report(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/help/reports', $this->validReport())->assertRedirect();

        $this->assertSame($user->id, ProblemReport::first()->user_id);
    }

    public function test_the_confirmation_email_is_logged_not_sent(): void
    {
        Log::spy();

        $this->post('/help/reports', $this->validReport());

        Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context) => str_contains($message, 'email simulated')
            && $context['reference'] === ProblemReport::first()->reference)->once();
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function invalidFields(): array
    {
        return [
            'name missing' => ['name', ''],
            'name over 100 characters' => ['name', str_repeat('a', 101)],
            'email missing' => ['email', ''],
            'email not an address' => ['email', 'not-an-email'],
            'email over 150 characters' => ['email', 'n@'.str_repeat('abcdefghi.', 15).'com'],
            'category missing' => ['category', ''],
            'category not in the list' => ['category', 'billing'],
            'description missing' => ['description', ''],
            'description under 20 characters' => ['description', 'Too short to help.'],
            'description over 2000 characters' => ['description', str_repeat('a', 2001)],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_each_validation_rule_rejects_bad_input_and_keeps_old_input(string $field, mixed $value): void
    {
        $response = $this->from('/help')->post('/help/reports', $this->validReport([$field => $value]));

        $response->assertRedirect('/help')
            ->assertSessionHasErrors($field)
            ->assertSessionHasInput('name', $field === 'name' ? $value : 'Aya Hassan');
        $this->assertDatabaseCount('problem_reports', 0);
    }

    public function test_validation_errors_show_under_their_fields(): void
    {
        $this->followingRedirects()
            ->from('/help')
            ->post('/help/reports', $this->validReport(['email' => 'not-an-email']))
            ->assertOk()
            ->assertSee('Enter a valid email address, like name@university.edu.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('value="Aya Hassan"', false);
    }

    public function test_a_server_error_keeps_the_input_and_shows_an_error(): void
    {
        Schema::drop('problem_reports');

        $this->post('/help/reports', $this->validReport())
            ->assertRedirect(route('help').'#report')
            ->assertSessionHas('report_failed')
            ->assertSessionHasInput('description');
    }

    public function test_reports_are_rate_limited_to_five_a_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/help/reports', $this->validReport())->assertRedirect();
        }

        $this->post('/help/reports', $this->validReport())->assertTooManyRequests();
        $this->assertDatabaseCount('problem_reports', 5);
    }

    public function test_config_category_labels_match_the_model(): void
    {
        $this->assertSame(ProblemReport::CATEGORIES, array_keys(config('milestone_home.report_categories')));
    }

    public function test_the_seeder_adds_three_sample_reports(): void
    {
        $this->seed(ProblemReportSeeder::class);

        $this->assertDatabaseCount('problem_reports', 3);
        $this->assertEqualsCanonicalizing(['open', 'in_progress', 'resolved'], ProblemReport::pluck('status')->all());
    }
}
