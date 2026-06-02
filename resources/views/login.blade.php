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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #FF6D00; min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        canvas#bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .floating { position: fixed; border-radius: 50%; background: rgba(255,255,255,0.08); animation: float 4s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }
        .card { background: white; border-radius: 24px; padding: 40px 36px; width: 100%; max-width: 380px; z-index: 2; position: relative; transition: opacity .4s, transform .4s; }
        .logo-wrap { width: 80px; height: 80px; background: #FF6D00; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .login-title { text-align: center; font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
        .login-sub { text-align: center; font-size: 13px; color: #888; margin-bottom: 28px; }
        .field label { font-size: 12px; font-weight: 600; color: #FF6D00; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; display: block; }
        .field input { width: 100%; padding: 13px 16px; border: 2px solid #FFE0B2; border-radius: 12px; font-size: 15px; color: #1a1a1a; outline: none; transition: border .2s, background .2s; background: #FFF8F0; }
        .field input:focus { border-color: #FF6D00; background: white; }
        .login-btn { width: 100% !important; padding: 14px !important; background: #FF6D00 !important; color: white !important; border: none !important; border-radius: 12px !important; font-size: 15px !important; font-weight: 700 !important; cursor: pointer !important; margin-top: 20px !important; letter-spacing: .5px; transition: background .2s, transform .15s, box-shadow .2s; box-shadow: 0 4px 16px rgba(255,109,0,0.3); }
        .login-btn:hover { background: #e06300 !important; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255,109,0,0.4); }
        .login-btn:active { transform: scale(0.98) !important; }
        .login-btn.loading { background: #FF8F00 !important; pointer-events: none; }
        .divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .divider span { font-size: 12px; color: #ccc; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #FFE0B2; }
        .error-msg { background: #fdecea; color: #c62828; border-left: 4px solid #e53935; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .overlay { position: fixed; inset: 0; background: #FF6D00; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 100; opacity: 0; pointer-events: none; transition: opacity .5s; }
        .overlay.show { opacity: 1; pointer-events: all; }
        .check-circle { width: 72px; height: 72px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; transform: scale(0); transition: transform .4s .2s cubic-bezier(.34,1.56,.64,1); }
        .check-circle.show { transform: scale(1); }
        .overlay-text { color: white; font-size: 20px; font-weight: 700; opacity: 0; transform: translateY(10px); transition: opacity .4s .4s, transform .4s .4s; }
        .overlay-text.show { opacity: 1; transform: translateY(0); }
        .overlay-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin-top: 8px; opacity: 0; transition: opacity .4s .5s; }
        .overlay-sub.show { opacity: 1; }
        @media (max-width: 480px) {
            .card { margin: 20px; padding: 28px 20px; }
        }
    </style>
</head>
<body>
<canvas id="bg"></canvas>
<div class="floating" style="width:120px;height:120px;top:-30px;left:-20px;animation-delay:0s"></div>
<div class="floating" style="width:80px;height:80px;bottom:20px;right:30px;animation-delay:1.5s"></div>
<div class="floating" style="width:50px;height:50px;top:40%;left:10px;animation-delay:.8s"></div>
<div class="floating" style="width:60px;height:60px;top:20px;right:60px;animation-delay:2s"></div>

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
    <div class="login-sub">Digite seu nome para continuar</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form id="loginForm" action="{{ secure_url(route('login.store', [], false)) }}" method="POST" autocomplete="off">
        @csrf
        <div class="field">
            <label>Seu nome</label>
            <input type="text" name="user_name" id="nameInput" placeholder="Ex: José Silva" value="{{ old('user_name') }}" required autocomplete="off"/>
        </div>
        <button type="button" class="login-btn" id="enterBtn" onclick="handleEnter()">Entrar →</button>
    </form>
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

document.getElementById('nameInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleEnter();
    }
});

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
</script>
</body>
</html>