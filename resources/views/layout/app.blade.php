<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>NOTESSYTEM</title>
    @vite(['resources/css/style.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar">
    <div class="logo">NOTESSYTEM</div>
    <ul>
        <li>
            <a href="{{ route('notes.index') }}">
                <span>📋</span> Gerenciar Notas
            </a>
        </li>
        <li>
            <a href="{{ route('notes.create') }}">
                <span>➕</span> Nova Nota
            </a>
        </li>
        <li>
            <a href="#" onclick="openSobreModal(); return false;">
                <span>👤</span> Sobre
            </a>
        </li>
    </ul>
</nav>

<div class="container">
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

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSobreModal();
    });
</script>

</body>
</html>