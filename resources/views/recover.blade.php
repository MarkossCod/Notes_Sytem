<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM — Recuperar Senha</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #FF6D00; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        canvas#bg { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .floating { position: fixed; border-radius: 50%; background: rgba(255,255,255,0.08); animation: float 4s ease-in-out infinite; }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-18px)} }
        .card { background: white; border-radius: 24px; padding: 40px 36px; width: 100%; max-width: 380px; z-index: 2; position: relative; }
        .logo-wrap { width: 64px; height: 64px; background: #FF6D00; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .card-title { text-align: center; font-size: 20px; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
        .card-sub { text-align: center; font-size: 13px; color: #888; margin-bottom: 24px; }
        .field { margin-bottom: 16px; }
        .field label { font-size: 12px; font-weight: 600; color: #FF6D00; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; display: block; }
        .field input { width: 100%; padding: 12px 14px; border: 2px solid #FFE0B2; border-radius: 10px; font-size: 14px; color: #1a1a1a; outline: none; background: #FFF8F0; transition: border .2s; }
        .field input:focus { border-color: #FF6D00; background: white; }
        .btn { width: 100%; padding: 14px; background: #FF6D00; color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 8px; box-shadow: 0 4px 16px rgba(255,109,0,0.3); transition: background .2s, transform .15s; }
        .btn:hover { background: #e06300; transform: translateY(-2px); }
        .btn-back { display: block; text-align: center; margin-top: 16px; color: #aaa; font-size: 13px; text-decoration: none; }
        .btn-back:hover { color: #555; }
        .error-msg { background: #fdecea; color: #c62828; border-left: 4px solid #e53935; padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        @media (max-width: 480px) { .card { margin: 20px; padding: 28px 20px; } }
    </style>
</head>
<body>
<canvas id="bg"></canvas>
<div class="floating" style="width:120px;height:120px;top:-30px;left:-20px;animation-delay:0s"></div>
<div class="floating" style="width:80px;height:80px;bottom:20px;right:30px;animation-delay:1.5s"></div>
<div class="floating" style="width:50px;height:50px;top:40%;left:10px;animation-delay:.8s"></div>

<div class="card">
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
    <div class="card-title">🔑 Recuperar Senha</div>
    <div class="card-sub">Digite seu nome de usuário para continuar</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form action="{{ secure_url(route('recover.question', [], false)) }}" method="POST" autocomplete="off">
        @csrf
        <div class="field">
            <label>Seu Nome</label>
            <input type="text" name="user_name" placeholder="Ex: João Silva" required autocomplete="off"/>
        </div>
        <button type="submit" class="btn">Continuar →</button>
    </form>

    <a href="{{ secure_url(route('login', [], false)) }}" class="btn-back">← Voltar ao login</a>
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
</script>
</body>
</html>