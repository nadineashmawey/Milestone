<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePrototypeContextRequest;
use App\Support\DashboardRoute;
use App\Support\Navigation;
use App\Support\ZinaDemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PrototypeContextController extends Controller
{
    /**
     * Switch who the prototype is viewed as. Stands in for the signed-in user until auth is merged.
     */
    public function update(UpdatePrototypeContextRequest $request, ZinaDemoData $demo): RedirectResponse
    {
        $demo->updateContext($request->safe()->only(['viewer', 'phase', 'role']));

        // A new role starts on its own dashboard: the page you were on belongs to the old role.
        if ($request->has('role')) {
            return redirect()->to(Navigation::href(DashboardRoute::forRole($demo->context()['role'])))->with('prototype_switched', true);
        }

        // Only same-site paths, so the form can't be used to redirect somewhere else.
        $target = (string) $request->input('redirect', '');

        return Str::startsWith($target, '/') && ! Str::startsWith($target, '//')
            ? redirect()->to($target)->with('prototype_switched', true)
            : redirect()->back()->with('prototype_switched', true);
    }
}
