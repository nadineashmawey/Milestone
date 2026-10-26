<?php

// The signed-in navbar (<x-navbar />), one menu per role, as in the design's roleNav.
// Each item: label, icon (public/partials/icon), route name, optional route params, optional
// "active" route patterns (defaults to the route itself) and an optional badge key from "badges".
// Items whose route doesn't exist yet render as a disabled "Coming soon" entry.

return [
    'roles' => [
        'student' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'student.dashboard'],
            ['label' => 'My projects', 'icon' => 'folder', 'route' => 'projects.index', 'active' => ['projects.*', 'student.projects.*']],
            ['label' => 'Tasks', 'icon' => 'check', 'route' => 'student.tasks.index', 'active' => ['student.tasks.*']],
            ['label' => 'Milestones', 'icon' => 'clock', 'route' => 'student.milestones.index', 'params' => ['project' => 'software-engineering-capstone'], 'active' => ['student.milestones.*']],
            ['label' => 'Team', 'icon' => 'users', 'route' => 'student.team.show', 'params' => ['project' => 'software-engineering-capstone'], 'active' => ['student.team.*']],
        ],
        'professor' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'professor.dashboard'],
            ['label' => 'Courses', 'icon' => 'folder', 'route' => 'professor.courses.index'],
            ['label' => 'Create project', 'icon' => 'plus', 'route' => 'professor.projects.create'],
            ['label' => 'Teams', 'icon' => 'users', 'route' => 'professor.teams.index', 'active' => ['professor.teams.*']],
            ['label' => 'Submissions', 'icon' => 'check', 'route' => 'professor.submissions.index', 'badge' => 'submissions'],
            ['label' => 'Risk Radar', 'icon' => 'chart', 'route' => 'professor.risk-radar.index'],
            ['label' => 'Extensions', 'icon' => 'calendar', 'route' => 'professor.extensions.index'],
            ['label' => 'Replay', 'icon' => 'clock', 'route' => 'professor.replay.index'],
        ],
        'ta' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'ta.dashboard'],
            ['label' => 'Reviews', 'icon' => 'check', 'route' => 'ta.reviews', 'badge' => 'reviews'],
            ['label' => 'Issues', 'icon' => 'bell', 'route' => 'ta.issues'],
            ['label' => 'Invitations', 'icon' => 'users', 'route' => 'ta.assignments', 'badge' => 'invitations'],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard'],
            ['label' => 'Users', 'icon' => 'users', 'route' => 'admin.users'],
            ['label' => 'Imports', 'icon' => 'upload', 'route' => 'admin.imports', 'active' => ['admin.imports*']],
            ['label' => 'Courses & terms', 'icon' => 'calendar', 'route' => 'admin.courses'],
            ['label' => 'Project records', 'icon' => 'folder', 'route' => 'admin.projects'],
            ['label' => 'Announcements', 'icon' => 'bell', 'route' => 'admin.announcements'],
            ['label' => 'Audit log', 'icon' => 'clock', 'route' => 'admin.audit'],
        ],
    ],

    // Badge counts (fictional prototype numbers until real data exists). "label" is read after the count by screen readers.
    'badges' => [
        'submissions' => ['count' => 7, 'label' => 'waiting'],
        'reviews' => ['count' => 5, 'label' => 'to review'],
        'invitations' => ['count' => 1, 'label' => 'pending'],
    ],

    'role_labels' => ['student' => 'Student', 'professor' => 'Professor', 'ta' => 'TA', 'admin' => 'Admin'],
];
