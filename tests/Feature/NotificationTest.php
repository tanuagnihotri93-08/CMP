<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_assigned_records_notify_users_of_every_builtin_role(): void
    {
        $actor = $this->userWithRole('manager');

        foreach (['super_admin', 'manager', 'employee'] as $roleName) {
            $recipient = $this->userWithRole($roleName);
            $this->actingAs($actor);

            $lead = Lead::create([
                'name' => ucfirst($roleName).' Notification Prospect',
                'status' => 'new',
                'assigned_to_user_id' => $recipient->id,
            ]);

            $notification = $recipient->fresh()
                ->notifications()
                ->sole();

            $this->assertNull($notification->read_at);
            $this->assertSame('Lead assigned to you.', $notification->data['title']);
            $this->assertSame('A new CRM record is ready for you.', $notification->data['message']);
            $this->assertSame('leads', $notification->data['module']);
            $this->assertSame($lead->id, $notification->data['record_id']);

            $this->actingAs($recipient)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Notifications')
                ->assertSee('1 unread')
                ->assertSee('Lead assigned to you.');
        }
    }

    public function test_assignee_can_mark_one_notification_read_and_mark_all_read(): void
    {
        $actor = $this->userWithRole('manager');
        $recipient = $this->userWithRole('employee');
        $this->actingAs($actor);

        Lead::create([
            'name' => 'First Notification Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $recipient->id,
        ]);
        Lead::create([
            'name' => 'Second Notification Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $recipient->id,
        ]);

        $notifications = $recipient->fresh()->notifications()->oldest()->get();

        $this->actingAs($recipient)
            ->post(route('notifications.read', $notifications[0]->id))
            ->assertRedirect();

        $this->assertNotNull($notifications[0]->fresh()->read_at);
        $this->assertNull($notifications[1]->fresh()->read_at);
        $this->assertSame(1, $recipient->fresh()->unreadNotifications()->count());

        $this->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $recipient->fresh()->unreadNotifications()->count());
    }

    public function test_reassigned_record_notifies_only_the_new_assignee(): void
    {
        $actor = $this->userWithRole('manager');
        $originalAssignee = $this->userWithRole('employee');
        $newAssignee = $this->userWithRole('super_admin');
        $this->actingAs($actor);

        $lead = Lead::create([
            'name' => 'Reassignment Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $originalAssignee->id,
        ]);

        $lead->assigned_to_user_id = $newAssignee->id;
        $lead->save();

        $this->assertSame(1, $originalAssignee->fresh()->notifications()->count());
        $this->assertSame(1, $newAssignee->fresh()->notifications()->count());
        $this->assertSame('A CRM record was assigned to you.', $newAssignee->fresh()
            ->notifications()
            ->sole()
            ->data['message']);
        $this->assertSame(0, $actor->fresh()->notifications()->count());
    }

    public function test_users_cannot_read_another_users_notifications(): void
    {
        $actor = $this->userWithRole('manager');
        $owner = $this->userWithRole('employee');
        $otherUser = $this->userWithRole('super_admin');

        $this->actingAs($actor);
        Lead::create([
            'name' => 'Private Notification Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $owner->id,
        ]);
        $notification = $owner->fresh()->notifications()->sole();

        $this->actingAs($otherUser)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
        $this->assertSame(0, $otherUser->fresh()->notifications()->count());
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->role()->associate(Role::query()->where('name', $roleName)->firstOrFail());
        $user->save();

        return $user;
    }
}
