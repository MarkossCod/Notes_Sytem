<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTESSYTEM</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#FF6D00">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
    <style>
        .navbar { animation: slideDown .4s cubic-bezier(.34,1.56,.64,1) both; }
        .page-transition { animation: pageEnter .5s cubic-bezier(.34,1.56,.64,1) both; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pageEnter {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">NOTESSYTEM</div>
    <ul>
        <li>
            <a href="{{ secure_url(route('notes.index', [], false)) }}">
                <span>📋</span> Gerenciar Notas
            </a>
        </li>
        <li>
            <a href="{{ secure_url(route('notes.create', [], false)) }}">
                <span>➕</span> Nova Nota
            </a>
        </li>
        <li>
            <a href="#" onclick="openSobreModal(); return false;">
                <span>👤</span> Sobre
            </a>
        </li>
        <li>
            <span style="color:rgba(255,255,255,0.8); font-size:13px; padding:8px 10px; display:flex; align-items:center; gap:6px;">
                👋 {{ session('user_name') }}
            </span>
        </li>
        <li>
            <form action="{{ secure_url(route('logout', [], false)) }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background:rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); color:white; padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; margin-top:0; width:auto;">
                    Sair
                </button>
            </form>
        </li>
    </ul>
</nav>

<div class="container page-transition">
    @yield('content')
</div>

{{-- MODAL SOBRE --}}
<div id="sobreModal" class="modal-overlay" onclick="closeSobreModal()">
    <div class="modal-box sobre-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>👤 Sobre o Sistema</h2>
            <button class="modal-close" onclick="closeSobreModal()">✕</button>
        </div>
        <div class="sobre-content">
            <div class="sobre-item">
                <span class="sobre-icon">🧑‍💻</span>
                <div>
                    <p class="sobre-label">Desenvolvedor</p>
                    <p class="sobre-value">Markos Samuell</p>
                </div>
            </div>
            <div class="sobre-item">
                <span class="sobre-icon">🏫</span>
                <div>
                    <p class="sobre-label">Instituição de Ensino</p>
                    <p class="sobre-value">SENAI-CTTI-MG</p>
                </div>
            </div>
            <div class="sobre-item">
                <span class="sobre-icon">💡</span>
                <div>
                    <p class="sobre-label">Motivo da Criação</p>
                    <p class="sobre-value">Sistema desenvolvido para organizar e gerenciar chamados de forma prática, permitindo criar notas e dividi-las em seções para melhor controle das atividades.</p>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        function openSobreModal() {
            document.getElementById('sobreModal').classList.add('modal-active');
        }

        function closeSobreModal() {
            document.getElementById('sobreModal').classList.remove('modal-active');
        }

        function openInfoModal() {
            document.getElementById('infoModal').classList.add('modal-active');
        }

        function openViewModal(title, content) {
            document.getElementById('viewModalTitle').innerText = title || '';
            document.getElementById('viewModalContent').innerText = content || '';
            document.getElementById('viewModal').classList.add('modal-active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('modal-active');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSobreModal();
                if(document.getElementById('infoModal')) closeModal('infoModal');
                if(document.getElementById('viewModal')) closeModal('viewModal');
            }
        });
    </script>
    @yield('modals')
</body>
</html>