<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LeadDummySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class SeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_crm_accounts_are_not_seeded_in_production(): void
    {
        $this->app->detectEnvironment(static fn () => 'production');

        app(DatabaseSeeder::class)->run();

        foreach (['admin@crm.com', 'manager@crm.com', 'employee@crm.com'] as $email) {
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }
    }

    public function test_default_crm_accounts_remain_available_in_testing(): void
    {
        app(DatabaseSeeder::class)->run();

        foreach (['admin@crm.com', 'manager@crm.com', 'employee@crm.com'] as $email) {
            $this->assertDatabaseHas('users', ['email' => $email]);
        }
    }

    public function test_demo_lead_seeder_uses_valid_values_and_is_repeatable(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        app(LeadDummySeeder::class)->run();
        app(LeadDummySeeder::class)->run();

        $this->assertDatabaseCount('leads', 60);
        $this->assertSame(
            0,
            \App\Models\Lead::query()
                ->whereNotIn('status', ['new', 'qualified', 'proposal', 'won', 'lost'])
                ->count()
        );
        $this->assertSame(
            0,
            \App\Models\Lead::query()
                ->whereNotIn('source', ['Website', 'Referral', 'Social Media', 'Direct'])
                ->count()
        );
        $this->assertSame(
            60,
            \App\Models\Lead::query()->where('assigned_to_user_id', $user->id)->count()
        );
    }
}
