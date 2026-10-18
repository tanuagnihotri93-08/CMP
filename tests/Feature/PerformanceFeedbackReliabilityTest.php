<?php

namespace Tests\Feature;

use App\Models\PerformanceFeedback;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PerformanceFeedbackReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_and_database_notification_are_created_together(): void
    {
        $manager = $this->userWithRole('manager');
        $employee = $this->userWithRole('employee');

        $this->actingAs($manager)
            ->post(route('performance-feedback.store'), [
                'receiver_id' => $employee->id,
                'message' => 'Strong work this month.',
                'rating' => 5,
            ])
            ->assertRedirect(route('performance-feedback.index'));

        $feedback = PerformanceFeedback::sole();
        $this->assertSame($employee->id, $feedback->receiver_id);
        $this->assertSame(5, $feedback->rating);
        $this->assertSame('Open', $feedback->status);
        $this->assertSame('New performance feedback', $employee->notifications()->sole()->data['title']);
    }

    public function test_feedback_record_rolls_back_when_notification_delivery_fails(): void
    {
        $manager = $this->userWithRole('manager');
        $employee = $this->userWithRole('employee');

        $this->mock(Dispatcher::class)
            ->shouldReceive('send')
            ->once()
            ->andThrow(new RuntimeException('Notification persistence failed.'));

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($manager)->post(route('performance-feedback.store'), [
                'receiver_id' => $employee->id,
                'message' => 'This record must roll back.',
                'rating' => 4,
            ]);

            $this->fail('The simulated notification failure should reach the test.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Notification persistence failed.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('performance_feedbacks', [
            'sender_id' => $manager->id,
            'receiver_id' => $employee->id,
            'message' => 'This record must roll back.',
        ]);
        $this->assertSame(0, $employee->fresh()->notifications()->count());
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->role()->associate(Role::query()->where('name', $roleName)->firstOrFail());
        $user->save();

        return $user;
    }
}
