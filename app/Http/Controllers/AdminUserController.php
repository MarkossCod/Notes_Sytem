<?php

namespace App\Http\Controllers;

use App\Models\NoteUser;
use App\Support\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /** Lists accounts with their application data totals. */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->value());

        $users = NoteUser::query()
            ->addSelect([
                'notes_count' => DB::table('notes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('notes.user_name', 'note_users.user_name'),
                'categories_count' => DB::table('categories')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('categories.user_name', 'note_users.user_name'),
            ])
            ->when($search, fn ($query) => $query->where('user_name', 'like', "%{$search}%"))
            ->orderByDesc('role')
            ->orderBy('user_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /** Creates an account without exposing public self-registration. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_name' => ['required', 'string', 'min:2', 'max:30', 'unique:note_users,user_name'],
            'password' => ['required', 'confirmed', StrongPassword::rule()],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'secret_question' => ['required', 'string', 'max:255'],
            'secret_answer' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        NoteUser::create([
            'user_name' => trim($validated['user_name']),
            'password' => Hash::make($validated['password']),
            'secret_question' => $validated['secret_question'],
            'secret_answer' => Hash::make(strtolower(trim($validated['secret_answer']))),
            'role' => $validated['role'],
            'active' => true,
        ]);

        return back()->with('success', 'Usuário criado com segurança.');
    }

    /** Changes role and access status while preserving at least one administrator. */
    public function update(Request $request, NoteUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'user'])],
            'active' => ['required', 'boolean'],
        ]);

        $removesAdminAccess = $user->isAdmin()
            && ($validated['role'] !== 'admin' || !(bool) $validated['active']);

        if ($removesAdminAccess && NoteUser::where('role', 'admin')->where('active', true)->count() <= 1) {
            return back()->withErrors(['users' => 'O sistema precisa manter pelo menos um administrador ativo.']);
        }

        if ($user->id === (int) $request->session()->get('user_id') && $removesAdminAccess) {
            return back()->withErrors(['users' => 'Você não pode remover o próprio acesso administrativo.']);
        }

        $user->update([
            'role' => $validated['role'],
            'active' => (bool) $validated['active'],
        ]);

        return back()->with('success', 'Permissões do usuário atualizadas.');
    }

    /** Replaces a user's password using the shared strong-password policy. */
    public function resetPassword(Request $request, NoteUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', StrongPassword::rule()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Senha de {$user->user_name} redefinida.");
    }
}
