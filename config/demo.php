<?php

// Demo accounts for testing the prototype. Seeded by DatabaseSeeder and listed on the sign-in page.
// Names match the fictional people the pages already show (App\Support\ZinaDemoData).
// Never seeded or shown in production; set DEMO_ACCOUNTS=false to hide them anywhere else.

return [
    'enabled' => (bool) env('DEMO_ACCOUNTS', env('APP_ENV', 'production') !== 'production'),

    'password' => 'demo1234',

    'accounts' => [
        ['name' => 'Aya Hassan', 'email' => 'student@demo.milestone.test', 'role' => 'Student', 'project_type' => 'miu', 'label' => 'Student · university projects'],
        ['name' => 'Omar Khaled', 'email' => 'independent@demo.milestone.test', 'role' => 'Student', 'project_type' => 'independent', 'label' => 'Student · independent projects'],
        ['name' => 'Sara Ahmed', 'email' => 'ta@demo.milestone.test', 'role' => 'TA', 'project_type' => 'miu', 'label' => 'Teaching assistant'],
        ['name' => 'Professor Lee', 'email' => 'professor@demo.milestone.test', 'role' => 'Professor', 'project_type' => 'miu', 'label' => 'Professor'],
        ['name' => 'University Admin', 'email' => 'admin@demo.milestone.test', 'role' => 'Admin', 'project_type' => 'miu', 'label' => 'Administrator'],
    ],
];
