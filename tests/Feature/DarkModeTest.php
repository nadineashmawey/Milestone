<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dark mode: one switch on <html>, set before the first paint on every layout, with a labelled toggle.
 */
class DarkModeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * One page per layout: public, auth, app, app-shell, professor, admin and TA.
     *
     * @return array<string, array{0: string, 1: string|null}>
     */
    private function pages(): array
    {
        return [
            'public (Home)' => ['/', null],
            'public (Help)' => ['/help', null],
            'auth (Sign in)' => ['/sign-in', null],
            'app (Notifications)' => ['/notifications', 'student'],
            'app-shell (Student dashboard)' => ['/student/dashboard', 'student'],
            'app (Jana’s My Projects)' => ['/student/projects', 'student'],
            'professor (Courses)' => ['/professor/courses', 'professor'],
            'admin (Users)' => ['/admin/users', 'admin'],
            'ta (Reviews)' => ['/ta/reviews', 'ta'],
        ];
    }

    public function test_every_layout_sets_the_theme_before_the_page_paints(): void
    {
        $this->seed();

        foreach ($this->pages() as $label => [$path, $role]) {
            $this->post(route('logout'));
            if ($role) {
                $this->actingAs(User::where('email', $role.'@demo.milestone.test')->first());
            }
            $html = $this->get($path)->assertOk()->getContent();
            $head = substr($html, 0, strpos($html, '</head>'));

            // Exactly one copy, in <head>, before the stylesheets.
            $this->assertSame(1, substr_count($html, "localStorage.getItem('milestone-theme')"), "{$label}: one theme-init script");
            $this->assertStringContainsString("root.classList.toggle('dark', dark)", $head, "{$label}: theme-init in <head>");
            $this->assertStringContainsString("prefers-color-scheme: dark", $head, "{$label}: follows the system without a saved choice");
            $this->assertLessThan(strpos($head, 'rel="stylesheet"'), strpos($head, 'milestone-theme'), "{$label}: before the CSS");
        }
    }

    public function test_the_toggle_is_labelled_and_reports_its_state(): void
    {
        $this->seed();

        foreach (['/' => null, '/student/dashboard' => 'student'] as $path => $role) {
            if ($role) {
                $this->actingAs(User::where('email', $role.'@demo.milestone.test')->first());
            }
            $html = $this->get($path)->getContent();

            $this->assertMatchesRegularExpression('#<button type="button" class="ms-navbar__icon[^"]*" data-navbar-theme aria-pressed="false" title="Dark mode">\s*<span class="ms-navbar__theme-dark">.*?<span class="ms-navbar__theme-light">.*?<span class="ms-sr">Dark mode</span>#s', $html, $path);
        }
    }

    public function test_the_ui_kit_documents_dark_mode(): void
    {
        $this->app['env'] = 'local';
        if (! \Illuminate\Support\Facades\Route::has('ui-kit')) {
            $this->markTestSkipped('The UI kit route is only registered locally.');
        }

        $this->get('/ui-kit')->assertOk()->assertSee('Dark mode')->assertSee('data-navbar-theme', false);
    }
}
