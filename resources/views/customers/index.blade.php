@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div>
            <h1>Customers</h1>
            <p>Manage your converted leads and customer relationships</p>
        </div>
        @if(auth()->user()->hasPermission('customers.create'))
            <a href="{{ route('customers.create') }}" class="primary-btn"><i class="bi bi-person-plus" aria-hidden="true"></i> Add Customer</a>
        @endif
    </div>

    <x-crm.validation-alert title="Please adjust the customer filters and try again." />

    @if(session('success'))
        <div class="module-alert">{{ session('success') }}</div>
    @endif

    <div class="module-stats crm-stats">
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-people" aria-hidden="true"></i></span><span>Total Customers</span><strong>{{ $totalCustomers }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span><span>Active</span><strong>{{ $activeCustomers }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-person-dash" aria-hidden="true"></i></span><span>Inactive</span><strong>{{ $inactiveCustomers }}</strong></div>
    </div>

    <div class="module-card lead-filter-card crm-card crm-filter-card">
        <div class="lead-filter-heading"><div><h2>Find customers</h2><small>Search the directory or filter by account status</small></div></div>
        <form class="lead-filter-controls" method="GET" action="{{ route('customers.index') }}" novalidate>
            <label class="lead-search-control">
                <span class="visually-hidden">Search customer records</span>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="search" @class(['crm-invalid-field' => $errors->has('search')]) aria-invalid="{{ $errors->has('search') ? 'true' : 'false' }}" @if($errors->has('search')) aria-describedby="crm-error-search" @endif value="{{ $filters['search'] ?? '' }}" placeholder="Search customers..." autocomplete="off">
                <x-crm.field-error field="search" />
            </label>
            <label>
                <span class="visually-hidden">Filter customers by status</span>
                <select name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
                    <option value="">All statuses</option>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="status" />
            </label>
            <button type="submit" class="primary-btn"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            @if($filters)
                <a href="{{ route('customers.index') }}" class="module-secondary-btn">Clear</a>
            @endif
        </form>
    </div>

    <div class="module-card enterprise-table-card crm-card crm-table-card">
        <div class="enterprise-table-heading">
            <div><h2>Customer Directory</h2><small>Customer relationships and account status</small></div>
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr><th>Name</th><th>Company</th><th>Contact</th><th>Status</th><th>Converted Lead</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr class="module-filter-row" data-filter-search="{{ mb_strtolower($customer->name.' '.$customer->company.' '.$customer->email.' '.$customer->phone.' '.(auth()->user()->hasPermission('leads.view') ? ($customer->lead?->name ?? '') : '')) }}" data-filter-status="{{ $customer->status }}">
                            <td><strong>{{ $customer->name }}</strong></td>
                            <td>{{ $customer->company ?: '—' }}</td>
                            <td>{{ $customer->email ?: $customer->phone ?: '—' }}</td>
                            <td><span class="module-badge crm-badge {{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td>
                            <td>
                                @if(!auth()->user()->hasPermission('leads.view'))
                                    {{ $customer->lead ? 'Linked lead' : 'No linked lead' }}
                                @else
                                    {{ $customer->lead?->name ?? 'No linked lead' }}
                                @endif
                            </td>
                            <td class="module-actions">
                                <a class="module-action-btn crm-action-btn crm-action-view" href="{{ route('customers.show', $customer) }}"><i class="bi bi-eye" aria-hidden="true"></i> View</a>
                                @if(auth()->user()->hasPermission('customers.update'))
                                    <a class="module-action-btn crm-action-btn crm-action-edit" href="{{ route('customers.edit', $customer) }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="crm-empty-cell" colspan="6">
                                @include('partials.crm-empty-state', [
                                    'icon' => 'bi-people',
                                    'title' => $filters ? 'No customers match these filters' : 'No customers yet',
                                    'description' => $filters
                                        ? 'Adjust the search or account status filter to find matching customers.'
                                        : 'Customers appear here after lead conversion or when a customer account is created.',
                                    'actionUrl' => !$filters && auth()->user()->hasPermission('customers.create') ? route('customers.create') : null,
                                    'actionLabel' => !$filters && auth()->user()->hasPermission('customers.create') ? 'Add customer' : null,
                                ])
                            </td>
                        </tr>
                    @endforelse
                    @if($customers->isNotEmpty())
                        <tr class="module-filter-empty" hidden><td class="crm-empty-cell" colspan="6">@include('partials.crm-empty-state', ['icon' => 'bi-search', 'title' => 'No customers match', 'description' => 'Try another search term or status filter.'])</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
