<?php

namespace App\Notifications;

use App\Models\PerformanceFeedback;
use Illuminate\Notifications\Notification;

class PerformanceFeedbackNotification extends Notification
{
    public function __construct(
        private readonly PerformanceFeedback $feedback
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New performance feedback',
            'message' => $this->feedback->message,
            'action_url' => route('performance-feedback.index'),
            'feedback_id' => $this->feedback->id,
            'sender_id' => $this->feedback->sender_id,
            'sender_name' => $this->feedback->sender?->name,
            'status' => $this->feedback->status,
            'created_at' => $this->feedback->created_at?->toIso8601String(),
        ];
    }
}
