<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

test('guests can view the public explore catalog', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create(['slug' => 'artwork']);

    $public = Thread::factory()->for($board)->public()->create(['subject' => 'Open sky']);
    Post::factory()->for($public)->for($user, 'author')->create(['body' => 'A public opening']);

    $teamOnly = Thread::factory()->for($board)->create(['subject' => 'Members only']);
    Post::factory()->for($teamOnly)->for($user, 'author')->create(['body' => 'Team secret']);

    $unlisted = Thread::factory()->for($board)->unlisted()->create(['subject' => 'Hidden link']);
    Post::factory()->for($unlisted)->for($user, 'author')->create(['body' => 'Unlisted body']);

    $private = Thread::factory()->for($board)->private()->create(['subject' => 'Just me']);
    Post::factory()->for($private)->for($user, 'author')->create(['body' => 'Private body']);

    $this->get(route('explore'))
        ->assertOk()
        ->assertSee('Open sky')
        ->assertSee('A public opening')
        ->assertSee('/artwork/')
        ->assertDontSee('Members only')
        ->assertDontSee('Team secret')
        ->assertDontSee('Hidden link')
        ->assertDontSee('Just me');
});

test('the explore catalog is empty when there are no public threads', function () {
    $this->get(route('explore'))
        ->assertOk()
        ->assertSee('No public threads yet.');
});

test('guests can view a boards public catalog by id', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user->currentTeam)->create([
        'name' => 'Artwork',
        'slug' => 'artwork',
        'description' => 'Drawings',
    ]);

    $public = Thread::factory()->for($board)->public()->create(['subject' => 'Open sky']);
    Post::factory()->for($public)->for($user, 'author')->create(['body' => 'A public opening']);

    $teamOnly = Thread::factory()->for($board)->create(['subject' => 'Members only']);
    Post::factory()->for($teamOnly)->for($user, 'author')->create();

    $this->get(route('explore.board', ['id' => $board->getKey()]))
        ->assertOk()
        ->assertSee('Artwork')
        ->assertSee('Drawings')
        ->assertSee('Open sky')
        ->assertDontSee('Members only');
});

test('explore board pages are keyed by id when slugs collide across teams', function () {
    $firstOwner = User::factory()->create();
    $secondOwner = User::factory()->create();

    $alpha = Board::factory()->for($firstOwner->currentTeam)->create([
        'name' => 'Alpha general',
        'slug' => 'general',
    ]);
    $beta = Board::factory()->for($secondOwner->currentTeam)->create([
        'name' => 'Beta general',
        'slug' => 'general',
    ]);

    $this->get(route('explore.board', ['id' => $beta->getKey()]))
        ->assertOk()
        ->assertSee('Beta general')
        ->assertDontSee('Alpha general');

    $this->get(route('explore.board', ['id' => $alpha->getKey()]))
        ->assertOk()
        ->assertSee('Alpha general')
        ->assertDontSee('Beta general');
});

test('an unknown explore board is not found', function () {
    $this->get(route('explore.board', ['id' => 999_999]))->assertNotFound();
});

test('the home page links to explore', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/explore', false)
        ->assertSee(__('boards.explore'));
});
