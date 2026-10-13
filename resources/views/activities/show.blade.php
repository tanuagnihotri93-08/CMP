@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Activity Details</h1><p>{{ $activity->subject }}</p></div>
        <div class="module-header-actions">
            @if(auth()->user()->hasPermission('activities.update'))
                <a href="{{ route('activities.edit', $activity) }}" class="primary-btn crm-action-btn crm-action-edit">Edit Activity</a>
            @endif
            <a href="{{ route('activities.index') }}" class="module-secondary-btn">All Activities</a>
        </div>
    </div>

    @if(session('success'))<div class="module-alert">{{ session('success') }}</div>@endif

    <div class="module-card module-profile-card crm-card crm-profile-card">
        <div class="module-profile-heading">
            <div class="module-profile-avatar"><i class="bi bi-activity" aria-hidden="true"></i></div>
            <div>
                <h2>{{ $activity->subject }}</h2>
                <p>{{ $activity->type }} · {{ $activity->activity_date?->format('M j, Y g:i A') ?? 'Date not set' }}</p>
            </div>
            <span class="module-badge crm-badge {{ $activity->status }}">{{ ucfirst($activity->status) }}</span>
        </div>
        <dl class="module-details">
            <div><dt>Activity Type</dt><dd>{{ $activity->type }}</dd></div>
            <div><dt>Subject</dt><dd>{{ $activity->subject }}</dd></div>
            <div><dt>Date</dt><dd>{{ $activity->activity_date?->format('M j, Y g:i A') ?? 'Date not set' }}</dd></div>
            <div><dt>Status</dt><dd><span class="module-badge crm-badge {{ $activity->status }}">{{ ucfirst($activity->status) }}</span></dd></div>
            <div><dt>Related Lead</dt><dd>
                @if($activity->lead)
                    @if(auth()->user()->hasPermission('leads.view'))
                        <a href="{{ route('leads.show', $activity->lead) }}">{{ $activity->lead->name }}</a>
                    @else
                        Linked lead
                    @endif
                @else — @endif
            </dd></div>
            <div><dt>Related Customer</dt><dd>
                @if($activity->customer)
                    @if(auth()->user()->hasPermission('customers.view'))
                        <a href="{{ route('customers.show', $activity->customer) }}">{{ $activity->customer->name }}</a>
                    @else
                        Linked customer
                    @endif
                @else — @endif
            </dd></div>
            <div class="module-detail-wide"><dt>Description</dt><dd>{{ $activity->description ?: 'No description provided.' }}</dd></div>
        </dl>
        @if(auth()->user()->hasPermission('activities.delete'))
            <form action="{{ route('activities.destroy', $activity) }}" method="POST" data-confirm="This will permanently delete this activity." novalidate>
                @csrf
                @method('DELETE')
                <button type="submit" class="module-danger-btn crm-action-btn crm-action-delete">Delete Activity</button>
            </form>
        @endif
    </div>
</div>
@endsection
