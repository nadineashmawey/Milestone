<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePrototypeContextRequest;
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

        // Only same-site paths, so the form can't be used to redirect somewhere else.
        $target = (string) $request->input('redirect', '');

        return Str::startsWith($target, '/') && ! Str::startsWith($target, '//')
            ? redirect()->to($target)->with('prototype_switched', true)
            : redirect()->back()->with('prototype_switched', true);
    }
}
