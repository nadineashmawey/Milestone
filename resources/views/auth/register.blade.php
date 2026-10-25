@extends('layouts.auth')

@section('title', 'Create account')

@section('content')
<div class="auth-layout">

    @include('auth.partials.intro', [
        'pill'     => 'Your project workspace',
        'headline' => 'Build something great together.',
        'text'     => 'Organize your capstone projects, collaborate with your team, and keep every milestone on track.',
        'points'   => [
            'Keep tasks, files, and deadlines organized',
            'Track project progress in one workspace',
            'Choose academic or independent project management',
        ],
    ])

    <section class="ms-card auth-card">
        <p class="eyebrow">Get started</p>
        <h2>Create your account</h2>
        <p class="auth-lead">Join Milestone and set up your project workspace.</p>

        @if ($errors->any())
            <div class="auth-error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" class="auth-form">
            @csrf

            <fieldset class="auth-account-types">
                <legend>How will you use Milestone?</legend>

                <div class="account-type-options">
                    <label class="account-type-option">
                        <input type="radio" name="account_type" value="miu"
                               {{ old('account_type') === 'miu' ? 'checked' : '' }} required>
                        <span class="account-type-title">University projects</span>
                        <span class="account-type-description">
                            Join a course project with your university, professor, or TA.
                        </span>
                        <span class="account-type-tag">Requires MIU email</span>
                    </label>

                    <label class="account-type-option is-green">
                        <input type="radio" name="account_type" value="independent"
                               {{ old('account_type') === 'independent' ? 'checked' : '' }} required>
                        <span class="account-type-title">Independent projects</span>
                        <span class="account-type-description">
                            Create your own project and invite teammates.
                        </span>
                        <span class="account-type-tag">Any email works</span>
                    </label>
                </div>
            </fieldset>

            <div class="ms-field">
                <label for="name">Full name</label>
                <input class="ms-input" type="text" id="name" name="name"
                       value="{{ old('name') }}" autocomplete="name"
                       placeholder="Enter your full name" required>
            </div>

            <div class="ms-field">
                <label for="email">Email address</label>
                <input class="ms-input" type="email" id="email" name="email"
                       value="{{ old('email') }}" autocomplete="email"
                       placeholder="you@example.com" aria-describedby="email-hint" required>
                <small id="email-hint" class="field-hint"></small>
            </div>

            <div class="auth-grid-2">
                <div class="ms-field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input class="ms-input" type="password" id="password" name="password"
                               autocomplete="new-password" placeholder="Create a password" required>
                        <button type="button" class="password-toggle"
                                data-toggle-password="password" aria-pressed="false">Show</button>
                    </div>
                </div>

                <div class="ms-field">
                    <label for="password_confirmation">Confirm password</label>
                    <div class="password-wrap">
                        <input class="ms-input" type="password" id="password_confirmation"
                               name="password_confirmation" autocomplete="new-password"
                               placeholder="Enter it again" required>
                        <button type="button" class="password-toggle"
                                data-toggle-password="password_confirmation" aria-pressed="false">Show</button>
                    </div>
                </div>
            </div>

            <button class="ms-button" type="submit">Create account</button>
        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </p>
    </section>
</div>

<script>
document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.togglePassword);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
        btn.setAttribute('aria-pressed', show);
    });
});

// Email hint follows the chosen account type
(function () {
    var hint = document.getElementById('email-hint');
    var text = {
        miu: 'Use your MIU email address.',
        independent: 'Any email address works.',
        none: 'MIU students need their MIU email. Independent students can use any email.'
    };
    function update() {
        var picked = document.querySelector('input[name="account_type"]:checked');
        hint.textContent = text[picked ? picked.value : 'none'];
    }
    document.querySelectorAll('input[name="account_type"]').forEach(function (r) {
        r.addEventListener('change', update);
    });
    update();
})();
</script>
@endsection
