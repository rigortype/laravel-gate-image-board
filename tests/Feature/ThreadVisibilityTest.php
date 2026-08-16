<?php

use App\Enums\TeamRole;
use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function authenticateVisibilityUser(?User $user = null): User
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
function visibilityThread(ThreadVisibility $visibility, ?User $author = null): array
{
    $author = authenticateVisibilityUser($author);
    $board = Board::factory()->for($author->currentTeam)->create();

    $thread = Thread::factory()
        ->for($board)
        ->state([
            'visibility' => $visibility,
            'share_token' => $visibility === ThreadVisibility::Unlisted ? '01visibilitytoken000000001' : null,
        ])
        ->create();

    Post::factory()->for($thread)->for($author, 'author')->create([
        'body' => $visibility->value.' body',
    ]);

    return [$author, $board, $thread->fresh(['openingPost', 'board.team'])];
}

test('members can create a public thread', function () {
    Storage::fake('public');

    $user = authenticateVisibilityUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    Livewire::test('pages::boards.show', ['board' => $board])
        ->set('subject', 'Open')
        ->set('body', 'Hello world')
        ->set('visibility', ThreadVisibility::Public->value)
        ->set('image', UploadedFile::fake()->image('pic.png'))
        ->call('createThread')
        ->assertHasNoErrors();

    $thread = Thread::query()->whereBelongsTo($board)->first();

    expect($thread?->visibility)->toBe(ThreadVisibility::Public)
        ->and($thread?->share_token)->toBeNull();
});

test('creating an unlisted thread issues a share token', function () {
    Storage::fake('public');

    $user = authenticateVisibilityUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    Livewire::test('pages::boards.show', ['board' => $board])
        ->set('body', 'Secret link')
        ->set('visibility', ThreadVisibility::Unlisted->value)
        ->set('image', UploadedFile::fake()->image('pic.png'))
        ->call('createThread')
        ->assertHasNoErrors();

    $thread = Thread::query()->whereBelongsTo($board)->first();

    expect($thread?->visibility)->toBe(ThreadVisibility::Unlisted)
        ->and($thread?->share_token)->not->toBeNull();
});

test('guests can view a public thread', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Public);
    auth()->logout();

    $this->get(route('threads.show', [
        'current_team' => $board->team,
        'board' => $board,
        'thread' => $thread,
    ]))
        ->assertOk()
        ->assertSee('public body');
});

test('guests cannot view a team only thread', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Team);
    auth()->logout();

    $this->get(route('threads.show', [
        'current_team' => $board->team,
        'board' => $board,
        'thread' => $thread,
    ]))->assertForbidden();
});

test('guests cannot view a private thread', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Private);
    auth()->logout();

    $this->get(route('threads.show', [
        'current_team' => $board->team,
        'board' => $board,
        'thread' => $thread,
    ]))->assertForbidden();
});

test('guests cannot view an unlisted thread by id', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Unlisted);
    auth()->logout();

    $this->get(route('threads.show', [
        'current_team' => $board->team,
        'board' => $board,
        'thread' => $thread,
    ]))->assertForbidden();
});

test('guests can view an unlisted thread by share token', function () {
    [, , $thread] = visibilityThread(ThreadVisibility::Unlisted);
    auth()->logout();

    $this->get(route('threads.shared', $thread))
        ->assertOk()
        ->assertSee('unlisted body');
});

test('an unknown share token is not found', function () {
    visibilityThread(ThreadVisibility::Unlisted);
    auth()->logout();

    $this->get('/t/01doesnotexisttoken0000000001')->assertNotFound();
});

test('members cannot view another members private thread', function () {
    [$author, $board, $thread] = visibilityThread(ThreadVisibility::Private);

    $member = User::factory()->create();
    $board->team->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($board->team);
    authenticateVisibilityUser($member);

    $this->get(route('threads.show', ['board' => $board, 'thread' => $thread]))
        ->assertForbidden();
});

test('the author can view their private thread', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Private);

    $this->get(route('threads.show', ['board' => $board, 'thread' => $thread]))
        ->assertOk()
        ->assertSee('private body');
});

test('guests only see public threads in the catalog', function () {
    $user = authenticateVisibilityUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    $public = Thread::factory()->for($board)->public()->create(['subject' => 'Public subject']);
    Post::factory()->for($public)->for($user, 'author')->create();

    $team = Thread::factory()->for($board)->create(['subject' => 'Team subject']);
    Post::factory()->for($team)->for($user, 'author')->create();

    auth()->logout();

    $this->get(route('boards.show', ['current_team' => $board->team, 'board' => $board]))
        ->assertOk()
        ->assertSee('Public subject')
        ->assertDontSee('Team subject');
});

test('members do not see another members unlisted thread in the catalog', function () {
    [$author, $board] = visibilityThread(ThreadVisibility::Unlisted);
    $author->currentTeam;

    $unlisted = $board->threads()->first();

    $member = User::factory()->create();
    $board->team->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($board->team);
    authenticateVisibilityUser($member);

    $this->get(route('boards.show', $board))
        ->assertOk()
        ->assertDontSee($unlisted->openingPost->body);
});

test('the author sees their unlisted thread in the catalog', function () {
    [, $board, $thread] = visibilityThread(ThreadVisibility::Unlisted);

    $this->get(route('boards.show', $board))
        ->assertOk()
        ->assertSee($thread->openingPost->body);
});
