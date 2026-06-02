@extends('layout.app')

@section('content')

<div class="show-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">

        <a href="{{ secure_url(route('notes.index', [], false)) }}" class="btn-back">← Voltar para notas</a>

        <button class="sidebar-info-btn" onclick="openInfoModal()">ℹ️ Informações da Nota</button>

        <p class="sidebar-section-label">Divisões</p>

        <ul class="sidebar-sections">
            @foreach($note->sections as $section)
            <li class="{{ $section->completed ? 'sidebar-section-done' : '' }}">
                <a href="#section-{{ $section->id }}">
                    📋 {{ $section->section_title }}
                </a>
            </li>
            @endforeach
        </ul>
    </aside>

    {{-- CONTEÚDO PRINCIPAL --}}
    <main class="main-content">

        @if(session('success'))
            <div class="alert-success">✅ {{ session('success') }}</div>
        @endif

        <div class="note-header">
            <div>
                <h1>{{ $note->title }}</h1>
                <div class="note-meta">
                    <span>📅 Criado em: {{ \Carbon\Carbon::parse($note->created_day)->format('d/m/Y') }}</span>
                    <span>✏️ Total de divisões: {{ $note->sections->count() }}</span>
                </div>
            </div>
            <form action="{{ secure_url(route('notes.destroy', [$note->id], false)) }}" method="POST"
                  onsubmit="return confirm('Tem certeza que deseja excluir esta nota?')" autocomplete="off">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete-note">🗑️ Excluir Nota</button>
            </form>
        </div>

        <h2 class="sections-title">Divisões da Nota</h2>

        <div class="sections">
            @foreach($note->sections as $section)
            <div class="section-card fadeIn {{ $section->completed ? 'section-completed' : '' }}"
                 id="section-{{ $section->id }}">

                <div class="section-card-content">
                    <div>
                        <h2>{{ $section->section_title }}</h2>
                        <p>{{ $section->section_content }}</p>
                    </div>
                    <div class="section-icons">

                        {{-- Botão Ver --}}
                        <button type="button" class="icon-btn"
                                title="Ver divisão"
                                data-title="{{ $section->section_title }}"
                                data-content="{{ $section->section_content }}"
                                onclick="openViewModal(this.dataset.title, this.dataset.content)">
                            👁️
                        </button>

                        {{-- Botão Editar --}}
                        <a href="{{ secure_url(route('notes.section.edit', [$note->id, $section->id], false)) }}"
                           class="icon-btn icon-edit" title="Editar">✏️</a>

                        {{-- Botão Concluir/Reabrir --}}
                        <form action="{{ secure_url(route('notes.section.complete', [$note->id, $section->id], false)) }}"
                              method="POST" style="display:inline" autocomplete="off">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="icon-btn" title="{{ $section->completed ? 'Reabrir' : 'Concluir' }}">
                                {{ $section->completed ? '🔄' : '✅' }}
                            </button>
                        </form>

                    </div>
                </div>

            </div>
            @endforeach
        </div>

    </main>

    {{-- PAINEL LATERAL DIREITO --}}
    <aside class="right-panel" id="add-section">

        @isset($editingSection)
            <h2 class="panel-title">Editar Divisão</h2>
            <form action="{{ secure_url(route('notes.section.update', [$note->id, $editingSection->id], false)) }}"
                  method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Título da Divisão</label>
                    <input type="text"
                           name="section_title"
                           value="{{ $editingSection->section_title }}"
                           autocomplete="off"
                           required>
                </div>

                <div class="form-group">
                    <label>Conteúdo da Divisão</label>
                    <textarea name="section_content"
                              autocomplete="off"
                              placeholder="Descreva os detalhes desta divisão...">{{ $editingSection->section_content }}</textarea>
                </div>

                <button type="submit">Salvar Alterações</button>
                <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="btn-cancel-block">Cancelar</a>

            </form>
        @else
            <h2 class="panel-title">Adicionar Nova Divisão</h2>
            <form action="{{ secure_url(route('notes.section', [$note->id], false)) }}" method="POST" autocomplete="off">
                @csrf

                <div class="form-group">
                    <label>Título da Divisão</label>
                    <input type="text"
                           name="section_title"
                           placeholder="Ex: Descrição do Problema"
                           autocomplete="off"
                           required>
                </div>

                <div class="form-group">
                    <label>Conteúdo da Divisão</label>
                    <textarea name="section_content"
                              autocomplete="off"
                              placeholder="Descreva os detalhes desta divisão..."></textarea>
                </div>

                <button type="submit">Adicionar Divisão</button>

            </form>
        @endisset

    </aside>

</div>

{{-- MODAL: INFORMAÇÕES DA NOTA --}}
<div id="infoModal" class="modal-overlay" onclick="closeModal('infoModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2>📋 {{ $note->title }}</h2>
            <button class="modal-close" onclick="closeModal('infoModal')">✕</button>
        </div>
        <p class="modal-date">📅 Criado em: {{ \Carbon\Carbon::parse($note->created_day)->format('d/m/Y') }}</p>
        <div class="modal-sections">
            @foreach($note->sections as $index => $section)
            <div class="modal-section-item {{ $section->completed ? 'modal-section-done' : '' }}">
                <div class="modal-section-number">{{ $index + 1 }}</div>
                <div class="modal-section-body">
                    <h3>{{ $section->section_title }}</h3>
                    <p>{{ $section->section_content }}</p>
                    @if($section->completed)
                        <span class="modal-badge-done">✅ Concluído</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- MODAL: VER DIVISÃO --}}
<div id="viewModal" class="modal-overlay" onclick="closeModal('viewModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="viewModalTitle"></h2>
            <button class="modal-close" onclick="closeModal('viewModal')">✕</button>
        </div>
        <div class="modal-view-content">
            <p id="viewModalContent"></p>
        </div>
    </div>
</div>

<script>
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
            closeModal('infoModal');
            closeModal('viewModal');
        }
    });
</script>

@endsection