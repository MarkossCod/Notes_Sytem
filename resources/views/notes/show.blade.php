@extends('layout.app')

@section('content')

<div class="show-layout" style="grid-template-columns: 220px 1fr;">

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
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="{{ secure_url(route('notes.section.create', [$note->id], false)) }}"
                   style="padding:8px 16px;background:#FF6D00;color:white;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:background .2s;"
                   onmouseover="this.style.background='#e06300'" onmouseout="this.style.background='#FF6D00'">
                    ➕ Nova Divisão
                </a>
                <form action="{{ secure_url(route('notes.destroy', [$note->id], false)) }}" method="POST"
                      onsubmit="return confirm('Tem certeza que deseja excluir esta nota?')" autocomplete="off">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete-note">🗑️ Excluir Nota</button>
                </form>
            </div>
        </div>

        <h2 class="sections-title">Divisões da Nota</h2>

        <div class="sections">
            @foreach($note->sections as $section)
            <div class="section-card fadeIn {{ $section->completed ? 'section-completed' : '' }}"
                 id="section-{{ $section->id }}">

                <div class="section-card-content">
                    <div style="flex:1;">
                        <h2>{{ $section->section_title }}</h2>
                        <p>{{ $section->section_content }}</p>

                        {{-- Imagens --}}
                        @if($section->images && count($section->images) > 0)
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                            @foreach($section->images as $img)
                            <img src="{{ asset('storage/' . $img) }}"
                                 style="width:70px;height:70px;object-fit:cover;border-radius:8px;border:2px solid #FFE0B2;cursor:pointer;"
                                 onclick="window.open(this.src)" alt="imagem"/>
                            @endforeach
                        </div>
                        @endif

                        {{-- Tabela --}}
                        @if($section->table_data)
                        @php $tableData = json_decode($section->table_data, true); @endphp
                        @if($tableData && isset($tableData['headers']) && count($tableData['headers']) > 0)
                        <div style="overflow-x:auto;margin-top:12px;">
                            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                                <thead>
                                    <tr>
                                        @foreach($tableData['headers'] as $header)
                                        <th style="background:#FF6D00;color:white;padding:7px 12px;text-align:left;font-weight:600;">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tableData['rows'] as $row)
                                    <tr>
                                        @foreach($row as $cell)
                                        <td style="padding:6px 12px;border-bottom:1px solid #f0f0f0;color:#333;">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                        @endif
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

</div>

@endsection

@section('modals')
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