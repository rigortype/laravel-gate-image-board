<?php

namespace App\Actions\Boards;

use App\Models\Board;
use App\Models\Team;

class CreateBoard
{
    /**
     * Create a board on the given team.
     */
    public function handle(Team $team, string $name, ?string $description = null): Board
    {
        return $team->boards()->create([
            'name' => $name,
            'slug' => Board::uniqueSlugFor($team, $name),
            'description' => $description,
        ]);
    }
}
