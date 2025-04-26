<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'New', 'color' => '#0d6efd', 'order' => 1],
            ['name' => 'Contacted', 'color' => '#6c757d', 'order' => 2],
            ['name' => 'Qualified', 'color' => '#198754', 'order' => 3],
            ['name' => 'Proposal', 'color' => '#fd7e14', 'order' => 4],
            ['name' => 'Negotiation', 'color' => '#ffc107', 'order' => 5],
            ['name' => 'Won', 'color' => '#20c997', 'order' => 6],
            ['name' => 'Lost', 'color' => '#dc3545', 'order' => 7],
            ['name' => 'Dormant', 'color' => '#6f42c1', 'order' => 8],
        ];

        foreach ($statuses as $status) {
            LeadStatus::updateOrCreate(
                ['name' => $status['name']],
                [
                    'color' => $status['color'],
                    'order' => $status['order'],
                    'active' => true
                ]
            );
        }

        // Create a few inactive statuses for testing
        if (app()->environment() !== 'production') {
            LeadStatus::factory(3)
                ->inactive()
                ->create(['order' => function() {
                    return LeadStatus::max('order') + 1;
                }]);
        }
    }
}