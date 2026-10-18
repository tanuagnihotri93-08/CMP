<?php

namespace Tests\Browser;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\DuskTestCase;
use Tests\Browser\Pages\DashboardPage;
use Tests\Browser\Pages\LoginPage;

class CriticalCrmJourneysTest extends DuskTestCase
{
    private string $password = 'dusk-browser-password';

    /** @var array<string, User> */
    private array $users = [];

    /** @var list<string> */
    private array $leadEmails = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'manager', 'employee'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->firstOrFail();
            $this->users[$roleName] = User::query()->forceCreate([
                'name' => 'Dusk '.Str::headline($roleName),
                'email' => 'dusk+'.Str::uuid().'@example.test',
                'password' => Hash::make($this->password),
                'role_id' => $role->id,
                'is_active' => true,
            ]);
        }
    }

    protected function tearDown(): void
    {
        $userIds = collect($this->users)->pluck('id')->all();

        if ($userIds !== []) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $userIds)
                ->delete();
            DB::table('audit_logs')->whereIn('user_id', $userIds)->delete();
            DB::table('sessions')->whereIn('user_id', $userIds)->delete();
        }

        if ($this->leadEmails !== []) {
            $leadIds = DB::table('leads')->whereIn('email', $this->leadEmails)->pluck('id');
            DB::table('customers')->whereIn('lead_id', $leadIds)->delete();
            DB::table('leads')->whereIn('id', $leadIds)->delete();
        }

        if ($userIds !== []) {
            DB::table('users')->whereIn('id', $userIds)->delete();
        }

        parent::tearDown();
    }

    public function test_user_can_log_in_and_reach_the_dashboard(): void
    {
        $employee = $this->users['employee'];

        $this->browse(function (\Laravel\Dusk\Browser $browser) use ($employee): void {
            $browser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $employee->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->on(new DashboardPage);
        });
    }

    public function test_custom_roles_allow_and_deny_the_expected_areas(): void
    {
        $admin = $this->users['super_admin'];
        $manager = $this->users['manager'];
        $employee = $this->users['employee'];

        $this->browse(function (\Laravel\Dusk\Browser $adminBrowser, \Laravel\Dusk\Browser $managerBrowser, \Laravel\Dusk\Browser $employeeBrowser) use ($admin, $manager, $employee): void {
            $adminBrowser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $admin->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->assertPathIs('/dashboard')
                ->visit('/admin/users')
                ->assertSee('User Management');

            $managerBrowser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $manager->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->assertPathIs('/dashboard')
                ->visit('/leads')
                ->assertSee('Leads Management')
                ->visit('/admin/users')
                ->assertSee('403');

            $employeeBrowser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $employee->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->assertPathIs('/dashboard')
                ->visit('/leads')
                ->assertSee('Leads Management')
                ->visit('/admin/users')
                ->assertSee('403');
        });
    }

    public function test_manager_can_create_update_and_convert_a_won_lead(): void
    {
        $manager = $this->users['manager'];
        $email = 'lead+'.Str::uuid().'@example.test';
        $this->leadEmails[] = $email;

        $this->browse(function (\Laravel\Dusk\Browser $browser) use ($manager, $email): void {
            $browser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $manager->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->assertPathIs('/dashboard')
                ->visit('/leads/create')
                ->type('name', 'Dusk Journey Prospect')
                ->type('email', $email)
                ->select('status', 'new')
                ->press('Save Lead')
                ->assertPathIs('/leads');

            $lead = Lead::query()->where('email', $email)->firstOrFail();

            $browser->visit('/leads/'.$lead->id.'/edit')
                ->select('status', 'won')
                ->press('Update Lead')
                ->visit('/leads/'.$lead->id)
                ->assertSee('Won')
                ->clickLink('Convert to Customer')
                ->assertPathIs('/customers/create')
                ->press('Create Customer')
                ->assertPathIs('/customers');
        });

        $lead = Lead::query()->where('email', $email)->firstOrFail();
        $this->assertSame('won', $lead->status);
        $this->assertDatabaseHas('customers', [
            'lead_id' => $lead->id,
            'email' => $email,
        ]);
    }

    public function test_assigned_user_can_open_and_read_their_notification(): void
    {
        $employee = $this->users['employee'];
        $email = 'notice+'.Str::uuid().'@example.test';
        $this->leadEmails[] = $email;
        $lead = Lead::withoutEvents(fn () => Lead::query()->create([
            'name' => 'Dusk Notification Lead',
            'email' => $email,
            'status' => 'new',
            'assigned_to_user_id' => $employee->id,
        ]));
        $notification = $employee->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\CrmRecordNotification',
            'data' => [
                'title' => 'Dusk assigned record notice',
                'message' => 'A new lead is ready for you.',
                'action_url' => url('/leads/'.$lead->id),
            ],
        ]);

        $this->browse(function (\Laravel\Dusk\Browser $browser) use ($employee, $lead): void {
            $browser->visit(new LoginPage)
                ->on(new LoginPage)
                ->type('email', $employee->email)
                ->type('password', $this->password)
                ->press('Access CRM Dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Dusk assigned record notice')
                ->click('.crm-notification-toggle')
                ->click('.crm-notification-entry')
                ->assertPathIs('/leads/'.$lead->id)
                ->assertSee('Dusk Notification Lead');
        });

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
