<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo accounts for testing (config/demo.php). Never created in production.
        if (config('demo.enabled') && ! app()->environment('production')) {
            foreach (config('demo.accounts') as $account) {
                [$first, $last] = array_pad(explode(' ', $account['name'], 2), 2, '');

                User::updateOrCreate(
                    ['email' => $account['email']],
                    [
                        'name' => $account['name'],
                        'first_name' => $first,
                        'last_name' => $last,
                        'role' => $account['role'],
                        'project_type' => $account['project_type'],
                        'password' => Hash::make(config('demo.password')),
                        'status' => 'active',
                    ],
                );
            }

            $this->call(ProjectSeeder::class);
        }

        $this->call(ProblemReportSeeder::class);
    }
}
