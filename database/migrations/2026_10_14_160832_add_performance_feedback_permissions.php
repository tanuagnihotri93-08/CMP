<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $createPermission = Permission::firstOrCreate(
            ['name' => 'performance.feedback.create'],
            ['description' => 'Create performance feedback']
        );

        $viewPermission = Permission::firstOrCreate(
            ['name' => 'performance.feedback.view'],
            ['description' => 'View performance feedback']
        );

        Role::whereIn('name', ['super_admin', 'manager'])
            ->get()
            ->each(function ($role) use ($createPermission) {
                $role->permissions()->syncWithoutDetaching([
                    $createPermission->id
                ]);
            });

        Role::whereIn('name', ['super_admin', 'manager', 'employee'])
            ->get()
            ->each(function ($role) use ($viewPermission) {
                $role->permissions()->syncWithoutDetaching([
                    $viewPermission->id
                ]);
            });
    }

    public function down(): void
    {
        foreach ([
            'performance.feedback.create',
            'performance.feedback.view',
        ] as $permissionName) {

            $permission = Permission::where('name', $permissionName)->first();

            if ($permission) {
                $permission->roles()->detach();
                $permission->delete();
            }
        }
    }
};