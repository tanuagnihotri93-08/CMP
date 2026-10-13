@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page crm-page roles-admin">
    <div class="page-header roles-admin-header">
        <div>
            <span class="roles-admin-eyebrow"><i class="bi bi-shield-lock" aria-hidden="true"></i> ACCESS GOVERNANCE</span>
            <h1>Roles &amp; Permissions</h1>
            <p>Review role access and configure permissions for your CRM teams</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="module-secondary-btn roles-admin-manage-users">
            <i class="bi bi-people" aria-hidden="true"></i>
            Manage Users
        </a>
    </div>

    @if(session('success'))
        <div class="module-alert" role="status">{{ session('success') }}</div>
    @endif

    <div class="roles-admin-intro crm-card">
        <div>
            <span class="roles-admin-intro-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
            <div>
                <strong>Role-based access</strong>
                <p>Permission changes apply to every user assigned to that role.</p>
            </div>
        </div>
        <span class="roles-admin-total">{{ $roles->count() }} {{ \Illuminate\Support\Str::plural('role', $roles->count()) }}</span>
    </div>

    @if($roles->isNotEmpty())
        <div class="module-card lead-filter-card crm-card crm-filter-card roles-admin-filter-card" data-crm-card-filter>
            <div class="lead-filter-heading">
                <div>
                    <h2>Find Roles</h2>
                    <small>Search role names and assigned capabilities</small>
                </div>
            </div>
            <div class="lead-filter-controls">
                <label class="lead-search-control">
                    <span class="visually-hidden">Search roles and permissions</span>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" data-crm-filter-search placeholder="Search roles..." autocomplete="off">
                </label>
                <label>
                    <span class="visually-hidden">Filter by role</span>
                    <select data-crm-filter-role>
                        <option value="">All roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    @endif

    <section class="roles-admin-grid" aria-label="CRM roles">
        @forelse($roles as $role)
            <article class="roles-admin-card crm-card crm-filterable-row"
                data-crm-filter-search="{{ mb_strtolower($role->display_name.' '.$role->name.' '.$role->permissions->pluck('description')->implode(' ')) }}"
                data-crm-filter-role="{{ $role->name }}">
                <div class="roles-admin-card-header">
                    <div>
                        <span class="roles-admin-card-kicker">ROLE PROFILE</span>
                        <div class="roles-admin-role-title">
                            <span class="roles-admin-role-badge crm-badge roles-admin-role-{{ $role->name }}">
                                <span aria-hidden="true"></span>
                                {{ $role->display_name }}
                            </span>
                        </div>
                        <p class="roles-admin-role-description">
                            @switch($role->name)
                                @case('super_admin') Full system access across the CRM. @break
                                @case('manager') Operational access for managing CRM teams and records. @break
                                @case('employee') Restricted access based on assigned work and permissions. @break
                                @default Configurable access for this role.
                            @endswitch
                        </p>
                    </div>
                    @if($role->name === 'super_admin')
                        <span class="roles-admin-lock" title="System role is protected">
                            <i class="bi bi-lock-fill" aria-hidden="true"></i>
                            Locked
                        </span>
                    @else
                        <span class="roles-admin-configurable">Configurable</span>
                    @endif
                </div>

                <div class="roles-admin-metrics">
                    <div class="roles-admin-metric-card">
                        <span class="roles-admin-metric-label"><i class="bi bi-people" aria-hidden="true"></i> Assigned users</span>
                        <strong>{{ $role->users_count }}</strong>
                    </div>
                    <div class="roles-admin-metric-card">
                        <span class="roles-admin-metric-label"><i class="bi bi-key" aria-hidden="true"></i> Permissions</span>
                        <strong>{{ $role->permissions->count() }}</strong>
                    </div>
                </div>

                <div class="roles-admin-permission-preview">
                    <h3 class="roles-admin-section-label"><i class="bi bi-grid-1x2" aria-hidden="true"></i> ACCESS OVERVIEW</h3>
                    <div class="roles-admin-preview-chips">
                        @forelse($role->permissions->take(4) as $permission)
                            <span class="roles-admin-permission-chip">{{ $permission->description }}</span>
                        @empty
                            <p class="roles-admin-no-permissions">No permissions assigned.</p>
                        @endforelse
                    </div>
                    @if($role->permissions->count() > 4)
                        <span class="roles-admin-more-permissions">+{{ $role->permissions->count() - 4 }} more permissions</span>
                    @endif
                </div>

                <div class="roles-admin-card-footer">
                    @if($role->name === 'super_admin')
                        <span class="roles-admin-protected-note">
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            Protected system role
                        </span>
                    @else
                        <a href="{{ route('admin.roles.edit', $role) }}" class="roles-admin-edit-btn">
                            <i class="bi bi-sliders" aria-hidden="true"></i>
                            Configure permissions
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="roles-admin-empty crm-card">
                <span class="roles-admin-empty-icon"><i class="bi bi-shield" aria-hidden="true"></i></span>
                <strong>No roles available</strong>
                <p>There are no role records to configure.</p>
            </div>
        @endforelse
        @if($roles->isNotEmpty())
            <div class="roles-admin-empty crm-card crm-filter-empty" role="status" aria-live="polite" hidden>
                <span class="roles-admin-empty-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
                <strong>No roles match</strong>
                <p>Try another search term or role filter.</p>
            </div>
        @endif
    </section>
</div>
@endsection
