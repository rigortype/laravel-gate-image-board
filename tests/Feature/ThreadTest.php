<?php

use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function authenticateThreadUser(?User $user = null): User
{
    $user ??= User::factory()->create();

    test()->actingAs($user);

    URL::defaults([
        'current_team' => $user->currentTeam->slug,
        'team' => $user->currentTeam->slug,
    ]);

    return $user;
}

test('members can start a thread with an image', function () {
    Storage::fake('public');

    $user = authenticateThreadUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    Livewire::test('pages::boards.show', ['board' => $board])
        ->set('subject', 'Hello')
        ->set('body', 'First post')
        ->set('image', UploadedFile::fake()->image('pic.png'))
        ->call('createThread')
        ->assertHasNoErrors()
        ->assertRedirect();

    $thread = Thread::query()->whereBelongsTo($board)->first();

    expect($thread)->not->toBeNull()
        ->and($thread->subject)->toBe('Hello')
        ->and($thread->openingPost->body)->toBe('First post')
        ->and($thread->openingPost->image)->not->toBeNull();

    Storage::disk('public')->assertExists($thread->openingPost->image->path);
});

test('a thread requires an image', function () {
    $user = authenticateThreadUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    Livewire::test('pages::boards.show', ['board' => $board])
        ->set('body', 'No image')
        ->call('createThread')
        ->assertHasErrors(['image']);

    expect(Thread::query()->whereBelongsTo($board)->exists())->toBeFalse();
});

test('members can view a thread', function () {
    $user = authenticateThreadUser();
    $board = Board::factory()->for($user->currentTeam)->create();
    $thread = Thread::factory()->for($board)->create();
    Post::factory()->for($thread)->for($user, 'author')->create();

    $this->get(route('threads.show', ['board' => $board, 'thread' => $thread]))
        ->assertOk()
        ->assertSee($thread->openingPost->body);
});

test('a thread on another board is not found', function () {
    $user = authenticateThreadUser();
    $board = Board::factory()->for($user->currentTeam)->create();
    $otherBoard = Board::factory()->for($user->currentTeam)->create();
    $thread = Thread::factory()->for($otherBoard)->create();

    $this->get(route('threads.show', ['board' => $board, 'thread' => $thread]))
        ->assertNotFound();
});
