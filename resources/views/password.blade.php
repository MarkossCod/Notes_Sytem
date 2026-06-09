<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM — Senha</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #FF6D00; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        canvas#bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .floating { position: fixed; border-radius: 50%; background: rgba(255,255,255,0.08); animation: float 4s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }
        .card { background: white; border-radius: 24px; padding: 40px 36px; width: 100%; max-width: 380px; z-index: 2; position: relative; transition: opacity .4s, transform .4s; }
        .logo-wrap { width: 64px; height: 64px; background: #FF6D00; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .card-title { text-align: center; font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .card-sub { text-align: center; font-size: 13px; color: #888; margin-bottom: 24px; }
        .user-badge { text-align: center; background: #FFF3E0; color: #FF6D00; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: inline-block; width: 100%; }
        .field { margin-bottom: 16px; }
        .field label { font-size: 12px; font-weight: 600; color: #FF6D00; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; display: block; }
        .field input { width: 100%; padding: 12px 14px; border: 2px solid #FFE0B2; border-radius: 10px; font-size: 14px; color: #1a1a1a; outline: none; background: #FFF8F0; transition: border .2s; }
        .field input:focus { border-color: #FF6D00; background: white; }
        .btn { width: 100%; padding: 14px; background: #FF6D00; color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 8px; box-shadow: 0 4px 16px rgba(255,109,0,0.3); transition: background .2s, transform .15s; }
        .btn:hover { background: #e06300; transform: translateY(-2px); }
        .btn-link { display: block; text-align: center; margin-top: 16px; color: #FF6D00; font-size: 13px; font-weight: 600; text-decoration: none; }
        .btn-link:hover { text-decoration: underline; }
        .btn-back { display: block; text-align: center; margin-top: 10px; color: #aaa; font-size: 13px; text-decoration: none; }
        .btn-back:hover { color: #555; }
        .error-msg { background: #fdecea; color: #c62828; border-left: 4px solid #e53935; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .overlay { position: fixed; inset: 0; background: #FF6D00; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 100; opacity: 0; pointer-events: none; transition: opacity .5s; }
        .overlay.show { opacity: 1; pointer-events: all; }
        .check-circle { width: 72px; height: 72px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; transform: scale(0); transition: transform .4s .2s cubic-bezier(.34,1.56,.64,1); }
        .check-circle.show { transform: scale(1); }
        .overlay-text { color: white; font-size: 20px; font-weight: 700; opacity: 0; transform: translateY(10px); transition: opacity .4s .4s, transform .4s .4s; }
        .overlay-text.show { opacity: 1; transform: translateY(0); }
        .overlay-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin-top: 8px; opacity: 0; transition: opacity .4s .5s; }
        .overlay-sub.show { opacity: 1; }
        @media (max-width: 480px) { .card { margin: 20px; padding: 28px 20px; } }
    </style>
</head>
<body>
<canvas id="bg"></canvas>
<div class="floating" style="width:120px;height:120px;top:-30px;left:-20px;animation-delay:0s"></div>
<div class="floating" style="width:80px;height:80px;bottom:20px;right:30px;animation-delay:1.5s"></div>
<div class="floating" style="width:50px;height:50px;top:40%;left:10px;animation-delay:.8s"></div>

<div class="card" id="loginCard">
    <div class="logo-wrap">
        <svg viewBox="0 0 64 64" width="64" height="64">
            <rect x="10" y="18" width="44" height="38" rx="5" fill="white"/>
            <rect x="8" y="12" width="48" height="12" rx="4" fill="#FF8F00"/>
            <circle cx="20" cy="18" r="3.5" fill="#FF6D00"/>
            <circle cx="32" cy="18" r="3.5" fill="#FF6D00"/>
            <circle cx="44" cy="18" r="3.5" fill="#FF6D00"/>
            <rect x="18" y="32" width="28" height="3" rx="1.5" fill="#FFE0B2"/>
            <rect x="18" y="40" width="20" height="3" rx="1.5" fill="#FFE0B2"/>
            <text x="32" y="50" text-anchor="middle" font-family="Arial" font-weight="900" font-size="13" fill="#FF6D00">NS</text>
        </svg>
    </div>
    <div class="card-title">Bem-vindo de volta!</div>
    <div class="card-sub">Digite sua senha para continuar</div>
    <div class="user-badge">👋 {{ session('pending_user') }}</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form id="passwordForm" action="{{ secure_url(route('password.check', [], false)) }}" method="POST" autocomplete="off">
        @csrf
        <div class="field">
            <label>Senha</label>
            <input type="password" name="password" id="passwordInput" placeholder="Digite sua senha" required autocomplete="off"/>
        </div>
        <button type="button" class="btn" id="enterBtn" onclick="handleEnter()">Entrar →</button>
    </form>

    <a href="{{ secure_url(route('recover', [], false)) }}" class="btn-link">🔑 Esqueci minha senha</a>
    <a href="{{ secure_url(route('login', [], false)) }}" class="btn-back">← Voltar</a>
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
function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
resizeCanvas();
window.addEventListener('resize', resizeCanvas);
let pts = Array.from({length: 25}, () => ({
    x: Math.random() * canvas.width, y: Math.random() * canvas.height,
    vx: (Math.random() - .5) * .4, vy: (Math.random() - .5) * .4, r: Math.random() * 3 + 1
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

function handleEnter() {
    const password = document.getElementById('passwordInput').value.trim();
    const btn = document.getElementById('enterBtn');
    const card = document.getElementById('loginCard');
    const overlay = document.getElementById('overlay');
    const check = document.getElementById('checkCircle');
    const text = document.getElementById('overlayText');
    const sub = document.getElementById('overlaySub');

    if (!password) {
        document.getElementById('passwordInput').style.borderColor = '#e53935';
        setTimeout(() => { document.getElementById('passwordInput').style.borderColor = '#FFE0B2'; }, 1500);
        return;
    }

    btn.textContent = 'Entrando...';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    text.textContent = `Bem-vindo, {{ session('pending_user') }}!`;

    setTimeout(() => {
        overlay.classList.add('show');
        setTimeout(() => { check.classList.add('show'); }, 100);
        setTimeout(() => { text.classList.add('show'); }, 200);
        setTimeout(() => { sub.classList.add('show'); }, 300);
    }, 400);

    setTimeout(() => {
        sub.textContent = 'Redirecionando...';
        document.getElementById('passwordForm').submit();
    }, 1500);
}

document.getElementById('passwordInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); handleEnter(); }
});
</script>
</body>
</html>