<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #FF6D00;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }
        #splash-canvas { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .splash-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            z-index: 2;
        }
        .logo-wrap { position: relative; width: 120px; height: 120px; }
        .logo-bg {
            width: 120px; height: 120px;
            background: rgba(255,255,255,0.15);
            border-radius: 28px;
            position: absolute;
            transform: scale(0);
            opacity: 0;
        }
        .logo-svg { position: absolute; inset: 0; opacity: 0; }
        .brand-text {
            font-family: Arial, sans-serif;
            font-size: 32px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 6px;
            opacity: 0;
            transform: translateY(20px);
        }
        .brand-sub {
            font-family: Arial, sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: rgba(255,255,255,0.75);
            letter-spacing: 3px;
            opacity: 0;
            transform: translateY(10px);
        }
        .progress-bar {
            width: 160px; height: 3px;
            background: rgba(255,255,255,0.2);
            border-radius: 2px;
            overflow: hidden;
            opacity: 0;
        }
        .progress-fill {
            height: 100%; width: 0%;
            background: #fff;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<canvas id="splash-canvas"></canvas>
<div class="splash-inner">
    <div class="logo-wrap">
        <div class="logo-bg" id="logoBg"></div>
        <svg class="logo-svg" id="logoSvg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
            <rect id="body" x="20" y="28" width="80" height="74" rx="8" fill="white" opacity="0"/>
            <rect id="spiral" x="16" y="22" width="88" height="16" rx="6" fill="#FF8F00" opacity="0"/>
            <circle id="h1" cx="36" cy="30" r="5" fill="#FF6D00" opacity="0"/>
            <circle id="h2" cx="56" cy="30" r="5" fill="#FF6D00" opacity="0"/>
            <circle id="h3" cx="76" cy="30" r="5" fill="#FF6D00" opacity="0"/>
            <circle id="h4" cx="96" cy="30" r="5" fill="#FF6D00" opacity="0"/>
            <rect id="l1" x="32" y="52" width="56" height="5" rx="2.5" fill="#FFE0B2" opacity="0"/>
            <rect id="l2" x="32" y="65" width="44" height="5" rx="2.5" fill="#FFE0B2" opacity="0"/>
            <rect id="l3" x="32" y="78" width="50" height="5" rx="2.5" fill="#FFE0B2" opacity="0"/>
            <rect id="l4" x="32" y="91" width="36" height="5" rx="2.5" fill="#FFE0B2" opacity="0"/>
            <text id="ns" x="60" y="104" text-anchor="middle" font-family="Arial" font-weight="900" font-size="22" fill="#FF6D00" opacity="0">NS</text>
        </svg>
    </div>
    <div class="brand-text" id="brandText">NOTESSYTEM</div>
    <div class="brand-sub" id="brandSub">GERENCIADOR DE NOTAS</div>
    <div class="progress-bar" id="progressBar"><div class="progress-fill" id="progressFill"></div></div>
</div>

<script>
const canvas = document.getElementById('splash-canvas');
const ctx = canvas.getContext('2d');

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
resizeCanvas();
window.addEventListener('resize', resizeCanvas);

function drawParticles(progress) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const count = 18;
    for (let i = 0; i < count; i++) {
        const angle = (i / count) * Math.PI * 2;
        const radius = 80 + Math.sin(progress * Math.PI * 2 + i) * 20;
        const x = canvas.width / 2 + Math.cos(angle + progress * 0.5) * radius * (1 + progress);
        const y = canvas.height / 2 + Math.sin(angle + progress * 0.5) * radius * (0.6 + progress * 0.4);
        const size = 3 + Math.sin(progress * Math.PI + i * 0.7) * 2;
        const alpha = 0.08 + Math.sin(progress * Math.PI + i) * 0.04;
        ctx.beginPath();
        ctx.arc(x, y, size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255,255,255,${alpha})`;
        ctx.fill();
    }
}

function ease(t) { return t < 0.5 ? 2*t*t : -1+(4-2*t)*t; }
function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

function animate(el, props, duration, delay, easeF) {
    return new Promise(resolve => {
        setTimeout(() => {
            const start = performance.now();
            const from = {};
            for (const k in props) {
                if (k === 'opacity') from[k] = parseFloat(el.style.opacity || 0);
                else if (k === 'translateY') from[k] = parseFloat((el.style.transform||'').replace(/translateY\(([^)]+)\)/,'$1')||0);
                else if (k === 'scale') from[k] = parseFloat((el.style.transform||'').replace(/scale\(([^)]+)\)/,'$1')||0);
            }
            function step(now) {
                const t = Math.min((now - start) / duration, 1);
                const e = easeF ? easeF(t) : ease(t);
                for (const k in props) {
                    const v = from[k] + (props[k] - from[k]) * e;
                    if (k === 'opacity') el.style.opacity = v;
                    else if (k === 'translateY') el.style.transform = `translateY(${v}px)`;
                    else if (k === 'scale') el.style.transform = `scale(${v})`;
                }
                if (t < 1) requestAnimationFrame(step); else resolve();
            }
            requestAnimationFrame(step);
        }, delay);
    });
}

function svgAttr(el, props, duration, delay) {
    return new Promise(resolve => {
        setTimeout(() => {
            const start = performance.now();
            const from = {};
            for (const k in props) from[k] = parseFloat(el.getAttribute(k) || 0);
            function step(now) {
                const t = Math.min((now - start) / duration, 1);
                const e = ease(t);
                for (const k in props) el.setAttribute(k, from[k] + (props[k] - from[k]) * e);
                if (t < 1) requestAnimationFrame(step); else resolve();
            }
            requestAnimationFrame(step);
        }, delay);
    });
}

let particleFrame;
function startParticles() {
    let p = 0;
    function loop() {
        p += 0.005;
        drawParticles(p % 1);
        particleFrame = requestAnimationFrame(loop);
    }
    loop();
}

async function runAnim() {
    startParticles();
    animate(document.getElementById('logoBg'), {scale: 1, opacity: 1}, 500, 200, easeOut);
    await new Promise(r => setTimeout(r, 300));
    animate(document.getElementById('logoSvg'), {opacity: 1}, 200, 0);
    svgAttr(document.getElementById('spiral'), {opacity: 1}, 300, 100);
    svgAttr(document.getElementById('body'), {opacity: 1}, 300, 250);
    ['h1','h2','h3','h4'].forEach((id, i) => svgAttr(document.getElementById(id), {opacity: 1}, 200, 400 + i * 80));
    ['l1','l2','l3','l4'].forEach((id, i) => svgAttr(document.getElementById(id), {opacity: 1}, 250, 750 + i * 100));
    svgAttr(document.getElementById('ns'), {opacity: 1}, 300, 1200);
    await new Promise(r => setTimeout(r, 1400));
    animate(document.getElementById('brandText'), {opacity: 1, translateY: 0}, 500, 0, easeOut);
    await new Promise(r => setTimeout(r, 200));
    animate(document.getElementById('brandSub'), {opacity: 1, translateY: 0}, 400, 0, easeOut);
    await new Promise(r => setTimeout(r, 300));
    animate(document.getElementById('progressBar'), {opacity: 1}, 300, 0);

    const fill = document.getElementById('progressFill');
    let w = 0;
    const prog = setInterval(() => {
        w += 2;
        fill.style.width = w + '%';
        if (w >= 100) {
            clearInterval(prog);
            cancelAnimationFrame(particleFrame);
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s';
                document.body.style.opacity = '0';
                setTimeout(() => window.location.href = '/notes', 500);
            }, 300);
        }
    }, 30);
}

runAnim();
</script>
</body>
</html>