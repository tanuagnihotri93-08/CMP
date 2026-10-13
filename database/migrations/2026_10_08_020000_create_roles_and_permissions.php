<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PERMISSIONS = [
        'dashboard.view' => 'View CRM dashboard',
        'records.view_all' => 'View all company records',
        'records.assign' => 'Assign records to active users',
        'leads.view' => 'View leads',
        'leads.create' => 'Create leads',
        'leads.update' => 'Update leads',
        'leads.delete' => 'Delete leads',
        'customers.view' => 'View customers',
        'customers.create' => 'Create customers',
        'customers.update' => 'Update customers',
        'customers.delete' => 'Delete customers',
        'follow_ups.view' => 'View follow ups',
        'follow_ups.create' => 'Create follow ups',
        'follow_ups.update' => 'Update follow ups',
        'follow_ups.delete' => 'Delete follow ups',
        'follow_ups.complete' => 'Complete follow ups',
        'activities.view' => 'View activities',
        'activities.create' => 'Create activities',
        'activities.update' => 'Update activities',
        'activities.delete' => 'Delete activities',
        'activities.complete' => 'Complete activities',
        'reports.view' => 'View reports',
        'audit_logs.view' => 'View audit logs',
        'users.manage' => 'Manage CRM users',
        'roles.manage' => 'Manage roles and permissions',
        'system.manage' => 'Manage system settings',
    ];

    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true);
        });

        $now = now();
        foreach (['super_admin' => 'Super Admin', 'manager' => 'Manager', 'employee' => 'Employee'] as $name => $displayName) {
            DB::table('roles')->insert([
                'name' => $name,
                'display_name' => $displayName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::PERMISSIONS as $name => $description) {
            DB::table('permissions')->insert([
                'name' => $name,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $roleIds = DB::table('roles')->pluck('id', 'name');
        $permissionIds = DB::table('permissions')->pluck('id', 'name');
        $managerPermissions = [
            'dashboard.view', 'records.view_all', 'records.assign',
            'leads.view', 'leads.create', 'leads.update',
            'customers.view', 'customers.create', 'customers.update',
            'follow_ups.view', 'follow_ups.create', 'follow_ups.update', 'follow_ups.delete', 'follow_ups.complete',
            'activities.view', 'activities.create', 'activities.update', 'activities.delete', 'activities.complete',
            'reports.view',
        ];
        $employeePermissions = [
            'dashboard.view',
            'leads.view', 'leads.create', 'leads.update',
            'customers.view',
            'follow_ups.view', 'follow_ups.create', 'follow_ups.update', 'follow_ups.complete',
            'activities.view', 'activities.create', 'activities.update', 'activities.complete',
        ];

        foreach ([
            'super_admin' => array_keys(self::PERMISSIONS),
            'manager' => $managerPermissions,
            'employee' => $employeePermissions,
        ] as $role => $permissions) {
            foreach ($permissions as $permission) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionIds[$permission],
                    'role_id' => $roleIds[$role],
                ]);
            }
        }

        $legacyRoleId = $roleIds['manager'];
        DB::table('users')
            ->whereNull('role_id')
            ->update(['role_id' => $legacyRoleId]);

        if (Schema::hasColumn('users', 'is_admin')) {
            DB::table('users')
                ->where('is_admin', true)
                ->update(['role_id' => $roleIds['super_admin']]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
