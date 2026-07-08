<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoteUser;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        if (session('user_name')) {
            return redirect()->route('notes.index');
        }
        return view('login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required|min:2|max:30'
        ]);

        $userName = trim($request->user_name);
        $noteUser = NoteUser::where('user_name', $userName)->first();

        // Usuário novo — redireciona para criar senha
        if (!$noteUser) {
            session(['pending_user' => $userName]);
            return redirect()->route('register');
        }

        // Usuário existe — valida senha direto aqui
        if (!$request->filled('password') || !Hash::check($request->password, $noteUser->password)) {
            return back()->withInput()->withErrors(['password' => 'Senha incorreta.']);
        }

        session(['user_name' => $userName]);
        session()->forget('pending_user');

        return redirect()->route('notes.index');
    }

    public function showRegister()
    {
        // Acesso direto (link "Criar conta" no login): sem pending_user, mostra
        // o formulário com o campo de nome. Acesso vindo do login (nome novo
        // digitado lá): pending_user já está setado, nome vem pré-preenchido.
        return view('register', [
            'pendingUser' => session('pending_user'),
        ]);
    }

    public function register(Request $request)
    {
        $pendingUser = session('pending_user');

        $rules = [
            'password'        => 'required|min:4|confirmed',
            'secret_question' => 'required',
            'secret_answer'   => 'required|min:2',
        ];

        // Sem pending_user (cadastro direto): também exige e valida o nome.
        if (!$pendingUser) {
            $rules['user_name'] = 'required|min:2|max:30|unique:note_users,user_name';
        }

        $request->validate($rules);

        $userName = $pendingUser ?: trim($request->user_name);

        NoteUser::create([
            'user_name'       => $userName,
            'password'        => Hash::make($request->password),
            'secret_question' => $request->secret_question,
            'secret_answer'   => strtolower(trim($request->secret_answer)),
        ]);

        session(['user_name' => $userName]);
        session()->forget('pending_user');

        return redirect()->route('notes.index');
    }

    public function showPassword()
    {
        if (!session('pending_user')) {
            return redirect()->route('login');
        }
        return view('password');
    }

    public function checkPassword(Request $request)
    {
        $request->validate(['password' => 'required']);

        $userName = session('pending_user');
        $noteUser = NoteUser::where('user_name', $userName)->first();

        if (!$noteUser || !Hash::check($request->password, $noteUser->password)) {
            return back()->withErrors(['password' => 'Senha incorreta.']);
        }

        session(['user_name' => $userName]);
        session()->forget('pending_user');

        return redirect()->route('notes.index');
    }

    public function showRecover()
    {
        return view('recover');
    }

    public function recoverQuestion(Request $request)
    {
        $request->validate(['user_name' => 'required']);

        $userName = trim($request->user_name);
        $noteUser = NoteUser::where('user_name', $userName)->first();

        if (!$noteUser) {
            return back()->withErrors(['user_name' => 'Usuário não encontrado.']);
        }

        session(['recover_user' => $userName]);
        return view('recover_answer', ['question' => $noteUser->secret_question]);
    }

    public function recoverReset(Request $request)
    {
        $request->validate([
            'secret_answer' => 'required',
            'password'      => 'required|min:4|confirmed',
        ]);

        $userName = session('recover_user');
        $noteUser = NoteUser::where('user_name', $userName)->first();

        if (!$noteUser) {
            return redirect()->route('login');
        }

        if (strtolower(trim($request->secret_answer)) !== $noteUser->secret_answer) {
            return back()->withErrors(['secret_answer' => 'Resposta incorreta.']);
        }

        $noteUser->update(['password' => Hash::make($request->password)]);
        session()->forget('recover_user');

        return redirect()->route('login')->with('success', 'Senha redefinida com sucesso!');
    }

    public function logout()
    {
        session()->forget('user_name');
        return redirect()->route('login');
    }
}