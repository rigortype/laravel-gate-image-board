<?php

use App\Enums\TeamRole;
use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function authenticatePostUser(?User $user = null): User
{
    $user ??= User::factory()->create();

    test()->actingAs($user);

    URL::defaults([
        'current_team' => $user->currentTeam->slug,
        'team' => $user->currentTeam->slug,
    ]);

    return $user;
}

/**
 * @return array{User, Board, Thread}
 */
function postFixture(?User $user = null): array
{
    $user = authenticatePostUser($user);
    $board = Board::factory()->for($user->currentTeam)->create();
    $thread = Thread::factory()->for($board)->create(['bumped_at' => now()->subHour()]);
    Post::factory()->for($thread)->for($user, 'author')->create(['body' => 'Opening']);

    return [$user, $board, $thread->fresh()];
}

test('members can reply with text', function () {
    [, $board, $thread] = postFixture();

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->set('body', 'A reply')
        ->call('reply')
        ->assertHasNoErrors();

    expect($thread->posts()->count())->toBe(2)
        ->and($thread->fresh()->bumped_at->greaterThan($thread->bumped_at))->toBeTrue();

    $this->assertDatabaseHas('posts', [
        'thread_id' => $thread->id,
        'body' => 'A reply',
    ]);
});

test('members can reply with only an image', function () {
    Storage::fake('public');

    [, $board, $thread] = postFixture();

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->set('image', UploadedFile::fake()->image('reply.jpg'))
        ->call('reply')
        ->assertHasNoErrors();

    $reply = $thread->posts()->oldest('id')->get()->last();

    expect($reply->image)->not->toBeNull();
    Storage::disk('public')->assertExists($reply->image->path);
});

test('a reply requires a body or an image', function () {
    [, $board, $thread] = postFixture();

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->call('reply')
        ->assertHasErrors(['body', 'image']);
});

test('authors can delete their replies', function () {
    [$user, $board, $thread] = postFixture();
    $reply = Post::factory()->for($thread)->for($user, 'author')->create(['body' => 'Remove me']);

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->call('deletePost', $reply->id)
        ->assertHasNoErrors();

    $this->assertModelMissing($reply);
});

test('the opening post cannot be deleted', function () {
    [$user, $board, $thread] = postFixture();
    $opening = $thread->openingPost;

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->call('deletePost', $opening->id)
        ->assertForbidden();

    $this->assertModelExists($opening);
});

test('members cannot delete another users reply', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = $owner->currentTeam;
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($team);

    [, $board, $thread] = postFixture($owner);
    $reply = Post::factory()->for($thread)->for($owner, 'author')->create(['body' => 'Stay']);

    authenticatePostUser($member);

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->call('deletePost', $reply->id)
        ->assertForbidden();

    $this->assertModelExists($reply);
});

test('owners can delete another users reply', function () {
    [$owner, $board, $thread] = postFixture();
    $author = User::factory()->create();
    $thread->board->team->members()->attach($author, ['role' => TeamRole::Member->value]);
    $reply = Post::factory()->for($thread)->for($author, 'author')->create(['body' => 'Gone']);

    Livewire::test('pages::threads.show', ['board' => $board, 'thread' => $thread])
        ->call('deletePost', $reply->id)
        ->assertHasNoErrors();

    $this->assertModelMissing($reply);
});
