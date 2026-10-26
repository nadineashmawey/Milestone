<?php

// Content for the public Home page (/) and Help center (/help).
// Copy matches Zina's approved design in docs/design-reference/.

$faqs = [
    'own-projects' => ['Can students create their own projects?', 'Yes. Independent projects use the same task, team, file and calendar tools, without professor supervision, submissions or grades. Any student can start one and invite up to 10 members.'],
    'who-submits' => ['Who can submit academic milestones?', 'Only the team leader assigned by the professor or TA. They can edit and resubmit before the deadline, and every version keeps its submitter and timestamp.'],
    'team-deadline' => ['What happens at the team-choosing deadline?', 'Teams lock and pending requests are cancelled. Unassigned students are placed automatically or by the professor, depending on the mode the professor chose.'],
    'leave-team' => ['Can I leave my team?', 'Not on your own. You send a leave request to the professor, and you stay on the team until they approve it.'],
    'ai-grading' => ['Does the AI grade students?', 'No. Risk and contribution signals are evidence for the professor, clearly labelled as simulated in this prototype. Grades are always set and released by the professor.'],
    'grade-changes' => ['Can a finalised grade be changed?', 'No. Once the professor finalises a grade it is locked. Disputes are handled by email with the professor, outside Milestone.'],
];

return [

    'nav' => [
        ['href' => '#how', 'label' => 'How it works'],
        ['href' => '#types', 'label' => 'Project types'],
        ['href' => '#roles', 'label' => 'Roles'],
        ['href' => '#faq', 'label' => 'FAQ'],
    ],

    'facts' => [
        ['value' => '4', 'label' => 'roles with their own permissions'],
        ['value' => '2', 'label' => 'project types, one set of tools'],
        ['value' => 'Every', 'label' => 'submission version kept with its author'],
        ['value' => '0', 'label' => 'grades decided by the AI'],
    ],

    'steps' => [
        ['title' => 'Set the structure', 'text' => 'The professor publishes the project once: milestones, deliverable types, team size limits, the team-choosing deadline and the grading total.', 'detail' => 'Professor · Create project'],
        ['title' => 'Work visibly', 'text' => 'Teams form before the deadline, then plan on a four-column board. Every status change, file version and blocker is recorded with who and when.', 'detail' => 'Students · Tasks, files, team'],
        ['title' => 'Review with context', 'text' => 'The team leader submits. TAs comment or review within their permissions, and only the professor scores the rubric and releases the grade.', 'detail' => 'TA and professor · Reviews'],
    ],

    'project_types' => [
        'academic' => [
            'badge' => 'Academic',
            'name' => 'Academic project',
            'tagline' => 'Run by a course. Supervised, submitted and graded.',
            'rows' => [
                'Created by' => 'The professor, inside a course and semester',
                'Team leader' => 'Assigned by the professor or TA (no voting)',
                'Team size' => 'Minimum and maximum set by the professor',
                'Submissions' => 'Leader submits each milestone; late work is flagged, not penalised',
                'Grades' => 'Professor only, per team, locked once finalised',
                'After the course' => 'Read-only 30 days after the semester ends',
            ],
        ],
        'independent' => [
            'badge' => 'Independent',
            'name' => 'Independent project',
            'tagline' => 'Started by any student. Same tools, no grading.',
            'rows' => [
                'Created by' => 'Any student, with no approval needed',
                'Team leader' => 'The creator, who can transfer leadership',
                'Team size' => 'Up to 10 members, by invite, link or code',
                'Submissions' => 'None. Teams plan and deliver on their own terms',
                'Grades' => 'None. No professor or TA involvement',
                'After the course' => 'Kept for as long as the team needs it',
            ],
        ],
    ],

    'roles' => [
        ['name' => 'Professor', 'tone' => 'blue', 'icon' => 'folder', 'points' => ['Publish projects and milestones', 'Lock teams and assign leaders', 'Score rubrics and release grades']],
        ['name' => 'Teaching assistant', 'tone' => 'violet', 'icon' => 'check', 'points' => ['Review assigned submissions', 'Write feedback within permissions', 'Report project issues']],
        ['name' => 'Student', 'tone' => 'green', 'icon' => 'users', 'points' => ['Join a team before the deadline', 'Track tasks and deadlines', 'Run independent projects too']],
        ['name' => 'Administrator', 'tone' => 'yellow', 'icon' => 'grid', 'points' => ['Import users from CSV', 'Manage courses and terms', 'Keep an immutable audit log']],
    ],

    'faqs' => array_values($faqs),

    'help_topics' => [
        'Accounts and sign in' => [
            ['Which email should I use?', 'Your university email. Student, professor and TA accounts must use the university domain, and professor and TA roles are verified by an administrator.'],
            ['I forgot my password.', 'Choose “Forgot password” on the sign-in page. A reset link is sent to your university email and expires after a short time.'],
            ['Why can’t I change my email or student ID?', 'They identify you across courses, so only an administrator can change them. You can request a correction from your profile.'],
        ],
        'Teams' => [$faqs['team-deadline'], $faqs['leave-team']],
        'Milestones and grades' => [$faqs['who-submits'], $faqs['ai-grading'], $faqs['grade-changes']],
        'Independent projects' => [
            $faqs['own-projects'],
            ['How do people join my independent project?', 'Invite them by email or username, or share the project code or link. You approve each join request, up to 10 members.'],
        ],
    ],

    // Keys must match App\Models\ProblemReport::CATEGORIES.
    'report_categories' => [
        'cant_sign_in' => 'Can’t sign in',
        'team_membership' => 'Team or membership',
        'submission_upload' => 'Submission or upload',
        'grades_feedback' => 'Grades or feedback',
        'other' => 'Something else',
    ],

];
