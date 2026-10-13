@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Follow Ups</h1><p>Schedule and track lead communications and reminders</p></div>
        @if(auth()->user()->hasPermission('follow_ups.create'))
            <a href="{{ route('followups.create') }}" class="primary-btn"><i class="bi bi-calendar-plus" aria-hidden="true"></i> Schedule Follow Up</a>
        @endif
    </div>

    <x-crm.validation-alert title="Please adjust the follow-up filters and try again." />

    @if(session('success'))<div class="module-alert">{{ session('success') }}</div>@endif

    <div class="module-stats crm-stats">
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><span>Scheduled</span><strong>{{ $scheduledCount }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span><span>Overdue</span><strong class="module-overdue-number">{{ $overdueCount }}</strong></div>
        <div class="module-stat crm-stat-card"><span class="module-stat-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span><span>Completed</span><strong>{{ $completedCount }}</strong></div>
    </div>

    <div class="module-card lead-filter-card crm-card crm-filter-card">
        <div class="lead-filter-heading"><div><h2>Find follow-ups</h2><small>Search reminders or filter by completion status</small></div></div>
        <form class="lead-filter-controls" method="GET" action="{{ route('followups.index') }}" novalidate>
            <label class="lead-search-control">
                <span class="visually-hidden">Search follow-ups</span>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="search" @class(['crm-invalid-field' => $errors->has('search')]) aria-invalid="{{ $errors->has('search') ? 'true' : 'false' }}" @if($errors->has('search')) aria-describedby="crm-error-search" @endif value="{{ $filters['search'] ?? '' }}" placeholder="Search follow-ups..." autocomplete="off">
                <x-crm.field-error field="search" />
            </label>
            <label>
                <span class="visually-hidden">Filter follow-ups by status</span>
                <select name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
                    <option value="">All statuses</option>
                    @foreach(['scheduled', 'completed'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <x-crm.field-error field="status" />
            </label>
            <button type="submit" class="primary-btn"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            @if($filters)
                <a href="{{ route('followups.index') }}" class="module-secondary-btn">Clear</a>
            @endif
        </form>
    </div>

    <div class="module-card enterprise-table-card crm-card crm-table-card">
        <div class="enterprise-table-heading">
            <div><h2>Reminder Schedule</h2><small>Upcoming and completed customer touchpoints</small></div>
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead><tr><th>Date &amp; Time</th><th>Lead</th><th>Type</th><th>Remark</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($followUps as $followUp)
                        @php($isOverdue = $followUp->status === 'scheduled' && ($followUp->date?->isPast() ?? false))
                        <tr class="module-filter-row {{ $isOverdue ? 'module-row-overdue' : '' }}" data-filter-search="{{ mb_strtolower((auth()->user()->hasPermission('leads.view') ? ($followUp->lead?->name ?? '') : '').' '.$followUp->type.' '.$followUp->remark) }}" data-filter-status="{{ $followUp->status }}">
                            <td>{{ $followUp->date?->format('M j, Y g:i A') ?? 'Date not set' }} @if($isOverdue)<small class="module-overdue-label">Overdue</small>@endif</td>
                            <td>
                                @if($followUp->lead && auth()->user()->hasPermission('leads.view'))
                                    <a href="{{ route('leads.show', $followUp->lead) }}">{{ $followUp->lead->name }}</a>
                                @elseif($followUp->lead)
                                    Linked lead
                                @else
                                    Lead removed
                                @endif
                            </td>
                            <td>{{ $followUp->type ?: '—' }}</td>
                            <td>{{ $followUp->remark ?: '—' }}</td>
                            <td><span class="module-badge crm-badge {{ $followUp->status }}">{{ ucfirst($followUp->status) }}</span></td>
                            <td class="module-actions">
                                <a class="module-action-btn crm-action-btn crm-action-view" href="{{ route('followups.show', $followUp) }}"><i class="bi bi-eye" aria-hidden="true"></i> View</a>
                                @if($followUp->status === 'scheduled' && auth()->user()->hasPermission('follow_ups.complete'))
                                    <form action="{{ route('followups.complete', $followUp) }}" method="POST" novalidate>
                                        @csrf
                                        <button class="module-action-btn module-action-complete" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Complete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="crm-empty-cell" colspan="6">
                                @include('partials.crm-empty-state', [
                                    'icon' => 'bi-calendar2-check',
                                    'title' => $filters ? 'No follow-ups match these filters' : 'No follow-ups scheduled',
                                    'description' => $filters
                                        ? 'Adjust the search or status filter to find matching reminders.'
                                        : 'Schedule a reminder to keep lead conversations moving forward.',
                                    'actionUrl' => !$filters && auth()->user()->hasPermission('follow_ups.create') ? route('followups.create') : null,
                                    'actionLabel' => !$filters && auth()->user()->hasPermission('follow_ups.create') ? 'Schedule follow-up' : null,
                                ])
                            </td>
                        </tr>
                    @endforelse
                    @if($followUps->isNotEmpty())
                        <tr class="module-filter-empty" hidden><td class="crm-empty-cell" colspan="6">@include('partials.crm-empty-state', ['icon' => 'bi-search', 'title' => 'No follow-ups match', 'description' => 'Try another search term or status filter.'])</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        {{ $followUps->links() }}
    </div>
</div>
@endsection
