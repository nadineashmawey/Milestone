<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * The prototype bar (PROTOTYPE badge, "Sample data. Changes are saved…", the Populated / Empty / Loading / Error
 * switcher) is gone from every page. The states themselves still open with ?state= in local and testing.
 */
class NoPrototypeBarTest extends TestCase
{
    use RefreshDatabase;

    private const BAR_MARKERS = [
        'Sample data. Changes are saved',
        'Preview page state',
        'aria-label="Prototype controls"',
        'class="as-proto',
        'Prototype: saved for this session',
    ];

    private function assertNoBar(string $html, string $where): void
    {
        foreach (self::BAR_MARKERS as $marker) {
            $this->assertStringNotContainsString($marker, $html, "{$where} still shows the prototype bar ({$marker})");
        }
        // The old switcher: links that set ?state=… for the same page.
        $this->assertDoesNotMatchRegularExpression('#<a href="[^"]*[?&]state=(empty|loading|error)#', $html, "{$where} still has a state switcher");
    }

    public function test_no_page_shows_the_prototype_bar_for_any_role(): void
    {
        $this->seed();

        foreach (['/', '/help', '/sign-in', '/sign-up'] as $path) {
            $this->assertNoBar($this->get($path)->assertOk()->getContent(), $path);
        }

        foreach (Navigation::ROLES as $role) {
            $this->actingAs(User::where('email', $role.'@demo.milestone.test')->first());
            $paths = [...array_column(Navigation::items($role), 'href'), route('profile.edit'), route('notifications')];
            foreach ($paths as $path) {
                foreach (['', '?state=empty', '?state=loading', '?state=error'] as $state) {
                    $this->assertNoBar($this->get($path.$state)->assertOk()->getContent(), "{$role} {$path}{$state}");
                }
            }
        }
    }

    public function test_no_view_or_stylesheet_brings_it_back(): void
    {
        $offenders = [];
        foreach ((new Finder)->files()->in([resource_path('views'), resource_path('css'), resource_path('js')]) as $file) {
            foreach ([...self::BAR_MARKERS, 'prototype-bar', "section('prototype-controls')", 'shellStates'] as $marker) {
                if (str_contains($file->getContents(), $marker)) {
                    $offenders[] = str_replace('\\', '/', $file->getRelativePathname()).": {$marker}";
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_the_states_still_open_through_the_query(): void
    {
        $this->seed();
        $this->actingAs(User::where('email', 'student@demo.milestone.test')->first());

        $this->get('/student/dashboard?state=empty')->assertOk()->assertSee('You’re not in any projects yet.');
        $this->get('/student/dashboard?state=loading')->assertOk()->assertSee('Loading your dashboard');
        $this->get('/student/dashboard?state=error')->assertOk()->assertSee('We couldn’t load your dashboard.');
    }
}
