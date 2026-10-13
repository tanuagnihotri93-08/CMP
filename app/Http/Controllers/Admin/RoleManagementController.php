<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->orderBy('id')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role): View
    {
        abort_if($role->name === 'super_admin', 403, 'Super Admin permissions cannot be changed.');
        $permissions = Permission::orderBy('name')->get();
        $assignedPermissions = $role->permissions->modelKeys();

        return view('admin.roles.edit', compact('role', 'permissions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'super_admin', 403, 'Super Admin permissions cannot be changed.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);

        $oldPermissions = $role->permissions()->orderBy('name')->pluck('name')->all();
        $permissionIds = $validated['permissions'] ?? [];
        $newPermissions = Permission::whereIn('id', $permissionIds)->orderBy('name')->pluck('name')->all();

        $role->permissions()->sync($permissionIds);

        if ($oldPermissions !== $newPermissions) {
            AuditLog::record(
                'permissions_changed',
                'roles',
                Role::class,
                (int) $role->id,
                "Permissions changed for {$role->display_name}.",
                ['permissions' => $oldPermissions],
                ['permissions' => $newPermissions]
            );
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role permissions updated successfully.');
    }
}
