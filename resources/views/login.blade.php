<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM — Entrar</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF6D00">
</head>
<body>
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#fdf3eb;">
    <div class="form-container" style="max-width:400px; width:100%;">

        <div style="text-align:center; margin-bottom:24px;">
            <div style="width:72px; height:72px; background:#FF6D00; border-radius:18px; display:flex; align-items:center; justify-content:center; margin:0 auto 16px;">
                <svg viewBox="0 0 72 72" width="72" height="72">
                    <rect x="12" y="18" width="48" height="44" rx="6" fill="white"/>
                    <rect x="10" y="13" width="52" height="12" rx="5" fill="#FF8F00"/>
                    <circle cx="22" cy="19" r="4" fill="#FF6D00"/>
                    <circle cx="36" cy="19" r="4" fill="#FF6D00"/>
                    <circle cx="50" cy="19" r="4" fill="#FF6D00"/>
                    <rect x="20" y="34" width="32" height="4" rx="2" fill="#FFE0B2"/>
                    <rect x="20" y="44" width="24" height="4" rx="2" fill="#FFE0B2"/>
                    <rect x="20" y="54" width="28" height="4" rx="2" fill="#FFE0B2"/>
                    <text x="36" y="50" text-anchor="middle" font-family="Arial" font-weight="900" font-size="16" fill="#FF6D00">NS</text>
                </svg>
            </div>
            <h1 style="font-size:22px; font-weight:700; color:#1a1a1a; margin-bottom:6px;">NOTESSYTEM</h1>
            <p style="font-size:14px; color:#888;">Digite seu nome para entrar</p>
        </div>

        @if($errors->any())
            <div class="alert-success" style="background:#fdecea; color:#c62828; border-left-color:#e53935; margin-bottom:16px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ secure_url(route('login.store', [], false)) }}" method="POST" autocomplete="off">
            @csrf
            <div class="form-group">
                <label for="user_name">Seu Nome</label>
                <input type="text"
                       id="user_name"
                       name="user_name"
                       placeholder="Ex: João Silva"
                       value="{{ old('user_name') }}"
                       autocomplete="off"
                       required>
            </div>
            <button type="submit" style="margin-top:20px;">Entrar →</button>
        </form>

    </div>
</div>
</body>
</html>