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

class ActivityTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_timeline_shows_visible_activity_and_the_user_who_created_it(): void
    {
        $viewer = User::factory()->create(['name' => 'Timeline Viewer']);
        $this->actingAs($viewer);
        $lead = Lead::create([
            'name' => 'Timeline Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $viewer->id,
        ]);
        Activity::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $viewer->id,
            'type' => 'Call',
            'subject' => 'Discovery call',
            'activity_date' => now(),
            'status' => 'pending',
        ]);
        FollowUp::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $viewer->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'status' => 'scheduled',
        ]);

        $this->actingAs($viewer)
            ->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Activity Timeline')
            ->assertSee('Lead created.')
            ->assertSee('Activity created.')
            ->assertSee('Follow up created.')
            ->assertSee('Timeline Viewer');
    }

    public function test_timeline_omits_history_for_related_records_the_viewer_cannot_access(): void
    {
        $viewer = User::factory()->create();
        $viewer->role()->associate(Role::query()->where('name', 'employee')->firstOrFail());
        $viewer->save();
        $otherUser = User::factory()->create();
        $this->actingAs($viewer);
        $lead = Lead::create([
            'name' => 'Assigned Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $viewer->id,
        ]);
        $this->actingAs($otherUser);
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $otherUser->id,
            'type' => 'Task',
            'subject' => 'Private related activity',
            'activity_date' => now(),
            'status' => 'pending',
        ]);
        $this->assertSame($otherUser->id, (int) $activity->assigned_to_user_id);

        $this->assertSame(1, AuditLog::query()
            ->where('record_type', Activity::class)
            ->where('record_id', $activity->id)
            ->count());

        $this->actingAs($viewer)
            ->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Activity Timeline')
            ->assertDontSee('Activity created.');
    }

    public function test_timeline_omits_activity_history_without_activity_view_permission(): void
    {
        $viewer = User::factory()->create();
        $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();
        $viewer->role()->associate($employeeRole);
        $viewer->save();
        $employeeRole->permissions()->detach(
            Permission::query()->where('name', 'activities.view')->value('id')
        );

        $this->actingAs($viewer);
        $lead = Lead::create([
            'name' => 'Permission Scoped Prospect',
            'status' => 'new',
            'assigned_to_user_id' => $viewer->id,
        ]);
        Activity::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $viewer->id,
            'type' => 'Task',
            'subject' => 'Permission scoped activity',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Activity Timeline')
            ->assertDontSee('Activity created.');
    }

    public function test_customer_timeline_includes_visible_linked_lead_and_activity_history(): void
    {
        $viewer = User::factory()->create(['name' => 'Customer Timeline Viewer']);
        $this->actingAs($viewer);
        $lead = Lead::create([
            'name' => 'Converted Prospect',
            'status' => 'won',
            'assigned_to_user_id' => $viewer->id,
        ]);
        $customer = Customer::create([
            'lead_id' => $lead->id,
            'assigned_to_user_id' => $viewer->id,
            'name' => 'Timeline Customer',
            'status' => 'active',
        ]);
        Activity::create([
            'customer_id' => $customer->id,
            'assigned_to_user_id' => $viewer->id,
            'type' => 'Task',
            'subject' => 'Customer follow-up',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $this->actingAs($viewer)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Activity Timeline')
            ->assertSee('Customer created.')
            ->assertSee('Lead created.')
            ->assertSee('Activity created.')
            ->assertSee('Customer Timeline Viewer');
    }
}
