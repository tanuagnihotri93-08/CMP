@if(!$editing)
    <div class="module-field">
        <label for="lead_id">Won lead to convert (optional)</label>
        <select id="lead_id" name="lead_id" @class(['crm-invalid-field' => $errors->has('lead_id')]) aria-invalid="{{ $errors->has('lead_id') ? 'true' : 'false' }}" @if($errors->has('lead_id')) aria-describedby="crm-error-lead-id" @endif>
            <option value="">Create without a lead</option>
            @foreach($leads as $availableLead)
                <option value="{{ $availableLead->id }}" @selected(old('lead_id', $lead?->id) == $availableLead->id)>
                    {{ $availableLead->name }}{{ $availableLead->company_name ? ' — '.$availableLead->company_name : '' }}
                </option>
            @endforeach
        </select>
        <x-crm.field-error field="lead_id" id="crm-error-lead-id" />
    </div>
@elseif($customer->lead)
    <p class="module-linked-record">Converted from lead: <a href="{{ route('leads.show', $customer->lead) }}">{{ $customer->lead->name }}</a></p>
@endif

<div class="module-form-grid">
    <div class="module-field">
        <label for="name">Name <span>*</span></label>
        <input id="name" name="name" @class(['crm-invalid-field' => $errors->has('name')]) aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="crm-error-name" @endif value="{{ old('name', $customer?->name ?? $lead?->name ?? '') }}" required maxlength="255">
        <x-crm.field-error field="name" />
    </div>
    <div class="module-field">
        <label for="company">Company</label>
        <input id="company" name="company" @class(['crm-invalid-field' => $errors->has('company')]) aria-invalid="{{ $errors->has('company') ? 'true' : 'false' }}" @if($errors->has('company')) aria-describedby="crm-error-company" @endif value="{{ old('company', $customer?->company ?? $lead?->company_name ?? '') }}" maxlength="255">
        <x-crm.field-error field="company" />
    </div>
    <div class="module-field">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" @class(['crm-invalid-field' => $errors->has('email')]) aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if($errors->has('email')) aria-describedby="crm-error-email" @endif value="{{ old('email', $customer?->email ?? $lead?->email ?? '') }}" maxlength="255">
        <x-crm.field-error field="email" />
    </div>
    <div class="module-field">
        <label for="phone">Phone</label>
        <input id="phone" name="phone" @class(['crm-invalid-field' => $errors->has('phone')]) aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" @if($errors->has('phone')) aria-describedby="crm-error-phone" @endif value="{{ old('phone', $customer?->phone ?? $lead?->phone ?? '') }}" maxlength="20">
        <x-crm.field-error field="phone" />
    </div>
    <div class="module-field module-field-wide">
        <label for="address">Address</label>
        <textarea id="address" name="address" @class(['crm-invalid-field' => $errors->has('address')]) aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" @if($errors->has('address')) aria-describedby="crm-error-address" @endif rows="3" maxlength="2000">{{ old('address', $customer?->address ?? '') }}</textarea>
        <x-crm.field-error field="address" />
    </div>
    <div class="module-field">
        <label for="status">Status</label>
        <select id="status" name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
            <option value="active" @selected(old('status', $customer?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $customer?->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        <x-crm.field-error field="status" />
    </div>
    @if($assignableUsers->isNotEmpty())
        <div class="module-field">
            <label for="assigned_to_user_id">Assigned to</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" @class(['crm-invalid-field' => $errors->has('assigned_to_user_id')]) aria-invalid="{{ $errors->has('assigned_to_user_id') ? 'true' : 'false' }}" @if($errors->has('assigned_to_user_id')) aria-describedby="crm-error-assigned-to-user-id" @endif>
                @foreach($assignableUsers as $assignee)
                    <option value="{{ $assignee->id }}" @selected((string) old('assigned_to_user_id', $customer?->assigned_to_user_id ?? auth()->id()) === (string) $assignee->id)>{{ $assignee->name }}</option>
                @endforeach
            </select>
            <x-crm.field-error field="assigned_to_user_id" id="crm-error-assigned-to-user-id" />
        </div>
    @endif
</div>
<div class="module-form-actions">
    <button class="primary-btn" type="submit">{{ $editing ? 'Save Changes' : 'Create Customer' }}</button>
    <a class="module-secondary-btn" href="{{ $editing ? route('customers.show', $customer) : route('customers.index') }}">Cancel</a>
</div>
