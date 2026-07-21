{{--
    PARCIAL: navegação lateral das páginas internas
    FINALIDADE: exibir atalhos do sistema, identificar o usuário da sessão e oferecer logout.
    REGRAS: o link Usuários aparece apenas quando session('user_role') é "admin"; a classe active acompanha a rota atual.
    USO: incluído por layout/app.blade.php.
    AO ALTERAR: use nomes de rotas existentes e mantenha o logout como formulário POST com @csrf.
--}}
<aside class="sidebar" id="sidebar">
    {{-- Identidade visual fixa da navegacao principal. --}}
    <div class="sidebar-brand">
        <img src="{{ secure_asset('icons/notessytem-logo-192.png') }}?v=2"
             class="sidebar-brand-logo" alt="Logo do NotesSytem" width="34" height="34">
        <span>NOTESSYTEM</span>
    </div>

    {{-- Resume a conta e a funcao armazenadas na sessao atual. --}}
    <div class="sidebar-user">
        <div class="sidebar-avatar">{{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}</div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ session('user_name') }}</span>
            <span class="sidebar-user-tag">{{ session('user_role') === 'admin' ? 'Administrador' : 'Usuário' }}</span>
        </div>
    </div>

    {{-- Links privados; o item administrativo e renderizado somente para administradores. --}}
    <nav class="sidebar-nav">
        <a href="{{ secure_url(route('notes.index', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('notes.index') ? 'active' : '' }}">
            <span class="sidebar-icon">🏠</span> Início
            <span class="sidebar-arrow">›</span>
        </a>
        <a href="{{ secure_url(route('notes.create', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('notes.create') ? 'active' : '' }}">
            <span class="sidebar-icon">➕</span> Nova Nota
            <span class="sidebar-arrow">›</span>
        </a>
        <a href="{{ secure_url(route('notes.index', [], false)) }}#notesGrid" class="sidebar-link">
            <span class="sidebar-icon">📋</span> Todas as Notas
            <span class="sidebar-arrow">›</span>
        </a>

        <a href="{{ secure_url(route('categories.index', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('categories.index') ? 'active' : '' }}">
            <span class="sidebar-icon">📁</span> Categorias
            <span class="sidebar-arrow">›</span>
        </a>
        <a href="{{ secure_url(route('panel.index', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('panel.*') ? 'active' : '' }}">
            <span class="sidebar-icon">📊</span> Painel
            <span class="sidebar-arrow">›</span>
        </a>
        <a href="{{ secure_url(route('trash.index', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('trash.*') ? 'active' : '' }}">
            <span class="sidebar-icon">🗑️</span> Lixeira
            <span class="sidebar-arrow">›</span>
        </a>

        @if(session('user_role') === 'admin')
        <a href="{{ secure_url(route('admin.users.index', [], false)) }}"
           class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <span class="sidebar-icon">🛡️</span> Usuários
            <span class="sidebar-arrow">›</span>
        </a>
        @endif

        <div class="sidebar-separator"></div>

        <a href="#" onclick="openSobreModal(); return false;" class="sidebar-link">
            <span class="sidebar-icon">👤</span> Sobre
            <span class="sidebar-arrow">›</span>
        </a>
    </nav>

    {{-- O logout usa POST e protecao CSRF para encerrar a sessao. --}}
    <div class="sidebar-footer">
        <form action="{{ secure_url(route('logout', [], false)) }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout">Sair</button>
        </form>
    </div>
</aside>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>
