@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div>
            <h1>Audit Logs</h1>
            <p>Review recorded CRM and authentication events</p>
        </div>
    </div>

    <x-crm.validation-alert title="Please adjust the audit filters and try again." />

    <div class="module-card enterprise-filter-card crm-card crm-filter-card">
        <div class="enterprise-table-heading"><div><h2>Filter audit events</h2><small>Search recorded actions and narrow the timeline</small></div></div>
        <form action="{{ route('audit-logs.index') }}" method="GET" class="module-form-grid" novalidate>
            <div class="module-field">
                <label for="search">Search</label>
                <input id="search" name="search" @class(['crm-invalid-field' => $errors->has('search')]) aria-invalid="{{ $errors->has('search') ? 'true' : 'false' }}" @if($errors->has('search')) aria-describedby="crm-error-search" @endif value="{{ $filters['search'] ?? '' }}" maxlength="150" placeholder="Search actions or descriptions">
                <x-crm.field-error field="search" />
            </div>
            <div class="module-field">
                <label for="module">Module</label>
                <select id="module" name="module" @class(['crm-invalid-field' => $errors->has('module')]) aria-invalid="{{ $errors->has('module') ? 'true' : 'false' }}" @if($errors->has('module')) aria-describedby="crm-error-module" @endif>
                    <option value="">All modules</option>
                    @foreach(['leads' => 'Leads', 'customers' => 'Customers', 'follow_ups' => 'Follow Ups', 'activities' => 'Activities', 'reports' => 'Reports', 'authentication' => 'Authentication', 'users' => 'Users', 'roles' => 'Roles'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['module'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="module" />
            </div>
            <div class="module-field">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id" @class(['crm-invalid-field' => $errors->has('user_id')]) aria-invalid="{{ $errors->has('user_id') ? 'true' : 'false' }}" @if($errors->has('user_id')) aria-describedby="crm-error-user-id" @endif>
                    <option value="">All users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="user_id" />
            </div>
            <div class="module-field">
                <label for="date_from">From</label>
                <input id="date_from" type="date" name="date_from" @class(['crm-invalid-field' => $errors->has('date_from')]) aria-invalid="{{ $errors->has('date_from') ? 'true' : 'false' }}" @if($errors->has('date_from')) aria-describedby="crm-error-date-from" @endif value="{{ $filters['date_from'] ?? '' }}">
                <x-crm.field-error field="date_from" />
            </div>
            <div class="module-field">
                <label for="date_to">To</label>
                <input id="date_to" type="date" name="date_to" @class(['crm-invalid-field' => $errors->has('date_to')]) aria-invalid="{{ $errors->has('date_to') ? 'true' : 'false' }}" @if($errors->has('date_to')) aria-describedby="crm-error-date-to" @endif value="{{ $filters['date_to'] ?? '' }}">
                <x-crm.field-error field="date_to" />
            </div>
            <div class="module-form-actions">
                <button class="primary-btn" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filter Logs</button>
                <a class="module-secondary-btn" href="{{ route('audit-logs.index') }}">Clear</a>
            </div>
        </form>
    </div>

    <div class="module-card enterprise-table-card crm-card crm-table-card">
        <div class="enterprise-table-heading">
            <div><h2>Audit history</h2><small>Chronological record of CRM and authentication events</small></div>
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M j, Y g:i:s A') }}</td>
                            <td>{{ $log->user?->name ?? $log->user_name }}</td>
                            <td><span class="module-badge crm-badge">{{ str_replace('_', ' ', ucfirst($log->action)) }}</span></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $log->module)) }}</td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="crm-empty-cell" colspan="5">
                                @include('partials.crm-empty-state', [
                                    'icon' => 'bi-shield-check',
                                    'title' => request()->query() ? 'No audit events match these filters' : 'No audit events recorded',
                                    'description' => request()->query()
                                        ? 'Adjust the filters or clear them to search all available audit events.'
                                        : 'CRM and authentication events will appear here as activity is recorded.',
                                    'actionUrl' => request()->query() ? route('audit-logs.index') : null,
                                    'actionLabel' => request()->query() ? 'Clear filters' : null,
                                    'actionIcon' => 'bi-arrow-counterclockwise',
                                ])
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    </div>
</div>
@endsection
