<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\User;

class LeadDummySeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'new',
            'qualified',
            'proposal',
            'won',
            'lost',
        ];

        $sources = [
            'Website',
            'Referral',
            'Social Media',
            'Direct',
        ];

        $assignableUserIds = User::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id');

        for ($i = 1; $i <= 60; $i++) {

            Lead::updateOrCreate(
                ['email' => 'lead'.$i.'@example.com'],
                [
                    'name' => 'Demo Lead '.$i,
                    'company_name' => 'Company '.$i,
                    'phone' => '9876543'.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'source' => $sources[array_rand($sources)],
                    'status' => $statuses[array_rand($statuses)],
                    'notes' => 'Dummy lead created for CRM testing.',
                    'assigned_to_user_id' => $assignableUserIds->isNotEmpty()
                        ? $assignableUserIds[($i - 1) % $assignableUserIds->count()]
                        : null,
                ]
            );
        }
    }
}
