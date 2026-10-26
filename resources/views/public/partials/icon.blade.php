{{-- Stroke icons from the design reference. Decorative only: always pair with visible text. --}}
<svg width="{{ $size ?? 20 }}" height="{{ $size ?? 20 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>
            @break
        @case('folder')
            <path d="M3 7.5V18a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H5a2 2 0 0 0-2 2v1.5Z"/>
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            @break
        @case('check')
            <path d="M21 11.5V12a9 9 0 1 1-5.34-8.23"/><path d="m21 4-9 9-3-3"/>
            @break
        @case('arrow')
            <path d="M5 12h14M13 6l6 6-6 6"/>
            @break
        @case('back')
            <path d="M19 12H5M11 6l-6 6 6 6"/>
            @break
        @case('search')
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            @break
        @case('message')
            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8M8 13h5"/>
            @break
        {{-- Added for the signed-in pages (app-shell). --}}
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break
        @case('chart')
            <path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/>
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break
        @case('bell')
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>
            @break
        @case('upload')
            <path d="M12 16V4M7 9l5-5 5 5"/><path d="M20 16v4H4v-4"/>
            @break
        @case('lock')
            <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
            @break
        @case('file')
            <path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>
            @break
        @case('link')
            <path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>
            @break
        @case('alert')
            <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/>
            @break
        @case('help')
            <circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17v.01"/>
            @break
        @case('refresh')
            <path d="M20 11a8 8 0 0 0-14.7-4.4L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14.7 4.4L20 16"/><path d="M20 20v-4h-4"/>
            @break
        @case('x')
            <path d="M6 6l12 12M18 6 6 18"/>
            @break
        @case('flag')
            <path d="M5 21V4M5 4h11l-2 4 2 4H5"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
            @break
        @case('logout')
            <path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
            @break
        @case('copy')
            <rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/>
            @break
    @endswitch
</svg>
