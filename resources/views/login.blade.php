{{--
    VIEW PÚBLICA: login
    FINALIDADE: receber nome e senha, exibir erros e encaminhar o usuário para cadastro ou recuperação.
    ENVIO: o formulário POST chama LoginController@store pela rota login.store.
    DADOS DE RETORNO: $errors mostra falhas de validação e session('success') confirma ações concluídas.
    AO ALTERAR: não remova @csrf, os names user_name/password nem autocomplete; o controlador depende desses campos.
--}}
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM — Entrar</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF6D00">
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
</head>
<body class="login-page">
<!-- Fundo animado decorativo; o formulario continua utilizavel sem ele. -->
<canvas id="bg"></canvas>
<div class="floating" style="width:120px;height:120px;top:-30px;left:-20px;animation-delay:0s"></div>
<div class="floating" style="width:80px;height:80px;bottom:20px;right:30px;animation-delay:1.5s"></div>
<div class="floating" style="width:50px;height:50px;top:40%;left:10px;animation-delay:.8s"></div>
<div class="floating" style="width:60px;height:60px;top:20px;right:60px;animation-delay:2s"></div>

<!-- Cartao principal com mensagens, credenciais e acesso ao cadastro ou recuperacao. -->
<div class="card" id="loginCard">
    <div class="logo-wrap">
        <svg viewBox="0 0 80 80" width="80" height="80">
            <rect x="14" y="22" width="52" height="46" rx="6" fill="white"/>
            <rect x="12" y="15" width="56" height="14" rx="5" fill="#FF8F00"/>
            <circle cx="26" cy="22" r="4" fill="#FF6D00"/>
            <circle cx="40" cy="22" r="4" fill="#FF6D00"/>
            <circle cx="54" cy="22" r="4" fill="#FF6D00"/>
            <rect x="22" y="38" width="36" height="4" rx="2" fill="#FFE0B2"/>
            <rect x="22" y="48" width="28" height="4" rx="2" fill="#FFE0B2"/>
            <text x="40" y="62" text-anchor="middle" font-family="Arial" font-weight="900" font-size="18" fill="#FF6D00">NS</text>
        </svg>
    </div>
    <div class="login-title">NOTESSYTEM</div>
    <div class="login-sub">Entre com seu nome e senha para continuar</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="success-msg">{{ session('success') }}</div>
    @endif

    <!-- Envia as credenciais ao fluxo autenticado e protegido por CSRF. -->
    <form id="loginForm" action="{{ secure_url(route('login.store', [], false)) }}" method="POST" autocomplete="off">
        @csrf
        <div class="field">
            <label>Seu nome</label>
            <input type="text" name="user_name" id="nameInput" placeholder="Ex: José Silva" value="{{ old('user_name') }}" required autocomplete="off"/>
        </div>
        <div class="field">
            <label>Senha</label>
            <div class="password-wrap">
                <input type="password" name="password" id="passwordInput" placeholder="Digite sua senha" autocomplete="current-password"/>
                <button type="button" class="toggle-pass" onclick="toggleSenha()" aria-label="Mostrar/ocultar senha">
                    <svg id="iconOlho" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>
        <button type="button" class="login-btn" id="enterBtn" onclick="handleEnter()">Entrar</button>
    </form>

    <a href="{{ secure_url(route('recover', [], false)) }}" class="btn-link">🔑 Esqueci minha senha</a>

    <div class="auth-divider"><span>ou</span></div>

    <a href="{{ secure_url(route('register', [], false)) }}" class="create-account-link">✨ Criar uma conta nova</a>
</div>

<div class="overlay" id="overlay">
    <div class="check-circle" id="checkCircle">
        <svg width="36" height="36" viewBox="0 0 36 36">
            <path d="M8 18l7 7 13-13" fill="none" stroke="#FF6D00" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    <div class="overlay-text" id="overlayText">Bem-vindo!</div>
    <div class="overlay-sub" id="overlaySub">Entrando no sistema...</div>
</div>

<script>
const canvas = document.getElementById('bg');
const ctx = canvas.getContext('2d');
// Mantem o fundo animado ajustado ao tamanho da janela.
function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

let pts = Array.from({length: 30}, () => ({
    x: Math.random() * canvas.width, y: Math.random() * canvas.height,
    vx: (Math.random() - .5) * .4, vy: (Math.random() - .5) * .4,
    r: Math.random() * 3 + 1
}));

function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    pts.forEach(p => {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(255,255,255,0.2)'; ctx.fill();
    });
    pts.forEach((a, i) => pts.slice(i + 1).forEach(b => {
        const d = Math.hypot(a.x - b.x, a.y - b.y);
        if (d < 120) {
            ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y);
            ctx.strokeStyle = `rgba(255,255,255,${.12 * (1 - d / 120)})`; ctx.lineWidth = .5; ctx.stroke();
        }
    }));
    requestAnimationFrame(draw);
}
draw();

// Alterna apenas a visibilidade local da senha digitada.
function toggleSenha() {
    const input = document.getElementById('passwordInput');
    input.type = input.type === 'password' ? 'text' : 'password';
}

function handleEnter() {
    const name = document.getElementById('nameInput').value.trim();
    const btn = document.getElementById('enterBtn');
    const card = document.getElementById('loginCard');
    const overlay = document.getElementById('overlay');
    const check = document.getElementById('checkCircle');
    const text = document.getElementById('overlayText');
    const sub = document.getElementById('overlaySub');

    if (!name) {
        document.getElementById('nameInput').style.borderColor = '#e53935';
        document.getElementById('nameInput').placeholder = 'Digite seu nome!';
        setTimeout(() => { document.getElementById('nameInput').style.borderColor = '#FFE0B2'; }, 1500);
        return;
    }

    btn.textContent = 'Entrando...';
    btn.classList.add('loading');
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    text.textContent = `Bem-vindo, ${name}!`;

    setTimeout(() => {
        overlay.classList.add('show');
        setTimeout(() => { check.classList.add('show'); }, 100);
        setTimeout(() => { text.classList.add('show'); }, 200);
        setTimeout(() => { sub.classList.add('show'); }, 300);
    }, 400);

    setTimeout(() => {
        sub.textContent = 'Redirecionando...';
        document.getElementById('loginForm').submit();
    }, 1500);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); handleEnter(); }
});
</script>
</body>
</html>
