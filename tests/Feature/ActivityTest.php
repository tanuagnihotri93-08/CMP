<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activities_can_be_created_viewed_updated_and_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Morgan Prospect',
            'status' => 'qualified',
        ]);
        $customer = Customer::create([
            'name' => 'Morgan Customer',
            'status' => 'active',
        ]);

        $this->get(route('activities.create', [
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
        ]))->assertOk();

        $this->post(route('activities.store'), [
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'type' => 'Meeting',
            'subject' => 'Product discussion',
            'description' => 'Reviewed product requirements.',
            'activity_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'pending',
        ])->assertRedirect(route('activities.index'));

        $activity = Activity::firstOrFail();
        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'type' => 'Meeting',
            'subject' => 'Product discussion',
            'status' => 'pending',
        ]);

        $this->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Product discussion');
        $this->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('Reviewed product requirements.');
        $this->get(route('activities.edit', $activity))->assertOk();
        $this->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Product discussion');
        $this->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('Product discussion');

        $this->put(route('activities.update', $activity), [
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'type' => 'Task',
            'subject' => 'Send proposal',
            'description' => 'Send the updated proposal.',
            'activity_date' => now()->addDay()->format('Y-m-d\TH:i'),
            'status' => 'completed',
        ])->assertRedirect(route('activities.index'));

        $this->assertDatabaseHas('activities', [
            'id' => $activity->id,
            'type' => 'Task',
            'subject' => 'Send proposal',
            'status' => 'completed',
        ]);

        $this->delete(route('activities.destroy', $activity))
            ->assertRedirect(route('activities.index'));
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    public function test_an_activity_must_have_a_valid_related_lead_or_customer(): void
    {
        $this->actingAs(User::factory()->create());
        $data = [
            'type' => 'Call',
            'subject' => 'Introductory call',
            'activity_date' => now()->format('Y-m-d\TH:i'),
            'status' => 'pending',
        ];

        $this->from(route('activities.create'))
            ->post(route('activities.store'), $data)
            ->assertSessionHasErrors(['lead_id', 'customer_id']);

        $lead = Lead::create([
            'name' => 'Valid Prospect',
            'status' => 'new',
        ]);

        $this->post(route('activities.store'), array_merge($data, [
            'lead_id' => $lead->id,
        ]))->assertRedirect();

        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'customer_id' => null,
            'subject' => 'Introductory call',
        ]);
    }
}
