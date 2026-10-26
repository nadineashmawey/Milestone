{{-- $full: the Home page gets anchor links and the mobile menu; the Help center gets a short bar. --}}
<header class="hp-nav" data-hp-nav>
    <div class="hp-nav-inner">
        <a class="hp-logo" href="{{ route('home') }}">
            <img src="{{ asset('images/milestone-logo.png') }}" alt="Milestone home" width="640" height="149">
        </a>

        @if ($full)
            <nav class="hp-nav-links" aria-label="Main">
                @foreach (config('milestone_home.nav') as $link)
                    <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                @endforeach
                <a href="{{ route('help') }}">Help</a>
            </nav>
        @endif

        <div @class(['hp-nav-actions', 'always' => ! $full])>
            @unless ($full)
                <x-button variant="ghost" :href="route('home')">@include('public.partials.icon', ['name' => 'back', 'size' => 18]) Home</x-button>
            @endunless
            @auth
                @if ($accountLinks['logout'])
                    <form method="POST" action="{{ $accountLinks['logout'] }}" class="hp-signout">
                        @csrf
                        <x-button type="submit" variant="ghost">Sign out</x-button>
                    </form>
                @endif
                <x-button :href="$accountLinks['dashboard']">Go to dashboard</x-button>
            @else
                @if ($full)
                    <x-button variant="ghost" :href="$accountLinks['login']">Sign in</x-button>
                    <x-button :href="$accountLinks['register']">Get started</x-button>
                @else
                    <x-button :href="$accountLinks['login']">Sign in</x-button>
                @endif
            @endauth
            @if ($full)
                <button type="button" class="hp-menu-toggle" aria-expanded="false" aria-controls="hp-mobile-menu" data-hp-menu-toggle>
                    <span class="hp-sr">Menu</span>
                    <span class="hp-menu-bar" aria-hidden="true"></span>
                    <span class="hp-menu-bar" aria-hidden="true"></span>
                </button>
            @endif
        </div>
    </div>

    @if ($full)
        <div class="hp-mobile-menu" id="hp-mobile-menu" hidden data-hp-menu>
            <nav aria-label="Mobile">
                @foreach (config('milestone_home.nav') as $link)
                    <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                @endforeach
                <a href="{{ route('help') }}">Help</a>
            </nav>
            <div class="hp-mobile-actions">
                @auth
                    <x-button :href="$accountLinks['dashboard']">Go to dashboard</x-button>
                    @if ($accountLinks['logout'])
                        <form method="POST" action="{{ $accountLinks['logout'] }}" class="hp-signout">
                            @csrf
                            <x-button type="submit" variant="secondary">Sign out</x-button>
                        </form>
                    @endif
                @else
                    <x-button variant="secondary" :href="$accountLinks['login']">Sign in</x-button>
                    <x-button :href="$accountLinks['register']">Create account</x-button>
                @endauth
            </div>
        </div>
    @endif
</header>
