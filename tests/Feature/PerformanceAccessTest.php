<?php

namespace Tests\Feature;

use App\Models\Performance;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceAccessTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, bool $withPermission): User
    {
        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucfirst(str_replace('_', ' ', $roleName))]
        );

        if ($withPermission) {
            $permission = Permission::firstOrCreate(
                ['name' => 'performance.view'],
                ['description' => 'View performance dashboard']
            );
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::factory()->create();

        // Factory har user ko manager role deti hai, isliye baad mein override
        $user->forceFill(['role_id' => $role->id, 'is_active' => true])->save();

        return $user->fresh();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/performance')->assertRedirect('/login');
    }

    public function test_employee_gets_403(): void
    {
        $this->actingAs($this->userWithRole('employee', false))
            ->get('/performance')
            ->assertForbidden();
    }

    public function test_manager_gets_200(): void
    {
        $this->actingAs($this->userWithRole('manager', true))
            ->get('/performance')
            ->assertOk();
    }

    public function test_super_admin_gets_200(): void
    {
        $this->actingAs($this->userWithRole('super_admin', true))
            ->get('/performance')
            ->assertOk();
    }

    public function test_manager_sees_only_employee_rows(): void
    {
        $manager = $this->userWithRole('manager', true);
        $employee = $this->userWithRole('employee', false);
        $otherManager = $this->userWithRole('manager', true);

        Performance::create(['user_id' => $employee->id, 'total_score' => 40]);
        Performance::create(['user_id' => $otherManager->id, 'total_score' => 90]);

        $response = $this->actingAs($manager)->get('/performance')->assertOk();

        $ids = $response->viewData('performance')->pluck('user_id')->all();

        $this->assertContains($employee->id, $ids);
        $this->assertNotContains($otherManager->id, $ids);
    }

    public function test_super_admin_sees_managers_and_employees(): void
    {
        $admin = $this->userWithRole('super_admin', true);
        $employee = $this->userWithRole('employee', false);
        $manager = $this->userWithRole('manager', true);

        Performance::create(['user_id' => $employee->id, 'total_score' => 40]);
        Performance::create(['user_id' => $manager->id, 'total_score' => 60]);
        Performance::create(['user_id' => $admin->id, 'total_score' => 99]);

        $response = $this->actingAs($admin)->get('/performance')->assertOk();

        $ids = $response->viewData('performance')->pluck('user_id')->all();

        $this->assertContains($employee->id, $ids);
        $this->assertContains($manager->id, $ids);
        $this->assertNotContains($admin->id, $ids);
    }
}