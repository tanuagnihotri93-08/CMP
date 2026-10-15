<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class SyncSpatiePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Sync Roles
        |--------------------------------------------------------------------------
        */

        $roleMap = [];

        foreach (Role::all() as $role) {

            $spatieRoleId = DB::table('spatie_roles')->insertGetId([
                'name' => $role->name,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roleMap[$role->id] = $spatieRoleId;
        }


        /*
        |--------------------------------------------------------------------------
        | Sync Permissions
        |--------------------------------------------------------------------------
        */

        $permissionMap = [];

        foreach (Permission::all() as $permission) {

            $spatiePermissionId = DB::table('spatie_permissions')->insertGetId([
                'name' => $permission->name,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissionMap[$permission->id] = $spatiePermissionId;
        }


        /*
        |--------------------------------------------------------------------------
        | Sync Role Permissions
        |--------------------------------------------------------------------------
        */

        $rolePermissions = DB::table('permission_role')->get();

        foreach ($rolePermissions as $item) {

            DB::table('spatie_role_has_permissions')->insert([
                'permission_id' => $permissionMap[$item->permission_id],
                'role_id' => $roleMap[$item->role_id],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Sync User Roles
        |--------------------------------------------------------------------------
        */

        foreach (User::whereNotNull('role_id')->get() as $user) {

            if (isset($roleMap[$user->role_id])) {

                DB::table('spatie_model_has_roles')->insert([
                    'role_id' => $roleMap[$user->role_id],
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);

            }
        }


    }
}