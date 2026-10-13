<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmptyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_crm_modules_present_contextual_states_and_allowed_actions(): void
    {
        $admin = $this->userWithRole('super_admin');
        $this->actingAs($admin);

        $this->get(route('leads.index'))
            ->assertOk()
            ->assertSee('No leads found')
            ->assertSee(route('leads.create'));
        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee('No customers yet')
            ->assertSee(route('customers.create'));
        $this->get(route('followups.index'))
            ->assertOk()
            ->assertSee('No follow-ups scheduled')
            ->assertSee(route('followups.create'));
        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('No activities recorded')
            ->assertSee(route('activities.create'));
        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('No leads data in this report')
            ->assertSee('No customers data in this report');
        $this->get(route('audit-logs.index'))
            ->assertOk()
            ->assertSee('No audit events recorded');
        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User directory')
            ->assertSee($admin->email);
    }

    public function test_empty_module_states_hide_create_actions_when_user_lacks_create_permission(): void
    {
        $employee = $this->userWithRole('employee');
        $this->actingAs($employee);

        $this->get(route('customers.index'))
            ->assertOk()
            ->assertSee('No customers yet')
            ->assertDontSee('crm-empty-state-action');
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => Role::where('name', $roleName)->value('id'),
            'is_admin' => $roleName === 'super_admin',
        ])->save();

        return $user;
    }
}
