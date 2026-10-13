<div class="module-form-grid">
    <div class="module-field">
        <label for="type">Activity Type <span>*</span></label>
        <select id="type" name="type" required @class(['crm-invalid-field' => $errors->has('type')]) aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}" @if($errors->has('type')) aria-describedby="crm-error-type" @endif>
            @foreach(['Call', 'Meeting', 'Email', 'Note', 'Task'] as $type)
                <option value="{{ $type }}" @selected(old('type', $activity?->type ?? 'Call') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <x-crm.field-error field="type" />
    </div>
    <div class="module-field">
        <label for="subject">Subject <span>*</span></label>
        <input id="subject" name="subject" @class(['crm-invalid-field' => $errors->has('subject')]) aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}" @if($errors->has('subject')) aria-describedby="crm-error-subject" @endif value="{{ old('subject', $activity?->subject ?? '') }}" maxlength="255" required>
        <x-crm.field-error field="subject" />
    </div>
    <div class="module-field">
        <label for="activity_date">Date <span>*</span></label>
        <input id="activity_date" type="datetime-local" name="activity_date" @class(['crm-invalid-field' => $errors->has('activity_date')]) aria-invalid="{{ $errors->has('activity_date') ? 'true' : 'false' }}" @if($errors->has('activity_date')) aria-describedby="crm-error-activity-date" @endif
               value="{{ old('activity_date', $activity?->activity_date?->format('Y-m-d\TH:i') ?? '') }}" required>
        <x-crm.field-error field="activity_date" id="crm-error-activity-date" />
    </div>
    <div class="module-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
            <option value="pending" @selected(old('status', $activity?->status ?? 'pending') === 'pending')>Pending</option>
            <option value="completed" @selected(old('status', $activity?->status ?? 'pending') === 'completed')>Completed</option>
        </select>
        <x-crm.field-error field="status" />
    </div>
    <div class="module-field">
        <label for="lead_id">Related Lead</label>
        <select id="lead_id" name="lead_id" @class(['crm-invalid-field' => $errors->has('lead_id')]) aria-invalid="{{ $errors->has('lead_id') ? 'true' : 'false' }}" @if($errors->has('lead_id')) aria-describedby="crm-error-lead-id" @endif>
            <option value="">No lead</option>
            @foreach($leads as $leadOption)
                <option value="{{ $leadOption->id }}" @selected((string) old('lead_id', $activity?->lead_id ?? $selectedLead?->id ?? '') === (string) $leadOption->id)>
                    {{ $leadOption->name }}{{ $leadOption->company_name ? ' — '.$leadOption->company_name : '' }}
                </option>
            @endforeach
        </select>
        <x-crm.field-error field="lead_id" id="crm-error-lead-id" />
    </div>
    <div class="module-field">
        <label for="customer_id">Related Customer</label>
        <select id="customer_id" name="customer_id" @class(['crm-invalid-field' => $errors->has('customer_id')]) aria-invalid="{{ $errors->has('customer_id') ? 'true' : 'false' }}" @if($errors->has('customer_id')) aria-describedby="crm-error-customer-id" @endif>
            <option value="">No customer</option>
            @foreach($customers as $customerOption)
                <option value="{{ $customerOption->id }}" @selected((string) old('customer_id', $activity?->customer_id ?? $selectedCustomer?->id ?? '') === (string) $customerOption->id)>
                    {{ $customerOption->name }}{{ $customerOption->company ? ' — '.$customerOption->company : '' }}
                </option>
            @endforeach
        </select>
        <x-crm.field-error field="customer_id" id="crm-error-customer-id" />
    </div>
    @if($errors->has('lead_id') || $errors->has('customer_id'))
        <p class="module-form-hint">Select at least one related lead or customer.</p>
    @endif
    <div class="module-field module-field-wide">
        <label for="description">Description</label>
        <textarea id="description" name="description" @class(['crm-invalid-field' => $errors->has('description')]) aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" @if($errors->has('description')) aria-describedby="crm-error-description" @endif rows="5" maxlength="5000">{{ old('description', $activity?->description ?? '') }}</textarea>
        <x-crm.field-error field="description" />
    </div>
    @if($assignableUsers->isNotEmpty())
        <div class="module-field">
            <label for="assigned_to_user_id">Assigned to</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" @class(['crm-invalid-field' => $errors->has('assigned_to_user_id')]) aria-invalid="{{ $errors->has('assigned_to_user_id') ? 'true' : 'false' }}" @if($errors->has('assigned_to_user_id')) aria-describedby="crm-error-assigned-to-user-id" @endif>
                @foreach($assignableUsers as $assignee)
                    <option value="{{ $assignee->id }}" @selected((string) old('assigned_to_user_id', $activity?->assigned_to_user_id ?? auth()->id()) === (string) $assignee->id)>{{ $assignee->name }}</option>
                @endforeach
            </select>
            <x-crm.field-error field="assigned_to_user_id" id="crm-error-assigned-to-user-id" />
        </div>
    @endif
</div>
<div class="module-form-actions">
    <button class="primary-btn" type="submit">{{ $editing ? 'Save Changes' : 'Create Activity' }}</button>
    <a class="module-secondary-btn" href="{{ $editing ? route('activities.show', $activity) : route('activities.index') }}">Cancel</a>
</div>
