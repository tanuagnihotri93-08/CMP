@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page crm-page user-admin">
    <div class="page-header user-admin-header">
        <div>
            <span class="user-admin-eyebrow">ADMINISTRATION</span>
            <h1>User Management</h1>
            <p>Assign CRM roles and manage account access</p>
        </div>
        @if(auth()->user()->hasPermission('roles.manage'))
            <a href="{{ route('admin.roles.index') }}" class="module-secondary-btn user-admin-manage-roles">Manage Roles</a>
        @endif
    </div>

    @if(session('success'))<div class="module-alert" role="status">{{ session('success') }}</div>@endif
    <x-crm.validation-alert title="Unable to update user access." />

    <section class="user-admin-card crm-card crm-table-card" aria-labelledby="user-directory-heading">
        <div class="user-admin-card-heading">
            <div>
                <h2 id="user-directory-heading">User directory</h2>
                <p>{{ $users->total() }} {{ \Illuminate\Support\Str::plural('account', $users->total()) }} across the CRM. Search filters the current page.</p>
            </div>
            <div class="user-admin-filters crm-filter-card" role="search" aria-label="Filter users">
                <label class="user-admin-search">
                    <span class="visually-hidden">Search users by name or email</span>
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" id="user-search" placeholder="Search name or email" autocomplete="off">
                </label>
                <label>
                    <span class="visually-hidden">Filter by role</span>
                    <select id="user-role-filter">
                        <option value="">All roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="visually-hidden">Filter by account status</span>
                    <select id="user-status-filter">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="user-admin-table-wrap">
            <table class="module-table user-admin-table">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Role</th>
                        <th scope="col">Account status</th>
                        <th scope="col">Access settings</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    @forelse($users as $managedUser)
                        <tr
                            class="user-admin-row"
                            data-user-search="{{ mb_strtolower($managedUser->name.' '.$managedUser->email) }}"
                            data-user-role="{{ $managedUser->role_id }}"
                            data-user-status="{{ $managedUser->is_active ? 'active' : 'inactive' }}">
                            <td data-label="User">
                                <div class="user-admin-identity">
                                    <span class="user-admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($managedUser->name, 0, 1)) }}</span>
                                    <span>
                                        <strong>{{ $managedUser->name }}</strong>
                                        <small>{{ $managedUser->email }}</small>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Role">
                                <span class="user-role-badge crm-badge user-role-{{ $managedUser->role?->name ?? 'unassigned' }}">
                                    {{ $managedUser->role?->display_name ?? 'Unassigned' }}
                                </span>
                            </td>
                            <td data-label="Account status">
                                <span class="user-status-badge crm-badge {{ $managedUser->is_active ? 'is-active' : 'is-inactive' }}">
                                    <span aria-hidden="true"></span>
                                    {{ $managedUser->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td data-label="Access settings">
                                @if(auth()->id() === $managedUser->id)
                                    <span class="user-admin-self-note">Your account</span>
                                @else
                                    @php($validationTargetMatches = (string) old('validation_target_user_id') === (string) $managedUser->id)
                                    <form action="{{ route('admin.users.update', $managedUser) }}" method="POST" class="user-admin-access-form" novalidate>
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="validation_target_user_id" value="{{ $managedUser->id }}">
                                        <label>
                                            <span class="visually-hidden">Role for {{ $managedUser->name }}</span>
                                            <select name="role_id" aria-label="Role for {{ $managedUser->name }}" @class(['crm-invalid-field' => $validationTargetMatches && $errors->has('role_id')]) aria-invalid="{{ $validationTargetMatches && $errors->has('role_id') ? 'true' : 'false' }}" @if($validationTargetMatches && $errors->has('role_id')) aria-describedby="crm-error-role-id-{{ $managedUser->id }}" @endif>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" @selected((int) ($validationTargetMatches ? old('role_id', $managedUser->role_id) : $managedUser->role_id) === $role->id)>{{ $role->display_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($validationTargetMatches)
                                                <x-crm.field-error field="role_id" id="crm-error-role-id-{{ $managedUser->id }}" />
                                            @endif
                                        </label>
                                        <label>
                                            <span class="visually-hidden">Account status for {{ $managedUser->name }}</span>
                                            <select name="is_active" aria-label="Account status for {{ $managedUser->name }}" @class(['crm-invalid-field' => $validationTargetMatches && $errors->has('is_active')]) aria-invalid="{{ $validationTargetMatches && $errors->has('is_active') ? 'true' : 'false' }}" @if($validationTargetMatches && $errors->has('is_active')) aria-describedby="crm-error-is-active-{{ $managedUser->id }}" @endif>
                                                <option value="1" @selected((string) ($validationTargetMatches ? old('is_active', (int) $managedUser->is_active) : (int) $managedUser->is_active) === '1')>Active</option>
                                                <option value="0" @selected((string) ($validationTargetMatches ? old('is_active', (int) $managedUser->is_active) : (int) $managedUser->is_active) === '0')>Inactive</option>
                                            </select>
                                            @if($validationTargetMatches)
                                                <x-crm.field-error field="is_active" id="crm-error-is-active-{{ $managedUser->id }}" />
                                            @endif
                                        </label>
                                        <button type="submit" class="user-admin-save-btn">
                                            <i class="bi bi-check2" aria-hidden="true"></i>
                                            Save Access
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="crm-empty-cell" colspan="4">
                                @include('partials.crm-empty-state', [
                                    'icon' => 'bi-people',
                                    'title' => 'No user accounts yet',
                                    'description' => 'Accounts will appear here when users are added to the CRM.',
                                ])
                            </td>
                        </tr>
                    @endforelse
                    <tr id="user-filter-empty" hidden>
                        <td colspan="4">
                            <div class="user-admin-empty user-admin-filter-empty" role="status" aria-live="polite">
                                <strong>No users match these filters</strong>
                                <p>Try a different search term, role, or account status.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="user-admin-pagination">{{ $users->links() }}</div>
        @endif
    </section>
</div>

<script>
    (() => {
        const search = document.getElementById('user-search');
        const role = document.getElementById('user-role-filter');
        const status = document.getElementById('user-status-filter');
        const rows = Array.from(document.querySelectorAll('.user-admin-row'));
        const empty = document.getElementById('user-filter-empty');

        const filterUsers = () => {
            const term = search.value.trim().toLocaleLowerCase();
            let visible = 0;

            rows.forEach((row) => {
                const matches = row.dataset.userSearch.includes(term)
                    && (!role.value || row.dataset.userRole === role.value)
                    && (!status.value || row.dataset.userStatus === status.value);
                row.hidden = !matches;
                visible += matches ? 1 : 0;
            });

            empty.hidden = visible !== 0 || rows.length === 0;
        };

        search.addEventListener('input', filterUsers);
        role.addEventListener('change', filterUsers);
        status.addEventListener('change', filterUsers);
    })();
</script>
@endsection
