<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        DB::transaction(function () use ($user, $newRole, $willBeActive): void {
            // Lock the active administrator set in a consistent order so concurrent
            // role changes cannot both pass the last-admin check.
            $activeSuperAdmins = User::query()
                ->where('is_active', true)
                ->whereHas('role', fn ($query) => $query->where('name', 'super_admin'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $oldRole = $lockedUser->role;
            $oldActive = (bool) $lockedUser->is_active;

            if ($oldRole?->name === 'super_admin' && ($newRole->name !== 'super_admin' || ! $willBeActive)) {
                $hasAnotherActiveAdmin = $activeSuperAdmins->contains(
                    fn (User $admin): bool => (int) $admin->id !== (int) $lockedUser->id
                );

                abort_unless($hasAnotherActiveAdmin, 422, 'At least one active Super Admin must remain.');
            }

            $lockedUser->forceFill([
                'role_id' => $newRole->id,
                'is_admin' => $newRole->name === 'super_admin',
                'is_active' => $willBeActive,
            ])->save();

            if ($oldRole?->id !== $newRole->id) {
                AuditLog::record(
                    'role_changed',
                    'users',
                    User::class,
                    (int) $lockedUser->id,
                    "Role changed for {$lockedUser->name}.",
                    ['role' => $oldRole?->name],
                    ['role' => $newRole->name]
                );
            }

            if ($oldActive !== $willBeActive) {
                AuditLog::record(
                    $willBeActive ? 'activated' : 'deactivated',
                    'users',
                    User::class,
                    (int) $lockedUser->id,
                    "User {$lockedUser->name} was ".($willBeActive ? 'activated' : 'deactivated').'.',
                    ['is_active' => $oldActive],
                    ['is_active' => $willBeActive]
                );
            }
        }, attempts: 3);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User access updated successfully.');
    }
}
