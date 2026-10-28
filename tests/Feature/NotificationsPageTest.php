<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Raghad's Notifications page: the shared shell and header, a real In-app / Email tab control, and labelled row actions.
 */
class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_for_every_role_in_the_shared_shell(): void
    {
        $this->seed();

        foreach (Navigation::ROLES as $role) {
            $html = $this->actingAs(User::where('email', $role.'@demo.milestone.test')->first())
                ->get(route('notifications'))->assertOk()->getContent();

            // Same shell and header as the other signed-in pages.
            $this->assertStringContainsString('<main id="main" class="as-main">', $html, $role);
            $this->assertMatchesRegularExpression('#<header class="as-head">\s*<div>\s*<p class="as-eyebrow">Your updates</p>\s*<h1>Notifications</h1>\s*<p class="as-head-lede">Personal alerts are separate#', $html, $role);
            $this->assertStringContainsString('data-navbar-role="'.$role.'"', $html);
            $this->assertStringContainsString('value="'.$role.'"', $html, "{$role}: the list follows the navbar's role");

            // A tablist with two tabs, each controlling its own panel.
            $this->assertSame(1, substr_count($html, 'role="tablist"'), $role);
            $this->assertSame(2, substr_count($html, 'role="tab"'), $role);
            $this->assertStringContainsString('id="notifications-tab-in-app" aria-controls="notifications-panel-in-app" aria-selected="true"', $html);
            $this->assertStringContainsString('id="notifications-tab-email" aria-controls="notifications-panel-email" aria-selected="false" tabindex="-1"', $html);
            $this->assertSame(2, substr_count($html, 'role="tabpanel"'), $role);
            $this->assertStringContainsString('Email preferences', $html);
        }
    }

    public function test_the_email_tab_shows_the_profile_email_preferences(): void
    {
        $html = $this->get(route('notifications'))->assertOk()->getContent();

        foreach (['Tasks', 'Deadlines', 'Announcements', 'Team requests', 'Feedback'] as $category) {
            $this->assertStringContainsString("<strong>{$category}</strong>", $html);
        }
        $this->assertStringContainsString('Email on', $html);
        $this->assertStringContainsString('href="'.route('profile.edit').'#pf-notify-title"', $html);
    }

    public function test_rows_have_labelled_actions_and_a_separate_unread_dot(): void
    {
        // Rows are drawn by resources/js/notifications.js, so the row template is checked at its source.
        $script = file_get_contents(resource_path('js/notifications.js'));

        $this->assertStringContainsString('<span class="notification-dot" aria-hidden="true"></span><button type="button" class="notification-main"', $script);
        $this->assertStringContainsString("aria-label=\"\${read ? 'Already read' : 'Mark as read'}\" title=\"\${read ? 'Already read' : 'Mark as read'}\"", $script);
        $this->assertStringContainsString('aria-label="Dismiss" title="Dismiss"', $script);
        $this->assertStringContainsString('<span class="ms-sr">Unread: </span>', $script);
        $this->assertStringContainsString("You’re all caught up.", $script);
        // Arrow keys move between the tabs.
        $this->assertStringContainsString("ArrowRight", $script);
        $this->assertStringContainsString("ArrowLeft", $script);
    }
}
