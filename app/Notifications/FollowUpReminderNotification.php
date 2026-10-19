<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public array $backoff = [10, 60];

    public function __construct(
        public readonly FollowUp $followUp,
        public readonly string $urgency = 'reminder'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Follow Up Reminder',
            'message' => "{$this->followUp->type} scheduled for {$this->followUp->lead?->name} in 5 minutes.",
            'module' => 'follow_ups',
            'record_id' => $this->followUp->id,
            'action_url' => route('followups.show', $this->followUp->id),
            'type' => 'reminder',
            'urgency' => $this->urgency,
            'follow_up_id' => $this->followUp->id,
            'lead_id' => $this->followUp->lead_id,
            'scheduled_at' => $this->followUp->date?->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Follow Up Reminder',
            'message' => "{$this->followUp->type} scheduled for {$this->followUp->lead?->name} in 5 minutes.",
            'module' => 'follow_ups',
            'record_id' => $this->followUp->id,
            'action_url' => route('followups.show', $this->followUp->id),
            'type' => 'reminder',
            'urgency' => $this->urgency,
            'follow_up_id' => $this->followUp->id,
            'lead_id' => $this->followUp->lead_id,
            'scheduled_at' => $this->followUp->date?->toIso8601String(),
        ]);
    }

    public function broadcastType(): string
    {
        return $this->urgency === 'emergency' ? 'emergency' : 'reminder';
    }
}
