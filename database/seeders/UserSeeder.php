<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create test users for development
        if (app()->environment() !== 'production') {
            // Create specific test users
            $testUsers = [
                [
                    'name' => 'Sales Manager',
                    'email' => 'manager@example.com',
                    'password' => Hash::make('password'),
                ],
                [
                    'name' => 'Sales Rep 1',
                    'email' => 'sales1@example.com',
                    'password' => Hash::make('password'),
                ],
                [
                    'name' => 'Sales Rep 2',
                    'email' => 'sales2@example.com',
                    'password' => Hash::make('password'),
                ],
            ];

            foreach ($testUsers as $userData) {
                User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => $userData['password'],
                        'email_verified_at' => now(),
                    ]
                );
            }

            // Create additional random users for testing
            User::factory(20)->create();
        }
    }
}