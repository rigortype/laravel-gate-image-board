<?php

namespace App\Actions\Teams;

use App\Actions\Boards\CreateBoard;
use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    public function __construct(private CreateBoard $createBoard) {}

    /**
     * Create a new team and add the user as owner.
     */
    public function handle(User $user, string $name, bool $isPersonal = false): Team
    {
        return DB::transaction(function () use ($user, $name, $isPersonal) {
            $team = Team::create([
                'name' => $name,
                'is_personal' => $isPersonal,
            ]);

            $team->memberships()->create([
                'user_id' => $user->id,
                'role' => TeamRole::Owner,
            ]);

            $this->createBoard->handle($team, 'General', 'General discussion');

            $user->switchTeam($team);

            return $team;
        });
    }
}
