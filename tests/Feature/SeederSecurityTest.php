<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_test_account_is_not_seeded_in_production(): void
    {
        $this->app->detectEnvironment(static fn () => 'production');

        app(DatabaseSeeder::class)->run();

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }
}
