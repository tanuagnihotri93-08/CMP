<div class="crm-empty-state" role="status">
    <span class="crm-empty-state-icon" aria-hidden="true">
        <i class="bi {{ $icon ?? 'bi-inbox' }}"></i>
    </span>
    <strong>{{ $title ?? 'Nothing to show yet' }}</strong>
    <p>{{ $description ?? 'There are no records to display right now.' }}</p>
    @if(!empty($actionUrl) && !empty($actionLabel))
        <a href="{{ $actionUrl }}" class="crm-empty-state-action">
            <i class="bi {{ $actionIcon ?? 'bi-plus-lg' }}" aria-hidden="true"></i>
            {{ $actionLabel }}
        </a>
    @endif
</div>
