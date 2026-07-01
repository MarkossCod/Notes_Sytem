<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM — Criar Senha</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
</head>
<body class="register-page">
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
    <div class="card-title">Criar Senha</div>
    <div class="card-sub">Primeiro acesso — configure sua senha</div>
    <div class="user-badge">👋 {{ session('pending_user') }}</div>

    @if($errors->any())
        <div class="error-msg">{{ $errors->first() }}</div>
    @endif

    <form action="{{ secure_url(route('register.store', [], false)) }}" method="POST" autocomplete="off">
        @csrf
        <div class="field">
            <label>Senha</label>
            <input type="password" name="password" placeholder="Mínimo 4 caracteres" required autocomplete="off"/>
        </div>
        <div class="field">
            <label>Confirmar Senha</label>
            <input type="password" name="password_confirmation" placeholder="Repita a senha" required autocomplete="off"/>
        </div>
        <div class="field">
            <label>Pergunta Secreta</label>
            <select name="secret_question" required>
                <option value="">Selecione uma pergunta...</option>
                <option value="Qual o nome do seu animal de estimação?">Qual o nome do seu animal de estimação?</option>
                <option value="Qual o nome da sua mãe?">Qual o nome da sua mãe?</option>
                <option value="Qual sua cidade natal?">Qual sua cidade natal?</option>
                <option value="Qual o nome do seu melhor amigo de infância?">Qual o nome do seu melhor amigo de infância?</option>
                <option value="Qual o modelo do seu primeiro carro?">Qual o modelo do seu primeiro carro?</option>
            </select>
        </div>
        <div class="field">
            <label>Resposta Secreta</label>
            <input type="text" name="secret_answer" placeholder="Sua resposta" required autocomplete="off"/>
        </div>
        <button type="submit" class="btn">Criar Conta →</button>
    </form>
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