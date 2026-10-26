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
        $demoAccounts = [
            ['name' => 'Student Demo', 'email' => 'student@demo.milestone.test', 'role' => 'student', 'project_type' => 'independent'],
            ['name' => 'TA Demo', 'email' => 'ta@demo.milestone.test', 'role' => 'ta', 'project_type' => 'miu'],
            ['name' => 'Professor Demo', 'email' => 'professor@demo.milestone.test', 'role' => 'professor', 'project_type' => 'miu'],
            ['name' => 'Admin Demo', 'email' => 'admin@demo.milestone.test', 'role' => 'admin', 'project_type' => 'miu'],
        ];

        foreach ($demoAccounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    ...$account,
                    'password' => Hash::make('demo1234'),
                    'status' => 'active',
                ],
            );
        }

        $this->call(ProblemReportSeeder::class);
    }
}
