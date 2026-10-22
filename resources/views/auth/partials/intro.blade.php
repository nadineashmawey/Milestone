{{-- resources/views/auth/partials/intro.blade.php
     Shared left panel so login and register can never drift apart.
     Params: $pill, $headline, $text, $points (array) --}}
<section class="auth-intro">
    <a href="{{ url('/') }}" class="auth-logo" aria-label="Milestone home">
        {{-- change to wherever your logo file lives --}}
        <img src="{{ asset('images/milestone-logo.png') }}" alt="Milestone">
    </a>

    <div class="auth-intro-body">
        <span class="auth-pill">
            <span class="auth-pill-dot" aria-hidden="true"></span>{{ $pill }}
        </span>

        <h1>{{ $headline }}</h1>
        <p>{{ $text }}</p>

        <ul class="auth-checklist">
            @foreach ($points as $point)
                <li>{{ $point }}</li>
            @endforeach
        </ul>
    </div>
</section>