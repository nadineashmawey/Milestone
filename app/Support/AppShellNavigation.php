<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Role-based top navigation for layouts/app-shell (DSH-2), following the App.tsx prototype.
 *
 * Links to teammates' pages resolve only once their route is registered. Until then they point at "#"
 * and say whose page they are waiting for, so nothing looks broken without explanation.
 */
final class AppShellNavigation
{
    /**
     * @return list<array{key: string, label: string, icon: string, href: string, available: bool, owner: string}>
     */
    public static function for(string $role): array
    {
        $items = match ($role) {
            'professor' => [
                ['dashboard', 'Dashboard', 'grid', 'professor.dashboard', [], 'Zina'],
                ['courses', 'Courses', 'folder', 'professor.courses.index', [], 'Nadine'],
                ['create-project', 'Create project', 'plus', 'professor.projects.create', [], 'Nadine'],
                ['teams', 'Teams', 'users', 'professor.teams.index', [], 'Jana'],
                ['submissions', 'Submissions', 'check', 'professor.submissions.index', [], 'Nadine'],
                ['risk-radar', 'Risk Radar', 'chart', 'professor.risk-radar.index', [], 'Nadine'],
                ['extensions', 'Extensions', 'calendar', 'professor.extensions.index', [], 'Nadine'],
            ],
            'ta' => [
                ['dashboard', 'Dashboard', 'grid', 'ta.dashboard', [], 'Habiba'],
                ['reviews', 'Reviews', 'check', 'ta.reviews', [], 'Raghad'],
                ['issues', 'Issues', 'bell', 'ta.issues', [], 'Raghad'],
                ['invitations', 'Invitations', 'users', 'ta.assignments', [], 'Raghad'],
            ],
            'admin' => [
                ['dashboard', 'Dashboard', 'grid', 'admin.dashboard', [], 'Habiba'],
                ['users', 'Users', 'users', 'admin.users', [], 'Habiba'],
                ['imports', 'Imports', 'upload', 'admin.imports', [], 'Habiba'],
                ['courses', 'Courses & terms', 'calendar', 'admin.courses', [], 'Habiba'],
                ['projects', 'Project records', 'folder', 'admin.projects', [], 'Habiba'],
                ['audit', 'Audit', 'clock', 'admin.audit', [], 'Habiba'],
            ],
            default => [
                ['dashboard', 'Dashboard', 'grid', 'student.dashboard', [], 'Zina'],
                ['projects', 'My projects', 'folder', 'projects.index', [], 'Jana'],
                ['tasks', 'Tasks', 'check', 'student.tasks.index', [], 'Jana'],
                ['milestones', 'Milestones', 'clock', 'student.milestones.index', ['project' => 'software-engineering-capstone'], 'Zina'],
                ['team', 'Team', 'users', 'student.team.show', ['project' => 'software-engineering-capstone'], 'Zina'],
            ],
        };

        return array_map(fn (array $item) => self::link(...$item), $items);
    }

    /**
     * A link to a page that may not exist yet in this branch.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function href(string $route, array $parameters = []): string
    {
        return Route::has($route) ? route($route, $parameters) : '#';
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{key: string, label: string, icon: string, href: string, available: bool, owner: string}
     */
    private static function link(string $key, string $label, string $icon, string $route, array $parameters, string $owner): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'href' => self::href($route, $parameters),
            'available' => Route::has($route),
            'owner' => $owner,
        ];
    }
}
