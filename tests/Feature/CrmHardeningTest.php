<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_validation_rejects_invalid_values_and_ignores_unvalidated_fields(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('leads.create'))
            ->post(route('leads.store'), [
                'name' => 'A',
                'email' => 'not-an-email',
                'phone' => 'abc123',
                'status' => 'admin',
                'source' => 'untrusted',
            ])
            ->assertSessionHasErrors(['name', 'company_name', 'email', 'phone', 'status', 'source']);

        $this->post(route('leads.store'), [
            'name' => '  Jordan Prospect  ',
            'company_name' => 'Jordan Company',
            'email' => ' JORDAN@EXAMPLE.TEST ',
            'phone' => '+1 (555) 555-0100',
            'status' => 'qualified',
            'source' => 'Website',
            'city' => 'Austin',
            'remarks' => 'Requested a product walkthrough.',
            'id' => 5000,
            'created_at' => '2000-01-01 00:00:00',
        ])->assertRedirect(route('leads.index'));

        $lead = Lead::firstOrFail();
        $this->assertNotSame(5000, $lead->id);
        $this->assertSame('Jordan Prospect', $lead->name);
        $this->assertSame('jordan@example.test', $lead->email);
        $this->assertSame('Austin', $lead->city);
        $this->assertSame('Requested a product walkthrough.', $lead->remarks);
        $this->assertNotSame('2000-01-01 00:00:00', $lead->created_at->toDateTimeString());

        $this->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Austin')
            ->assertSee('Requested a product walkthrough.');
        $this->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Austin')
            ->assertSee('Requested a product walkthrough.');
    }

    public function test_lead_validation_errors_render_the_shared_alert_and_invalid_field_state(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('leads.create'))
            ->post(route('leads.store'), [
                'name' => 'A',
                'company_name' => 'Validation Company',
                'email' => 'validation@example.test',
                'phone' => '+1 (555) 555-0101',
                'source' => 'Website',
                'status' => 'new',
                'city' => 'Austin',
                'remarks' => 'Keep this remark.',
            ])
            ->assertRedirect(route('leads.create'))
            ->assertSessionHasErrors('name');

        $this->get(route('leads.create'))
            ->assertOk()
            ->assertSee('crm-validation-alert', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="crm-error-name"', false)
            ->assertSee('value="Austin"', false)
            ->assertSee('Keep this remark.', false)
            ->assertSee('crm-field-error', false);
    }

    public function test_lead_core_fields_are_required_and_city_and_remarks_are_limited(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('leads.store'), [
            'name' => 'Required fields prospect',
            'status' => 'new',
        ])->assertSessionHasErrors(['company_name', 'email', 'phone', 'source']);

        $validLead = [
            'name' => 'Bounded fields prospect',
            'company_name' => 'Bounded Company',
            'email' => 'bounded@example.test',
            'phone' => '+1 (555) 555-0115',
            'source' => 'Website',
            'status' => 'new',
        ];

        $this->from(route('leads.create'))
            ->post(route('leads.store'), $validLead + [
                'city' => str_repeat('C', 101),
                'remarks' => str_repeat('R', 1001),
            ])
            ->assertSessionHasErrors(['city', 'remarks']);

        $lead = Lead::create([
            'name' => 'Update required fields prospect',
            'status' => 'new',
        ]);

        $this->put(route('leads.update', $lead), [
            'name' => 'Updated prospect',
            'status' => 'qualified',
        ])->assertSessionHasErrors(['company_name', 'email', 'phone', 'source']);
    }

    public function test_lead_email_is_unique_on_create_and_update_without_blocking_self_update(): void
    {
        $this->actingAs(User::factory()->create());
        $firstLead = Lead::create([
            'name' => 'First Prospect',
            'email' => 'FIRST@example.test',
            'status' => 'new',
        ]);
        $secondLead = Lead::create([
            'name' => 'Second Prospect',
            'email' => 'second@example.test',
            'status' => 'new',
        ]);

        $this->from(route('leads.create'))
            ->post(route('leads.store'), [
                'name' => 'Duplicate Prospect',
                'company_name' => 'Duplicate Company',
                'email' => ' FIRST@example.test ',
                'phone' => '+1 (555) 555-0102',
                'source' => 'Website',
                'status' => 'new',
            ])
            ->assertSessionHasErrors('email');

        $this->put(route('leads.update', $firstLead), [
            'name' => 'First Prospect Updated',
            'company_name' => 'First Company',
            'email' => 'first@example.test',
            'phone' => '+1 (555) 555-0103',
            'source' => 'Website',
            'status' => 'qualified',
        ])->assertRedirect(route('leads.index'));

        $this->from(route('leads.edit', $secondLead))
            ->put(route('leads.update', $secondLead), [
                'name' => 'Second Prospect',
                'company_name' => 'Second Company',
                'email' => 'first@example.test',
                'phone' => '+1 (555) 555-0104',
                'source' => 'Referral',
                'status' => 'new',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame(2, Lead::count());
    }

    public function test_database_email_constraints_protect_leads_and_customers(): void
    {
        Lead::create([
            'name' => 'Unique lead',
            'email' => 'unique-lead@example.test',
            'status' => 'new',
        ]);
        Customer::create([
            'name' => 'Unique customer',
            'email' => 'unique-customer@example.test',
            'status' => 'active',
        ]);

        try {
            Lead::create([
                'name' => 'Duplicate lead',
                'email' => 'unique-lead@example.test',
                'status' => 'new',
            ]);
            $this->fail('The database must enforce unique lead emails.');
        } catch (QueryException) {
            $this->assertSame(1, Lead::where('email', 'unique-lead@example.test')->count());
        }

        try {
            Customer::create([
                'name' => 'Duplicate customer',
                'email' => 'unique-customer@example.test',
                'status' => 'active',
            ]);
            $this->fail('The database must enforce unique customer emails.');
        } catch (QueryException) {
            $this->assertSame(1, Customer::where('email', 'unique-customer@example.test')->count());
        }
    }

    public function test_customer_validation_prevents_duplicate_email_and_lead_conversion(): void
    {
        $this->actingAs(User::factory()->create());
        $lead = Lead::create([
            'name' => 'Won Prospect',
            'status' => 'won',
        ]);
        $customer = Customer::create([
            'name' => 'Existing Customer',
            'email' => 'EXISTING@example.test',
            'status' => 'active',
        ]);

        $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                'name' => 'Duplicate Customer',
                'email' => ' existing@example.test ',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('email');

        $this->post(route('customers.store'), [
            'lead_id' => $lead->id,
            'name' => 'Won Prospect',
            'email' => $customer->email,
            'status' => 'active',
        ])->assertSessionHasErrors('email');

        $this->post(route('customers.store'), [
            'lead_id' => $lead->id,
            'name' => 'Won Prospect',
            'status' => 'active',
            'unexpected_field' => 'ignored',
        ])->assertRedirect(route('customers.index'));

        $this->from(route('customers.create'))
            ->post(route('customers.store'), [
                'name' => 'Invalid Customer',
                'status' => 'administrator',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(2, Customer::count());
        $this->assertDatabaseHas('customers', [
            'lead_id' => $lead->id,
            'name' => 'Won Prospect',
        ]);
    }

    public function test_follow_ups_and_activities_reject_invalid_relations_dates_and_statuses(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('followups.create'))
            ->post(route('followups.store'), [
                'lead_id' => 99999,
                'date' => 'not-a-date',
                'type' => 'Invalid',
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors(['lead_id', 'date', 'type', 'status']);

        $this->from(route('activities.create'))
            ->post(route('activities.store'), [
                'lead_id' => 99999,
                'customer_id' => null,
                'type' => 'Invalid',
                'subject' => '',
                'activity_date' => 'not-a-date',
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors([
                'lead_id',
                'type',
                'subject',
                'activity_date',
                'status',
            ]);

        $this->assertSame(0, FollowUp::count());
        $this->assertSame(0, Activity::count());
    }

    public function test_guest_cannot_access_or_mutate_module_records_by_direct_url(): void
    {
        $lead = Lead::create(['name' => 'Protected Lead', 'status' => 'new']);
        $customer = Customer::create(['name' => 'Protected Customer', 'status' => 'active']);
        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'status' => 'scheduled',
        ]);
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => 'Note',
            'subject' => 'Protected Activity',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $this->get(route('leads.edit', $lead))->assertRedirect(route('login'));
        $this->get(route('customers.show', $customer))->assertRedirect(route('login'));
        $this->get(route('followups.edit', $followUp))->assertRedirect(route('login'));
        $this->get(route('activities.edit', $activity))->assertRedirect(route('login'));
        $this->delete(route('activities.destroy', $activity))->assertRedirect(route('login'));

        $this->assertDatabaseHas('activities', ['id' => $activity->id]);
    }

    public function test_destructive_forms_require_client_side_confirmation(): void
    {
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => Role::query()->where('name', 'super_admin')->value('id'),
            'is_admin' => true,
        ])->save();
        $this->actingAs($user);
        $lead = Lead::create(['name' => 'Confirmation Lead', 'status' => 'new']);
        $activity = Activity::create([
            'lead_id' => $lead->id,
            'type' => 'Note',
            'subject' => 'Confirmation activity',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $this->get(route('leads.index'))
            ->assertOk()
            ->assertSee('data-confirm=', false);
        $this->get(route('activities.show', $activity))
            ->assertOk()
            ->assertSee('data-confirm="This will permanently delete this activity."', false);
    }

    public function test_unknown_record_ids_return_a_not_found_response_with_feedback(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('leads.show', 99999))
            ->assertNotFound()
            ->assertSee('data-error="The requested record could not be found."', false);

        $this->get(route('leads.index'))
            ->assertOk()
            ->assertSee('data-validation-error="false"', false);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $this->from(route('login'));
        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.'.random_int(1, 254),
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'unknown@example.test',
                'password' => 'incorrect-password',
            ])->assertRedirect(route('login'));
        }

        $this->post(route('login'), [
            'email' => 'unknown@example.test',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }
}
