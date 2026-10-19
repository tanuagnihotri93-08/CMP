<?php

namespace Tests\Feature;

use App\Models\FollowUp;
use App\Models\PerformanceFeedback;
use App\Models\User;
use App\Notifications\PerformanceFeedbackNotification;
use App\Notifications\FollowUpReminderNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class NotificationBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_up_notification_uses_laravels_private_user_channel_and_broadcast_event_type(): void
    {
        $user = new User;
        $user->id = 42;
        $followUp = new FollowUp;
        $followUp->id = 9;
        $notification = new FollowUpReminderNotification($followUp, 'reminder');
        $payload = $notification->toBroadcast($user)->data;
        $event = new BroadcastNotificationCreated($user, $notification, $payload);

        $this->assertSame('reminder', $notification->broadcastType());
        $this->assertSame('reminder', $event->broadcastWith()['type']);
        $this->assertSame('private-App.Models.User.42', $event->broadcastOn()[0]->name);
    }

    public function test_emergency_follow_up_keeps_its_emergency_broadcast_type(): void
    {
        $user = new User;
        $notification = new FollowUpReminderNotification(new FollowUp, 'emergency');
        $event = new BroadcastNotificationCreated($user, $notification, []);

        $this->assertSame('emergency', $event->broadcastWith()['type']);
        $this->assertInstanceOf(PrivateChannel::class, $event->broadcastOn()[0]);
    }

    public function test_performance_feedback_notification_is_broadcast_as_well_as_stored(): void
    {
        $notification = new PerformanceFeedbackNotification(new PerformanceFeedback([
            'message' => 'Good work this month.',
        ]));

        $this->assertContains('broadcast', $notification->via(new User));
        $this->assertSame('performance_feedback', $notification->broadcastType());
        $this->assertSame(
            'performance_feedback',
            $notification->toBroadcast(new User)->data['type']
        );
    }

    public function test_broadcast_channel_auth_allows_only_the_signed_in_users_channel(): void
    {
        config(['broadcasting.default' => 'reverb']);
        Broadcast::channel('App.Models.User.{id}', fn (User $user, string $id): bool => (int) $user->id === (int) $id);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'private-App.Models.User.'.$user->id,
            ])
            ->assertOk();

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-App.Models.User.'.($user->id + 1),
        ])->assertForbidden();
    }
}
