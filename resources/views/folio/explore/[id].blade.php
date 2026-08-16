<?php

use App\Enums\ThreadVisibility;
use App\Models\Board;
use Illuminate\View\View;

use function Laravel\Folio\name;
use function Laravel\Folio\render;

name('explore.board');

render(function (View $view, string $id) {
    $board = Board::query()->findOrFail($id);

    return $view
        ->with('board', $board)
        ->with('threads', $board->threads()
            ->where('visibility', ThreadVisibility::Public)
            ->with(['openingPost.author', 'openingPost.image', 'board.team'])
            ->withCount('posts')
            ->latest('bumped_at')
            ->paginate(20));
});

?>

<x-layouts::app :title="$board->name">
    <section class="w-full space-y-8" data-test="explore-board">
        <div class="space-y-2">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item :href="route('explore')">{{ __('boards.explore') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $board->name }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:heading size="xl">{{ $board->name }}</flux:heading>
            <flux:text>/{{ $board->slug }}/</flux:text>
            @if ($board->description)
                <flux:subheading>{{ $board->description }}</flux:subheading>
            @endif
        </div>

        <div class="space-y-3">
            @forelse ($threads as $thread)
                <x-public-thread-row :thread="$thread" />
            @empty
                <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('boards.explore_empty_board', ['name' => $board->name]) }}
                </flux:text>
            @endforelse
        </div>

        <flux:pagination :paginator="$threads" />
    </section>
</x-layouts::app>
