<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Board;
use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Determine whether the user can view the thread.
     */
    public function view(User $user, Thread $thread): bool
    {
        return $user->belongsToTeam($thread->board->team);
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
}
