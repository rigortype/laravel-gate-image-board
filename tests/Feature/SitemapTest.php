<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

test('the sitemap lists the home page, boards, and public threads', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create();

    $public = Thread::factory()->for($board)->public()->create(['subject' => 'Public thread']);
    Post::factory()->for($public)->for($user, 'author')->create();

    $teamOnly = Thread::factory()->for($board)->create(['subject' => 'Team thread']);
    Post::factory()->for($teamOnly)->for($user, 'author')->create();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertHeader('content-type', 'text/xml; charset=UTF-8')
        ->assertSee(route('home'), false)
        ->assertSee($board->url(), false)
        ->assertSee($public->url(), false)
        ->assertDontSee($teamOnly->url(), false);
});

test('the sitemap omits unlisted and private threads', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create();

    $unlisted = Thread::factory()->for($board)->unlisted()->create();
    Post::factory()->for($unlisted)->for($user, 'author')->create();

    $private = Thread::factory()->for($board)->private()->create();
    Post::factory()->for($private)->for($user, 'author')->create();

    $this->get(route('sitemap'))
        ->assertOk()
        ->assertDontSee($unlisted->url(), false)
        ->assertDontSee($private->url(), false);
});
