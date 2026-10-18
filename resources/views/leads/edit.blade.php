@extends('layouts.app')

@section('content')

<div class="page-wrapper crm-page">

    <!-- Header -->
    <div class="page-header crm-header">
        <div>
            <h1>
                Edit Lead
            </h1>
            <p>
                Update lead information and sales pipeline details
            </p>
        </div>

        <a href="{{ route('leads.index') }}" class="secondary-btn">
            Back to Leads
        </a>
    </div>

    <!-- Form Card -->
    <div class="form-card crm-card crm-form-card">
        <form action="{{ route('leads.update', $lead->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-crm.validation-alert />

            <div class="form-grid">

                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">
                        Name <span>*</span>
                    </label>
                    <input type="text"
                        id="name"
                        name="name"
                        required
                        maxlength="255"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('name')])
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @if($errors->has('name')) aria-describedby="crm-error-name" @endif
                        value="{{ old('name', $lead->name) }}"
                        placeholder="Enter lead name">
                    <x-crm.field-error field="name" />
                </div>

                <!-- Company -->
                <div class="form-group">
                    <label class="form-label">
                        Company <span>*</span>
                    </label>
                    <input type="text"
                        id="company_name"
                        name="company_name"
                        required
                        maxlength="255"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('company_name')])
                        aria-invalid="{{ $errors->has('company_name') ? 'true' : 'false' }}"
                        @if($errors->has('company_name')) aria-describedby="crm-error-company-name" @endif
                        value="{{ old('company_name', $lead->company_name) }}"
                        placeholder="Enter company name">
                    <x-crm.field-error field="company_name" id="crm-error-company-name" />
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">
                        Email <span>*</span>
                    </label>
                    <input type="email"
                        id="email"
                        name="email"
                        required
                        maxlength="255"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('email')])
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @if($errors->has('email')) aria-describedby="crm-error-email" @endif
                        value="{{ old('email', $lead->email) }}"
                        placeholder="Enter email address">
                    <x-crm.field-error field="email" />
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label class="form-label">
                        Phone <span>*</span>
                    </label>
                    <input type="text"
                        id="phone"
                        name="phone"
                        required
                        maxlength="20"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('phone')])
                        aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                        @if($errors->has('phone')) aria-describedby="crm-error-phone" @endif
                        value="{{ old('phone', $lead->phone) }}"
                        placeholder="Enter phone number">
                    <x-crm.field-error field="phone" />
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label class="form-label">
                        Status <span>*</span>
                    </label>
                    <select id="status" name="status" required @class(['form-select', 'crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
                        <option value="new" @selected(old('status', $lead->status) === 'new')>New</option>
                        <option value="qualified" @selected(old('status', $lead->status) === 'qualified')>Qualified</option>
                        <option value="proposal" @selected(old('status', $lead->status) === 'proposal')>Proposal</option>
                        <option value="won" @selected(old('status', $lead->status) === 'won')>Won</option>
                        <option value="lost" @selected(old('status', $lead->status) === 'lost')>Lost</option>
                    </select>
                    <x-crm.field-error field="status" />
                </div>

                @if($assignableUsers->isNotEmpty())
                <div class="form-group">
                    <label class="form-label">Assigned to</label>
                    <select id="assigned_to_user_id" name="assigned_to_user_id" @class(['form-select', 'crm-invalid-field' => $errors->has('assigned_to_user_id')]) aria-invalid="{{ $errors->has('assigned_to_user_id') ? 'true' : 'false' }}" @if($errors->has('assigned_to_user_id')) aria-describedby="crm-error-assigned-to-user-id" @endif>
                        @foreach($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) old('assigned_to_user_id', $lead->assigned_to_user_id) === (string) $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                    <x-crm.field-error field="assigned_to_user_id" id="crm-error-assigned-to-user-id" />
                </div>
                @endif

                <!-- Source -->
                <div class="form-group">
                    <label class="form-label">
                    Source <span>*</span>
                    </label>
                    <select id="source" name="source" required @class(['form-select', 'crm-invalid-field' => $errors->has('source')]) aria-invalid="{{ $errors->has('source') ? 'true' : 'false' }}" @if($errors->has('source')) aria-describedby="crm-error-source" @endif>
                        <option value="">Select Source</option>
                        <option value="Website" @selected(old('source', $lead->source) === 'Website')>Website</option>
                        <option value="Referral" @selected(old('source', $lead->source) === 'Referral')>Referral</option>
                        <option value="Social Media" @selected(old('source', $lead->source) === 'Social Media')>Social Media</option>
                        <option value="Direct" @selected(old('source', $lead->source) === 'Direct')>Direct</option>
                    </select>
                    <x-crm.field-error field="source" />
                </div>

                <div class="form-group">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" name="city"
                        @class(['form-control', 'crm-invalid-field' => $errors->has('city')])
                        aria-invalid="{{ $errors->has('city') ? 'true' : 'false' }}"
                        @if($errors->has('city')) aria-describedby="crm-error-city" @endif
                        maxlength="100" placeholder="Enter city" value="{{ old('city', $lead->city) }}">
                    <x-crm.field-error field="city" />
                </div>

                <!-- Notes (Fixed to span full grid width) -->
                <div class="form-group full-width">
                    <label class="form-label">
                        Notes
                    </label>
                    <textarea
                        id="notes"
                        name="notes"
                        @class(['form-textarea', 'crm-invalid-field' => $errors->has('notes')])
                        aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                        @if($errors->has('notes')) aria-describedby="crm-error-notes" @endif
                        rows="5"
                        placeholder="Add lead notes...">{{ old('notes', $lead->notes) }}</textarea>
                    <x-crm.field-error field="notes" />
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks"
                        @class(['form-textarea', 'crm-invalid-field' => $errors->has('remarks')])
                        aria-invalid="{{ $errors->has('remarks') ? 'true' : 'false' }}"
                        @if($errors->has('remarks')) aria-describedby="crm-error-remarks" @endif
                        maxlength="1000" rows="4" placeholder="Add remarks">{{ old('remarks', $lead->remarks) }}</textarea>
                    <x-crm.field-error field="remarks" />
                </div>

            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="{{ route('leads.index') }}" class="secondary-btn">
                    Cancel
                </a>
                <button type="submit" class="primary-btn">
                    Update Lead
                </button>
            </div>

        </form>
    </div>

</div>

<style>
/* ================================
   CRM PRO LEAD FORM DESIGN (FIXED)
================================ */
.page-wrapper {
    width: 100%;
    padding: 25px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 35px;
}

.page-header h1 {
    font-size: 36px;
    font-weight: 850;
    color: #0f172a;
    letter-spacing: -1px;
    margin: 0;
}

.page-header p {
    margin-top: 8px;
    color: #64748b;
    font-size: 15px;
}

.form-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 35px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 50px rgba(15,23,42,.08);
    position: relative;
    overflow: hidden;
}

.form-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, #2563eb, #7c3aed);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
}

.form-group {
    margin-bottom: 22px;
}

/* FIXED: Full width span for notes */
.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 9px;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.form-label span {
    color: #ef4444;
}

.form-control,
.form-select,
.form-textarea {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 15px 16px;
    color: #0f172a;
    font-size: 14px;
    transition: .3s;
    outline: none;
}

.form-control:hover,
.form-select:hover,
.form-textarea:hover {
    border-color: #cbd5e1;
}

.form-control:focus,
.form-select:focus,
.form-textarea:focus {
    background: white;
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,.12);
}

.form-textarea {
    min-height: 130px;
    resize: none;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid #e5e7eb;
}

.primary-btn {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    padding: 14px 32px;
    border-radius: 14px;
    border: none;
    font-weight: 700;
    transition: .35s;
    cursor: pointer;
    box-shadow: 0 12px 30px rgba(37,99,235,.25);
}

.primary-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 40px rgba(37,99,235,.35);
}

.secondary-btn {
    background: white;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 14px 28px;
    border-radius: 14px;
    font-weight: 700;
    transition: .3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.secondary-btn:hover {
    background: #f8fafc;
    transform: translateY(-3px);
}

@media(max-width: 900px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .form-group.full-width {
        grid-column: auto;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
/* Form inputs and labels contrast fix */
.form-control,
.form-select,
.form-textarea,
.project-input {
    color: #0f172a !important;
    background-color: #f8fafc !important;
}

.form-label,
.project-card label {
    color: #334155 !important;
}

.form-control::placeholder,
.project-input::placeholder {
    color: #94a3b8 !important;
}
</style>

@endsection
