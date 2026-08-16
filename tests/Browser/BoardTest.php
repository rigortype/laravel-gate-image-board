<?php

use App\Models\Board;
use App\Models\User;
use Laravel\Dusk\Browser;

test('a team member can open the boards catalog', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create([
        'name' => 'Dusk Board',
        'slug' => 'dusk-board',
    ]);

    $this->browse(function (Browser $browser) use ($user, $board) {
        $browser->loginAs($user)
            ->visitRoute('boards.index', ['current_team' => $user->currentTeam->slug])
            ->assertSee('Boards')
            ->assertSee($board->name)
            ->click('@board-card')
            ->waitForLocation('/'.$user->currentTeam->slug.'/boards/'.$board->slug)
            ->assertSee($board->name)
            ->assertPresent('@create-thread-form');
    });
});
