@props(['name'])
@php
$paths = [
    'folder' => 'M3 7V5a1 1 0 0 1 1-1h5l2 3h9a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7Z',
    'calendar' => 'M5 3v4M19 3v4M3 10h18M4 5h16a1 1 0 0 1 1 1v14H3V6a1 1 0 0 1 1-1Z',
    'chart' => 'M4 3v17h17M7 15l4-5 4 3 5-7',
    'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M17 4a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-4M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z',
    'check' => 'm8 11 3 3 8-9M21 12a9 9 0 1 1-7-9',
    'trash' => 'M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7M14 10v7',
    'upload' => 'M12 16V3m-5 5 5-5 5 5M3 15v6h18v-6',
    'download' => 'M12 3v13m-5-5 5 5 5-5M3 18v3h18v-3',
    'file' => 'M5 2h9l5 5v15H5ZM14 2v6h5M8 12h8M8 16h8',
    'clock' => 'M12 8v4l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    'message' => 'M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z',
    'bell' => 'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4',
];
@endphp
<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['folder'] }}"/></svg>
