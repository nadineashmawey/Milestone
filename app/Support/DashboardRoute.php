<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Where each role's dashboard lives. Used after sign-in and by "Go to dashboard" links.
 */
final class DashboardRoute
{
    public static function name(?Authenticatable $user): string
    {
        return self::forRole(Navigation::normalise((string) ($user->role ?? '')));
    }

    public static function forRole(string $role): string
    {
        return match ($role) {
            'ta' => 'ta.dashboard',
            'professor' => 'professor.dashboard',
            'admin' => 'admin.dashboard',
            default => 'student.dashboard',
        };
    }

    public static function url(?Authenticatable $user): string
    {
        return Navigation::href(self::name($user));
    }
}
