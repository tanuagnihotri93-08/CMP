<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::with('role')->orderBy('name')->paginate(25);
        $roles = Role::orderBy('id')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot change your own account role or status.');

        $validated = $request->validate([
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'is_active' => ['required', 'boolean'],
        ]);

        $newRole = Role::findOrFail($validated['role_id']);
        $willBeActive = (bool) $validated['is_active'];

        if ($user->role?->name === 'super_admin' && ($newRole->name !== 'super_admin' || !$willBeActive)) {
            $remainingAdmins = User::whereHas('role', fn ($query) => $query->where('name', 'super_admin'))
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->exists();
            abort_unless($remainingAdmins, 422, 'At least one active Super Admin must remain.');
        }

        $oldRole = $user->role;
        $oldActive = (bool) $user->is_active;

        $user->forceFill([
            'role_id' => $newRole->id,
            'is_admin' => $newRole->name === 'super_admin',
            'is_active' => $willBeActive,
        ])->save();

        if ($oldRole?->id !== $newRole->id) {
            AuditLog::record(
                'role_changed',
                'users',
                User::class,
                (int) $user->id,
                "Role changed for {$user->name}.",
                ['role' => $oldRole?->name],
                ['role' => $newRole->name]
            );
        }

        if ($oldActive !== $willBeActive) {
            AuditLog::record(
                $willBeActive ? 'activated' : 'deactivated',
                'users',
                User::class,
                (int) $user->id,
                "User {$user->name} was ".($willBeActive ? 'activated' : 'deactivated').'.',
                ['is_active' => $oldActive],
                ['is_active' => $willBeActive]
            );
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User access updated successfully.');
    }
}
