<?php

namespace App\Http\Middleware;

use App\Models\NoteUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNoteUserAuthenticated
{
    /** Allows access only when the session belongs to an active account. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = NoteUser::where('user_name', $request->session()->get('user_name'))->first();

        if (!$user || !$user->isActive()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'user_name' => $user ? 'Esta conta está inativa. Procure o administrador.' : 'Faça login para continuar.',
            ]);
        }

        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $user->user_name,
            'user_role' => $user->role,
        ]);
        $request->attributes->set('note_user', $user);

        return $next($request);
    }
}
