<?php

namespace App\Support;

use App\Models\User;

/**
 * Who is acting: the signed-in user, or, with nobody signed in, the demo account for the demo role
 * (the same role the navbar shows, see Navigation::role()). Only the demo accounts in config/demo.php
 * can stand in, so production with no demo accounts always needs a real sign-in.
 */
final class CurrentUser
{
    public static function get(): ?User
    {
        if ($user = auth()->user()) {
            return $user;
        }

        if (! config('demo.enabled')) {
            return null;
        }

        $role = Navigation::role();
        $account = collect(config('demo.accounts', []))->first(fn (array $account) => Navigation::normalise($account['role']) === $role && ($account['project_type'] ?? 'miu') === 'miu');

        return $account ? User::where('email', $account['email'])->first() : null;
    }
}
