<?php

use App\Enums\ThreadVisibility;
use App\Models\Thread;
use Illuminate\View\View;

use function Laravel\Folio\name;
use function Laravel\Folio\render;

name('explore');

render(function (View $view) {
    return $view->with('threads', Thread::query()
        ->where('visibility', ThreadVisibility::Public)
        ->with(['openingPost.author', 'openingPost.image', 'board.team'])
        ->withCount('posts')
        ->latest('bumped_at')
        ->paginate(20));
});

?>

<x-layouts::app :title="__('Explore')">
    <section class="w-full space-y-8" data-test="explore-catalog">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Explore') }}</flux:heading>
            <flux:subheading>{{ __('Public threads anyone can find and read.') }}</flux:subheading>
        </div>

        <div class="space-y-3">
            @forelse ($threads as $thread)
                <x-public-thread-row :thread="$thread" :show-board="true" />
            @empty
                <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                    {{ __('No public threads yet.') }}
                </flux:text>
            @endforelse
        </div>

        <flux:pagination :paginator="$threads" />
    </section>
</x-layouts::app>
