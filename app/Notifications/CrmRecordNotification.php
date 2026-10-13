<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CrmRecordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly string $module,
        private readonly int $recordId,
        private readonly ?string $actionUrl = null,
        private readonly string $type = 'info'
    ) {}

    public function via(object $notifiable): array
    {
        // Database ke sath real-time broadcast ke liye 'broadcast' channel bhi add kar sakte hain
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
{
    return [
        'title' => $this->title,
        'message' => $this->message,
        'module' => $this->module,
        'record_id' => $this->recordId,
        'action_url' => $this->actionUrl ?? $this->recordUrl(),
        'type' => $this->type,
        'created_at' => now()->toIso8601String(),
    ];
}

public function toBroadcast(object $notifiable): BroadcastMessage
{
    return new BroadcastMessage([
        'title' => $this->title,
        'message' => $this->message,
        'module' => $this->module,
        'record_id' => $this->recordId,
        'action_url' => $this->actionUrl ?? $this->recordUrl(),
        'type' => $this->type,
    ]);
}

private function recordUrl(): string
{
    $routePrefix = ['follow_ups' => 'followups'][$this->module] ?? $this->module;

    return route("{$routePrefix}.show", $this->recordId);
}
}