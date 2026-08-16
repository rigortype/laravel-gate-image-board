<?php

use App\Enums\ThreadVisibility;

test('dotted board keys resolve to the english copy', function () {
    expect(__('boards.index'))->toBe('Boards')
        ->and(__('boards.explore'))->toBe('Explore')
        ->and(__('boards.visibility.public'))->toBe('Public')
        ->and(__('boards.explore_empty_board', ['name' => 'Artwork']))->toBe('No public threads on Artwork.');
});

test('a missing board key is returned as the key', function () {
    expect(__('boards.missing'))->toBe('boards.missing');
});

test('visibility labels and descriptions come from the language file', function (ThreadVisibility $visibility, string $label, string $help) {
    expect($visibility->label())->toBe(__($label))
        ->and($visibility->description())->toBe(__($help));
})->with([
    'public' => [ThreadVisibility::Public, 'boards.visibility.public', 'boards.visibility.public_help'],
    'team' => [ThreadVisibility::Team, 'boards.visibility.team', 'boards.visibility.team_help'],
    'unlisted' => [ThreadVisibility::Unlisted, 'boards.visibility.unlisted', 'boards.visibility.unlisted_help'],
    'private' => [ThreadVisibility::Private, 'boards.visibility.private', 'boards.visibility.private_help'],
]);
