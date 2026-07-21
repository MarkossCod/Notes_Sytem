{{--
    VIEW: página inicial de notas
    FINALIDADE: apresentar totais, listar notas ativas e abrir uma visualização rápida sem editar o registro.
    DADOS RECEBIDOS: $notes e os totais são calculados por NoteController@index para o usuário autenticado.
    INTERAÇÕES: os atributos data-* de cada botão alimentam o modal; topSearchInput filtra os cards pelo título.
    AO ALTERAR: mantenha os data-* e seus IDs correspondentes no modal ou atualize também openNotePreview().
--}}
@extends('layout.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="title">Olá, {{ session('user_name') }}! 👋</h1>
        <div class="title-underline"></div>
    </div>
</div>

@if(session('success'))
    <div class="note-page-alert note-page-alert--success" role="status" aria-live="polite">
        <span aria-hidden="true">✓</span>
        <span>{{ session('success') }}</span>
        <a href="{{ secure_url(route('trash.index', [], false)) }}">Abrir Lixeira</a>
    </div>
@endif

<!-- Indicadores calculados apenas com dados do usuario autenticado. -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-value">{{ $totalNotes }}</div>
        <div class="stat-label">Total de notas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-value">{{ $recentNotesCount }}</div>
        <div class="stat-label">Notas nos últimos 7 dias</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📁</div>
        <div class="stat-value">{{ $categoriesCount }}</div>
        <div class="stat-label">Categorias criadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🗑️</div>
        <div class="stat-value">{{ $trashNotesCount }}</div>
        <div class="stat-label">Notas na Lixeira</div>
    </div>
</div>

<div class="page-header" style="margin-top:8px;">
    <div>
        <h2 class="title" style="font-size:18px;">Suas Notas</h2>
        <div class="title-underline"></div>
    </div>
</div>

<!-- Grade de notas ativas com visualizacao rapida e acesso a edicao. -->
<div class="notes-grid" id="notesGrid">
    @foreach($notes as $note)
    @php
        // O popup recebe somente metadados seguros; o arquivo continua protegido pela rota autenticada.
        $previewAttachments = collect($note->attachments ?? [])->map(function ($attachment, $index) use ($note) {
            return [
                'name' => $attachment['name'] ?? 'Arquivo anexado',
                'size' => (int) ($attachment['size'] ?? 0),
                'url' => secure_url(route('notes.attachments.show', [$note->id, $index], false)),
            ];
        })->values();
    @endphp
    <div class="card fadeIn" data-title="{{ strtolower($note->title) }}">

        <h2>{{ $note->title }}</h2>

        <div class="card-meta">
            <span class="card-date">
                📅 Criado em: {{ \Carbon\Carbon::parse($note->created_day)->format('d/m/Y') }}
            </span>
            <span class="note-card-status note-card-status--{{ $note->status }}">
                {{ match($note->status) {
                    'concluida' => '✓ Concluída',
                    'pendente' => '○ Pendente',
                    default => '● Em andamento',
                } }}
            </span>
        </div>

        <div class="card-actions">
            <button type="button" class="btn btn-secondary note-preview-btn"
                    data-title="{{ $note->title }}"
                    data-date="{{ \Carbon\Carbon::parse($note->created_day)->format('d/m/Y') }}"
                    data-category="{{ optional($note->category)->name ?: 'Sem categoria' }}"
                    data-status="{{ $note->status }}"
                    data-priority="{{ $note->priority }}"
                    data-tags='@json(array_values($note->tags ?? []))'
                    data-attachments='@json($previewAttachments)'
                    data-content="{{ \App\Support\NoteContent::toPlainText($note->content) }}"
                    onclick="openNotePreview(this)">Visualizar</button>
            <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}?edit=1" class="btn">Editar nota</a>
        </div>

    </div>
    @endforeach
</div>

<script>
    // Centraliza a apresentacao dos valores persistidos de status e prioridade.
    const noteStatusLabels = {
        em_andamento: 'Em andamento',
        pendente: 'Pendente',
        concluida: 'Concluída'
    };
    const notePriorityLabels = { baixa: 'Baixa', media: 'Média', alta: 'Alta' };

    // Lê os atributos data-* do botão, converte códigos em rótulos e preenche o modal sem solicitar novamente os dados ao servidor.
    function openNotePreview(button) {
        document.getElementById('notePreviewTitle').textContent = button.dataset.title;
        document.getElementById('notePreviewDate').textContent = button.dataset.date;
        document.getElementById('notePreviewCategory').textContent = button.dataset.category;
        document.getElementById('notePreviewStatus').textContent = noteStatusLabels[button.dataset.status] || button.dataset.status;
        document.getElementById('notePreviewPriority').textContent = notePriorityLabels[button.dataset.priority] || button.dataset.priority;
        document.getElementById('notePreviewContent').textContent = button.dataset.content || 'Nenhum conteúdo informado.';

        const tagsContainer = document.getElementById('notePreviewTags');
        const tags = parsePreviewList(button.dataset.tags);
        tagsContainer.innerHTML = '';
        if (tags.length === 0) {
            const emptyTags = document.createElement('span');
            emptyTags.className = 'note-preview-empty';
            emptyTags.textContent = 'Nenhuma etiqueta';
            tagsContainer.appendChild(emptyTags);
        } else {
            tags.forEach(tag => {
                const chip = document.createElement('span');
                chip.className = 'note-preview-tag';
                chip.textContent = tag;
                tagsContainer.appendChild(chip);
            });
        }

        renderPreviewAttachments(parsePreviewList(button.dataset.attachments));

        document.getElementById('notePreviewModal').classList.add('modal-active');
    }

    // Converte listas JSON do botão sem interromper o popup caso um registro antigo esteja incompleto.
    function parsePreviewList(value) {
        try {
            const parsed = JSON.parse(value || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    // Mostra o estado dos anexos e cria links protegidos para abrir cada arquivo.
    function renderPreviewAttachments(attachments) {
        const status = document.getElementById('notePreviewAttachmentStatus');
        const list = document.getElementById('notePreviewAttachments');
        list.innerHTML = '';

        if (attachments.length === 0) {
            status.textContent = 'Nenhum arquivo anexado';
            const empty = document.createElement('span');
            empty.className = 'note-preview-empty';
            empty.textContent = 'Esta nota não possui arquivos.';
            list.appendChild(empty);
            return;
        }

        status.textContent = attachments.length === 1
            ? '1 arquivo anexado'
            : `${attachments.length} arquivos anexados`;

        attachments.forEach(attachment => {
            const item = document.createElement('div');
            item.className = 'note-preview-attachment';

            const details = document.createElement('div');
            details.className = 'note-preview-attachment-details';
            const icon = document.createElement('span');
            icon.className = 'note-preview-attachment-icon';
            icon.textContent = '📎';
            const text = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = attachment.name || 'Arquivo anexado';
            const size = document.createElement('span');
            size.textContent = formatPreviewFileSize(Number(attachment.size || 0));
            text.append(name, size);
            details.append(icon, text);

            const link = document.createElement('a');
            link.className = 'note-preview-attachment-link';
            link.href = attachment.url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'Ver arquivo';

            item.append(details, link);
            list.appendChild(item);
        });
    }

    // Formata o tamanho armazenado no banco para uma leitura simples no popup.
    function formatPreviewFileSize(bytes) {
        if (!bytes) return 'Tamanho não informado';
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1048576) return `${Math.round(bytes / 1024)} KB`;
        return `${(bytes / 1048576).toFixed(1)} MB`;
    }

    // Fecha a visualizacao sem alterar a nota.
    function closeNotePreview() {
        document.getElementById('notePreviewModal').classList.remove('modal-active');
    }

    // Normaliza a busca para minúsculas e alterna display; os cards continuam no HTML e nenhuma nota é modificada.
    function filterNotes(query) {
        query = query.toLowerCase();
        document.querySelectorAll('#notesGrid .card').forEach(card => {
            card.style.display = card.dataset.title.includes(query) ? '' : 'none';
        });
    }
    const topSearch = document.getElementById('topSearchInput');
    if (topSearch) {
        topSearch.addEventListener('input', function () {
            filterNotes(this.value);
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeNotePreview();
    });
</script>

@endsection

@section('modals')
<!-- Modal somente leitura mantido fora da grade para cobrir toda a viewport. -->
<div id="notePreviewModal" class="modal-overlay note-preview-overlay" onclick="closeNotePreview()">
    <div class="modal-box note-preview-modal" onclick="event.stopPropagation()">
        <div class="note-preview-header">
            <div>
                <span class="note-preview-kicker">Visualização da nota</span>
                <h2 id="notePreviewTitle"></h2>
            </div>
            <button type="button" class="modal-close" onclick="closeNotePreview()" aria-label="Fechar">✕</button>
        </div>

        <div class="note-preview-meta-grid">
            <div class="note-preview-info"><span>📅 Criado em</span><strong id="notePreviewDate"></strong></div>
            <div class="note-preview-info"><span>📁 Categoria</span><strong id="notePreviewCategory"></strong></div>
            <div class="note-preview-info"><span>● Status</span><strong id="notePreviewStatus"></strong></div>
            <div class="note-preview-info"><span>⚑ Prioridade</span><strong id="notePreviewPriority"></strong></div>
        </div>

        <div class="note-preview-section">
            <span class="note-preview-label">Etiquetas</span>
            <div id="notePreviewTags" class="note-preview-tags"></div>
        </div>

        <div class="note-preview-section note-preview-attachments-section">
            <div class="note-preview-attachments-heading">
                <span class="note-preview-label">Anexos</span>
                <span id="notePreviewAttachmentStatus" class="note-preview-attachment-status"></span>
            </div>
            <div id="notePreviewAttachments" class="note-preview-attachments"></div>
        </div>

        <div class="note-preview-section">
            <span class="note-preview-label">Conteúdo da nota</span>
            <p id="notePreviewContent" class="note-preview-content"></p>
        </div>
    </div>
</div>
@endsection
