@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Add Customer</h1><p>Convert a won lead or create a customer record</p></div>
        <a href="{{ route('customers.index') }}" class="module-secondary-btn">Back to Customers</a>
    </div>

    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('customers.store') }}" method="POST" novalidate>
            @csrf
            @include('customers._form', ['customer' => null, 'editing' => false])
        </form>
    </div>
</div>
@endsection
