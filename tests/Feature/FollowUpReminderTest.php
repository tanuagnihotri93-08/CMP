<?php

namespace Tests\Feature;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use App\Notifications\FollowUpReminderNotification;
use App\Console\Commands\SendFollowUpReminders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FollowUpReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_scan_has_a_matching_composite_index(): void
    {
        $this->assertTrue(Schema::hasIndex(
            'follow_ups',
            ['status', 'reminder_sent_at', 'date']
        ));
    }

    public function test_reminder_is_sent_to_assigned_user_five_minutes_before_follow_up(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole('employee');
        $lead = Lead::create([
            'name' => 'Reminder Lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(5),
            'type' => 'Call',
            'remark' => 'Reminder test',
            'status' => 'scheduled',
            'assigned_to_user_id' => $user->id,
        ]);

        app(SendFollowUpReminders::class)->handle();

        Queue::assertPushed(SendQueuedNotifications::class, function ($job) use ($followUp): bool {
            $notification = $job->notification;
            return $notification instanceof FollowUpReminderNotification
                && $notification->followUp->is($followUp);
        });

        $this->assertNotNull($followUp->fresh()->reminder_sent_at);
    }

    public function test_reminder_is_not_sent_when_follow_up_is_more_than_five_minutes_away(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole('employee');
        $lead = Lead::create([
            'name' => 'Far Future Lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(10),
            'type' => 'Meeting',
            'remark' => 'Too early',
            'status' => 'scheduled',
            'assigned_to_user_id' => $user->id,
        ]);

        app(SendFollowUpReminders::class)->handle();

        $this->assertNull($followUp->fresh()->reminder_sent_at);
    }

    public function test_reminder_is_not_sent_when_follow_up_is_already_completed(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole('employee');
        $lead = Lead::create([
            'name' => 'Completed Lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(5),
            'type' => 'Call',
            'remark' => 'Already completed',
            'status' => 'completed',
            'assigned_to_user_id' => $user->id,
        ]);

        app(SendFollowUpReminders::class)->handle();

        $this->assertNull($followUp->fresh()->reminder_sent_at);
    }

    public function test_duplicate_reminder_is_not_sent_when_reminder_sent_at_is_set(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole('employee');
        $lead = Lead::create([
            'name' => 'Duplicate Lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(5),
            'type' => 'Call',
            'remark' => 'Duplicate test',
            'status' => 'scheduled',
            'assigned_to_user_id' => $user->id,
            'reminder_sent_at' => now(),
        ]);

        app(SendFollowUpReminders::class)->handle();

        $this->assertNotNull($followUp->fresh()->reminder_sent_at);
    }

    public function test_notification_uses_existing_queue_settings(): void
    {
        Queue::fake();

        $user = $this->createUserWithRole('employee');
        $lead = Lead::create([
            'name' => 'Queue Settings Lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(5),
            'type' => 'Email',
            'remark' => 'Queue settings test',
            'status' => 'scheduled',
            'assigned_to_user_id' => $user->id,
        ]);

        $user->notify(new FollowUpReminderNotification($followUp));

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job): bool {
            return $job->tries === 3
                && $job->timeout === 60
                && $job->backoff() === [10, 60]
                && $job->afterCommit === true;
        });
    }

    public function test_reminder_is_not_sent_to_inactive_assigned_user(): void
    {
        Queue::fake();

        $inactiveUser = User::factory()->create();
        $inactiveUser->forceFill(['is_active' => false])->save();
        $inactiveUser->refresh();

        $this->assertFalse($inactiveUser->is_active);

        $inactiveRole = Role::query()->where('name', 'employee')->firstOrFail();
        $inactiveUser->role()->associate($inactiveRole);
        $inactiveUser->save();

        $lead = Lead::create([
            'name' => 'Inactive Assignee Lead',
            'status' => 'new',
            'assigned_to_user_id' => $inactiveUser->id,
        ]);

        FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addMinutes(5),
            'type' => 'Call',
            'remark' => 'Inactive assignee',
            'status' => 'scheduled',
            'assigned_to_user_id' => $inactiveUser->id,
        ]);

        $followUp = FollowUp::where('type', 'Call')->where('remark', 'Inactive assignee')->first();

        app(SendFollowUpReminders::class)->handle();

        $this->assertNull($followUp->fresh()->reminder_sent_at);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::query()->where('name', $roleName)->firstOrFail();
        $user->role()->associate($role);
        $user->save();

        return $user;
    }
}
