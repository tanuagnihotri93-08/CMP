<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;

class LeadDummySeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'New',
            'Contacted',
            'Proposal',
            'Won',
            'Lost'
        ];

        $sources = [
            'Website',
            'Referral',
            'LinkedIn',
            'Advertisement',
            'Cold Call'
        ];

        for ($i = 1; $i <= 60; $i++) {

            Lead::create([
                'name' => 'Demo Lead '.$i,
                'company_name' => 'Company '.$i,
                'email' => 'lead'.$i.'@example.com',
                'phone' => '9876543'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'source' => $sources[array_rand($sources)],
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'Dummy lead created for CRM testing.',
                'assigned_to_user_id' => $i <= 30 ? 20 : 21,
            ]);
        }
    }
}