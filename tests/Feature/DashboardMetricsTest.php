<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_lead_chart_returns_six_calendar_months_from_one_aggregated_series(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 18, 12));
        $user = User::factory()->create();

        foreach ([5 => 1, 4 => 2, 2 => 3, 0 => 4] as $monthsAgo => $count) {
            $date = now()->startOfMonth()->subMonths($monthsAgo)->addDays(2);

            for ($index = 0; $index < $count; $index++) {
                $lead = Lead::create([
                    'name' => "Lead {$monthsAgo}-{$index}",
                    'status' => 'new',
                    'assigned_to_user_id' => $user->id,
                ]);
                $lead->forceFill([
                    'created_at' => $date->copy()->addMinutes($index),
                    'updated_at' => $date->copy()->addMinutes($index),
                ])->saveQuietly();
            }
        }

        $outsideWindowLead = Lead::create([
            'name' => 'Outside chart window',
            'status' => 'new',
            'assigned_to_user_id' => $user->id,
        ]);
        $outsideWindowDate = now()->startOfMonth()->subMonths(6)->addDays(2);
        $outsideWindowLead->forceFill([
            'created_at' => $outsideWindowDate,
            'updated_at' => $outsideWindowDate,
        ])->saveQuietly();

        $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $growth = $response->viewData('monthlyLeadGrowth');

        $this->assertSame([
            'May 2026' => 1,
            'Jun 2026' => 2,
            'Jul 2026' => 0,
            'Aug 2026' => 3,
            'Sep 2026' => 0,
            'Oct 2026' => 4,
        ], $growth->all());
    }
}
