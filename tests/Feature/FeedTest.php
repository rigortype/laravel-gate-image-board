<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

test('the feed lists public threads', function () {
    $user = User::factory()->create(['name' => 'Feed Author']);
    $board = Board::factory()->for($user->currentTeam)->create();

    $public = Thread::factory()->for($board)->public()->create(['subject' => 'Public feed thread']);
    Post::factory()->for($public)->for($user, 'author')->create(['body' => 'Visible summary']);

    $teamOnly = Thread::factory()->for($board)->create(['subject' => 'Hidden feed thread']);
    Post::factory()->for($teamOnly)->for($user, 'author')->create();

    $this->get('/feed')
        ->assertOk()
        ->assertSee('Public feed thread')
        ->assertSee('Visible summary')
        ->assertSee('Feed Author')
        ->assertSee($public->url(), false)
        ->assertDontSee('Hidden feed thread');
});

test('the document head advertises the feed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('type="application/atom+xml"', false)
        ->assertSee('/feed', false);
});
