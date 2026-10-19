<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Performance;
use App\Models\PerformanceHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_command_scores_lowercase_completed_followups_and_reuses_grouped_counts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createScoringRecords($user);

        $this->artisan('performance:calculate')->assertExitCode(0);

        $performance = Performance::where('user_id', $user->id)->sole();
        $this->assertSame(10, $performance->lead_score);
        $this->assertSame(50, $performance->followup_score);
        $this->assertSame(0, $performance->activity_score);
        $this->assertSame(0, $performance->conversion_score);
        $this->assertSame(15, $performance->total_score);
        $this->assertSame(1, $performance->leads_count);
        $this->assertSame(1, $performance->customers_count);
        $this->assertSame(1, $performance->pending_followups_count);

        $this->assertDatabaseHas('performance_histories', [
            'user_id' => $user->id,
            'followup_score' => 50,
            'total_score' => 15,
            'pending_followups_count' => 1,
        ]);
    }

    public function test_analytics_counts_scheduled_followups_as_pending(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->createScoringRecords($user);

        $response = $this->get(route('analytics.index'))->assertOk();

        $this->assertSame(1, $response->viewData('pendingFollowUps'));
    }

    public function test_performance_dashboard_counts_scheduled_followups_as_pending(): void
    {
        $manager = User::factory()->create();
        $permission = Permission::firstOrCreate(
            ['name' => 'performance.view'],
            ['description' => 'View performance dashboard']
        );
        $manager->role->permissions()->syncWithoutDetaching([$permission->id]);
        $employee = User::factory()->create();
        $employee->forceFill([
            'role_id' => Role::query()->where('name', 'employee')->value('id'),
        ])->save();

        $this->actingAs($manager);
        $this->createScoringRecords($employee);

        $response = $this->get(route('performance.index'))->assertOk();

        $this->assertSame(1, $response->viewData('totalPendingFollowUps'));
    }

    public function test_performance_dashboard_counts_each_lowercase_lead_status(): void
    {
        $manager = User::factory()->create();
        $permission = Permission::firstOrCreate(
            ['name' => 'performance.view'],
            ['description' => 'View performance dashboard']
        );
        $manager->role->permissions()->syncWithoutDetaching([$permission->id]);

        $employee = User::factory()->create();
        $employee->forceFill([
            'role_id' => Role::query()->where('name', 'employee')->value('id'),
        ])->save();

        foreach (['new', 'qualified', 'proposal', 'won', 'lost'] as $status) {
            Lead::create([
                'name' => ucfirst($status).' lead',
                'status' => $status,
                'assigned_to_user_id' => $employee->id,
            ]);
        }

        $response = $this->actingAs($manager)
            ->get(route('performance.index'))
            ->assertOk();

        $this->assertSame([
            'New' => 1,
            'Qualified' => 1,
            'Proposal' => 1,
            'Won' => 1,
            'Lost' => 1,
        ], $response->viewData('leadStatus'));
    }

    public function test_performance_dashboard_limits_trend_history_to_recent_ninety_days(): void
    {
        $manager = User::factory()->create();
        $permission = Permission::firstOrCreate(
            ['name' => 'performance.view'],
            ['description' => 'View performance dashboard']
        );
        $manager->role->permissions()->syncWithoutDetaching([$permission->id]);
        $employee = User::factory()->create();
        $employee->forceFill([
            'role_id' => Role::query()->where('name', 'employee')->value('id'),
        ])->save();
        Performance::create([
            'user_id' => $employee->id,
            'total_score' => 50,
        ]);

        for ($daysAgo = 0; $daysAgo < 120; $daysAgo++) {
            PerformanceHistory::create([
                'user_id' => $employee->id,
                'snapshot_date' => today()->subDays($daysAgo),
                'total_score' => 50,
            ]);
        }

        $response = $this->actingAs($manager)
            ->get(route('performance.index'))
            ->assertOk();

        $trend = $response->viewData('performanceTrend')->first();
        $this->assertCount(90, $trend['dates']);
        $this->assertCount(90, $trend['scores']);
    }

    public function test_conversion_rate_uses_won_leads_consistently_across_dashboard_analytics_and_performance(): void
    {
        $manager = User::factory()->create();
        $employee = User::factory()->create();
        $employee->forceFill([
            'role_id' => Role::query()->where('name', 'employee')->value('id'),
        ])->save();
        $permission = Permission::firstOrCreate(
            ['name' => 'performance.view'],
            ['description' => 'View performance dashboard']
        );
        $manager->role->permissions()->syncWithoutDetaching([$permission->id]);

        Lead::create(['name' => 'Won lead', 'status' => 'won', 'assigned_to_user_id' => $employee->id]);
        Lead::create(['name' => 'New lead one', 'status' => 'new', 'assigned_to_user_id' => $employee->id]);
        Lead::create(['name' => 'New lead two', 'status' => 'new', 'assigned_to_user_id' => $employee->id]);

        foreach (range(1, 4) as $number) {
            Customer::create([
                'name' => "Unlinked customer {$number}",
                'status' => 'active',
                'assigned_to_user_id' => $employee->id,
            ]);
        }

        $this->actingAs($manager);
        $dashboardRate = $this->get(route('dashboard'))->assertOk()->viewData('conversionRate');
        $analyticsRate = $this->get(route('analytics.index'))->assertOk()->viewData('conversionRate');
        $performanceDashboardRate = $this->get(route('performance.index'))->assertOk()->viewData('conversionRate');

        $this->artisan('performance:calculate')->assertExitCode(0);
        $performanceRate = Performance::where('user_id', $employee->id)->sole()->conversion_score;

        $this->assertSame(33.33, $dashboardRate);
        $this->assertSame(33.33, $analyticsRate);
        $this->assertSame(33.33, $performanceDashboardRate);
        $this->assertSame(33, $performanceRate);
    }

    private function createScoringRecords(User $user): void
    {
        $lead = Lead::create([
            'name' => 'Performance metric lead',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);

        Customer::create([
            'lead_id' => $lead->id,
            'name' => 'Performance metric customer',
            'status' => 'active',
            'assigned_to_user_id' => $user->id,
        ]);

        foreach (['completed', 'scheduled'] as $status) {
            FollowUp::create([
                'lead_id' => $lead->id,
                'date' => now(),
                'type' => 'Call',
                'status' => $status,
                'assigned_to_user_id' => $user->id,
            ]);
        }
    }
}
