<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\DashboardRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route(DashboardRoute::name($request->user())));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:161'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'account_type' => ['required', 'in:miu,independent'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validated['account_type'] === 'miu' && ! str_ends_with(strtolower($validated['email']), '@miuegypt.edu.eg')) {
            return back()->withErrors(['email' => 'Use your MIU email ending in @miuegypt.edu.eg.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $nameParts = preg_split('/\s+/', trim($validated['name']), 2, PREG_SPLIT_NO_EMPTY);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $user = User::create([
            'name' => trim($validated['name']),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'Student',
            'project_type' => $validated['account_type'],
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('projects.index')->with('success', 'Your account has been created.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
