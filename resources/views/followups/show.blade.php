@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>{{ $followUp->type ?: 'Follow Up' }}</h1><p>Scheduled for {{ $followUp->date?->format('M j, Y g:i A') ?? 'Date not set' }}</p></div>
        <div class="module-header-actions">
            @if(auth()->user()->hasPermission('follow_ups.update'))
                <a href="{{ route('followups.edit', $followUp) }}" class="primary-btn crm-action-btn crm-action-edit">Edit Follow Up</a>
            @endif
            <a href="{{ route('followups.index') }}" class="module-secondary-btn">All Follow Ups</a>
        </div>
    </div>

    @if(session('success'))<div class="module-alert">{{ session('success') }}</div>@endif

    <div class="module-card module-profile-card crm-card crm-profile-card">
        <div class="module-profile-heading">
            <div class="module-profile-avatar"><i class="bi bi-calendar-event" aria-hidden="true"></i></div>
            <div>
                <h2>{{ $followUp->type ?: 'Follow Up' }}</h2>
                <p>{{ $followUp->date?->format('M j, Y g:i A') ?? 'Date not set' }}</p>
            </div>
            <span class="module-badge crm-badge {{ $followUp->status }}">{{ ucfirst($followUp->status) }}</span>
        </div>
        <dl class="module-details">
            <div><dt>Lead</dt><dd>
                @if($followUp->lead)
                    @if(auth()->user()->hasPermission('leads.view'))
                        <a href="{{ route('leads.show', $followUp->lead) }}">{{ $followUp->lead->name }}</a>
                    @else
                        Linked lead
                    @endif
                @else Lead removed @endif
            </dd></div>
            <div><dt>Date &amp; Time</dt><dd>{{ $followUp->date?->format('M j, Y g:i A') ?? 'Date not set' }}</dd></div>
            <div><dt>Type</dt><dd>{{ $followUp->type ?: '—' }}</dd></div>
            <div><dt>Status</dt><dd><span class="module-badge crm-badge {{ $followUp->status }}">{{ ucfirst($followUp->status) }}</span></dd></div>
            <div><dt>Remark</dt><dd>{{ $followUp->remark ?: '—' }}</dd></div>
        </dl>
        <div class="module-form-actions">
            @if($followUp->status === 'scheduled' && auth()->user()->hasPermission('follow_ups.complete'))
                <form action="{{ route('followups.complete', $followUp) }}" method="POST" novalidate>
                    @csrf
                    <button class="primary-btn" type="submit">Mark Complete</button>
                </form>
            @endif
            @if(auth()->user()->hasPermission('follow_ups.delete'))
                <form action="{{ route('followups.destroy', $followUp) }}" method="POST" data-confirm="This will permanently delete this follow up." novalidate>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="module-danger-btn crm-action-btn crm-action-delete">Delete Follow Up</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
