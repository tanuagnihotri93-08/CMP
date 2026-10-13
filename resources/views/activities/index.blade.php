@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Activities</h1><p>Track business interactions with leads and customers</p></div>
        @if(auth()->user()->hasPermission('activities.create'))
            <a href="{{ route('activities.create') }}" class="primary-btn"><i class="bi bi-plus-lg" aria-hidden="true"></i> Add Activity</a>
        @endif
    </div>

    <x-crm.validation-alert title="Please adjust the activity filters and try again." />

    @if(session('success'))<div class="module-alert">{{ session('success') }}</div>@endif

    <div class="module-stats crm-stats">
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-activity" aria-hidden="true"></i></span><span>Total Activities</span><strong>{{ $totalActivities }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><span>Pending</span><strong>{{ $pendingCount }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><span>Completed</span><strong>{{ $completedCount }}</strong></div>
    </div>

    <div class="module-card lead-filter-card crm-card crm-filter-card">
        <div class="lead-filter-heading"><div><h2>Find activities</h2><small>Search interactions or filter by completion status</small></div></div>
        <form class="lead-filter-controls" method="GET" action="{{ route('activities.index') }}" novalidate>
            <label class="lead-search-control">
                <span class="visually-hidden">Search activities</span>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="search" @class(['crm-invalid-field' => $errors->has('search')]) aria-invalid="{{ $errors->has('search') ? 'true' : 'false' }}" @if($errors->has('search')) aria-describedby="crm-error-search" @endif value="{{ $filters['search'] ?? '' }}" placeholder="Search activities..." autocomplete="off">
                <x-crm.field-error field="search" />
            </label>
            <label>
                <span class="visually-hidden">Filter activities by status</span>
                <select name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
                    <option value="">All statuses</option>
                    @foreach(['pending', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="status" />
            </label>
            <button type="submit" class="primary-btn"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            @if($filters)
                <a href="{{ route('activities.index') }}" class="module-secondary-btn">Clear</a>
            @endif
        </form>
    </div>

    <div class="module-card enterprise-table-card crm-card crm-table-card">
        <div class="enterprise-table-heading">
            <div><h2>Activity List</h2><small>Interactions recorded across leads and customers</small></div>
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead>
                    <tr><th>Date</th><th>Activity</th><th>Related Lead</th><th>Related Customer</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr class="module-filter-row" data-filter-search="{{ mb_strtolower($activity->subject.' '.$activity->type.' '.(auth()->user()->hasPermission('leads.view') ? ($activity->lead?->name ?? '') : '').' '.(auth()->user()->hasPermission('customers.view') ? ($activity->customer?->name ?? '') : '')) }}" data-filter-status="{{ $activity->status }}">
                            <td>{{ $activity->activity_date?->format('M j, Y g:i A') ?? 'Date not set' }}</td>
                            <td><strong>{{ $activity->subject }}</strong><small class="module-subtext">{{ $activity->type }}</small></td>
                            <td>
                                @if($activity->lead && auth()->user()->hasPermission('leads.view'))
                                    <a href="{{ route('leads.show', $activity->lead) }}">{{ $activity->lead->name }}</a>
                                @elseif($activity->lead)
                                    Linked lead
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($activity->customer && auth()->user()->hasPermission('customers.view'))
                                    <a href="{{ route('customers.show', $activity->customer) }}">{{ $activity->customer->name }}</a>
                                @elseif($activity->customer)
                                    Linked customer
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="module-badge crm-badge {{ $activity->status }}">{{ ucfirst($activity->status) }}</span></td>
                            <td class="module-actions">
                                <a class="module-action-btn crm-action-btn crm-action-view" href="{{ route('activities.show', $activity) }}"><i class="bi bi-eye" aria-hidden="true"></i> View</a>
                                @if(auth()->user()->hasPermission('activities.update'))
                                    <a class="module-action-btn crm-action-btn crm-action-edit" href="{{ route('activities.edit', $activity) }}"><i class="bi bi-pencil" aria-hidden="true"></i> Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="crm-empty-cell" colspan="6">
                                @include('partials.crm-empty-state', [
                                    'icon' => 'bi-activity',
                                    'title' => $filters ? 'No activities match these filters' : 'No activities recorded',
                                    'description' => $filters
                                        ? 'Adjust the search or status filter to find matching activities.'
                                        : 'Log a call, meeting, email, or task to keep customer and lead history up to date.',
                                    'actionUrl' => !$filters && auth()->user()->hasPermission('activities.create') ? route('activities.create') : null,
                                    'actionLabel' => !$filters && auth()->user()->hasPermission('activities.create') ? 'Add activity' : null,
                                ])
                            </td>
                        </tr>
                    @endforelse
                    @if($activities->isNotEmpty())
                        <tr class="module-filter-empty" hidden><td class="crm-empty-cell" colspan="6">@include('partials.crm-empty-state', ['icon' => 'bi-search', 'title' => 'No activities match', 'description' => 'Try another search term or status filter.'])</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{ $activities->links() }}
    </div>
</div>
@endsection
