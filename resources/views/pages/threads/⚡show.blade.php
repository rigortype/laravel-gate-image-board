<?php

use App\Actions\Posts\ReplyToThread;
use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Post;
use App\Models\Thread;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts::app'), Title('Thread')] class extends Component {
    use WithFileUploads, WithPagination;

    public ?Board $board = null;

    public Thread $thread;

    public string $body = '';

    public mixed $image = null;

    public function mount(?Board $board = null): void
    {
        $this->thread->loadMissing(['openingPost', 'board.team']);

        $this->board = $board ?? $this->thread->board;

        Gate::authorize('view', [$this->thread, $this->presentedShareToken()]);
    }

    public function reply(ReplyToThread $replyToThread): void
    {
        Gate::authorize('create', [Post::class, $this->thread, $this->presentedShareToken()]);

        $this->validate([
            'body' => ['required_without:image', 'nullable', 'string', 'max:10000'],
            'image' => ['required_without:body', 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $replyToThread->handle(
            $this->thread,
            Auth::user(),
            $this->body ?? '',
            $this->image,
        );

        $this->reset('body', 'image');
        $this->thread->refresh();

        Flux::toast(variant: 'success', text: __('Reply posted.'));
    }

    public function deletePost(int $postId): void
    {
        $post = $this->thread->posts()->findOrFail($postId);

        Gate::authorize('delete', $post);

        abort_if($post->is($this->thread->openingPost), 403);

        $post->image?->delete();
        $post->delete();

        Flux::toast(variant: 'success', text: __('Post deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    #[Computed]
    public function posts(): LengthAwarePaginator
    {
        return $this->thread->posts()
            ->with(['author', 'image', 'thread.board.team'])
            ->oldest()
            ->paginate(50);
    }

    public function canReply(): bool
    {
        return Auth::user()?->can('create', [Post::class, $this->thread, $this->presentedShareToken()]) ?? false;
    }

    public function canSeeShareLink(): bool
    {
        return $this->thread->visibility === ThreadVisibility::Unlisted
            && $this->thread->share_token !== null
            && $this->thread->isAuthoredBy(Auth::user());
    }

    private function presentedShareToken(): ?string
    {
        return request()->routeIs('threads.shared') ? $this->thread->share_token : null;
    }
}; ?>

<section class="w-full space-y-8">
    <div class="space-y-2">
        <flux:breadcrumbs>
            @auth
                <flux:breadcrumbs.item :href="route('boards.index')" wire:navigate>{{ __('Boards') }}</flux:breadcrumbs.item>
            @endauth
            <flux:breadcrumbs.item :href="route('boards.show', $board)" wire:navigate>{{ $board->name }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $thread->subject ?: __('Thread #:id', ['id' => $thread->id]) }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex flex-wrap items-center gap-2">
            <flux:heading size="xl">{{ $thread->subject ?: __('No subject') }}</flux:heading>
            <flux:badge :color="$thread->visibility->badgeColor()">{{ $thread->visibility->label() }}</flux:badge>
        </div>

        @if ($this->canSeeShareLink())
            <flux:callout variant="subtle" icon="link" data-test="share-link">
                <flux:callout.heading>{{ __('Share link') }}</flux:callout.heading>
                <flux:callout.text>{{ route('threads.shared', $thread) }}</flux:callout.text>
            </flux:callout>
        @endif
    </div>

    <div class="space-y-4">
        @foreach ($this->posts as $post)
            <article
                id="p{{ $post->id }}"
                class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900"
                wire:key="post-{{ $post->id }}"
                data-test="post"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                        <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $post->author->name }}</span>
                        · No.{{ $post->id }}
                        · {{ $post->created_at->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
                    </div>

                    @if (Auth::user()?->can('delete', $post) && ! $post->is($this->thread->openingPost))
                        <flux:button
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="deletePost({{ $post->id }})"
                            wire:confirm="{{ __('Delete this post?') }}"
                            data-test="delete-post-button"
                        />
                    @endif
                </div>

                @if ($post->image)
                    <a href="{{ $post->image->url() }}" target="_blank" rel="noopener" class="mt-3 inline-block">
                        <img
                            src="{{ $post->image->url() }}"
                            alt="{{ $post->image->original_filename }}"
                            class="max-h-80 max-w-full rounded-lg"
                        />
                    </a>
                    <flux:text class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        {{ $post->image->original_filename }}
                        @if ($post->image->width && $post->image->height)
                            · {{ $post->image->width }}×{{ $post->image->height }}
                        @endif
                    </flux:text>
                @endif

                <p class="mt-3 whitespace-pre-wrap">{{ $post->body }}</p>
            </article>
        @endforeach
    </div>

    <flux:pagination :paginator="$this->posts" />

    @if ($this->canReply())
        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('Reply') }}</flux:heading>

            <form wire:submit="reply" class="space-y-4" data-test="reply-form">
                <flux:textarea wire:model="body" :label="__('Comment')" rows="4" data-test="reply-body" />
                <flux:input wire:model="image" type="file" :label="__('Image')" accept="image/jpeg,image/png,image/gif,image/webp" data-test="reply-image" />

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="" class="max-h-48 rounded-lg" />
                @endif

                <flux:button variant="primary" type="submit" data-test="reply-submit">
                    {{ __('Post reply') }}
                </flux:button>
            </form>
        </flux:card>
    @elseif (! Auth::check())
        <flux:callout>
            <flux:callout.text>
                <a href="{{ route('login') }}" class="underline">{{ __('Log in') }}</a>
                {{ __('to reply.') }}
            </flux:callout.text>
        </flux:callout>
    @endif
</section>
