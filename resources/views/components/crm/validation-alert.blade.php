@if($errors->any())
    @php($errorCount = $errors->count())
    <div class="crm-validation-alert" role="alert" aria-live="assertive" aria-atomic="true">
        <span class="crm-validation-alert-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span>
        <div>
            <strong>{{ $title ?? 'Please correct the highlighted fields.' }}</strong>
            <p>
                {{ $errorCount }} {{ $errorCount === 1 ? 'issue needs' : 'issues need' }} attention.
                Review the highlighted fields below.
            </p>
        </div>
    </div>
@endif
