<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAndFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_won_lead_can_be_converted_to_a_customer(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Jamie Customer',
            'company_name' => 'Example Co',
            'email' => 'jamie@example.test',
            'phone' => '555-0100',
            'status' => 'won',
        ]);

        $this->get(route('customers.create', ['lead_id' => $lead->id]))
            ->assertOk()
            ->assertSee('jamie@example.test')
            ->assertSee('Convert');

        $this->post(route('customers.store'), [
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'company' => $lead->company_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('customers', [
            'lead_id' => $lead->id,
            'name' => 'Jamie Customer',
            'company' => 'Example Co',
            'status' => 'active',
        ]);

        $this->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('View Customer');

        $customer = Customer::where('lead_id', $lead->id)->firstOrFail();
        $this->get(route('customers.index'))->assertOk();
        $this->get(route('customers.show', $customer))->assertOk();
        $this->get(route('customers.edit', $customer))->assertOk();
    }

    public function test_follow_ups_can_be_scheduled_and_marked_complete(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Taylor Prospect',
            'status' => 'qualified',
        ]);

        $this->post(route('followups.store'), [
            'lead_id' => $lead->id,
            'date' => now()->addDay()->format('Y-m-d\TH:i'),
            'type' => 'Call',
            'remark' => 'Discuss the proposal',
            'status' => 'scheduled',
        ])->assertRedirect();

        $followUp = FollowUp::firstOrFail();
        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'lead_id' => $lead->id,
            'type' => 'Call',
            'status' => 'scheduled',
        ]);

        $this->post(route('followups.complete', $followUp))
            ->assertRedirect(route('followups.show', $followUp));

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'status' => 'completed',
        ]);

        $this->get(route('followups.index'))->assertOk();
        $this->get(route('followups.show', $followUp))->assertOk();
        $this->get(route('followups.edit', $followUp))->assertOk();
    }

    public function test_only_won_leads_can_be_converted(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Open Prospect',
            'status' => 'qualified',
        ]);

        $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                'lead_id' => $lead->id,
                'name' => $lead->name,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('lead_id');

        $this->assertSame(0, Customer::count());
    }

    public function test_opening_conversion_for_an_already_converted_lead_redirects_to_existing_customer(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Converted Prospect',
            'status' => 'won',
        ]);
        $customer = Customer::create([
            'lead_id' => $lead->id,
            'name' => 'Converted Customer',
            'status' => 'active',
        ]);
        $lead->update(['status' => 'qualified']);

        $this->get(route('customers.create', ['lead_id' => $lead->id]))
            ->assertRedirect(route('customers.show', $customer))
            ->assertSessionHas('success', 'This lead has already been converted to a customer.');

        $this->assertSame(1, Customer::where('lead_id', $lead->id)->count());
    }

    public function test_dashboard_conversion_kpi_counts_won_leads(): void
    {
        $this->actingAs(User::factory()->create());
        Lead::create([
            'name' => 'Won Prospect',
            'status' => 'won',
        ]);
        Lead::create([
            'name' => 'Legacy Converted Prospect',
            'status' => 'converted',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('convertedLeads', 1)
            ->assertViewHas('conversionRate', 50);
    }

    public function test_customer_can_be_updated_and_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill([
            'role_id' => Role::where('name', 'super_admin')->value('id'),
            'is_admin' => true,
        ])->save();
        $this->actingAs($admin);
        $customer = Customer::create([
            'name' => 'Original Customer',
            'status' => 'active',
        ]);

        $this->put(route('customers.update', $customer), [
            'name' => 'Updated Customer',
            'company' => 'Updated Co',
            'email' => 'updated@example.test',
            'phone' => '555-0101',
            'address' => '10 Main Street',
            'status' => 'inactive',
        ])->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'status' => 'inactive',
        ]);

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_follow_ups_can_be_updated_and_deleted_and_validate_input(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Follow Up Prospect',
            'status' => 'qualified',
        ]);
        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'remark' => 'Initial call',
            'status' => 'scheduled',
        ]);

        $this->put(route('followups.update', $followUp), [
            'lead_id' => $lead->id,
            'date' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'type' => 'Meeting',
            'remark' => 'Updated discussion',
            'status' => 'completed',
        ])->assertRedirect(route('followups.show', $followUp));

        $this->assertDatabaseHas('follow_ups', [
            'id' => $followUp->id,
            'type' => 'Meeting',
            'remark' => 'Updated discussion',
            'status' => 'completed',
        ]);

        $this->from(route('followups.edit', $followUp))
            ->put(route('followups.update', $followUp), [
                'lead_id' => $lead->id,
                'date' => 'not-a-date',
                'type' => 'Invalid type',
                'remark' => 'Invalid update',
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors(['date', 'type', 'status']);

        $this->delete(route('followups.destroy', $followUp))
            ->assertRedirect(route('followups.index'));

        $this->assertDatabaseMissing('follow_ups', ['id' => $followUp->id]);
    }

    public function test_dashboard_counts_pending_follow_ups_and_completed_activities(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'KPI Prospect',
            'status' => 'new',
        ]);
        FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'status' => 'scheduled',
        ]);
        FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDays(2),
            'type' => 'Meeting',
            'status' => 'completed',
        ]);
        Activity::create([
            'lead_id' => $lead->id,
            'type' => 'Note',
            'subject' => 'Completed activity',
            'activity_date' => now(),
            'status' => 'completed',
        ]);
        Activity::create([
            'lead_id' => $lead->id,
            'type' => 'Task',
            'subject' => 'Pending activity',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('pendingFollowUps', 1)
            ->assertViewHas('completedActivities', 1)
            ->assertSee('Total Leads')
            ->assertSee('Won Leads / Converted Customers')
            ->assertSee('Total Customers')
            ->assertSee('Pending Follow Ups')
            ->assertSee('Completed Activities')
            ->assertSee('Recent Activities')
            ->assertSee('Upcoming Follow Ups')
            ->assertSee('Completed activity')
            ->assertSee('KPI Prospect');
    }

    public function test_dashboard_lists_the_nearest_upcoming_follow_ups(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create(['name' => 'Nearest reminder lead', 'status' => 'new']);
        $nearestFollowUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Demo',
            'status' => 'scheduled',
        ]);
        $nearestFollowUp->forceFill(['created_at' => now()->subMonth()])->saveQuietly();

        foreach (range(1, 5) as $days) {
            FollowUp::create([
                'lead_id' => $lead->id,
                'date' => now()->addDays($days + 7),
                'type' => 'Meeting',
                'status' => 'scheduled',
            ]);
        }

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Demo');
    }
}
