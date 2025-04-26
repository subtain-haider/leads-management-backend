<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Email', 'icon' => 'mail', 'color' => '#0d6efd'],
            ['name' => 'Call', 'icon' => 'phone', 'color' => '#198754'],
            ['name' => 'Meeting', 'icon' => 'users', 'color' => '#6f42c1'],
            ['name' => 'Task', 'icon' => 'check-square', 'color' => '#fd7e14'],
            ['name' => 'Note', 'icon' => 'file-text', 'color' => '#6c757d'],
            ['name' => 'SMS', 'icon' => 'message-square', 'color' => '#20c997'],
            ['name' => 'Social Media', 'icon' => 'instagram', 'color' => '#e83e8c'],
            ['name' => 'Demo', 'icon' => 'monitor', 'color' => '#343a40'],
            ['name' => 'Follow Up', 'icon' => 'repeat', 'color' => '#fd7e14'],
            ['name' => 'Quote', 'icon' => 'file', 'color' => '#20c997'],
            ['name' => 'Invoice', 'icon' => 'credit-card', 'color' => '#0d6efd'],
            ['name' => 'Proposal', 'icon' => 'briefcase', 'color' => '#6f42c1'],
        ];

        foreach ($types as $type) {
            ActivityType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'icon' => $type['icon'],
                    'color' => $type['color'],
                    'active' => true
                ]
            );
        }
    }
}