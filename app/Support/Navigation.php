<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Everything the navbar needs (<x-navbar />). The one place that decides the current role:
 * the signed-in user's role, or, with nobody signed in, the demo role kept in the session.
 */
final class Navigation
{
    public const ROLES = ['student', 'professor', 'ta', 'admin'];

    public static function role(): string
    {
        $user = auth()->user();

        return $user ? self::normalise((string) $user->role) : app(ZinaDemoData::class)->context()['role'];
    }

    /**
     * Turns stored role names ("Student", "TA", "Teaching assistant") into navbar keys.
     */
    public static function normalise(string $role): string
    {
        return match (strtolower(trim($role))) {
            'ta', 'teaching assistant' => 'ta',
            'professor' => 'professor',
            'admin', 'administrator' => 'admin',
            default => 'student',
        };
    }

    public static function roleLabel(string $role): string
    {
        return config('navigation.role_labels.'.$role, 'Student');
    }

    /**
     * The role's items with their resolved link, availability, active state and badge.
     *
     * @return list<array{label: string, icon: string, href: string|null, available: bool, active: bool, badge: array{count: int, label: string}|null}>
     */
    public static function items(string $role): array
    {
        return array_map(function (array $item) {
            $available = Route::has($item['route']);
            $badge = isset($item['badge']) ? config('navigation.badges.'.$item['badge']) : null;

            return [
                'label' => $item['label'],
                'icon' => $item['icon'],
                'href' => $available ? route($item['route'], $item['params'] ?? []) : null,
                'available' => $available,
                'active' => $available && request()->routeIs($item['active'] ?? $item['route']),
                'badge' => $badge,
            ];
        }, config('navigation.roles.'.$role, []));
    }

    /**
     * Who is shown in the avatar menu: the signed-in user, or the demo person for the role.
     *
     * @return array{name: string, detail: string, initials: string, avatar: int, signed_in: bool}
     */
    public static function person(string $role): array
    {
        $demo = app(ZinaDemoData::class);
        $user = auth()->user();

        if ($user) {
            return ['name' => $user->name, 'detail' => $user->email, 'initials' => $demo->initials($user->name), 'avatar' => 0, 'signed_in' => true];
        }

        $person = match ($role) {
            'professor' => $demo->person('lee'),
            'ta' => $demo->person('sara'),
            'admin' => $demo->person('admin'),
            default => $demo->viewer(),
        };

        return ['name' => $person['name'], 'detail' => 'Demo · '.self::roleLabel($role), 'initials' => $demo->initials($person['name']), 'avatar' => $person['avatar'], 'signed_in' => false];
    }

    /**
     * The dev-only role switcher: local environment, nobody signed in.
     */
    public static function canSwitchRole(): bool
    {
        return app()->environment('local') && ! auth()->check() && Route::has('prototype.context');
    }

    /**
     * A link that may point at a teammate's page. Resolves to "#" until that route is registered.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function href(string $route, array $parameters = []): string
    {
        return Route::has($route) ? route($route, $parameters) : '#';
    }

    /**
     * Account links for the guest navbar and Home's calls to action.
     *
     * @return array{login: string, register: string, dashboard: string, logout: string|null}
     */
    public static function accountLinks(): array
    {
        // Until Jana's Sign in and Sign up routes exist, the links use Laravel's standard paths.
        return [
            'login' => Route::has('login') ? route('login') : url('/login'),
            'register' => Route::has('register') ? route('register') : url('/register'),
            'dashboard' => DashboardRoute::url(auth()->user()),
            'logout' => Route::has('logout') ? route('logout') : null,
        ];
    }
}
