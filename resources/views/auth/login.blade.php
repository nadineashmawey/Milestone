@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
<div class="auth-layout">
    @include('auth.partials.intro', [
        'pill' => 'University project management',
        'headline' => 'Group projects that don’t fall apart.',
        'text' => 'One focused workspace for academic supervision and student-led independent projects.',
        'points' => [
            'One clear source for tasks, files, and deadlines',
            'Review workflows with transparent permissions',
            'Academic and independent projects together',
        ],
    ])

    <section class="ms-card auth-card">
        <p class="eyebrow">Welcome back</p>
        <h2>Sign in to Milestone</h2>
        <p class="auth-lead">Use your MIU or independent-project account to continue.</p>

        @if ($errors->any())
            <div class="auth-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="auth-form">
            @csrf
            <div class="ms-field">
                <label for="email">Email address</label>
                <input class="ms-input" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required autofocus>
            </div>
            <div class="ms-field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input class="ms-input" type="password" id="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" data-toggle-password="password" aria-pressed="false">Show</button>
                </div>
            </div>
            <div class="auth-row">
                <label class="check-inline"><input type="checkbox" name="remember" value="1"> Remember me</label>
            </div>
            <button class="ms-button" type="submit">Sign in</button>
        </form>
        <p class="auth-switch">Don’t have an account? <a href="{{ route('register') }}">Create account</a></p>
    </section>
</div>

<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.dataset.togglePassword);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.textContent = show ? 'Hide' : 'Show';
        button.setAttribute('aria-pressed', show);
    });
});
</script>
@endsection
