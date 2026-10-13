@props(['events'])

<section class="module-card crm-card crm-activity-timeline" aria-labelledby="crm-activity-timeline-heading">
    <div class="module-card-heading">
        <div>
            <h2 id="crm-activity-timeline-heading">Activity Timeline</h2>
            <p>Recent activity and changes, with the user who made them.</p>
        </div>
    </div>

    @if($events->isEmpty())
        <p class="crm-activity-timeline-empty">No activity history is available for this record.</p>
    @else
        <ol class="crm-activity-timeline-list">
            @foreach($events as $event)
                <li class="crm-activity-timeline-item">
                    <span class="crm-activity-timeline-marker" aria-hidden="true">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <div class="crm-activity-timeline-content">
                        <div class="crm-activity-timeline-title">
                            {{ $event->description }}
                        </div>
                        <div class="crm-activity-timeline-meta">
                            <span>{{ $event->user_name }}</span>
                            <span aria-hidden="true">·</span>
                            <time datetime="{{ $event->created_at->toIso8601String() }}">
                                {{ $event->created_at->format('M j, Y \a\t g:i A') }}
                            </time>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>

        @if($events->hasPages())
            <div class="crm-activity-timeline-pagination">
                {{ $events->links() }}
            </div>
        @endif
    @endif
</section>
