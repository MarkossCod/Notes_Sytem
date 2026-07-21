{{--
    VIEW: Lixeira de notas
    FINALIDADE: pesquisar notas excluídas, restaurá-las ou removê-las definitivamente de forma individual ou em lote.
    DADOS RECEBIDOS: $notes é paginado; $stats alimenta os indicadores; $loadError informa falhas de carregamento.
    ORIGEM DOS DADOS: TrashController@index. Restore usa PATCH; exclusão individual e esvaziamento usam DELETE.
    AO ALTERAR: ações permanentes devem continuar usando o modal de confirmação e formulários protegidos por @csrf.
--}}
@extends('layout.app')

@section('content')
<div class="trash-page" data-trash-page>
    {{-- Cabecalho da pagina e acao em lote para esvaziar a Lixeira. --}}
    <header class="trash-header">
        <div class="trash-heading">
            <span class="trash-heading-icon" aria-hidden="true">🗑️</span>
            <div>
                <h1>Lixeira</h1>
                <p>Notas excluídas que podem ser restauradas ou removidas permanentemente.</p>
            </div>
        </div>

        <form action="{{ secure_url(route('trash.empty', [], false)) }}" method="POST" class="js-confirm-delete"
              data-confirm-title="Esvaziar lixeira?"
              data-confirm-message="Todas as notas serão excluídas permanentemente. Esta ação não poderá ser desfeita.">
            @csrf
            @method('DELETE')
            <button type="submit" class="trash-empty-btn" @disabled($stats['total'] === 0)>🗑️ Esvaziar lixeira</button>
        </form>
    </header>

    @if(session('success'))
        <div class="trash-alert trash-alert--success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error') || $loadError)
        <div class="trash-alert trash-alert--error">⚠ {{ session('error') ?: $loadError }}</div>
    @endif

    {{-- Indicadores resumem volume, retencao, espaco estimado e protecao dos dados. --}}
    <section class="trash-stats" aria-label="Resumo da lixeira">
        <x-trash.stat-card icon="🗑" :value="$stats['total']" label="Notas na lixeira" footer="Arquivos excluídos" tone="red" />
        <x-trash.stat-card icon="◷" :value="$stats['retention'] . ' dias'" label="Retenção automática" footer="Tempo para exclusão definitiva" tone="green" />
        <x-trash.stat-card icon="↶" :value="$stats['size']" label="Espaço ocupado" footer="Dados na lixeira" tone="orange" />
        <x-trash.stat-card icon="♢" value="Seguro" label="Seus dados estão protegidos" footer="Privacidade garantida" tone="purple" />
    </section>

    <section class="trash-list-card">
        {{-- Busca, ordenacao e paginacao sao processadas no servidor e preservadas na URL. --}}
        <form method="GET" action="{{ secure_url(route('trash.index', [], false)) }}" class="trash-toolbar">
            <label class="trash-search">
                <span aria-hidden="true">⌕</span>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar na lixeira..." aria-label="Buscar na lixeira">
            </label>
            <div class="trash-toolbar-actions">
                <label class="trash-sort">
                    <span aria-hidden="true">⇅</span>
                    <select name="order" onchange="this.form.submit()" aria-label="Ordenar notas">
                        <option value="recent" @selected(request('order') !== 'oldest')>Mais recentes</option>
                        <option value="oldest" @selected(request('order') === 'oldest')>Mais antigas</option>
                    </select>
                </label>
                <button type="button" class="trash-view-toggle" id="trashViewToggle" aria-label="Alternar visualização" title="Alternar visualização">▦</button>
            </div>
        </form>

        <div class="trash-loading" id="trashLoading" role="status" aria-live="polite">
            <span class="trash-spinner"></span> Carregando notas...
        </div>

        @if(!$loadError && $notes->isEmpty())
            <div class="trash-empty-state">
                <span aria-hidden="true">🗑️</span>
                <h2>A lixeira está vazia</h2>
                <p>As notas excluídas aparecerão aqui durante {{ $stats['retention'] }} dias.</p>
                <a href="{{ secure_url(route('notes.index', [], false)) }}">Voltar para as notas</a>
            </div>
        @elseif(!$loadError)
            <div class="trash-table-wrap" id="trashResults">
                <table class="trash-table">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Categoria</th>
                            <th>Excluída em</th>
                            <th>Tamanho</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notes as $note)
                            <tr>
                                <td data-label="Nota">
                                    <div class="trash-note-cell">
                                        <span class="trash-note-icon" aria-hidden="true">▤</span>
                                        <div>
                                            <strong>{{ $note->title }}</strong>
                                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($note->content ?: 'Sem conteúdo'), 78) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Categoria"><span class="trash-category">{{ optional($note->category)->name ?: 'Sem categoria' }}</span></td>
                                <td data-label="Excluída em">{{ $note->deleted_at->format('d/m/Y') }} às {{ $note->deleted_at->format('H:i') }}</td>
                                <td data-label="Tamanho">{{ $note->estimatedSizeLabel() }}</td>
                                <td data-label="Ação">
                                    <div class="trash-row-actions">
                                        <form action="{{ secure_url(route('trash.restore', $note->id, false)) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="trash-action trash-action--restore" title="Restaurar nota"><span>↶</span>Restaurar</button>
                                        </form>
                                        <form action="{{ secure_url(route('trash.destroy', $note->id, false)) }}" method="POST" class="js-confirm-delete"
                                              data-confirm-title="Excluir permanentemente?"
                                              data-confirm-message="A nota “{{ $note->title }}” será removida e não poderá ser restaurada.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="trash-action trash-action--delete" title="Excluir permanentemente"><span>♲</span>Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <footer class="trash-pagination">
                <span>Mostrando {{ $notes->firstItem() }} a {{ $notes->lastItem() }} de {{ $notes->total() }} notas</span>
                <div class="trash-pages">
                    @if($notes->onFirstPage()) <span class="disabled">‹</span> @else <a href="{{ $notes->previousPageUrl() }}" aria-label="Página anterior">‹</a> @endif
                    <span class="current">{{ $notes->currentPage() }}</span>
                    @if($notes->hasMorePages()) <a href="{{ $notes->nextPageUrl() }}" aria-label="Próxima página">›</a> @else <span class="disabled">›</span> @endif
                </div>
                <form method="GET" action="{{ secure_url(route('trash.index', [], false)) }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="order" value="{{ request('order', 'recent') }}">
                    <select name="per_page" onchange="this.form.submit()" aria-label="Notas por página">
                        @foreach([10, 20, 50] as $amount)
                            <option value="{{ $amount }}" @selected((int) request('per_page', 10) === $amount)>{{ $amount }} por página</option>
                        @endforeach
                    </select>
                </form>
            </footer>
        @endif
    </section>
</div>

@endsection

@section('modals')
{{-- Um unico modal confirma as duas acoes irreversiveis da pagina. --}}
<div id="trashConfirmModal" class="modal-overlay" onclick="closeTrashConfirm()">
    <div class="trash-confirm-modal" onclick="event.stopPropagation()" role="dialog" aria-modal="true" aria-labelledby="trashConfirmTitle">
        <span class="trash-confirm-icon" aria-hidden="true">!</span>
        <h2 id="trashConfirmTitle">Excluir permanentemente?</h2>
        <p id="trashConfirmMessage"></p>
        <div class="trash-confirm-actions">
            <button type="button" onclick="closeTrashConfirm()">Cancelar</button>
            <button type="button" id="trashConfirmButton" class="danger">Excluir permanentemente</button>
        </div>
    </div>
</div>

<script>
(function () {
    // Inicializa as interações somente depois que o modal existe no documento.
    const modal = document.getElementById('trashConfirmModal');
    const confirmButton = document.getElementById('trashConfirmButton');
    let pendingDeleteForm = null;

    window.addEventListener('load', () => document.getElementById('trashLoading')?.classList.add('hidden'));

    document.getElementById('trashViewToggle')?.addEventListener('click', function () {
        document.getElementById('trashResults')?.classList.toggle('trash-table-wrap--grid');
        this.classList.toggle('active');
    });

    // Copia título e mensagem do formulário clicado para um único modal. Só envia o formulário depois da confirmação explícita.
    document.querySelectorAll('.js-confirm-delete').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            pendingDeleteForm = form;
            document.getElementById('trashConfirmTitle').textContent = form.dataset.confirmTitle;
            document.getElementById('trashConfirmMessage').textContent = form.dataset.confirmMessage;
            modal.classList.add('modal-active');
        });
    });

    window.closeTrashConfirm = function () {
        pendingDeleteForm = null;
        modal.classList.remove('modal-active');
    };

    confirmButton.addEventListener('click', () => {
        if (!pendingDeleteForm) return;

        confirmButton.disabled = true;
        confirmButton.textContent = 'Excluindo...';
        pendingDeleteForm.submit();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') window.closeTrashConfirm();
    });
})();
</script>
@endsection
