<div class="module-form-grid">
    <div class="module-field">
        <label for="lead_id">Lead <span>*</span></label>
        <select id="lead_id" name="lead_id" required @class(['crm-invalid-field' => $errors->has('lead_id')]) aria-invalid="{{ $errors->has('lead_id') ? 'true' : 'false' }}" @if($errors->has('lead_id')) aria-describedby="crm-error-lead-id" @endif>
            <option value="">Select a lead</option>
            @foreach($leads as $leadOption)
                <option value="{{ $leadOption->id }}" @selected(old('lead_id', $followUp?->lead_id ?? $lead?->id) == $leadOption->id)>
                    {{ $leadOption->name }}{{ $leadOption->company_name ? ' — '.$leadOption->company_name : '' }}
                </option>
            @endforeach
        </select>
        <x-crm.field-error field="lead_id" id="crm-error-lead-id" />
    </div>
    <div class="module-field">
        <label for="date">Date &amp; Time <span>*</span></label>
        <input id="date" type="datetime-local" name="date" @class(['crm-invalid-field' => $errors->has('date')]) aria-invalid="{{ $errors->has('date') ? 'true' : 'false' }}" @if($errors->has('date')) aria-describedby="crm-error-date" @endif
               value="{{ old('date', $followUp?->date?->format('Y-m-d\TH:i') ?? '') }}" required>
        <x-crm.field-error field="date" />
    </div>
    <div class="module-field">
        <label for="type">Type <span>*</span></label>
        <select id="type" name="type" required @class(['crm-invalid-field' => $errors->has('type')]) aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}" @if($errors->has('type')) aria-describedby="crm-error-type" @endif>
            @foreach(['Call', 'Meeting', 'Email', 'Demo'] as $type)
                <option value="{{ $type }}" @selected(old('type', $followUp?->type ?? 'Call') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <x-crm.field-error field="type" />
    </div>
    <div class="module-field">
        <label for="status">Status</label>
        <select id="status" name="status" @class(['crm-invalid-field' => $errors->has('status')]) aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}" @if($errors->has('status')) aria-describedby="crm-error-status" @endif>
            <option value="scheduled" @selected(old('status', $followUp?->status ?? 'scheduled') === 'scheduled')>Scheduled</option>
            <option value="completed" @selected(old('status', $followUp?->status ?? 'scheduled') === 'completed')>Completed</option>
        </select>
        <x-crm.field-error field="status" />
    </div>
    <div class="module-field module-field-wide">
        <label for="remark">Remark</label>
        <textarea id="remark" name="remark" @class(['crm-invalid-field' => $errors->has('remark')]) aria-invalid="{{ $errors->has('remark') ? 'true' : 'false' }}" @if($errors->has('remark')) aria-describedby="crm-error-remark" @endif rows="4" maxlength="2000">{{ old('remark', $followUp?->remark ?? '') }}</textarea>
        <x-crm.field-error field="remark" />
    </div>
    @if($assignableUsers->isNotEmpty())
        <div class="module-field">
            <label for="assigned_to_user_id">Assigned to</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" @class(['crm-invalid-field' => $errors->has('assigned_to_user_id')]) aria-invalid="{{ $errors->has('assigned_to_user_id') ? 'true' : 'false' }}" @if($errors->has('assigned_to_user_id')) aria-describedby="crm-error-assigned-to-user-id" @endif>
                @foreach($assignableUsers as $assignee)
                    <option value="{{ $assignee->id }}" @selected((string) old('assigned_to_user_id', $followUp?->assigned_to_user_id ?? auth()->id()) === (string) $assignee->id)>{{ $assignee->name }}</option>
                @endforeach
            </select>
            <x-crm.field-error field="assigned_to_user_id" id="crm-error-assigned-to-user-id" />
        </div>
    @endif
</div>
<div class="module-form-actions">
    <button class="primary-btn" type="submit">{{ $editing ? 'Save Changes' : 'Schedule Follow Up' }}</button>
    <a class="module-secondary-btn" href="{{ $editing ? route('followups.show', $followUp) : route('followups.index') }}">Cancel</a>
</div>
