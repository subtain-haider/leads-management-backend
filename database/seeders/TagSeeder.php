<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Hot Lead', 'color' => '#dc3545'],
            ['name' => 'Cold Lead', 'color' => '#0d6efd'],
            ['name' => 'VIP', 'color' => '#ffc107'],
            ['name' => 'Follow Up', 'color' => '#fd7e14'],
            ['name' => 'Returning Customer', 'color' => '#198754'],
            ['name' => 'Referral', 'color' => '#6f42c1'],
            ['name' => 'Government', 'color' => '#20c997'],
            ['name' => 'Enterprise', 'color' => '#6c757d'],
            ['name' => 'SMB', 'color' => '#0dcaf0'],
            ['name' => 'Startup', 'color' => '#d63384'],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['name' => $tag['name']],
                ['color' => $tag['color']]
            );
        }
    }
}