<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_lead_creation_and_updates_are_logged_with_user_and_changed_values(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin);

        $this->post(route('leads.store'), [
            'name' => 'Audit Prospect',
            'company_name' => 'Audit Company',
            'email' => 'audit@example.test',
            'phone' => '+1 (555) 555-0114',
            'source' => 'Website',
            'status' => 'new',
        ])->assertRedirect(route('leads.index'));

        $lead = Lead::firstOrFail();
        $createdLog = AuditLog::where('action', 'created')
            ->where('module', 'leads')
            ->firstOrFail();

        $this->assertSame($admin->id, $createdLog->user_id);
        $this->assertSame($admin->name, $createdLog->user_name);
        $this->assertSame($admin->name, $createdLog->user->name);
        $this->assertSame('Audit Prospect', $createdLog->new_values['name']);

        $this->put(route('leads.update', $lead), [
            'name' => 'Audit Prospect Updated',
            'company_name' => 'Audit Company',
            'email' => 'audit@example.test',
            'phone' => '+1 (555) 555-0114',
            'source' => 'Website',
            'status' => 'won',
        ])->assertRedirect(route('leads.index'));

        $updateLog = AuditLog::where('action', 'updated')->latest()->firstOrFail();
        $statusLog = AuditLog::where('action', 'status_changed')->latest()->firstOrFail();

        $this->assertSame(['new', 'won'], [
            $statusLog->old_values['status'],
            $statusLog->new_values['status'],
        ]);
        $this->assertSame('Audit Prospect Updated', $updateLog->new_values['name']);
        $this->assertSame($lead->id, $statusLog->record_id);
    }

    public function test_customer_conversion_is_logged_as_customer_creation_and_lead_conversion(): void
    {
        $this->actingAs($this->adminUser());
        $lead = Lead::create(['name' => 'Convert Me', 'status' => 'won']);

        $this->post(route('customers.store'), [
            'lead_id' => $lead->id,
            'name' => 'Converted Account',
            'status' => 'active',
        ])->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'created',
            'module' => 'customers',
            'record_type' => Customer::class,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'converted',
            'module' => 'leads',
            'record_type' => Lead::class,
            'record_id' => $lead->id,
        ]);
    }

    public function test_login_logout_and_failed_attempts_are_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'audit-user@example.test',
            'password' => 'correct-password',
        ]);

        $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'));

        $failedLog = AuditLog::where('action', 'failed_login')->firstOrFail();
        $this->assertNull($failedLog->user_id);
        $this->assertSame($user->email, $failedLog->new_values['email']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'module' => 'authentication',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
            'module' => 'authentication',
        ]);
    }

    public function test_audit_log_records_are_append_only(): void
    {
        $log = AuditLog::record(
            'created',
            'leads',
            Lead::class,
            1,
            'Lead created.'
        );

        try {
            $log->forceFill(['description' => 'Tampered'])->save();
            $this->fail('Audit log updates must be rejected.');
        } catch (LogicException $exception) {
            $this->assertSame('Audit logs are append-only.', $exception->getMessage());
        }

        try {
            $log->delete();
            $this->fail('Audit log deletions must be rejected.');
        } catch (LogicException $exception) {
            $this->assertSame('Audit logs are append-only.', $exception->getMessage());
        }

        $this->assertDatabaseHas('audit_logs', [
            'id' => $log->id,
            'description' => 'Lead created.',
        ]);
    }

    public function test_completion_and_dependent_record_deletions_are_logged(): void
    {
        $this->actingAs($this->adminUser());
        $lead = Lead::create(['name' => 'Lifecycle Prospect', 'status' => 'won']);
        $customer = Customer::create([
            'lead_id' => $lead->id,
            'name' => 'Lifecycle Customer',
            'status' => 'active',
        ]);
        $followUp = FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->addDay(),
            'type' => 'Call',
            'status' => 'scheduled',
        ]);
        $activity = Activity::create([
            'customer_id' => $customer->id,
            'type' => 'Task',
            'subject' => 'Complete task',
            'activity_date' => now(),
            'status' => 'pending',
        ]);

        $followUp->update(['status' => 'completed']);
        $activity->update(['status' => 'completed']);
        $followUp->delete();
        $activity->delete();
        $lead->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'completed',
            'module' => 'follow_ups',
            'record_id' => $followUp->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'completed',
            'module' => 'activities',
            'record_id' => $activity->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'module' => 'follow_ups',
            'record_id' => $followUp->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'module' => 'activities',
            'record_id' => $activity->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'module' => 'customers',
            'record_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'deleted',
            'module' => 'leads',
            'record_id' => $lead->id,
        ]);
    }

    public function test_audit_log_page_requires_admin_and_supports_filters(): void
    {
        $admin = $this->adminUser();
        $actor = User::factory()->create();
        $this->actingAs($actor);

        AuditLog::record(
            'updated',
            'leads',
            Lead::class,
            15,
            'Lead profile updated.',
            ['status' => 'new'],
            ['status' => 'won']
        );

        $this->get(route('audit-logs.index'))->assertForbidden();

        $this->actingAs($admin)
            ->get(route('audit-logs.index', [
                'search' => 'profile',
                'module' => 'leads',
                'user_id' => $actor->id,
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Lead profile updated.')
            ->assertSee($actor->name);
    }

    private function adminUser(): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => Role::where('name', 'super_admin')->value('id'),
            'is_admin' => true,
        ])->save();

        return $user;
    }
}
