@props([
    'thread',
    'showBoard' => false,
])

<article
    {{ $attributes->merge(['class' => 'flex gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900']) }}
    data-test="explore-thread-row"
>
    @if ($thread->openingPost?->image)
        <a href="{{ $thread->url() }}" class="shrink-0">
            <img
                src="{{ $thread->openingPost->image->url() }}"
                alt=""
                class="size-20 rounded-lg object-cover"
            />
        </a>
    @endif

    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
            <flux:heading size="lg">
                <a href="{{ $thread->url() }}" class="hover:underline">
                    {{ $thread->subject ?: __('No subject') }}
                </a>
            </flux:heading>
            <flux:badge size="sm" :color="$thread->visibility->badgeColor()">
                {{ $thread->visibility->label() }}
            </flux:badge>
        </div>

        @if ($showBoard)
            <flux:text class="mt-1">
                <a
                    href="{{ route('explore.board', ['id' => $thread->board->getKey()]) }}"
                    class="hover:underline"
                    data-test="explore-board-link"
                >/{{ $thread->board->slug }}/</a>
            </flux:text>
        @endif

        <flux:text class="mt-1 truncate">
            {{ $thread->openingPost?->body }}
        </flux:text>
        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
            {{ trans_choice(':count post|:count posts', $thread->posts_count, ['count' => $thread->posts_count]) }}
            ·
            {{ $thread->bumped_at->diffForHumans() }}
        </flux:text>
    </div>
</article>
