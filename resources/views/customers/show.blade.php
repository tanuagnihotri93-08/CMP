@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>{{ $customer->name }}</h1><p>Customer profile and communication history</p></div>
        <div class="module-header-actions">
            @if(auth()->user()->hasPermission('customers.update'))
                <a href="{{ route('customers.edit', $customer) }}" class="primary-btn crm-action-btn crm-action-edit">Edit Customer</a>
            @endif
            @if(auth()->user()->hasPermission('activities.create'))
                <a href="{{ route('activities.create', ['customer_id' => $customer->id]) }}" class="module-secondary-btn">Add Activity</a>
            @endif
            <a href="{{ route('customers.index') }}" class="module-secondary-btn">All Customers</a>
        </div>
    </div>

    @if(session('success'))<div class="module-alert">{{ session('success') }}</div>@endif

    <div class="module-card module-profile-card crm-card crm-profile-card">
        <div class="module-profile-heading">
            <div class="module-profile-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</div>
            <div>
                <h2>{{ $customer->name }}</h2>
                <p>{{ $customer->company ?: 'Customer account' }}</p>
            </div>
            <span class="module-badge crm-badge {{ $customer->status }}">{{ ucfirst($customer->status) }}</span>
        </div>
        <dl class="module-details">
            <div><dt>Company</dt><dd>{{ $customer->company ?: '—' }}</dd></div>
            <div><dt>Email</dt><dd>{{ $customer->email ?: '—' }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $customer->phone ?: '—' }}</dd></div>
            <div><dt>Status</dt><dd><span class="module-badge crm-badge {{ $customer->status }}">{{ ucfirst($customer->status) }}</span></dd></div>
            <div><dt>Address</dt><dd>{{ $customer->address ?: '—' }}</dd></div>
            <div><dt>Converted Lead</dt><dd>
                @if($customer->lead)
                    @if(auth()->user()->hasPermission('leads.view'))
                        <a href="{{ route('leads.show', $customer->lead) }}">{{ $customer->lead->name }}</a>
                    @else
                        Linked lead
                    @endif
                @else
                    No linked lead
                @endif
            </dd></div>
        </dl>
        @if(auth()->user()->hasPermission('customers.delete'))
            <form action="{{ route('customers.destroy', $customer) }}" method="POST" data-confirm="This will permanently delete this customer record." novalidate>
                @csrf
                @method('DELETE')
                <button type="submit" class="module-danger-btn crm-action-btn crm-action-delete">Delete Customer</button>
            </form>
        @endif
    </div>

    <x-crm.activity-timeline :events="$timeline" />

    @if($customer->lead && auth()->user()->hasPermission('follow_ups.view'))
        <div class="module-card module-history enterprise-table-card crm-card crm-table-card">
            <div class="module-card-heading">
                <h2>Lead Follow Ups</h2>
                @if(auth()->user()->hasPermission('follow_ups.create'))
                    <a class="module-secondary-btn" href="{{ route('followups.create', ['lead_id' => $customer->lead->id]) }}">Schedule Follow Up</a>
                @endif
            </div>
            <div class="module-table-wrap">
                <table class="module-table">
                    <thead><tr><th>Date</th><th>Type</th><th>Remark</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($followUps as $followUp)
                            <tr>
                                <td>{{ $followUp->date?->format('M j, Y g:i A') ?? 'Date not set' }}</td>
                                <td>{{ $followUp->type }}</td>
                                <td>{{ $followUp->remark ?: '—' }}</td>
                                <td><span class="module-badge crm-badge {{ $followUp->status }}">{{ ucfirst($followUp->status) }}</span></td>
                                <td>
                                    @if(auth()->user()->hasPermission('follow_ups.view'))
                                        <a href="{{ route('followups.show', $followUp) }}">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="module-empty">No follow ups have been scheduled for this lead.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            {{ $followUps->links() }}
            </div>
        </div>
    @endif

    <div class="module-card module-history enterprise-table-card crm-card crm-table-card">
        <div class="module-card-heading">
            <h2>Activities</h2>
            @if(auth()->user()->hasPermission('activities.view'))
                <a class="module-secondary-btn" href="{{ route('activities.index') }}">All Activities</a>
            @endif
        </div>
        <div class="module-table-wrap">
            <table class="module-table">
                <thead><tr><th>Date</th><th>Type</th><th>Subject</th><th>Lead</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->activity_date?->format('M j, Y g:i A') ?? 'Date not set' }}</td>
                            <td>{{ $activity->type }}</td>
                            <td>{{ $activity->subject }}</td>
                            <td>
                                @if($activity->lead && auth()->user()->hasPermission('leads.view'))
                                    <a href="{{ route('leads.show', $activity->lead) }}">{{ $activity->lead->name }}</a>
                                @elseif($activity->lead)
                                    Linked lead
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="module-badge crm-badge {{ $activity->status }}">{{ ucfirst($activity->status) }}</span></td>
                            <td>
                                @if(auth()->user()->hasPermission('activities.view'))
                                    <a href="{{ route('activities.show', $activity) }}">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="module-empty">No activities recorded for this customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
