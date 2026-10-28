<?php

namespace Tests\Feature;

use App\Support\ZinaDemoData;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validProfile(array $overrides = []): array
    {
        return array_replace_recursive([
            'first_name' => 'Aya',
            'last_name' => 'Hassan-Ali',
            'date_format' => 'j M Y',
            'time_format' => '24h',
            'week_start' => 'sunday',
            'notifications' => [
                'tasks' => ['in_app' => '1'],
                'deadlines' => ['in_app' => '1', 'email' => '1'],
                'feedback' => ['email' => '1'],
            ],
        ], $overrides);
    }

    private function asRole(string $role): static
    {
        return $this->withSession([ZinaDemoData::SESSION_KEY => ['context' => ['role' => $role]]]);
    }

    public function test_a_student_sees_editable_names_and_read_only_identity_fields(): void
    {
        $this->get('/profile')
            ->assertOk()
            ->assertSee('Aya Hassan')
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('aya.hassan@university.edu')
            ->assertSee('Student ID')
            ->assertSee('20230012')
            ->assertSee('Program')
            ->assertSee('Request a correction')
            ->assertDontSee('name="email"', false)
            ->assertDontSee('name="student_id"', false);
    }

    public function test_each_role_sees_its_own_fields(): void
    {
        $this->asRole('professor')->get('/profile')->assertSee('Professor Lee')->assertSee('Department')->assertSee('CS 342 · Database Systems')->assertDontSee('Student ID');
        $this->asRole('ta')->get('/profile')->assertSee('Sara Ahmed')->assertSee('Assigned courses')->assertSee('Comment only');
        $this->asRole('admin')->get('/profile')->assertSee('Two-factor authentication')->assertSee('Manage in Security')->assertSee('Habiba’s page');
    }

    public function test_notification_preferences_cover_every_category_and_channel(): void
    {
        $response = $this->get('/profile')->assertOk();

        foreach (['tasks', 'deadlines', 'announcements', 'team_requests', 'feedback'] as $category) {
            $response->assertSee('name="notifications['.$category.'][in_app]"', false)
                ->assertSee('name="notifications['.$category.'][email]"', false);
        }
    }

    public function test_saving_keeps_the_changes_and_confirms(): void
    {
        $this->put('/profile', $this->validProfile())->assertRedirect('/profile')->assertSessionHas('profile_saved');

        $this->get('/profile')
            ->assertSee('Saved')
            ->assertSee('Aya Hassan-Ali')
            ->assertSee('Your profile and notification preferences are up to date.')
            ->assertDontSee('Prototype: saved for this session')
            ->assertSee('<option value="24h" selected>', false);
    }

    public function test_identity_fields_cannot_be_changed_through_the_form(): void
    {
        $this->put('/profile', $this->validProfile(['email' => 'someone@else.com', 'role' => 'admin', 'student_id' => '1']));

        $this->get('/profile')->assertSee('aya.hassan@university.edu')->assertDontSee('someone@else.com');
    }

    public function test_names_are_required_and_checked(): void
    {
        $this->from('/profile')
            ->put('/profile', $this->validProfile(['first_name' => '', 'last_name' => '<script>']))
            ->assertRedirect('/profile')
            ->assertSessionHasErrors(['first_name' => 'Enter your first name.', 'last_name' => 'Use letters, spaces, hyphens or apostrophes.']);
    }

    public function test_display_preferences_must_be_listed_options(): void
    {
        $this->from('/profile')
            ->put('/profile', $this->validProfile(['time_format' => 'sundial']))
            ->assertSessionHasErrors('time_format');
    }

    public function test_deadline_reminders_need_at_least_one_channel(): void
    {
        $this->from('/profile')
            ->put('/profile', $this->validProfile(['notifications' => ['deadlines' => ['in_app' => '0', 'email' => '0']]]))
            ->assertSessionHasErrors(['notifications.deadlines']);
    }

    public function test_a_failed_save_shows_the_error_and_keeps_unticked_boxes_unticked(): void
    {
        // No session assertion on this response: reading it first would hide the flashed errors from the next request.
        $this->from('/profile')->put('/profile', $this->validProfile(['notifications' => ['deadlines' => ['in_app' => '0', 'email' => '0']]]));

        $html = $this->get('/profile')
            ->assertSee('We couldn’t save your changes.')
            ->assertSee('Keep at least one channel on for deadline reminders')
            ->getContent();

        // Announcements are on by default but weren't sent, so they must come back unticked.
        $this->assertMatchesRegularExpression('/name="notifications\[tasks\]\[in_app\]" value="1"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="notifications\[announcements\]\[in_app\]" value="1"\s+checked/', $html);
    }

    public function test_the_form_warns_about_unsaved_changes(): void
    {
        $this->get('/profile')->assertSee('data-as-unsaved', false)->assertSee('You have unsaved changes.');
    }

    public function test_each_state_renders(): void
    {
        $this->get('/profile?state=empty')->assertOk()->assertSee('Welcome to Milestone. Let’s finish your profile.')->assertSee('Skip for now');
        $this->get('/profile?state=loading')->assertOk()->assertSee('Loading your profile');
        $this->get('/profile?state=error')->assertOk()->assertSee('We couldn’t load your profile.');
    }
}
