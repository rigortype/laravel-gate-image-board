<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Enums\ThreadVisibility;
use App\Models\Post;
use App\Models\Thread;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view the post.
     */
    public function view(?User $user, Post $post, ?string $shareToken = null): bool
    {
        return $user?->can('view', [$post->thread, $shareToken])
            ?? $post->thread->visibility === ThreadVisibility::Public;
    }

    /**
     * Determine whether the user can reply to the thread.
     */
    public function create(User $user, Thread $thread, ?string $shareToken = null): bool
    {
        return $user->belongsToTeam($thread->board->team)
            && $user->can('view', [$thread, $shareToken]);
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
