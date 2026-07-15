@extends('layout.app')

@section('content')

<div class="cat-page">
<div class="cat-header">
    <div class="cat-header-title">
        <div class="cat-header-icon">📁</div>
        <div>
            <h1>Categorias</h1>
            <p>Organize suas notas por categorias para encontrar tudo com mais facilidade.</p>
        </div>
    </div>
    <button class="btn-new-cat" onclick="openCatModal()">
        <span>➕</span> Nova Categoria
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📁</div>
        <div class="stat-value">{{ $totalCategories }}</div>
        <div class="stat-label">Total de categorias</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-value">{{ $notesCategorized }}</div>
        <div class="stat-label">Notas categorizadas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $notesConcluded }}</div>
        <div class="stat-label">Notas concluídas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎨</div>
        <div class="stat-value">{{ $colorsUsed }}</div>
        <div class="stat-label">Cores em uso</div>
    </div>
</div>

<div class="cat-toolbar">
    <div class="cat-search">
        🔍 <input type="text" id="catSearchInput" placeholder="Buscar categorias...">
    </div>
    <div class="cat-sort" onclick="toggleCatSort()">
        ⇅ Ordenar: <span id="catSortLabel">A-Z</span>
    </div>
</div>

<div class="cat-table-wrap">
    @if($categories->isEmpty())
        <div class="cat-empty">
            <div style="font-size:32px;margin-bottom:8px;">📁</div>
            Nenhuma categoria criada ainda. Clique em "Nova Categoria" para começar.
        </div>
    @else
    <table class="cat-table" id="catTable">
        <thead>
            <tr>
                <th>Categoria</th>
                <th class="cat-count">Notas</th>
                <th class="cat-count">Concluídas</th>
                <th>Status</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            @php
                $concluded = $category->notes->filter(function ($note) {
                    return $note->sections->count() > 0 && $note->sections->every(fn ($s) => $s->completed);
                })->count();
            @endphp
            <tr data-name="{{ strtolower($category->name) }}">
                <td>
                    <div class="cat-row-name">
                        <div class="cat-icon-box" style="background: {{ $category->color }}22;">
                            {{ $category->icon }}
                        </div>
                        <div>
                            <strong>
                                <span class="cat-dot" style="background: {{ $category->color }};"></span>
                                {{ $category->name }}
                            </strong>
                            @if($category->description)
                                <span class="desc">{{ $category->description }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="cat-count">
                    <strong>{{ $category->notes_count }}</strong>
                    <small>Notas</small>
                </td>
                <td class="cat-count">
                    <strong>{{ $concluded }}</strong>
                    <small>Concluídas</small>
                </td>
                <td>
                    <span class="cat-status {{ $category->active ? 'active' : 'inactive' }}">
                        {{ $category->active ? 'Ativa' : 'Inativa' }}
                    </span>
                </td>
                <td class="cat-action">
                    <button class="cat-action-btn" onclick="toggleCatMenu(event, {{ $category->id }})">⋯</button>
                    <div class="cat-action-menu" id="catMenu{{ $category->id }}">
                        <button type="button" onclick='openCatModal(@json($category))'>✏️Editar</button>
                        <form action="{{ secure_url(route('categories.toggle', $category->id, false)) }}" method="POST">
                            @csrf @method("PATCH")
                            <button type="submit">{{ $category->active ? '⏸️ Desativar' : '▶️ Ativar' }}</button>
                        </form>
                        <form action="{{ secure_url(route('categories.destroy', $category->id, false)) }}" method="POST"
                              onsubmit="return confirm('Excluir esta categoria? As notas ligadas a ela ficarão sem categoria.');">
                            @csrf @method("DELETE")
                            <button type="submit" class="danger">🗑️Excluir</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
</div>

<script>
    function toggleCatSort() {
        const label = document.getElementById('catSortLabel');
        const rows = Array.from(document.querySelectorAll('#catTable tbody tr'));
        const tbody = document.querySelector('#catTable tbody');
        const asc = label.textContent === 'A-Z';
        rows.sort((a, b) => asc
            ? b.dataset.name.localeCompare(a.dataset.name)
            : a.dataset.name.localeCompare(b.dataset.name));
        rows.forEach(r => tbody.appendChild(r));
        label.textContent = asc ? 'Z-A' : 'A-Z';
    }

    document.getElementById('catSearchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#catTable tbody tr').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });

    function toggleCatMenu(e, id) {
        e.stopPropagation();
        document.querySelectorAll('.cat-action-menu.open').forEach(m => {
            if (m.id !== 'catMenu' + id) m.classList.remove('open');
        });
        document.getElementById('catMenu' + id).classList.toggle('open');
    }

    document.addEventListener('click', function () {
        document.querySelectorAll('.cat-action-menu.open').forEach(m => m.classList.remove('open'));
    });

    function selectCatIcon(icon) {
        document.getElementById('catIcon').value = icon;
        document.querySelectorAll('.cat-icon-option').forEach(el => {
            el.classList.toggle('selected', el.dataset.icon === icon);
        });
    }

    function selectCatColor(color) {
        document.getElementById('catColor').value = color;
        document.querySelectorAll('.cat-color-swatch').forEach(el => {
            el.classList.toggle('selected', el.dataset.color === color);
        });
    }

    function openCatModal(category) {
        const form = document.getElementById('catForm');
        const title = document.getElementById('catModalTitle');

        if (category) {
            title.textContent = 'Editar Categoria';
            form.action = `/categories/${category.id}`;
            document.getElementById('catFormMethod').value = 'PUT';
            document.getElementById('catName').value = category.name || '';
            document.getElementById('catDescription').value = category.description || '';
            selectCatIcon(category.icon || '📁');
            selectCatColor(category.color || '#ff7b00');
        } else {
            title.textContent = 'Nova Categoria';
            form.action = "{{ secure_url(route('categories.store', [], false)) }}";
            document.getElementById('catFormMethod').value = 'POST';
            form.reset();
            selectCatIcon('📁');
            selectCatColor('#ff7b00');
        }

        document.getElementById('catModal').classList.add('modal-active');
    }

    function closeCatModal() {
        document.getElementById('catModal').classList.remove('modal-active');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeCatModal();
    });

    selectCatIcon('📁');
    selectCatColor('#ff7b00');
</script>

@endsection

@section('modals')
{{-- MODAL NOVA/EDITAR CATEGORIA --}}
<div id="catModal" class="modal-overlay" onclick="closeCatModal()">
    <div class="modal-box cat-modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="cat-modal-heading">
                <span class="cat-modal-icon">📁</span>
                <div>
                    <h2 id="catModalTitle">Nova Categoria</h2>
                    <p>Organize suas notas com uma categoria personalizada.</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeCatModal()">✕</button>
        </div>

        <form id="catForm" method="POST" action="{{ secure_url(route('categories.store', [], false)) }}">
            @csrf
            <input type="hidden" name="_method" id="catFormMethod" value="POST">

            <div class="form-group">
                <label>Nome <span class="required">*</span></label>
                <input type="text" name="name" id="catName" required maxlength="255" placeholder="Ex: Trabalho">
            </div>

            <div class="form-group">
                <label>Descrição</label>
                <textarea name="description" id="catDescription" style="min-height:70px" placeholder="Uma breve descrição da categoria..."></textarea>
            </div>

            <div class="form-group">
                <label>Ícone</label>
                <div class="cat-icon-picker" id="catIconPicker">
                    @foreach(['📁','💼','🎓','💰','❤️','✈️','📌','🏠','🛒','🎯','📚','⚙️'] as $ic)
                        <div class="cat-icon-option" data-icon="{{ $ic }}" onclick="selectCatIcon('{{ $ic }}')">{{ $ic }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="icon" id="catIcon" value="📁">
            </div>

            <div class="form-group">
                <label>Cor</label>
                <div class="cat-color-swatches" id="catColorPicker">
                    @foreach(['#ff7b00','#8B5CF6','#22C55E','#EC4899','#3B82F6','#EF4444','#14B8A6','#F59E0B'] as $col)
                        <div class="cat-color-swatch" style="background: {{ $col }};" data-color="{{ $col }}" onclick="selectCatColor('{{ $col }}')"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="catColor" value="#ff7b00">
            </div>

            <div class="cat-modal-actions">
                <button type="button" class="cat-modal-cancel" onclick="closeCatModal()">Cancelar</button>
                <button type="submit" class="cat-modal-save">Salvar Categoria</button>
            </div>
        </form>
    </div>
</div>
@endsection
