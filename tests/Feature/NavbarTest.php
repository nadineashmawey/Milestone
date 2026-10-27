<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ZinaDemoData;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class NavbarTest extends TestCase
{
    use RefreshDatabase;

    /** Labels only one role's menu has. */
    private const OWN = [
        'student' => ['My projects', 'Tasks', 'Milestones', 'Team'],
        'professor' => ['Courses', 'Create project', 'Teams', 'Submissions', 'Risk Radar', 'Extensions', 'Replay'],
        'ta' => ['Reviews', 'Issues', 'Invitations'],
        'admin' => ['Users', 'Imports', 'Courses &amp; terms', 'Project records', 'Announcements', 'Audit log'],
    ];

    private function asDemoRole(string $role): static
    {
        return $this->withSession([ZinaDemoData::SESSION_KEY => ['context' => ['role' => $role]]]);
    }

    /**
     * The bar's own <nav> (not the mobile panel), so labels are counted once.
     */
    private function navOf(string $html): string
    {
        preg_match('#<nav class="ms-navbar__nav" aria-label="[^"]+ navigation">(.*?)</nav>#s', $html, $match);
        $this->assertNotEmpty($match, 'The page has no role navbar.');

        return $match[1];
    }

    /**
     * @return list<string>
     */
    private function labels(string $nav): array
    {
        preg_match_all('#<span class="ms-navbar__label">(.*?)</span>#', $nav, $matches);

        return $matches[1];
    }

    public function test_each_role_gets_only_its_own_items(): void
    {
        foreach (self::OWN as $role => $own) {
            $nav = $this->navOf($this->asDemoRole($role)->get('/profile')->assertOk()->getContent());
            $labels = $this->labels($nav);

            $this->assertSame('Dashboard', $labels[0], "{$role} starts with Dashboard");
            foreach ($own as $label) {
                $this->assertContains($label, $labels, "{$role} should see {$label}");
            }
            foreach (self::OWN as $otherRole => $others) {
                if ($otherRole === $role) {
                    continue;
                }
                foreach (array_diff($others, $own) as $label) {
                    $this->assertNotContains($label, $labels, "{$role} must not see {$otherRole}’s {$label}");
                }
            }
        }
    }

    public function test_a_student_never_sees_submissions_or_users(): void
    {
        $labels = $this->labels($this->navOf($this->asDemoRole('student')->get('/student/dashboard')->getContent()));

        $this->assertNotContains('Submissions', $labels);
        $this->assertNotContains('Users', $labels);
    }

    public function test_the_signed_in_users_role_wins_over_the_demo_role(): void
    {
        $this->seed(DatabaseSeeder::class);
        $ta = User::where('email', 'ta@demo.milestone.test')->first();

        $html = $this->actingAs($ta)->asDemoRole('admin')->get('/profile')->getContent();

        $this->assertStringContainsString('aria-label="TA navigation"', $html);
        $this->assertContains('Reviews', $this->labels($this->navOf($html)));
        $this->assertStringContainsString('Account menu for Sara Ahmed', $html);
    }

    public function test_the_active_item_has_aria_current_and_is_the_only_one(): void
    {
        $nav = $this->navOf($this->asDemoRole('student')->get('/student/projects/software-engineering-capstone/milestones')->getContent());

        $this->assertSame(1, substr_count($nav, 'aria-current="page"'));
        $this->assertMatchesRegularExpression('#aria-current="page"\s*>\s*<svg.*?</svg>\s*<span class="ms-navbar__label">Milestones</span>#s', $nav);
    }

    public function test_badges_are_announced_to_screen_readers(): void
    {
        $nav = $this->navOf($this->asDemoRole('professor')->get('/professor/dashboard')->getContent());

        $this->assertMatchesRegularExpression('#Submissions</span>\s*<span class="ms-navbar__badge" aria-hidden="true">7</span>\s*<span class="ms-sr">, 7 waiting</span>#', $nav);

        $nav = $this->navOf($this->asDemoRole('ta')->get('/profile')->getContent());
        $this->assertStringContainsString(', 5 to review', $nav);
        $this->assertStringContainsString(', 1 pending', $nav);
    }

    public function test_a_missing_route_renders_as_coming_soon_without_errors(): void
    {
        $this->assertFalse(Route::has('professor.replay.index'));
        config(['navigation.roles.student' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'student.dashboard'],
            ['label' => 'Not built yet', 'icon' => 'folder', 'route' => 'a.route.nobody.has', 'params' => ['x' => 1], 'badge' => 'missing-badge'],
        ]]);

        $nav = $this->navOf($this->asDemoRole('student')->get('/student/dashboard')->assertOk()->getContent());

        $this->assertMatchesRegularExpression('#<span class="ms-navbar__link is-disabled" aria-disabled="true" title="Coming soon"[^>]*>.*?Not built yet#s', $nav);
        $this->assertStringNotContainsString('href="#"', $nav);

        $replay = $this->navOf($this->asDemoRole('professor')->get('/professor/dashboard')->getContent());
        $this->assertMatchesRegularExpression('#aria-disabled="true" title="Coming soon"[^>]*>.*?Replay#s', $replay);
    }

    public function test_the_right_side_has_role_theme_help_notifications_and_account(): void
    {
        $html = $this->asDemoRole('student')->get('/student/dashboard')->getContent();

        $this->assertStringContainsString('<span class="ms-navbar__role ms-navbar__wide">Student</span>', $html);
        $this->assertStringContainsString('data-navbar-theme', $html);
        $this->assertStringContainsString('href="'.route('help').'"', $html);
        $this->assertStringContainsString('data-notifications-toggle', $html);
        $this->assertStringContainsString('href="'.route('profile.edit').'"', $html);
        $this->assertMatchesRegularExpression('#aria-controls="ms-navbar-menu" data-navbar-menu-toggle#', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
    }

    public function test_the_dev_role_switcher_only_appears_locally(): void
    {
        $this->get('/student/dashboard')->assertDontSee('data-navbar-role-switch', false);

        $this->app['env'] = 'local';
        $this->get('/student/dashboard')->assertSee('data-navbar-role-switch', false)->assertSee('View as Admin');

        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('email', 'student@demo.milestone.test')->first())
            ->get('/student/dashboard')
            ->assertDontSee('data-navbar-role-switch', false);
    }

    public function test_public_pages_use_the_guest_variant(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('ms-navbar--guest', $home);
        $this->assertStringNotContainsString('ms-navbar--app', $home);
        $this->assertStringContainsString('Get started', $home);

        // Help keeps its short bar: back to Home and Sign in, no section links.
        $help = $this->get('/help')->assertOk()->getContent();
        $this->assertStringContainsString('ms-navbar--guest', $help);
        $this->assertStringContainsString('href="'.route('home').'"', $help);
        $this->assertStringNotContainsString('data-navbar-menu-toggle', $help);
    }

    public function test_every_signed_in_page_has_exactly_one_navbar(): void
    {
        $this->seed(DatabaseSeeder::class);
        $pages = [
            'student@demo.milestone.test' => ['/student/dashboard', '/student/projects/software-engineering-capstone/milestones', '/student/projects/software-engineering-capstone/team', '/my-projects', '/my-projects/software-engineering-capstone', '/student-tasks', '/student-tasks/design-document', '/profile', '/notifications'],
            'independent@demo.milestone.test' => ['/my-projects/create'],
            'professor@demo.milestone.test' => ['/professor/dashboard', '/professor/courses', '/professor/projects/create', '/professor/teams', '/professor/submissions', '/professor/risk-radar', '/professor/extensions'],
            'ta@demo.milestone.test' => ['/ta/dashboard', '/ta/reviews', '/ta/issues', '/ta/invitations', '/ta/tasks'],
            'admin@demo.milestone.test' => ['/admin/dashboard', '/admin/users', '/admin/imports', '/admin/courses', '/admin/projects', '/admin/announcements', '/admin/audit'],
        ];

        foreach ($pages as $email => $paths) {
            $this->actingAs(User::where('email', $email)->first());
            foreach ($paths as $path) {
                $html = $this->get($path)->assertOk()->getContent();
                $this->assertSame(1, preg_match_all('#<header class="ms-navbar ms-navbar--app"#', $html), "{$path} should have one navbar");
                $this->assertSame(1, substr_count($html, 'id="main"'), "{$path} needs exactly one id=\"main\" for the skip link");
            }
        }
    }

    public function test_no_view_has_its_own_site_navigation(): void
    {
        $offenders = [];
        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            if (in_array($path, ['components/navbar.blade.php', 'partials/navbar-item.blade.php'], true)) {
                continue;
            }
            $html = $file->getContents();

            // In-page navs (tabs, steps, footer links) are fine; site navigation and top bars live only in <x-navbar>.
            preg_match_all('#<nav\b[^>]*>#', $html, $navs);
            foreach ($navs[0] as $nav) {
                if (preg_match('#aria-label="[^"]*(navigation|^Main|Mobile)"|class="[^"]*(navbar|topbar|sidebar|ms-navigation)#i', $nav)) {
                    $offenders[] = "{$path}: {$nav}";
                }
            }
            if (preg_match('#class="[^"]*(topbar|ms-sidebar|navbar)[^"]*"#', $html) && ! str_contains($html, '<x-navbar')) {
                $offenders[] = "{$path}: top bar markup";
            }
            if (preg_match("#@(section|yield)\('(navigation|topbar-actions|workspace-name)'#", $html)) {
                $offenders[] = "{$path}: old navigation section";
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_the_ui_kit_shows_every_navbar_variant(): void
    {
        if (! Route::has('ui-kit')) {
            $this->markTestSkipped('The UI kit route is only registered locally.');
        }

        $html = $this->get('/ui-kit')->assertOk()->getContent();
        foreach (['Student', 'Professor', 'TA', 'Admin'] as $label) {
            $this->assertStringContainsString('aria-label="'.$label.' navigation (preview)"', $html);
        }
    }
}
