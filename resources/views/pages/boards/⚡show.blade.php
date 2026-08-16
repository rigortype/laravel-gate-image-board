<?php

use App\Actions\Threads\CreateThread;
use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Thread;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts::app'), Title('Board')] class extends Component {
    use WithFileUploads, WithPagination;

    public Board $board;

    public string $subject = '';

    public string $body = '';

    public string $visibility = ThreadVisibility::Team->value;

    public mixed $image = null;

    public function mount(Board $board): void
    {
        $this->board = $board;

        Gate::authorize('view', $board);
    }

    public function createThread(CreateThread $createThread): void
    {
        Gate::authorize('create', [Thread::class, $this->board]);

        $validated = $this->validate([
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'visibility' => ['required', Rule::enum(ThreadVisibility::class)],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $thread = $createThread->handle(
            $this->board,
            Auth::user(),
            $validated['body'],
            $this->image,
            $validated['subject'] ?: null,
            ThreadVisibility::from($validated['visibility']),
        );

        $this->reset('subject', 'body', 'image');
        $this->visibility = ThreadVisibility::Team->value;

        Flux::toast(variant: 'success', text: __('Thread created.'));

        $this->redirectRoute('threads.show', [
            'board' => $this->board,
            'thread' => $thread,
        ], navigate: true);
    }

    /**
     * @return LengthAwarePaginator<int, Thread>
     */
    #[Computed]
    public function threads(): LengthAwarePaginator
    {
        return $this->board->threads()
            ->visibleInCatalogTo(Auth::user(), $this->board)
            ->with(['openingPost.author', 'openingPost.image', 'board.team'])
            ->withCount('posts')
            ->latest('bumped_at')
            ->paginate(20);
    }

    public function canCreateThread(): bool
    {
        return Auth::user()?->can('create', [Thread::class, $this->board]) ?? false;
    }
}; ?>

<section class="w-full space-y-8">
    <div class="space-y-2">
        <flux:breadcrumbs>
            @auth
                <flux:breadcrumbs.item :href="route('boards.index')" wire:navigate>{{ __('Boards') }}</flux:breadcrumbs.item>
            @endauth
            <flux:breadcrumbs.item>{{ $board->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:heading size="xl">{{ $board->name }}</flux:heading>
        <flux:text>/{{ $board->slug }}/</flux:text>
        @if ($board->description)
            <flux:subheading>{{ $board->description }}</flux:subheading>
        @endif
    </div>

    @if ($this->canCreateThread())
        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('New thread') }}</flux:heading>

            <form wire:submit="createThread" class="space-y-4" data-test="create-thread-form">
                <flux:input wire:model="subject" :label="__('Subject')" type="text" data-test="thread-subject" />
                <flux:textarea wire:model="body" :label="__('Comment')" rows="4" required data-test="thread-body" />
                <flux:radio.group wire:model="visibility" :label="__('Visibility')" variant="cards" data-test="thread-visibility">
                    @foreach (ThreadVisibility::cases() as $option)
                        <flux:radio :value="$option->value" :label="$option->label()" :description="$option->description()" />
                    @endforeach
                </flux:radio.group>
                <flux:input wire:model="image" type="file" :label="__('Image')" accept="image/jpeg,image/png,image/gif,image/webp" required data-test="thread-image" />

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="" class="max-h-48 rounded-lg" />
                @endif

                <flux:button variant="primary" type="submit" data-test="create-thread-submit">
                    {{ __('Post thread') }}
                </flux:button>
            </form>
        </flux:card>
    @endif

    <div class="space-y-3">
        @forelse ($this->threads as $thread)
            <a
                href="{{ route('threads.show', ['board' => $board, 'thread' => $thread]) }}"
                wire:navigate
                class="flex gap-4 rounded-xl border border-zinc-200 bg-white p-4 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500"
                wire:key="thread-{{ $thread->id }}"
                data-test="thread-row"
            >
                @if ($thread->openingPost?->image)
                    <img
                        src="{{ $thread->openingPost->image->url() }}"
                        alt=""
                        class="size-20 shrink-0 rounded-lg object-cover"
                    />
                @endif

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="lg">
                            {{ $thread->subject ?: __('No subject') }}
                        </flux:heading>
                        <flux:badge size="sm" :color="$thread->visibility->badgeColor()">
                            {{ $thread->visibility->label() }}
                        </flux:badge>
                    </div>
                    <flux:text class="mt-1 truncate">
                        {{ $thread->openingPost?->body }}
                    </flux:text>
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ trans_choice(':count post|:count posts', $thread->posts_count, ['count' => $thread->posts_count]) }}
                        ·
                        {{ $thread->bumped_at->diffForHumans() }}
                    </flux:text>
                </div>
            </a>
        @empty
            <flux:text class="py-8 text-center text-zinc-500 dark:text-zinc-400">
                {{ __('No threads yet. Post one with an image.') }}
            </flux:text>
        @endforelse
    </div>

    <flux:pagination :paginator="$this->threads" />
</section>
