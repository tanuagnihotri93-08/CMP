<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\PerformanceHistory;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_rejects_invalid_status_dates_and_oversized_description(): void
    {
        $admin = $this->userWithRole('super_admin');
        $this->grant($admin, 'projects.create');

        $this->actingAs($admin)
            ->from(route('projects.create'))
            ->post(route('projects.store'), [
                'name' => 'Valid project',
                'description' => str_repeat('x', 5001),
                'start_date' => '2026-10-01T12:30',
                'end_date' => '2026-10-02',
                'status' => 'Unknown',
            ])
            ->assertSessionHasErrors(['description', 'start_date', 'status']);

        $this->assertSame(0, Project::count());
    }

    public function test_task_create_rejects_inactive_assignees_and_invalid_field_types(): void
    {
        $manager = $this->userWithRole('manager');
        $this->grant($manager, 'tasks.create');
        $inactive = $this->userWithRole('employee', false);
        $project = Project::create([
            'name' => 'Validation project',
            'status' => 'Active',
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager)
            ->from(route('tasks.create'))
            ->post(route('tasks.store'), [
                'project_id' => (string) $project->id,
                'assigned_to' => $inactive->id,
                'title' => 'Valid title',
                'description' => str_repeat('x', 5001),
                'priority' => 'Urgent',
                'status' => 'Pending',
                'due_date' => 'tomorrow',
            ])
            ->assertSessionHasErrors(['assigned_to', 'description', 'priority', 'due_date']);

        $this->assertSame(0, Task::count());
    }

    public function test_employee_cannot_submit_a_project_reference_outside_their_access(): void
    {
        $employee = $this->userWithRole('employee');
        $colleague = $this->userWithRole('employee');
        $this->grant($employee, 'tasks.create');
        $project = Project::create([
            'name' => 'Colleague project',
            'status' => 'Active',
            'created_by' => $colleague->id,
        ]);
        Task::create([
            'project_id' => $project->id,
            'assigned_to' => $colleague->id,
            'created_by' => $colleague->id,
            'title' => 'Colleague task',
            'priority' => 'Medium',
            'status' => 'Pending',
        ]);

        $this->actingAs($employee)
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'assigned_to' => $employee->id,
                'title' => 'Unauthorized project task',
                'priority' => 'Medium',
                'status' => 'Pending',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('tasks', ['title' => 'Unauthorized project task']);
    }

    public function test_task_update_rejects_inactive_assignees(): void
    {
        $manager = $this->userWithRole('manager');
        $this->grant($manager, 'tasks.update');
        $inactive = $this->userWithRole('employee', false);
        $project = Project::create([
            'name' => 'Update project',
            'status' => 'Active',
            'created_by' => $manager->id,
        ]);
        $task = Task::create([
            'project_id' => $project->id,
            'assigned_to' => $manager->id,
            'created_by' => $manager->id,
            'title' => 'Existing task',
            'priority' => 'Medium',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($manager)
            ->from(route('tasks.edit', $task))
            ->put(route('tasks.update', $task), [
                'project_id' => $project->id,
                'assigned_to' => $inactive->id,
                'title' => 'Existing task',
                'priority' => 'Medium',
                'status' => 'Pending',
            ]);

        $this->assertSame(302, $response->status(), $response->getContent());
        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $manager->id,
        ]);
    }

    public function test_feedback_receiver_must_be_active(): void
    {
        $manager = $this->userWithRole('manager');
        $this->grant($manager, 'performance.feedback.create');
        $inactiveEmployee = $this->userWithRole('employee', false);

        $response = $this->actingAs($manager)
            ->from(route('performance-feedback.create'))
            ->post(route('performance-feedback.store'), [
                'receiver_id' => $inactiveEmployee->id,
                'message' => 'A useful review',
                'rating' => 4,
            ]);

        $this->assertSame(302, $response->status(), $response->getContent());
        $response->assertSessionHasErrors('receiver_id');

        $this->assertDatabaseCount('performance_feedbacks', 0);
    }

    public function test_performance_chart_safely_encodes_user_controlled_labels(): void
    {
        $manager = $this->userWithRole('manager');
        $this->grant($manager, 'performance.view');
        $employee = $this->userWithRole('employee');
        $payload = '</script><script>alert(1)</script>';
        $employee->forceFill(['name' => $payload])->save();
        PerformanceHistory::create([
            'user_id' => $employee->id,
            'snapshot_date' => today(),
            'total_score' => 75,
        ]);

        $this->actingAs($manager)
            ->get(route('performance.index'))
            ->assertOk()
            ->assertDontSee($payload, false);
    }

    private function userWithRole(string $roleName, bool $active = true): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => $role->id,
            'is_active' => $active,
        ])->save();

        return $user->fresh();
    }

    private function grant(User $user, string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        $user->role->permissions()->syncWithoutDetaching([$permission->id]);
    }
}
