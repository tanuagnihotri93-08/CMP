@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Edit Follow Up</h1><p>Update the schedule, communication notes, or completion status</p></div>
        <a href="{{ route('followups.show', $followUp) }}" class="module-secondary-btn">Back to Follow Up</a>
    </div>
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('followups.update', $followUp) }}" method="POST" novalidate>
            @csrf
            @method('PUT')
            @include('followups._form', ['editing' => true])
        </form>
    </div>
</div>
@endsection
