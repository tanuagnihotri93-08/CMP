<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $superAdminRole = Role::where('name', 'super_admin')->first();

        $managerRole = Role::where('name', 'manager')->first();

        $employeeRole = Role::where('name', 'employee')->first();

        User::updateOrCreate(
            [
                'email' => 'admin@crm.com',
            ],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'manager@crm.com',
            ],
            [
                'name' => 'CRM Manager',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'employee@crm.com',
            ],
            [
                'name' => 'CRM Employee',
                'password' => Hash::make('password'),
                'role_id' => $employeeRole->id,
                'is_active' => true,
            ]
        );

    }
}
