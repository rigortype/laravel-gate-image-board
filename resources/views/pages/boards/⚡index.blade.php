<?php

use App\Actions\Boards\CreateBoard;
use App\Models\Board;
use App\Models\Team;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app'), Title('Boards')] class extends Component {
    public string $name = '';

    public string $description = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', [Board::class, $this->team()]);
    }

    public function createBoard(CreateBoard $createBoard): void
    {
        $team = $this->team();

        Gate::authorize('create', [Board::class, $team]);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $createBoard->handle($team, $validated['name'], $validated['description'] ?? null);

        $this->reset('name', 'description');

        $this->dispatch('close-modal', name: 'create-board');

        Flux::toast(variant: 'success', text: __('Board created.'));
    }

    /**
     * @return Collection<int, Board>
     */
    #[Computed]
    public function boards(): Collection
    {
        return $this->team()
            ->boards()
            ->withCount('threads')
            ->latest()
            ->get();
    }

    public function canCreateBoard(): bool
    {
        return Auth::user()->can('create', [Board::class, $this->team()]);
    }

    private function team(): Team
    {
        $team = Auth::user()->currentTeam;

        abort_unless($team instanceof Team, 403);

        return $team;
    }
}; ?>

<section class="w-full space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('boards.index') }}</flux:heading>
            <flux:subheading>{{ __('Threads and images for :team', ['team' => $this->team()->name]) }}</flux:subheading>
        </div>

        @if ($this->canCreateBoard())
            <flux:modal.trigger name="create-board">
                <flux:button variant="primary" icon="plus" data-test="create-board-button">
                    {{ __('New board') }}
                </flux:button>
            </flux:modal.trigger>
        @endif
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($this->boards as $board)
            <a
                href="{{ route('boards.show', $board) }}"
                wire:navigate
                class="block rounded-xl border border-zinc-200 bg-white p-4 hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500"
                data-test="board-card"
            >
                <flux:heading size="lg">{{ $board->name }}</flux:heading>
                <flux:text class="mt-1">/{{ $board->slug }}/</flux:text>
                @if ($board->description)
                    <flux:text class="mt-2">{{ $board->description }}</flux:text>
                @endif
                <flux:text class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ trans_choice(':count thread|:count threads', $board->threads_count, ['count' => $board->threads_count]) }}
                </flux:text>
            </a>
        @empty
            <flux:text class="col-span-full py-8 text-center text-zinc-500 dark:text-zinc-400">
                {{ __('No boards yet.') }}
            </flux:text>
        @endforelse
    </div>

    @if ($this->canCreateBoard())
        <flux:modal name="create-board" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
            <form wire:submit="createBoard" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Create a board') }}</flux:heading>
                    <flux:subheading>{{ __('A board is a topic area for threads.') }}</flux:subheading>
                </div>

                <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus data-test="create-board-name" />
                <flux:input wire:model="description" :label="__('Description')" type="text" data-test="create-board-description" />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit" data-test="create-board-submit">
                        {{ __('Create board') }}
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    @endif
</section>
