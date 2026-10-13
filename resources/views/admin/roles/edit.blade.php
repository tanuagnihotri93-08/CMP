@extends('layouts.app')

@section('content')
@php
    $permissionGroupLabels = [
        'dashboard' => 'Dashboard',
        'records' => 'Record governance',
        'leads' => 'Lead management',
        'customers' => 'Customer management',
        'follow_ups' => 'Follow-up management',
        'activities' => 'Activity management',
        'reports' => 'Reports and analytics',
        'audit_logs' => 'Audit logs',
        'users' => 'User administration',
        'roles' => 'Role administration',
        'system' => 'System settings',
    ];
    $groupedPermissions = $permissions->groupBy(
        fn ($permission) => $permissionGroupLabels[\Illuminate\Support\Str::before($permission->name, '.')] ?? 'Other permissions'
    );
    $selectedPermissionIds = collect(old('permissions', $assignedPermissions))
        ->map(fn ($id) => (int) $id)
        ->all();
    $permissionErrorsPresent = collect($errors->keys())
        ->contains(fn ($key) => $key === 'permissions' || str_starts_with($key, 'permissions.'));
@endphp
<div class="page-wrapper module-page crm-page roles-admin">
    <div class="page-header roles-admin-header">
        <div>
            <span class="roles-admin-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i> ACCESS GOVERNANCE</span>
            <h1>Edit {{ $role->display_name }} Permissions</h1>
            <p>Changes apply to every user assigned to this role.</p>
        </div>
        <a href="{{ route('admin.roles.index') }}" class="module-secondary-btn roles-admin-manage-users">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back to Roles
        </a>
    </div>

    <x-crm.validation-alert title="Review the permission selections." />

    <div class="roles-admin-edit-summary">
        <span class="roles-admin-role-badge crm-badge roles-admin-role-{{ $role->name }}">
            <span aria-hidden="true"></span>
            {{ $role->display_name }}
        </span>
        <span>{{ $permissions->count() }} available permissions</span>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="roles-admin-form" novalidate>
        @csrf
        @method('PUT')
        <x-crm.field-error field="permissions" />

        @forelse($groupedPermissions as $groupName => $groupPermissions)
            @php($groupIcon = match ($groupName) {
                'Dashboard' => 'bi-speedometer2',
                'Record governance' => 'bi-diagram-3',
                'Lead management' => 'bi-person-lines-fill',
                'Customer management' => 'bi-buildings',
                'Follow-up management' => 'bi-calendar-check',
                'Activity management' => 'bi-activity',
                'Reports and analytics' => 'bi-bar-chart-line',
                'Audit logs' => 'bi-clock-history',
                'User administration' => 'bi-people',
                'Role administration' => 'bi-shield-check',
                'System settings' => 'bi-gear',
                default => 'bi-grid',
            })
            <section class="roles-admin-permission-group crm-card" aria-labelledby="permission-group-{{ $loop->index }}">
                <header class="roles-admin-group-header">
                    <span class="roles-admin-group-icon" aria-hidden="true"><i class="bi {{ $groupIcon }}"></i></span>
                    <div>
                        <h2 id="permission-group-{{ $loop->index }}">{{ $groupName }}</h2>
                        <p>Choose the capabilities this role can use.</p>
                    </div>
                    <span class="roles-admin-group-count">{{ $groupPermissions->count() }} {{ \Illuminate\Support\Str::plural('permission', $groupPermissions->count()) }}</span>
                </header>

                <div class="roles-admin-permission-list">
                    @foreach($groupPermissions as $permission)
                        @php($isEnabled = in_array($permission->id, $selectedPermissionIds, true))
                        <label class="roles-admin-permission-row {{ $isEnabled ? 'is-enabled' : '' }}">
                            <span class="roles-admin-permission-copy">
                                <strong>{{ $permission->description }}</strong>
                                <small>{{ $permission->name }}</small>
                            </span>
                            <span class="roles-admin-permission-state">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    aria-label="{{ $permission->description }}"
                                    aria-invalid="{{ $permissionErrorsPresent ? 'true' : 'false' }}"
                                    @if($permissionErrorsPresent) aria-describedby="crm-error-permissions" @endif
                                    @checked($isEnabled)>
                                <span class="roles-admin-switch" aria-hidden="true"></span>
                                <span class="roles-admin-state-label" aria-hidden="true"></span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="roles-admin-empty crm-card">
                <span class="roles-admin-empty-icon"><i class="bi bi-shield" aria-hidden="true"></i></span>
                <strong>No permissions defined</strong>
                <p>There are no permissions available to assign to this role.</p>
            </section>
        @endforelse

        <div class="roles-admin-form-footer">
            <p><i class="bi bi-info-circle" aria-hidden="true"></i> Updates are recorded in the audit log.</p>
            <div>
                <a href="{{ route('admin.roles.index') }}" class="module-secondary-btn">Cancel</a>
                <button type="submit" class="roles-admin-save-btn">
                    <i class="bi bi-check2" aria-hidden="true"></i>
                    Save permissions
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
