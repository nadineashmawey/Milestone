import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/pages/home.css', 'resources/js/pages/home.js', 'resources/css/pages/app-shell.css', 'resources/js/pages/app-shell.js', 'resources/css/pages/student-dashboard.css', 'resources/css/pages/milestones.css', 'resources/css/pages/student-team.css', 'resources/css/pages/professor-dashboard.css'],
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
