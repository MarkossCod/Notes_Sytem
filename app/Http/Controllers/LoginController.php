<?php

namespace App\Http\Controllers;

use App\Models\NoteUser;
use App\Services\ActivityLogger;
use App\Support\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_DECAY_SECONDS = 60;

    private const MAX_REGISTRATIONS_PER_HOUR = 3;

    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = NoteUser::where('user_name', $request->session()->get('user_name'))->first();

        if ($user?->isActive()) {
            return redirect()->route('notes.index');
        }

        if ($request->session()->has('user_name')) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('login');
    }

    /** Authenticates an active account and throttles repeated failures. */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'user_name' => ['required', 'string', 'min:2', 'max:30'],
            'password' => ['nullable', 'string'],
        ]);

        $userName = trim($credentials['user_name']);
        $throttleKey = $this->loginThrottleKey($request, $userName);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('user_name'))->withErrors([
                'password' => "Muitas tentativas. Tente novamente em {$seconds} segundos.",
            ]);
        }

        $user = NoteUser::where('user_name', $userName)->first();

        if (! $user) {
            $request->session()->put('pending_user', $userName);

            return redirect()->route('register');
        }

        if (empty($credentials['password']) || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

            return back()->withInput($request->only('user_name'))->withErrors([
                'password' => 'Usuário ou senha inválidos.',
            ]);
        }

        if (! $user->isActive()) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);

            return back()->withInput($request->only('user_name'))->withErrors([
                'user_name' => 'Esta conta está inativa. Procure o administrador.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $user->user_name,
            'user_role' => $user->role,
        ]);
        $user->update(['last_login_at' => now()]);
        $this->activityLogger->record('login', 'Entrou no sistema.', $user);

        return redirect()->route('notes.index');
    }

    public function showRegister(Request $request): View
    {
        return view('register', [
            'pendingUser' => $request->session()->get('pending_user'),
        ]);
    }

    /** Creates a public user account with the strong-password policy and no elevated privileges. */
    public function register(Request $request): RedirectResponse
    {
        $pendingUser = $request->session()->get('pending_user');
        $registrationKey = 'registration|'.$request->ip();

        if (RateLimiter::tooManyAttempts($registrationKey, self::MAX_REGISTRATIONS_PER_HOUR)) {
            return back()->withErrors([
                'user_name' => 'Limite de cadastros atingido. Tente novamente mais tarde.',
            ]);
        }

        $rules = [
            'password' => ['required', 'confirmed', StrongPassword::rule()],
            'secret_question' => ['required', 'string', 'max:255'],
            'secret_answer' => ['required', 'string', 'min:2', 'max:255'],
        ];

        if (! $pendingUser) {
            $rules['user_name'] = ['required', 'string', 'min:2', 'max:30', 'unique:note_users,user_name'];
        }

        $validated = $request->validate($rules);
        $userName = trim($pendingUser ?: $validated['user_name']);

        if (NoteUser::where('user_name', $userName)->exists()) {
            $request->session()->forget('pending_user');

            return redirect()->route('login')->withErrors(['user_name' => 'Este usuário já está cadastrado.']);
        }

        $user = NoteUser::create([
            'user_name' => $userName,
            'password' => Hash::make($validated['password']),
            'secret_question' => $validated['secret_question'],
            'secret_answer' => Hash::make(strtolower(trim($validated['secret_answer']))),
            'role' => 'user',
            'active' => true,
        ]);

        RateLimiter::hit($registrationKey, 3600);
        $request->session()->regenerate();
        $request->session()->forget('pending_user');
        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $user->user_name,
            'user_role' => $user->role,
        ]);

        $this->activityLogger->record('account_created', 'Criou a própria conta no sistema.', $user);

        return redirect()->route('notes.index')->with('success', 'Conta criada com sucesso.');
    }

    public function showRecover(): View
    {
        return view('recover');
    }

    /** Displays the recovery question only for an active registered account. */
    public function recoverQuestion(Request $request): View|RedirectResponse
    {
        $request->validate(['user_name' => ['required', 'string', 'max:30']]);

        $userName = trim($request->string('user_name')->value());
        $user = NoteUser::where('user_name', $userName)->where('active', true)->first();

        if (! $user) {
            return back()->withErrors(['user_name' => 'Usuário não encontrado ou conta inativa.']);
        }

        $request->session()->put('recover_user_id', $user->id);

        return view('recover_answer', ['question' => $user->secret_question]);
    }

    /** Resets a password after validating the protected recovery answer. */
    public function recoverReset(Request $request): RedirectResponse
    {
        $request->validate([
            'secret_answer' => ['required', 'string'],
            'password' => ['required', 'confirmed', StrongPassword::rule()],
        ]);

        $user = NoteUser::whereKey($request->session()->get('recover_user_id'))
            ->where('active', true)
            ->first();

        if (! $user) {
            return redirect()->route('login');
        }

        $answer = strtolower(trim($request->string('secret_answer')->value()));
        $recoveryKey = 'recovery|'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($recoveryKey, self::MAX_LOGIN_ATTEMPTS)) {
            return back()->withErrors(['secret_answer' => 'Muitas tentativas de recuperação. Aguarde um minuto.']);
        }

        if (! Hash::check($answer, $user->secret_answer)) {
            RateLimiter::hit($recoveryKey, self::LOGIN_DECAY_SECONDS);

            return back()->withErrors(['secret_answer' => 'Resposta incorreta.']);
        }

        RateLimiter::clear($recoveryKey);
        $user->update(['password' => Hash::make($request->string('password')->value())]);
        $request->session()->forget('recover_user_id');

        return redirect()->route('login')->with('success', 'Senha redefinida com sucesso!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function loginThrottleKey(Request $request, string $userName): string
    {
        return Str::transliterate(Str::lower($userName)).'|'.$request->ip();
    }
}
