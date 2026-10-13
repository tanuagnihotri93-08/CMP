@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Edit Activity</h1><p>Update activity details and status</p></div>
        <a href="{{ route('activities.show', $activity) }}" class="module-secondary-btn">Back to Activity</a>
    </div>
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('activities.update', $activity) }}" method="POST" novalidate>
            @csrf
            @method('PUT')
            @include('activities._form', ['editing' => true])
        </form>
    </div>
</div>
@endsection
