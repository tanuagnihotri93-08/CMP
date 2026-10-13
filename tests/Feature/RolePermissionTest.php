<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_full_crm_and_system_access(): void
    {
        $admin = $this->userWithRole('super_admin');
        $lead = Lead::create([
            'name' => 'Admin-owned lead',
            'status' => 'new',
            'assigned_to_user_id' => $admin->id,
        ]);
        $this->actingAs($admin);

        $this->get(route('leads.index'))->assertOk();
        $this->get(route('leads.create'))->assertSee('Assigned to');
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk();
        $this->get(route('audit-logs.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->delete(route('leads.destroy', $lead))->assertRedirect(route('leads.index'));
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_manager_can_manage_crm_but_cannot_delete_critical_records_or_manage_system(): void
    {
        $manager = $this->userWithRole('manager');
        $this->assertSame('manager', $manager->role->name);
        $this->assertContains('leads.view', $manager->role->permissions()->pluck('name')->all());
        $this->assertTrue($manager->hasPermission('leads.view'));
        $lead = Lead::create(['name' => 'Manager lead', 'status' => 'new']);
        $customer = Customer::create(['name' => 'Manager customer', 'status' => 'active']);
        $this->actingAs($manager);

        $this->get(route('leads.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->get(route('leads.index'))
            ->assertDontSee('data-confirm="This will permanently delete the lead', false);
        $this->get(route('audit-logs.index'))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.roles.index'))->assertForbidden();
        $this->delete(route('leads.destroy', $lead))->assertForbidden();
        $this->delete(route('customers.destroy', $customer))->assertForbidden();
        $this->assertDatabaseHas('leads', ['id' => $lead->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_employee_is_limited_to_assigned_records_and_permitted_actions(): void
    {
        $employee = $this->userWithRole('employee');
        $otherUser = $this->userWithRole('employee');
        $ownLead = Lead::create([
            'name' => 'Assigned prospect',
            'status' => 'new',
            'assigned_to_user_id' => $employee->id,
        ]);
        $otherLead = Lead::create([
            'name' => 'Other prospect',
            'status' => 'new',
            'assigned_to_user_id' => $otherUser->id,
        ]);
        $customer = Customer::create([
            'name' => 'Employee customer',
            'status' => 'active',
            'assigned_to_user_id' => $employee->id,
        ]);
        $followUp = FollowUp::create([
            'lead_id' => $ownLead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'status' => 'scheduled',
            'assigned_to_user_id' => $employee->id,
        ]);
        $activity = Activity::create([
            'lead_id' => $ownLead->id,
            'type' => 'Note',
            'subject' => 'Assigned activity',
            'activity_date' => now(),
            'status' => 'pending',
            'assigned_to_user_id' => $employee->id,
        ]);
        $this->actingAs($employee);

        $this->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Assigned prospect')
            ->assertDontSee('Other prospect');
        $this->get(route('leads.show', $ownLead))->assertOk();
        $this->get(route('leads.show', $otherLead))->assertNotFound();
        $this->get(route('followups.show', $followUp))->assertOk();
        $this->get(route('activities.show', $activity))->assertOk();
        $this->get(route('customers.show', $customer))->assertOk();
        $this->get(route('customers.show', $customer))
            ->assertDontSee('Edit Customer')
            ->assertDontSee('Delete Customer');
        $this->get(route('leads.show', $ownLead))
            ->assertDontSee('Convert to Customer');
        $this->get(route('followups.show', $followUp))
            ->assertDontSee('Delete Follow Up');
        $this->get(route('activities.show', $activity))
            ->assertDontSee('Delete Activity');
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('audit-logs.index'))->assertForbidden();
        $this->get(route('admin.roles.index'))->assertForbidden();
        $this->delete(route('leads.destroy', $ownLead))->assertForbidden();
        $this->delete(route('followups.destroy', $followUp))->assertForbidden();
        $this->delete(route('activities.destroy', $activity))->assertForbidden();
        $this->from(route('customers.index'))
            ->post(route('customers.store'), [
                'name' => 'Unauthorized customer',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_employee_dashboard_does_not_offer_customer_creation(): void
    {
        $this->actingAs($this->userWithRole('employee'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('customers.create'), false);
    }

    public function test_employee_cannot_update_another_users_follow_up_even_with_an_accessible_lead(): void
    {
        $employee = $this->userWithRole('employee');
        $otherEmployee = $this->userWithRole('employee');
        $lead = Lead::create([
            'name' => 'Employee accessible lead',
            'status' => 'new',
            'assigned_to_user_id' => $employee->id,
        ]);
        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'remark' => 'Private reminder',
            'status' => 'scheduled',
            'assigned_to_user_id' => $otherEmployee->id,
        ]);
        $this->actingAs($employee);

        $this->put(route('followups.update', $followUp), [
            'lead_id' => $lead->id,
            'date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'type' => 'Meeting',
            'remark' => 'Unauthorized change',
            'status' => 'completed',
        ])->assertNotFound();

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'remark' => 'Private reminder',
            'status' => 'scheduled',
            'assigned_to_user_id' => $otherEmployee->id,
        ]);
    }

    public function test_employee_created_records_are_assigned_to_them_and_unassigned_links_are_denied(): void
    {
        $employee = $this->userWithRole('employee');
        $otherEmployee = $this->userWithRole('employee');
        $otherLead = Lead::create([
            'name' => 'Private lead',
            'status' => 'new',
            'assigned_to_user_id' => $otherEmployee->id,
        ]);
        $this->actingAs($employee);

        $this->post(route('leads.store'), [
            'name' => 'Employee created lead',
            'status' => 'new',
        ])->assertRedirect(route('leads.index'));

        $createdLead = Lead::where('name', 'Employee created lead')->firstOrFail();
        $this->assertSame($employee->id, $createdLead->assigned_to_user_id);

        $this->from(route('followups.create'))
            ->post(route('followups.store'), [
                'lead_id' => $otherLead->id,
                'date' => now()->addDay()->format('Y-m-d\TH:i'),
                'type' => 'Call',
                'status' => 'scheduled',
            ])
            ->assertNotFound();

        $this->post(route('followups.store'), [
            'lead_id' => $createdLead->id,
            'date' => now()->addDay()->format('Y-m-d\TH:i'),
            'type' => 'Call',
            'status' => 'scheduled',
        ])->assertRedirect(route('followups.index'));

        $this->assertSame(
            $employee->id,
            FollowUp::where('lead_id', $createdLead->id)->firstOrFail()->assigned_to_user_id
        );

        $this->post(route('activities.store'), [
            'lead_id' => $createdLead->id,
            'type' => 'Call',
            'subject' => 'Employee activity',
            'activity_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'pending',
            'assigned_to_user_id' => $otherEmployee->id,
        ])->assertSessionHasErrors('assigned_to_user_id');

        $this->post(route('activities.store'), [
            'lead_id' => $createdLead->id,
            'type' => 'Call',
            'subject' => 'Employee activity',
            'activity_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'pending',
        ])->assertRedirect(route('activities.index'));

        $this->assertSame(
            $employee->id,
            Activity::where('subject', 'Employee activity')->firstOrFail()->assigned_to_user_id
        );
    }

    public function test_user_role_and_activation_changes_are_audited_and_last_super_admin_is_protected(): void
    {
        $admin = $this->userWithRole('super_admin');
        $employee = $this->userWithRole('employee');
        $this->actingAs($admin);

        $this->put(route('admin.users.update', $employee), [
            'role_id' => Role::where('name', 'manager')->value('id'),
            'is_active' => false,
        ])->assertRedirect(route('admin.users.index'));

        $employee->refresh();
        $this->assertSame('manager', $employee->role->name);
        $this->assertFalse($employee->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'users',
            'action' => 'role_changed',
            'record_id' => $employee->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'users',
            'action' => 'deactivated',
            'record_id' => $employee->id,
        ]);

        $this->put(route('admin.users.update', $admin), [
            'role_id' => Role::where('name', 'manager')->value('id'),
            'is_active' => false,
        ])->assertStatus(422);
    }

    public function test_role_permission_changes_are_logged_and_inactive_users_are_blocked(): void
    {
        $admin = $this->userWithRole('super_admin');
        $employee = $this->userWithRole('employee');
        $this->actingAs($admin);

        $permission = Permission::where('name', 'leads.view')->firstOrFail();
        $this->put(route('admin.roles.update', $employee->role), [
            'permissions' => [$permission->id],
        ])->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'roles',
            'action' => 'permissions_changed',
            'record_id' => $employee->role_id,
        ]);

        $employee->forceFill(['is_active' => false])->save();
        $this->actingAs($employee)
            ->get(route('leads.index'))
            ->assertForbidden();
    }

    public function test_permission_management_views_group_existing_permissions_and_remain_admin_only(): void
    {
        $admin = $this->userWithRole('super_admin');
        $employee = $this->userWithRole('employee');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('ACCESS GOVERNANCE')
            ->assertSee('Protected system role')
            ->assertSee('Configure permissions');

        $this->get(route('admin.roles.edit', $employee->role))
            ->assertOk()
            ->assertSee('Lead management')
            ->assertSee('Reports and analytics')
            ->assertSee('name="permissions[]"', false)
            ->assertSee('Save permissions');

        $manager = $this->userWithRole('manager');
        $this->actingAs($manager)
            ->get(route('admin.roles.edit', $employee->role))
            ->assertForbidden();
    }

    public function test_manager_can_assign_a_record_but_inactive_users_cannot_sign_in(): void
    {
        $manager = $this->userWithRole('manager');
        $employee = $this->userWithRole('employee');
        $this->actingAs($manager);

        $this->post(route('leads.store'), [
            'name' => 'Assigned by manager',
            'status' => 'qualified',
            'assigned_to_user_id' => $employee->id,
        ])->assertRedirect(route('leads.index'));

        $assignedLead = Lead::where('name', 'Assigned by manager')->firstOrFail();
        $this->assertSame($employee->id, $assignedLead->assigned_to_user_id);
        $this->actingAs($employee)
            ->get(route('leads.show', $assignedLead))
            ->assertOk();

        $employee->forceFill(['is_active' => false])->save();
        auth()->logout();
        $this->from(route('login'))->post(route('login'), [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->forceFill([
            'role_id' => $role->id,
            'is_admin' => $roleName === 'super_admin',
        ])->save();

        return $user;
    }
}
