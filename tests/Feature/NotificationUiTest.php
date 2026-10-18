<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_their_notification_list_and_read_states(): void
    {
        $user = $this->userWithRole('employee');

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\CrmRecordNotification',
            'data' => [
                'title' => 'Unread CRM notice',
                'message' => 'An update needs your attention.',
            ],
        ]);

        $readNotification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\CrmRecordNotification',
            'data' => [
                'title' => 'Read CRM notice',
                'message' => 'This update was already reviewed.',
            ],
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('1 unread')
            ->assertSee('Unread CRM notice')
            ->assertSee('An update needs your attention.')
            ->assertSee('Unread')
            ->assertSee('Read CRM notice')
            ->assertSee('Read');

        $this->assertNotNull($readNotification->read_at);
    }

    public function test_clicking_a_notification_marks_it_read_and_redirects_to_its_page(): void
    {
        $user = $this->userWithRole('employee');
        $destination = route('performance-feedback.index');

        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\PerformanceFeedbackNotification',
            'data' => [
                'title' => 'New performance feedback',
                'message' => 'Keep up the good work.',
                'action_url' => $destination,
            ],
        ]);

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect($destination);

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_unsafe_notification_destinations_fall_back_to_the_previous_page(): void
    {
        $user = $this->userWithRole('employee');
        $dashboard = route('dashboard');

        foreach ([
            'https://attacker.example/phish',
            '//attacker.example/phish',
            'javascript:alert(1)',
        ] as $actionUrl) {
            $notification = $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\CrmRecordNotification',
                'data' => ['action_url' => $actionUrl],
            ]);

            $this->actingAs($user)
                ->from('https://attacker.example/phish')
                ->post(route('notifications.read', $notification->id))
                ->assertRedirect($dashboard);

            $this->assertNotNull($notification->fresh()->read_at);
        }
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->role()->associate(Role::query()->where('name', $roleName)->firstOrFail());
        $user->save();

        return $user;
    }
}
