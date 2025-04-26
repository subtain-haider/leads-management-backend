<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            ['name' => 'Website Contact Form', 'description' => 'Leads generated from the contact form on our website'],
            ['name' => 'Direct Email', 'description' => 'Leads who emailed the company directly'],
            ['name' => 'Phone Inquiry', 'description' => 'Leads who called the company phone number'],
            ['name' => 'Google Ads', 'description' => 'Leads from Google Ads campaigns'],
            ['name' => 'Facebook Ads', 'description' => 'Leads from Facebook Ads campaigns'],
            ['name' => 'LinkedIn Ads', 'description' => 'Leads from LinkedIn Ads campaigns'],
            ['name' => 'Trade Show', 'description' => 'Leads collected during trade shows and events'],
            ['name' => 'Referral', 'description' => 'Leads referred by existing customers or partners'],
            ['name' => 'Cold Outreach', 'description' => 'Leads generated from cold calling or emailing campaigns'],
            ['name' => 'Content Download', 'description' => 'Leads who downloaded gated content from our website'],
            ['name' => 'Webinar Registration', 'description' => 'Leads who registered for a company webinar'],
            ['name' => 'Partner Referral', 'description' => 'Leads referred by business partners'],
        ];

        foreach ($sources as $source) {
            LeadSource::updateOrCreate(
                ['name' => $source['name']],
                [
                    'description' => $source['description'],
                    'active' => true,
                ]
            );
        }
    }
}