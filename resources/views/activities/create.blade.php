@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Add Activity</h1><p>Record an interaction, note, or task</p></div>
        <a href="{{ route('activities.index') }}" class="module-secondary-btn">Back to Activities</a>
    </div>
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('activities.store') }}" method="POST" novalidate>
            @csrf
            @include('activities._form', ['activity' => null, 'editing' => false])
        </form>
    </div>
</div>
@endsection
