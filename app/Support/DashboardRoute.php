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
        return match (strtolower((string) ($user->role ?? ''))) {
            'ta', 'teaching assistant' => 'ta.dashboard',
            'professor' => 'professor.dashboard',
            'admin' => 'admin.dashboard',
            default => 'student.dashboard',
        };
    }

    public static function url(?Authenticatable $user): string
    {
        return AppShellNavigation::href(self::name($user));
    }
}
