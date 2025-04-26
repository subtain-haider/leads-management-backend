<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'United States', 'code' => 'US', 'phone_code' => '+1'],
            ['name' => 'United Kingdom', 'code' => 'GB', 'phone_code' => '+44'],
            ['name' => 'Canada', 'code' => 'CA', 'phone_code' => '+1'],
            ['name' => 'Australia', 'code' => 'AU', 'phone_code' => '+61'],
            ['name' => 'Germany', 'code' => 'DE', 'phone_code' => '+49'],
            ['name' => 'France', 'code' => 'FR', 'phone_code' => '+33'],
            ['name' => 'Japan', 'code' => 'JP', 'phone_code' => '+81'],
            ['name' => 'China', 'code' => 'CN', 'phone_code' => '+86'],
            ['name' => 'India', 'code' => 'IN', 'phone_code' => '+91'],
            ['name' => 'Brazil', 'code' => 'BR', 'phone_code' => '+55'],
            ['name' => 'Mexico', 'code' => 'MX', 'phone_code' => '+52'],
            ['name' => 'Spain', 'code' => 'ES', 'phone_code' => '+34'],
            ['name' => 'Italy', 'code' => 'IT', 'phone_code' => '+39'],
            ['name' => 'Russia', 'code' => 'RU', 'phone_code' => '+7'],
            ['name' => 'South Africa', 'code' => 'ZA', 'phone_code' => '+27'],
            ['name' => 'Saudi Arabia', 'code' => 'SA', 'phone_code' => '+966'],
            ['name' => 'United Arab Emirates', 'code' => 'AE', 'phone_code' => '+971'],
            ['name' => 'Singapore', 'code' => 'SG', 'phone_code' => '+65'],
            ['name' => 'Malaysia', 'code' => 'MY', 'phone_code' => '+60'],
            ['name' => 'Netherlands', 'code' => 'NL', 'phone_code' => '+31'],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'phone_code' => $country['phone_code'],
                    'active' => true
                ]
            );
        }

        // Create additional random countries if we're not in production
        // if (app()->environment() !== 'production') {
        //     Country::factory(30)->create();
        // }
    }
}