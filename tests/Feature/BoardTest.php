<?php

use App\Enums\TeamRole;
use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

function authenticateBoardUser(?User $user = null): User
{
    $user ??= User::factory()->create();

    test()->actingAs($user);

    URL::defaults([
        'current_team' => $user->currentTeam->slug,
        'team' => $user->currentTeam->slug,
    ]);

    return $user;
}

test('guests are redirected from the boards index', function () {
    $user = User::factory()->create();

    $this->get(route('boards.index', ['current_team' => $user->currentTeam->slug]))
        ->assertRedirect(route('login'));
});

test('team members can view the boards index', function () {
    $user = authenticateBoardUser();

    $this->get(route('boards.index'))->assertOk();
});

test('creating a team also creates a general board', function () {
    $user = authenticateBoardUser();

    Livewire::test('pages::teams.index')
        ->set('name', 'Image Club')
        ->call('createTeam')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('boards', [
        'name' => 'General',
        'slug' => 'general',
    ]);
});

test('owners can create a board', function () {
    $user = authenticateBoardUser();

    Livewire::test('pages::boards.index')
        ->set('name', 'Artwork')
        ->set('description', 'Drawings')
        ->call('createBoard')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('boards', [
        'team_id' => $user->currentTeam->id,
        'name' => 'Artwork',
        'slug' => 'artwork',
        'description' => 'Drawings',
    ]);
});

test('board slugs are unique per team', function () {
    $user = authenticateBoardUser();

    Board::factory()->for($user->currentTeam)->create([
        'name' => 'Artwork',
        'slug' => 'artwork',
    ]);

    Livewire::test('pages::boards.index')
        ->set('name', 'Artwork')
        ->call('createBoard')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('boards', [
        'team_id' => $user->currentTeam->id,
        'slug' => 'artwork-1',
    ]);
});

test('members cannot create boards', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $team = $owner->currentTeam;
    $team->members()->attach($member, ['role' => TeamRole::Member->value]);
    $member->switchTeam($team);

    authenticateBoardUser($member);

    Livewire::test('pages::boards.index')
        ->set('name', 'Secret')
        ->call('createBoard')
        ->assertForbidden();
});

test('members can view a board', function () {
    $user = authenticateBoardUser();
    $board = Board::factory()->for($user->currentTeam)->create();

    $this->get(route('boards.show', $board))->assertOk();
});

test('a board from another team is not found', function () {
    $user = authenticateBoardUser();
    $foreign = Board::factory()->create(['slug' => 'foreign']);

    $this->get(route('boards.show', $foreign))->assertNotFound();
});
