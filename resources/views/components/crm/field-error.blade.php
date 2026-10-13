@php
    $fieldMessages = collect($errors->getMessages())
        ->filter(fn ($messages, $key) => $key === $field || str_starts_with($key, $field.'.'))
        ->flatten();
    $errorId = $id ?? 'crm-error-'.\Illuminate\Support\Str::slug($field);
@endphp

@if($fieldMessages->isNotEmpty())
    <small id="{{ $errorId }}" class="crm-field-error" role="alert">
        <i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i>
        <span>{{ $fieldMessages->first() }}</span>
    </small>
@endif
