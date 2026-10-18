<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
