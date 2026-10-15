<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'performance.view'],
            ['description' => 'View team performance dashboard']
        );

        Role::whereIn('name', ['super_admin', 'manager'])
            ->get()
            ->each(fn ($role) => $role->permissions()->syncWithoutDetaching([$permission->id]));
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'performance.view')->first();

        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
};