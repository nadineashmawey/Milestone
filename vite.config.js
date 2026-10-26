import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/student-projects.css',
                'resources/js/app.js',
                'resources/js/student-project-board.js',
                'resources/js/student-project-portal.js',
                'resources/css/pages/home.css',
                'resources/js/pages/home.js',
                'resources/css/pages/app-shell.css',
                'resources/js/pages/app-shell.js',
                'resources/css/pages/student-dashboard.css',
                'resources/css/pages/milestones.css',
                'resources/css/pages/student-team.css',
                'resources/css/pages/professor-dashboard.css',
                'resources/css/pages/profile.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
