<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Laravel\Dusk\Browser;

test('guests can read a public thread', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create([
        'name' => 'Public Board',
        'slug' => 'public-board',
    ]);
    $thread = Thread::factory()->for($board)->public()->create([
        'subject' => 'Dusk public thread',
    ]);
    Post::factory()->for($thread)->for($user, 'author')->create([
        'body' => 'Visible to guests in the browser.',
    ]);

    $this->browse(function (Browser $browser) use ($board, $thread) {
        $browser->visitRoute('threads.show', [
            'current_team' => $board->team->slug,
            'board' => $board->slug,
            'thread' => $thread->id,
        ])
            ->assertSee('Dusk public thread')
            ->assertSee('Visible to guests in the browser.')
            ->assertSee('Public');
    });
});
