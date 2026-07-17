<?php

namespace App\Http\Middleware;

use App\Models\NoteUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    /** Restricts administrative routes to active users with the admin role. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->attributes->get('note_user')
            ?? NoteUser::find($request->session()->get('user_id'));

        abort_unless($user?->isActive() && $user->isAdmin(), 403);

        return $next($request);
    }
}
