<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        
        $this->call([
            CountrySeeder::class,
            LeadStatusSeeder::class,
            ActivityTypeSeeder::class,
            TagSeeder::class,
            LeadSourceSeeder::class,
        ]);

        if (app()->environment() !== 'production') {
            $this->call(LeadSeeder::class);
        }
    }
}