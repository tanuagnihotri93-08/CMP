<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class LoginPage extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function assert(Browser $browser): void
    {
        $browser->assertPathIs('/login')
            ->assertSee('CRM Pro')
            ->assertPresent('#email')
            ->assertPresent('#password');
    }

}
