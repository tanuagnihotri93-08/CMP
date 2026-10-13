@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Schedule Follow Up</h1><p>Set a reminder for a call, meeting, email, or demo</p></div>
        <a href="{{ route('followups.index') }}" class="module-secondary-btn">Back to Follow Ups</a>
    </div>
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('followups.store') }}" method="POST" novalidate>
            @csrf
            @include('followups._form', ['followUp' => null, 'editing' => false])
        </form>
    </div>
</div>
@endsection
