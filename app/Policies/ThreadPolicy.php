<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Enums\ThreadVisibility;
use App\Models\Board;
use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Determine whether the user can view the thread.
     */
    public function view(?User $user, Thread $thread, ?string $shareToken = null): bool
    {
        return match ($thread->visibility) {
            ThreadVisibility::Public => true,
            ThreadVisibility::Team => $user?->belongsToTeam($thread->board->team) ?? false,
            ThreadVisibility::Unlisted => $this->isAuthorOrModerator($user, $thread)
                || $this->shareTokenMatches($thread, $shareToken),
            ThreadVisibility::Private => $this->isAuthorOrModerator($user, $thread),
        };
    }

    /**
     * Determine whether the user can start a thread on the board.
     */
    public function create(User $user, Board $board): bool
    {
        return $user->belongsToTeam($board->team);
    }

    /**
     * Determine whether the user can delete the thread.
     */
    public function delete(User $user, Thread $thread): bool
    {
        return $user->hasTeamPermission($thread->board->team, TeamPermission::UpdateTeam);
    }

    /**
     * Determine whether the user wrote the opening post or can moderate the team.
     */
    protected function isAuthorOrModerator(?User $user, Thread $thread): bool
    {
        if ($user === null) {
            return false;
        }

        if ($thread->isAuthoredBy($user)) {
            return true;
        }

        return $user->hasTeamPermission($thread->board->team, TeamPermission::UpdateTeam);
    }

    /**
     * Determine whether the presented share token unlocks the thread.
     */
    protected function shareTokenMatches(Thread $thread, ?string $shareToken): bool
    {
        return $thread->share_token !== null
            && $shareToken !== null
            && hash_equals($thread->share_token, $shareToken);
    }
}
