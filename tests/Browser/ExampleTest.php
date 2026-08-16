<?php

use Laravel\Dusk\Browser;

test('the welcome page renders', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Laravel');
    });
});
