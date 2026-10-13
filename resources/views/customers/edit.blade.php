@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    <div class="page-header crm-header">
        <div><h1>Edit Customer</h1><p>Update customer contact and account details</p></div>
        <a href="{{ route('customers.show', $customer) }}" class="module-secondary-btn">Back to Customer</a>
    </div>

    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        @include('partials.crm-form-errors')
        <form action="{{ route('customers.update', $customer) }}" method="POST" novalidate>
            @csrf
            @method('PUT')
            @include('customers._form', ['editing' => true])
        </form>
    </div>
</div>
@endsection
