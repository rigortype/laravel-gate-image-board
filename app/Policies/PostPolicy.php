<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view the post.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->belongsToTeam($post->thread->board->team);
    }

    /**
     * Determine whether the user can reply to the thread.
     */
    public function create(User $user, Thread $thread): bool
    {
        return $user->belongsToTeam($thread->board->team);
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        if ($post->user_id === $user->id) {
            return true;
        }

        return $user->hasTeamPermission($post->thread->board->team, TeamPermission::UpdateTeam);
    }
}
