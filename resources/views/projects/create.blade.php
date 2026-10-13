@extends('layouts.app')

@section('content')
<div class="page-wrapper module-page lead-crud-page crm-page">
    
    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>Create Project</h1>
            <p>Add a new CRM project and set its initial timeline</p>
        </div>
        <a href="{{ route('projects.index') }}" class="module-secondary-btn">Back to Projects</a>
    </div>

    <!-- Form Card -->
    <div class="module-card module-form-card lead-crud-form-card crm-card crm-form-card">
        
        <x-crm.validation-alert />

        <form action="{{ route('projects.store') }}" method="POST" novalidate>
            @csrf

            <div class="form-grid module-form-grid">

                <!-- Project Name -->
                <div class="form-group module-field">
                    <label class="form-label">Project Name <span>*</span></label>
                    <input type="text"
                        id="name"
                        name="name"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('name')])
                        value="{{ old('name') }}"
                        placeholder="Enter project name"
                        required>
                    <x-crm.field-error field="name" />
                </div>

                <!-- Client Name -->
                <div class="form-group module-field">
                    <label class="form-label">Client Name</label>
                    <input type="text"
                        id="client_name"
                        name="client_name"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('client_name')])
                        value="{{ old('client_name') }}"
                        placeholder="Enter client name">
                    <x-crm.field-error field="client_name" />
                </div>

                <!-- Start Date -->
                <div class="form-group module-field">
                    <label class="form-label">Start Date</label>
                    <input type="date"
                        id="start_date"
                        name="start_date"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('start_date')])
                        value="{{ old('start_date') }}">
                    <x-crm.field-error field="start_date" />
                </div>

                <!-- End Date -->
                <div class="form-group module-field">
                    <label class="form-label">End Date</label>
                    <input type="date"
                        id="end_date"
                        name="end_date"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('end_date')])
                        value="{{ old('end_date') }}">
                    <x-crm.field-error field="end_date" />
                </div>

                <!-- Status -->
                <div class="form-group module-field">
                    <label class="form-label">Status <span>*</span></label>
                    <select id="status" name="status" @class(['form-select', 'crm-invalid-field' => $errors->has('status')]) required>
                        <option value="Planning" {{ old('status') == 'Planning' ? 'selected' : '' }}>Planning</option>
                        <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <x-crm.field-error field="status" />
                </div>

                <!-- Description (Full Width) -->
                <div class="form-group full-width module-field-wide module-field">
                    <label class="form-label">Description</label>
                    <textarea
                        id="description"
                        name="description"
                        @class(['form-textarea', 'crm-invalid-field' => $errors->has('description')])
                        rows="4"
                        placeholder="Enter project description...">{{ old('description') }}</textarea>
                    <x-crm.field-error field="description" />
                </div>

            </div>

            <!-- Actions -->
            <div class="form-actions module-form-actions">
                <a href="{{ route('projects.index') }}" class="module-secondary-btn secondary-btn">
                    Cancel
                </a>
                <button type="submit" class="primary-btn">
                    Save Project
                </button>
            </div>

        </form>
    </div>

</div>
@endsection