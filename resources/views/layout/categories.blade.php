{{--
    VIEW: gerenciamento de categorias
    FINALIDADE: listar, pesquisar, ordenar, cadastrar, editar, ativar, desativar e excluir categorias do usuário atual.
    DADOS RECEBIDOS: $categories contém a lista e contagens; os quatro totais alimentam os cartões de resumo.
    ORIGEM DOS DADOS: CategoryController@index. Os formulários chamam store, update, toggle e destroy.
    AO ALTERAR: preserve data-name, IDs iniciados por cat e os métodos POST/PUT/PATCH/DELETE, pois o JavaScript e as rotas dependem deles.
--}}
@extends('layout.app')

@section('content')

{{-- Cabecalho da gestao e atalho para cadastrar uma nova categoria. --}}
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

{{-- Indicadores calculados pelo controlador para o usuario autenticado. --}}
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

{{-- Busca e ordenacao atuam localmente sobre as categorias carregadas. --}}
<div class="cat-toolbar">
    <div class="cat-search">
        🔍 <input type="text" id="catSearchInput" placeholder="Buscar categorias...">
    </div>
    <div class="cat-sort" onclick="toggleCatSort()">
        ⇅ Ordenar: <span id="catSortLabel">A-Z</span>
    </div>
</div>

{{-- A tabela apresenta estado, total de notas e total concluido por categoria. --}}
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
                    <strong>{{ $category->completed_notes_count }}</strong>
                    <small>Concluídas</small>
                </td>
                <td>
                    <span class="cat-status {{ $category->active ? 'active' : 'inactive' }}">
                        {{ $category->active ? 'Ativa' : 'Inativa' }}
                    </span>
                </td>
                <td class="cat-action">
                    <button type="button" class="cat-action-btn"
                            onclick="toggleCatMenu(event, {{ $category->id }})"
                            aria-label="Abrir opções de {{ $category->name }}"
                            aria-controls="catMenu{{ $category->id }}" aria-haspopup="true">
                        {{-- Ícone More Square fornecido pelo usuário; o viewBox remove a área de crédito que não faz parte do desenho. --}}
                        <svg class="cat-action-icon" viewBox="0 0 500 500" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M360.5 450.2H140.1c-49.7-.1-89.9-40.3-90-90V139.8c.1-49.7 40.3-89.9 90-90h220.3c49.7.1 89.9 40.3 90 90v220.3c0 49.8-40.2 90-89.9 90.1ZM140.1 81.8c-32 0-58 26-58 58v220.3c0 32 26 58 58 58h220.3c32 0 58-26 58-58V139.8c0-32-26-58-58-58H140.1Z"/>
                            <circle fill="currentColor" cx="160.8" cy="250" r="24"/>
                            <circle fill="currentColor" cx="250.3" cy="250" r="24"/>
                            <circle fill="currentColor" cx="339.8" cy="250" r="24"/>
                        </svg>
                    </button>
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

<script>
    // Reordena no navegador as linhas já carregadas. Depende de #catTable, data-name e do texto A-Z/Z-A exibido em catSortLabel.
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

    // Compara a busca com data-name e apenas oculta as linhas; nenhum registro é excluído ou consultado novamente.
    document.getElementById('catSearchInput')?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#catTable tbody tr').forEach(row => {
            row.style.display = row.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Fecha outros menus e abre o menu cujo ID segue o padrão catMenu + id da categoria.
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

    // Sem categoria, prepara POST para criação. Com categoria, preenche os campos e troca rota/método para PUT de atualização.
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
{{-- O formulario compartilhado muda rota e metodo conforme a operacao escolhida. --}}
{{-- MODAL NOVA/EDITAR CATEGORIA --}}
<div id="catModal" class="modal-overlay" onclick="closeCatModal()">
    <div class="modal-box" style="max-width:480px;" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="catModalTitle">Nova Categoria</h2>
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

            <button type="submit">Salvar Categoria</button>
        </form>
    </div>
</div>
@endsection
