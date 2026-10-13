<div
    id="crm-flash-alerts"
    hidden
    data-success="{{ session('success', '') }}"
    data-error="{{ session('error') ?: ($feedbackError ?? '') }}"
    data-validation-error="{{ $errors->any() ? 'true' : 'false' }}">
</div>
