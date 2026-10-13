<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_routes_require_authentication(): void
    {
        foreach (['dashboard', 'leads.index', 'customers.index', 'followups.index', 'activities.index'] as $route) {
            $this->get(route($route))
                ->assertRedirect(route('login'))
                ->assertSessionHas('error', 'Sign in to access CRM records.');
        }

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-error="Sign in to access CRM records."', false);
    }

    public function test_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'crm-user@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->get(route('login'))->assertOk();
        $sessionIdBeforeLogin = session()->getId();

        $this->post(route('login'), [
            'email' => 'crm-user@example.test',
            'password' => 'correct-password',
        ])->assertRedirect('/dashboard')
            ->assertSessionHas('success', 'Signed in successfully.');

        $this->assertNotSame($sessionIdBeforeLogin, session()->getId());
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect('/login')
            ->assertSessionHas('success', 'Signed out successfully.');

        $this->assertGuest();
    }

    public function test_login_inputs_have_programmatically_associated_labels(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<label for="email">', false)
            ->assertSee('id="email"', false)
            ->assertSee('<label for="password">', false)
            ->assertSee('id="password"', false);
    }

    public function test_login_form_disables_native_validation_and_keeps_required_fields(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<form method="POST" action="'.route('login').'" novalidate>', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('placeholder="Enter email"
required', false)
            ->assertSee('placeholder="Enter password"
required', false);
    }

    public function test_login_form_posts_to_the_current_application_path(): void
    {
        $response = $this->get(route('login'))->assertOk();

        $this->assertStringContainsString(
            'action="'.url()->current().'"',
            $response->getContent()
        );
    }

    public function test_guest_cannot_call_logout_endpoint(): void
    {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_login_does_not_authenticate_user(): void
    {
        User::factory()->create([
            'email' => 'crm-user@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'crm-user@example.test',
                'password' => 'incorrect-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('crm-validation-alert', false)
            ->assertSee('crm-field-error', false)
            ->assertSee('aria-invalid="true"', false);

        $this->assertGuest();
    }

    public function test_invalid_login_input_uses_crm_validation_ui(): void
    {
        foreach ([
            ['email' => '', 'password' => '', 'errors' => ['email', 'password']],
            ['email' => 'not-an-email', 'password' => 'secret', 'errors' => ['email']],
            ['email' => '<script>alert(1)</script>', 'password' => 'secret', 'errors' => ['email']],
        ] as $input) {
            $this->from(route('login'))
                ->post(route('login'), [
                    'email' => $input['email'],
                    'password' => $input['password'],
                ])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors($input['errors']);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('crm-validation-alert', false)
                ->assertSee('crm-field-error', false)
                ->assertSee('aria-invalid="true"', false);
        }

        $this->assertGuest();
    }
}
