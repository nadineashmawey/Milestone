<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Support\ZinaDemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, ZinaDemoData $demo): View
    {
        $state = $demo->state($request);

        return view('zina.profile.edit', [
            'state' => $state,
            'profile' => $demo->profile(newAccount: $state === 'empty'),
            'context' => $demo->context(),
            'categories' => ZinaDemoData::NOTIFICATION_CATEGORIES,
            'channels' => ZinaDemoData::NOTIFICATION_CHANNELS,
            'options' => ZinaDemoData::DISPLAY_OPTIONS,
        ]);
    }

    public function update(UpdateProfileRequest $request, ZinaDemoData $demo): RedirectResponse
    {
        $demo->saveProfile($request->validated());

        return redirect()->route('profile.edit')->with('profile_saved', true);
    }
}
