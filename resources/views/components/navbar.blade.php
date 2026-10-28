{{--
    The one Milestone navbar. See docs/navbar.md.
      <x-navbar />                         signed-in bar for the current role (layouts/app includes it; pages never do)
      <x-navbar variant="guest" />         public header for Home and Help ( :full="false" gives Help's short bar)
    Preview only (the /ui-kit page): role="professor" forces a role, preview stops it sticking to the top.
--}}
@props(['variant' => 'app', 'full' => true, 'role' => null, 'preview' => false])
@php
    use App\Support\DashboardRoute;
    use App\Support\Navigation;

    $id = 'ms-navbar'.($preview ? '-preview-'.($role ?? $variant) : '');
    $links = Navigation::accountLinks();
@endphp

{{-- The saved theme is applied before the page paints by partials/theme-init (in layouts/base). --}}

@if ($variant === 'guest')
    <header {{ $attributes->class(['ms-navbar', 'ms-navbar--guest', 'is-preview' => $preview]) }} data-navbar data-navbar-guest>
        <div class="ms-navbar__inner">
            <a class="ms-navbar__brand" href="{{ route('home') }}">
                <img src="{{ asset('images/milestone-logo.png') }}" alt="Milestone home" width="640" height="149">
            </a>

            @if ($full)
                <nav class="ms-navbar__nav ms-navbar__nav--guest" aria-label="Main">
                    @foreach (config('milestone_home.nav') as $link)
                        <a class="ms-navbar__link" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                    @endforeach
                    <a class="ms-navbar__link" href="{{ route('help') }}" @if (request()->routeIs('help')) aria-current="page" @endif>Help</a>
                </nav>
            @endif

            <div @class(['ms-navbar__actions', 'is-short' => ! $full])>
                <button type="button" class="ms-navbar__icon" data-navbar-theme aria-pressed="false" title="Dark mode">
                    <span class="ms-navbar__theme-dark">@include('public.partials.icon', ['name' => 'moon', 'size' => 18])</span>
                    <span class="ms-navbar__theme-light">@include('public.partials.icon', ['name' => 'sun', 'size' => 18])</span>
                    <span class="ms-sr">Dark mode</span>
                </button>
                @unless ($full)
                    <x-button variant="ghost" :href="route('home')">@include('public.partials.icon', ['name' => 'back', 'size' => 18]) Home</x-button>
                @endunless
                @auth
                    @if ($links['logout'])
                        <form method="POST" action="{{ $links['logout'] }}" class="ms-navbar__form ms-navbar__wide">
                            @csrf
                            <x-button type="submit" variant="ghost">Sign out</x-button>
                        </form>
                    @endif
                    <x-button :href="$links['dashboard']" @class(['ms-navbar__wide' => $full])>Go to dashboard</x-button>
                @else
                    @if ($full)
                        <x-button variant="ghost" :href="$links['login']" class="ms-navbar__wide">Sign in</x-button>
                        <x-button :href="$links['register']" class="ms-navbar__wide">Get started</x-button>
                    @else
                        <x-button :href="$links['login']">Sign in</x-button>
                    @endif
                @endauth
                @if ($full)
                    <button type="button" class="ms-navbar__menu-button" aria-expanded="false" aria-controls="{{ $id }}-menu" data-navbar-menu-toggle>
                        @include('public.partials.icon', ['name' => 'menu', 'size' => 20])<span class="ms-sr">Menu</span>
                    </button>
                @endif
            </div>
        </div>

        @if ($full)
            {{-- Without JavaScript the menu button can't open, so show the links in the bar instead. --}}
            <noscript><style>@media (max-width: 900px) { .ms-navbar--guest .ms-navbar__inner { flex-wrap: wrap; padding-block: 12px; } .ms-navbar--guest .ms-navbar__nav--guest { display: flex; order: 3; width: 100%; overflow-x: auto; } .ms-navbar--guest .ms-navbar__menu-button { display: none; } }</style></noscript>
            <div class="ms-navbar__panel" id="{{ $id }}-menu" hidden data-navbar-menu>
                <nav aria-label="Mobile">
                    @foreach (config('milestone_home.nav') as $link)
                        <a class="ms-navbar__panel-link" href="{{ $link['href'] }}">{{ $link['label'] }}</a>
                    @endforeach
                    <a class="ms-navbar__panel-link" href="{{ route('help') }}">Help</a>
                </nav>
                <div class="ms-navbar__panel-actions">
                    @auth
                        <x-button :href="$links['dashboard']">Go to dashboard</x-button>
                        @if ($links['logout'])
                            <form method="POST" action="{{ $links['logout'] }}" class="ms-navbar__form">
                                @csrf
                                <x-button type="submit" variant="secondary">Sign out</x-button>
                            </form>
                        @endif
                    @else
                        <x-button variant="secondary" :href="$links['login']">Sign in</x-button>
                        <x-button :href="$links['register']">Create account</x-button>
                    @endauth
                </div>
            </div>
        @endif
    </header>
@else
    @php
        $role = in_array($role, Navigation::ROLES, true) ? $role : Navigation::role();
        $roleLabel = Navigation::roleLabel($role);
        $items = Navigation::items($role);
        $person = Navigation::person($role);
        $homeHref = Navigation::href(DashboardRoute::forRole($role));
        $notificationsHref = Navigation::href('notifications');
        $profileHref = Navigation::href('profile.edit');
        $switchable = ! $preview && Navigation::canSwitchRole();
        $navLabel = ($roleLabel === 'TA' ? 'TA' : $roleLabel).' navigation'.($preview ? ' (preview)' : '');
    @endphp
    <header {{ $attributes->class(['ms-navbar', 'ms-navbar--app', 'is-preview' => $preview]) }} data-navbar data-navbar-role="{{ $role }}">
        <div class="ms-navbar__inner">
            <a class="ms-navbar__brand" href="{{ $homeHref === '#' ? route('home') : $homeHref }}">
                <img src="{{ asset('images/milestone-logo.png') }}" alt="Milestone, {{ $roleLabel }} dashboard" width="640" height="149">
            </a>

            <nav class="ms-navbar__nav" aria-label="{{ $navLabel }}">
                <ul class="ms-navbar__list">
                    @foreach ($items as $item)
                        <li>@include('partials.navbar-item', ['item' => $item, 'class' => 'ms-navbar__link'])</li>
                    @endforeach
                </ul>
            </nav>

            <div class="ms-navbar__actions">
                <span class="ms-navbar__role ms-navbar__wide">{{ $roleLabel }}</span>

                @if ($switchable)
                    <form method="POST" action="{{ route('prototype.context') }}" class="ms-navbar__form ms-navbar__switch ms-navbar__wide" data-navbar-role-switch>
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                        <label for="{{ $id }}-role" class="ms-sr">Preview the navbar as</label>
                        <select id="{{ $id }}-role" name="role" title="Dev only: preview each role’s navbar">
                            @foreach (Navigation::ROLES as $option)
                                <option value="{{ $option }}" @selected($option === $role)>View as {{ Navigation::roleLabel($option) }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="ms-button ms-button--small">Switch</button></noscript>
                    </form>
                @endif

                <button type="button" class="ms-navbar__icon ms-navbar__wide" data-navbar-theme aria-pressed="false" title="Dark mode">
                    <span class="ms-navbar__theme-dark">@include('public.partials.icon', ['name' => 'moon', 'size' => 18])</span>
                    <span class="ms-navbar__theme-light">@include('public.partials.icon', ['name' => 'sun', 'size' => 18])</span>
                    <span class="ms-sr">Dark mode</span>
                </button>

                <a class="ms-navbar__icon ms-navbar__wide" href="{{ route('help') }}" title="Help">
                    @include('public.partials.icon', ['name' => 'help', 'size' => 19])<span class="ms-sr">Help</span>
                </a>

                {{-- Notifications preview. The full page and its data are Raghad's (shared/notifications). --}}
                <div class="ms-navbar__dropdown ms-notification-menu" data-navbar-dropdown>
                    <button type="button" class="ms-navbar__icon ms-navbar__bell has-unread" aria-expanded="false" aria-controls="{{ $id }}-notifications"
                        data-navbar-dropdown-toggle data-notifications-toggle data-notifications-role="{{ $role }}" aria-label="Notifications" title="Notifications">
                        @include('public.partials.icon', ['name' => 'bell', 'size' => 19])
                        <span class="ms-navbar__dot" aria-hidden="true"></span>
                    </button>
                    <section class="ms-navbar__popover ms-notifications-popover" id="{{ $id }}-notifications" hidden data-navbar-dropdown-panel data-notifications-popover aria-label="Notifications">
                        <header>
                            <strong>Notifications</strong>
                            @if ($notificationsHref !== '#')
                                <a href="{{ $notificationsHref }}" data-notifications-view-all>View all</a>
                            @else
                                <span aria-disabled="true" title="Coming soon">View all</span>
                            @endif
                        </header>
                        <div data-notification-preview><p class="ms-notification-preview__empty">Open the bell to load your latest updates.</p></div>
                    </section>
                </div>

                <div class="ms-navbar__dropdown" data-navbar-dropdown>
                    <button type="button" class="ms-navbar__avatar-button" aria-expanded="false" aria-controls="{{ $id }}-account" data-navbar-dropdown-toggle title="Your account">
                        <span class="ms-navbar__avatar ms-navbar__avatar--{{ $person['avatar'] % 5 }}" aria-hidden="true">{{ $person['initials'] }}</span>
                        <span class="ms-sr">Account menu for {{ $person['name'] }}</span>
                    </button>
                    <div class="ms-navbar__popover ms-navbar__account" id="{{ $id }}-account" hidden data-navbar-dropdown-panel>
                        <p class="ms-navbar__account-name"><strong>{{ $person['name'] }}</strong><small>{{ $person['detail'] }}</small></p>
                        <ul>
                            <li>
                                @if ($profileHref !== '#')
                                    <a href="{{ $profileHref }}" @if (request()->routeIs('profile.*')) aria-current="page" @endif>@include('public.partials.icon', ['name' => 'user', 'size' => 17]) Profile</a>
                                @else
                                    <span aria-disabled="true" title="Coming soon">@include('public.partials.icon', ['name' => 'user', 'size' => 17]) Profile</span>
                                @endif
                            </li>
                            @if ($person['signed_in'] && $links['logout'])
                                <li>
                                    <form method="POST" action="{{ $links['logout'] }}" class="ms-navbar__form">
                                        @csrf
                                        <button type="submit">@include('public.partials.icon', ['name' => 'logout', 'size' => 17]) Sign out</button>
                                    </form>
                                </li>
                            @elseif (! $person['signed_in'])
                                <li><a href="{{ $links['login'] }}">@include('public.partials.icon', ['name' => 'logout', 'size' => 17]) Sign in</a></li>
                            @endif
                        </ul>
                    </div>
                </div>

                <button type="button" class="ms-navbar__menu-button" aria-expanded="false" aria-controls="{{ $id }}-menu" data-navbar-menu-toggle>
                    @include('public.partials.icon', ['name' => 'menu', 'size' => 20])<span class="ms-sr">Menu</span>
                </button>
            </div>
        </div>

        {{-- Phones and small tablets: the full list with labels. --}}
        <div class="ms-navbar__panel" id="{{ $id }}-menu" hidden data-navbar-menu>
            <ul class="ms-navbar__panel-list" aria-label="{{ $navLabel }}">
                @foreach ($items as $item)
                    <li>@include('partials.navbar-item', ['item' => $item, 'class' => 'ms-navbar__panel-link'])</li>
                @endforeach
            </ul>
            <div class="ms-navbar__panel-tools">
                <span class="ms-navbar__role">{{ $roleLabel }}</span>
                @if ($switchable)
                    <form method="POST" action="{{ route('prototype.context') }}" class="ms-navbar__form ms-navbar__switch" data-navbar-role-switch>
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                        <label for="{{ $id }}-role-mobile" class="ms-sr">Preview the navbar as</label>
                        <select id="{{ $id }}-role-mobile" name="role">
                            @foreach (Navigation::ROLES as $option)
                                <option value="{{ $option }}" @selected($option === $role)>View as {{ Navigation::roleLabel($option) }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="ms-button ms-button--small">Switch</button></noscript>
                    </form>
                @endif
                <button type="button" class="ms-navbar__tool" data-navbar-theme aria-pressed="false">
                    <span class="ms-navbar__theme-dark">@include('public.partials.icon', ['name' => 'moon', 'size' => 18])</span>
                    <span class="ms-navbar__theme-light">@include('public.partials.icon', ['name' => 'sun', 'size' => 18])</span>
                    Dark mode
                </button>
                <a class="ms-navbar__tool" href="{{ route('help') }}">@include('public.partials.icon', ['name' => 'help', 'size' => 18]) Help</a>
            </div>
        </div>
    </header>
@endif
