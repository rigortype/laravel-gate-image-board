<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Board;
use App\Models\Team;
use App\Models\User;

class BoardPolicy
{
    /**
     * Determine whether the user can view any boards on the team.
     */
    public function viewAny(User $user, Team $team): bool
    {
        return $user->belongsToTeam($team);
    }

    /**
     * Determine whether the user can view the board.
     */
    public function view(User $user, Board $board): bool
    {
        return $user->belongsToTeam($board->team);
    }

    /**
     * Determine whether the user can create boards on the team.
     */
    public function create(User $user, Team $team): bool
    {
        return $user->hasTeamPermission($team, TeamPermission::UpdateTeam);
    }

    /**
     * Determine whether the user can update the board.
     */
    public function update(User $user, Board $board): bool
    {
        return $user->hasTeamPermission($board->team, TeamPermission::UpdateTeam);
    }

    /**
     * Determine whether the user can delete the board.
     */
    public function delete(User $user, Board $board): bool
    {
        return $user->hasTeamPermission($board->team, TeamPermission::UpdateTeam);
    }
}
