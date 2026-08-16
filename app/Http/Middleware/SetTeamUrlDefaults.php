<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    /**
     * Set the default URL parameters for team-based routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->teamSlug($request);

        if ($slug !== null) {
            URL::defaults([
                'current_team' => $slug,
                'team' => $slug,
            ]);
        }

        return $next($request);
    }

    /**
     * Resolve the team slug from the route or the authenticated user.
     */
    protected function teamSlug(Request $request): ?string
    {
        $team = $request->route('current_team');

        if ($team instanceof Team) {
            return $team->slug;
        }

        if (is_string($team) && $team !== '') {
            return $team;
        }

        return $request->user()?->currentTeam?->slug;
    }
}
