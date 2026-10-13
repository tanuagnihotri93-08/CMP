<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_lists_filter_and_paginate_records_in_the_database(): void
    {
        $manager = $this->userWithRole('manager');
        $lead = Lead::create([
            'name' => 'Search Target Lead',
            'status' => 'won',
            'assigned_to_user_id' => $manager->id,
        ]);

        for ($index = 0; $index < 26; $index++) {
            if ($index < 25) {
                Lead::create([
                    'name' => "Other Lead {$index}",
                    'status' => 'new',
                    'assigned_to_user_id' => $manager->id,
                ]);
            }

            $status = $index === 25 ? 'inactive' : 'active';
            $customer = Customer::create([
                'lead_id' => $index === 25 ? $lead->id : null,
                'name' => $index === 25 ? 'Search Target Customer' : "Customer {$index}",
                'status' => $status,
                'assigned_to_user_id' => $manager->id,
            ]);

            FollowUp::create([
                'lead_id' => $lead->id,
                'date' => now()->addDays($index),
                'type' => 'Call',
                'remark' => $index === 25 ? 'Search Target Reminder' : null,
                'status' => $index === 25 ? 'completed' : 'scheduled',
                'assigned_to_user_id' => $manager->id,
            ]);

            Activity::create([
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'type' => 'Note',
                'subject' => $index === 25 ? 'Search Target Activity' : "Activity {$index}",
                'activity_date' => now()->subMinutes($index),
                'status' => $index === 25 ? 'completed' : 'pending',
                'assigned_to_user_id' => $manager->id,
            ]);
        }

        $this->actingAs($manager);

        $leads = $this->get(route('leads.index'))
            ->assertOk()
            ->viewData('leads');
        $this->assertSame(26, $leads->total());
        $this->assertCount(25, $leads->items());

        $leadSearchResponse = $this->get(route('leads.index', ['search' => 'Search Target', 'status' => 'won']))
            ->assertOk()
            ->assertSee('Search Target Lead');
        $this->assertSame(26, $leadSearchResponse->viewData('totalLeads'));

        $customerResponse = $this->get(route('customers.index', ['search' => 'Search Target', 'status' => 'inactive']))
            ->assertOk();
        $customers = $customerResponse->viewData('customers');
        $this->assertSame(1, $customers->total());
        $this->assertSame(26, $customerResponse->viewData('totalCustomers'));

        $followUps = $this->get(route('followups.index', ['search' => 'Search Target', 'status' => 'completed']))
            ->assertOk()
            ->viewData('followUps');
        $this->assertSame(1, $followUps->total());

        $activities = $this->get(route('activities.index', ['search' => 'Search Target', 'status' => 'completed']))
            ->assertOk()
            ->viewData('activities');
        $this->assertSame(1, $activities->total());
    }

    public function test_report_summaries_are_aggregated_and_preserve_status_and_growth_values(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);
        $lead = Lead::create([
            'name' => 'Won Report Lead',
            'source' => 'Referral',
            'status' => 'won',
            'assigned_to_user_id' => $manager->id,
        ]);
        $customer = Customer::create([
            'lead_id' => $lead->id,
            'name' => 'Report Customer',
            'status' => 'active',
            'assigned_to_user_id' => $manager->id,
        ]);
        FollowUp::create([
            'lead_id' => $lead->id,
            'date' => now()->subDay(),
            'type' => 'Call',
            'status' => 'scheduled',
            'assigned_to_user_id' => $manager->id,
        ]);
        Activity::create([
            'customer_id' => $customer->id,
            'type' => 'Meeting',
            'subject' => 'Report activity',
            'activity_date' => now(),
            'status' => 'completed',
            'assigned_to_user_id' => $manager->id,
        ]);

        $response = $this->get(route('reports.index'))->assertOk();

        $this->assertSame(1, $response->viewData('leadReport')['total']);
        $this->assertSame(1, $response->viewData('leadReport')['by_status']['won']);
        $this->assertSame(1, $response->viewData('leadReport')['by_source']['Referral']);
        $this->assertSame(1, $response->viewData('customerReport')['active']);
        $this->assertSame(1, $response->viewData('customerReport')['growth'][now()->format('Y-m')]);
        $this->assertSame(1, $response->viewData('followUpReport')['overdue']);
        $this->assertSame(1, $response->viewData('activityReport')['completed']);
    }

    public function test_pdf_export_refuses_unbounded_large_results_with_a_clear_message(): void
    {
        $manager = $this->userWithRole('manager');
        $this->actingAs($manager);
        $timestamp = now()->toDateTimeString();
        $rows = [];

        for ($index = 0; $index < 1001; $index++) {
            $rows[] = [
                'name' => "PDF limit lead {$index}",
                'status' => 'new',
                'assigned_to_user_id' => $manager->id,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('leads')->insert($chunk);
        }

        $this->from(route('reports.index'))
            ->get(route('reports.export', ['module' => 'leads', 'format' => 'pdf']))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors('export');
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->forceFill([
            'role_id' => Role::query()->where('name', $roleName)->value('id'),
        ])->save();

        return $user;
    }
}
