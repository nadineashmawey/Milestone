<?php

namespace App\Http\Middleware;

use App\Support\DashboardRoute;
use App\Support\Navigation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role pages only open for their own role (route('…')->middleware('role:professor')). Anyone else gets the
 * access-denied page (DSH-4) with their own navbar, instead of another role's page.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $current = Navigation::role();

        if ($current !== $role) {
            return response()->view('errors.access-denied', [
                'roleLabel' => Navigation::roleLabel($current),
                'dashboardHref' => Navigation::href(DashboardRoute::forRole($current)),
            ], 403);
        }

        return $next($request);
    }
}
