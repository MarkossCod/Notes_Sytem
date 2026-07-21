{{--
    VIEW: Administração de usuários
    FINALIDADE: mostrar indicadores das contas e permitir que um administrador cadastre ou altere usuários.
    DADOS RECEBIDOS: $metrics contém os totais; $users é a lista paginada; $errors e session('success') exibem retornos dos formulários.
    ORIGEM DOS DADOS: AdminUserController@index. As gravações usam store, update e resetPassword do mesmo controlador.
    AO ALTERAR: preserve @csrf, @method e os atributos name dos campos, pois eles correspondem à validação do controlador.
--}}
@extends('layout.app')

@section('content')
<div class="user-admin-page">
    {{-- Cabecalho e atalho para abrir o cadastro administrativo. --}}
    <header class="user-admin-header">
        <div>
            <span class="user-admin-kicker">🛡️ Administração</span>
            <h1>Gerenciar usuários</h1>
            <p>Cadastre contas, controle permissões e bloqueie acessos sem alterar os dados das notas.</p>
        </div>
        <button type="button" class="user-admin-primary" data-open-user-form>＋ Novo usuário</button>
    </header>

    @if(session('success'))
        <div class="user-admin-alert user-admin-alert--success">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="user-admin-alert user-admin-alert--error">⚠ {{ $errors->first() }}</div>
    @endif

    {{-- Indicadores consolidados das contas cadastradas. --}}
    <section class="user-performance-summary" aria-label="Resumo dos usuários">
        <article><span class="user-performance-icon">👥</span><div><strong>{{ $metrics['total'] }}</strong><small>Usuários cadastrados</small></div></article>
        <article><span class="user-performance-icon is-green">✓</span><div><strong>{{ $metrics['active'] }}</strong><small>Contas ativas</small></div></article>
        <article><span class="user-performance-icon is-red">⊘</span><div><strong>{{ $metrics['blocked'] }}</strong><small>Contas bloqueadas</small></div></article>
        <article><span class="user-performance-icon is-purple">🛡</span><div><strong>{{ $metrics['admins'] }}</strong><small>Administradores</small></div></article>
    </section>

    {{-- Formulario recolhivel para criacao segura de usuarios. --}}
    <section class="user-create-card" id="userCreateCard" @if(!$errors->any()) hidden @endif>
        <div class="user-create-heading">
            <div><h2>Criar conta</h2><p>A senha deve ter ao menos 8 caracteres, maiúscula, minúscula, número e símbolo.</p></div>
            <button type="button" class="user-create-close" data-close-user-form aria-label="Fechar">×</button>
        </div>
        <form action="{{ secure_url(route('admin.users.store', [], false)) }}" method="POST" class="user-create-grid">
            @csrf
            <label>Nome do usuário<input type="text" name="user_name" value="{{ old('user_name') }}" required maxlength="30"></label>
            <label>Função<select name="role" required><option value="user">Usuário</option><option value="admin" @selected(old('role') === 'admin')>Administrador</option></select></label>
            <label>Senha temporária<input type="password" name="password" required autocomplete="new-password"></label>
            <label>Confirmar senha<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
            <label>Pergunta de recuperação<select name="secret_question" required>
                <option value="">Selecione...</option>
                <option>Qual o nome do seu animal de estimação?</option>
                <option>Qual o nome da sua mãe?</option>
                <option>Qual sua cidade natal?</option>
                <option>Qual o nome do seu melhor amigo de infância?</option>
            </select></label>
            <label>Resposta de recuperação<input type="text" name="secret_answer" required autocomplete="off"></label>
            <button type="submit" class="user-admin-primary user-create-submit">Criar usuário</button>
        </form>
    </section>

    {{-- Busca, desempenho, permissao e redefinicao de senha por usuario. --}}
    <section class="user-list-card">
        <form method="GET" action="{{ secure_url(route('admin.users.index', [], false)) }}" class="user-admin-search">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar usuários..." aria-label="Buscar usuários">
        </form>

        <div class="user-admin-table-wrap">
            <table class="user-admin-table">
                <thead><tr><th>Usuário</th><th>Desempenho</th><th>Último acesso</th><th>Editar acesso</th><th>Segurança</th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td data-label="Usuário">
                            <div class="user-identity"><span>{{ strtoupper(substr($user->user_name, 0, 1)) }}</span><div><strong>{{ $user->user_name }}</strong>@if($user->id === session('user_id'))<small>Você</small>@endif</div></div>
                        </td>
                        <td data-label="Desempenho">
                            @php($completionRate = $user->notes_count > 0 ? round(($user->completed_notes_count / $user->notes_count) * 100) : 0)
                            <div class="user-performance-data">
                                <span><strong>{{ $user->notes_count }}</strong> notas</span>
                                <span><strong>{{ $user->completed_notes_count }}</strong> concluídas</span>
                                <span><strong>{{ $user->recent_notes_count }}</strong> novas em 30 dias</span>
                                <span><strong>{{ $user->trashed_notes_count }}</strong> na Lixeira</span>
                                <span><strong>{{ $user->categories_count }}</strong> categorias</span>
                                <div class="user-performance-rate"><i style="width: {{ $completionRate }}%"></i></div>
                                <small>{{ $completionRate }}% de conclusão</small>
                            </div>
                        </td>
                        <td data-label="Último acesso">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca acessou' }}</td>
                        <td data-label="Permissão e status">
                            <form action="{{ secure_url(route('admin.users.update', [$user->id], false)) }}" method="POST" class="user-access-form">
                                @csrf @method('PATCH')
                                <input type="text" name="user_name" value="{{ $user->user_name }}" required maxlength="30" aria-label="Nome de {{ $user->user_name }}">
                                <select name="role" aria-label="Função de {{ $user->user_name }}"><option value="user" @selected($user->role === 'user')>Usuário</option><option value="admin" @selected($user->role === 'admin')>Administrador</option></select>
                                <select name="active" class="{{ $user->active ? 'is-active' : 'is-inactive' }}" aria-label="Status de {{ $user->user_name }}"><option value="1" @selected($user->active)>Ativo</option><option value="0" @selected(!$user->active)>Bloqueado</option></select>
                                <button type="submit">Salvar</button>
                            </form>
                        </td>
                        <td data-label="Segurança">
                            <details class="user-password-reset">
                                <summary>Redefinir senha</summary>
                                <form action="{{ secure_url(route('admin.users.password', [$user->id], false)) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="password" name="password" placeholder="Nova senha forte" required autocomplete="new-password">
                                    <input type="password" name="password_confirmation" placeholder="Confirmar senha" required autocomplete="new-password">
                                    <button type="submit">Atualizar senha</button>
                                </form>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="user-admin-empty">Nenhum usuário encontrado.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="user-admin-pagination">{{ $users->links() }}</div>
        @endif
    </section>
</div>

<script>
// Controla somente a abertura e o foco do formulario de novo usuario.
(function () {
    const card = document.getElementById('userCreateCard');
    document.querySelector('[data-open-user-form]').addEventListener('click', () => {
        card.hidden = false;
        card.querySelector('input').focus();
    });
    document.querySelector('[data-close-user-form]').addEventListener('click', () => card.hidden = true);
})();
</script>
@endsection
