<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenetrationFindingRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_project_view_only_displays_tasks_assigned_to_them(): void
    {
        $employee = $this->userWithRole('employee');
        $colleague = $this->userWithRole('employee');
        $this->grant($employee, 'projects.view');
        $project = Project::create([
            'name' => 'Shared project',
            'status' => 'Active',
            'created_by' => $employee->id,
        ]);
        Task::create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
            'created_by' => $employee->id,
            'title' => 'Employee task',
            'priority' => 'Medium',
            'status' => 'Pending',
        ]);
        Task::create([
            'project_id' => $project->id,
            'assigned_to' => $colleague->id,
            'created_by' => $colleague->id,
            'title' => 'Colleague task',
            'priority' => 'High',
            'status' => 'Pending',
        ]);

        $this->actingAs($employee)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Employee task')
            ->assertDontSee('Colleague task');
    }

    public function test_manager_and_super_admin_can_still_see_all_tasks_in_a_project(): void
    {
        $creator = $this->userWithRole('employee');
        $assignee = $this->userWithRole('employee');
        $project = Project::create([
            'name' => 'Team project',
            'status' => 'Active',
            'created_by' => $creator->id,
        ]);
        Task::create([
            'project_id' => $project->id,
            'assigned_to' => $assignee->id,
            'created_by' => $creator->id,
            'title' => 'Team task',
            'priority' => 'Medium',
            'status' => 'Pending',
        ]);

        foreach (['manager', 'super_admin'] as $role) {
            $viewer = $this->userWithRole($role);
            $this->grant($viewer, 'projects.view');
            $this->actingAs($viewer)
                ->get(route('projects.show', $project))
                ->assertOk()
                ->assertSee('Team task');
        }
    }

    public function test_customer_cannot_be_attached_to_a_lead_the_employee_cannot_access(): void
    {
        $employee = $this->userWithRole('employee');
        $colleague = $this->userWithRole('employee');
        $this->grant($employee, 'customers.update');
        $customer = Customer::create([
            'name' => 'Employee customer',
            'status' => 'active',
            'assigned_to_user_id' => $employee->id,
        ]);
        $foreignLead = Lead::create([
            'name' => 'Colleague won lead',
            'status' => 'won',
            'assigned_to_user_id' => $colleague->id,
        ]);

        $this->actingAs($employee)
            ->put(route('customers.update', $customer), [
                'lead_id' => $foreignLead->id,
                'name' => $customer->name,
                'status' => 'active',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'lead_id' => null,
        ]);
    }

    public function test_customer_cannot_be_attached_to_an_accessible_lead_that_is_not_won(): void
    {
        $employee = $this->userWithRole('employee');
        $this->grant($employee, 'customers.update');
        $customer = Customer::create([
            'name' => 'Employee customer',
            'status' => 'active',
            'assigned_to_user_id' => $employee->id,
        ]);
        $openLead = Lead::create([
            'name' => 'Employee open lead',
            'status' => 'qualified',
            'assigned_to_user_id' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->from(route('customers.edit', $customer))
            ->put(route('customers.update', $customer), [
                'lead_id' => $openLead->id,
                'name' => $customer->name,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('lead_id');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'lead_id' => null,
        ]);
    }

    public function test_customer_update_still_allows_attaching_an_accessible_won_lead(): void
    {
        $employee = $this->userWithRole('employee');
        $this->grant($employee, 'customers.update');
        $customer = Customer::create([
            'name' => 'Employee customer',
            'status' => 'active',
            'assigned_to_user_id' => $employee->id,
        ]);
        $wonLead = Lead::create([
            'name' => 'Employee won lead',
            'status' => 'won',
            'assigned_to_user_id' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->put(route('customers.update', $customer), [
                'lead_id' => $wonLead->id,
                'name' => $customer->name,
                'status' => 'active',
            ])
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'lead_id' => $wonLead->id,
        ]);
    }

    public function test_deactivated_user_is_logged_out_and_cannot_access_protected_pages(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);
        User::whereKey($manager->id)->update(['is_active' => false]);

        $this->get(route('analytics.index'))->assertForbidden();

        $this->assertGuest();
    }

    public function test_analytics_access_requires_the_assigned_permission_for_managers(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager)
            ->get(route('analytics.index'))
            ->assertOk();

        $permission = Permission::where('name', 'analytics.view')->firstOrFail();
        $manager->role->permissions()->detach($permission->id);

        $this->actingAs($manager->fresh())
            ->get(route('analytics.index'))
            ->assertForbidden();
    }

    public function test_task_cannot_be_assigned_to_an_inactive_user(): void
    {
        $employee = $this->userWithRole('employee');
        $inactiveUser = $this->userWithRole('employee', false);
        $this->grant($employee, 'tasks.create');
        $project = Project::create([
            'name' => 'Task project',
            'status' => 'Active',
            'created_by' => $employee->id,
        ]);

        $this->actingAs($employee)
            ->from(route('tasks.create'))
            ->post(route('tasks.store'), [
                'project_id' => $project->id,
                'assigned_to' => $inactiveUser->id,
                'title' => 'Task for inactive account',
                'priority' => 'Medium',
                'status' => 'Pending',
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseMissing('tasks', [
            'title' => 'Task for inactive account',
        ]);
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
