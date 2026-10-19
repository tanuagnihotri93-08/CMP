<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'analytics.view'],
            ['description' => 'View CRM analytics']
        );

        Role::query()
            ->whereIn('name', ['super_admin', 'manager'])
            ->get()
            ->each(fn (Role $role) => $role->permissions()->syncWithoutDetaching([$permission->id]));
    }

    public function down(): void
    {
        $permission = Permission::query()->where('name', 'analytics.view')->first();

        if (! $permission) {
            return;
        }

        $permission->roles()->detach();
        $permission->delete();
    }
};
