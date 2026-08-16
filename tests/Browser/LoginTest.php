<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('guests can view the login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->assertSee('Log in to your account')
            ->assertPresent('@login-button');
    });
});

test('users can log in from the browser', function () {
    $user = User::factory()->create();

    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('@login-button')
            ->waitForLocation('/'.$user->currentTeam->slug.'/dashboard')
            ->assertSee('Boards');
    });
});
