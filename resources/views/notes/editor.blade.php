{{--
    PARCIAL: editor compartilhado de notas
    FINALIDADE: montar título, data, categoria, etiquetas, status, prioridade, anexos e conteúdo para criar ou editar uma nota.
    DADOS RECEBIDOS: $note é null na criação e contém o registro na edição; $categories contém apenas categorias ativas do usuário.
    FLUXO: sem nota envia POST para notes.store; com nota envia PUT para notes.update. $isSaved controla as diferenças da interface.
    AO ALTERAR: mantenha names, IDs e valores de status/prioridade sincronizados com NoteController e com o JavaScript deste arquivo.
--}}
{{-- $isSaved separa criação de edição; $startEditing permite abrir uma nota salva já com os campos liberados pela query ?edit=1. --}}
@php
    $isSaved = isset($note) && $note;
    $startEditing = $isSaved && request()->query('edit') === '1';
@endphp

<div class="note-editor-wrap {{ $isSaved ? 'note-editor-saved' : 'note-editor-create' }}">

    {{-- A rota e o método mudam conforme $isSaved: POST cria uma nota; PUT atualiza somente a nota já carregada. --}}
    <form id="noteForm" action="{{ secure_url($isSaved ? route('notes.update', [$note->id], false) : route('notes.store', [], false)) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        @if($isSaved) @method('PUT') @endif

        <div class="ne-top">
            <div class="ne-title-row">
                <input type="text" id="title" name="title" class="ne-title-input"
                       value="{{ old('title', $isSaved ? $note->title : '') }}" placeholder="Nova Nota" required>
            </div>

            @if($isSaved)
            <div class="ne-actions">
                <button type="button" class="ne-btn" id="neEditBtn">
                    ✏️ Editar
                </button>
                <button type="button" class="ne-btn ne-btn-danger" id="neDeleteBtn" aria-controls="noteDeleteModal">
                    🗑️ Excluir nota
                </button>
            </div>
            @endif
        </div>

        <div class="ne-meta">
            <div class="ne-meta-pill ne-calendar-status" id="calendarField">
                <button type="button" class="calendar-field-input" id="calendarFieldBtn"
                        style="border:none;background:none;padding:0;font-size:12.5px;color:#666;display:flex;align-items:center;gap:6px;">
                    <span aria-hidden="true">📅</span>
                    <span>Criado em: <strong id="calendarFieldText">selecione a data</strong></span>
                </button>

                <div class="calendar-popover" id="calendarPopover">
                    <header class="calendar-header">
                        <button type="button" class="calendar-nav-btn" id="calendarPrevBtn" aria-label="Mês anterior">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        <span class="calendar-heading" id="calendarHeading"></span>
                        <button type="button" class="calendar-nav-btn" id="calendarNextBtn" aria-label="Próximo mês">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </header>
                    <div class="calendar-weekdays">
                        <span>D</span><span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span>
                    </div>
                    <div class="calendar-grid" id="calendarGrid"></div>
                </div>
            </div>

            <span class="ne-meta-pill ne-save-status">✏️ {{ $isSaved ? 'Nota salva' : 'Nova nota — ainda não salva' }}</span>
            <button type="submit" class="ne-btn ne-btn-primary ne-meta-save">💾 {{ $isSaved ? 'Salvar alterações' : 'Salvar Nota' }}</button>

            <input type="hidden" id="created_day" name="created_day" required>
        </div>

        <div class="ne-grid">

            {{-- COLUNA ESQUERDA --}}
            <aside class="ne-panel">

                <div class="ne-side-block">
                    <p class="ne-side-label">📁 Categoria</p>
                    <select name="category_id" class="ne-select">
                        <option value="">Selecione...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($isSaved && $note->category_id == $category->id)>{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ne-side-block">
                    <p class="ne-side-label">🏷️ Etiquetas</p>
                    <div class="ne-tags" id="neTags"></div>
                    <button type="button" class="ne-tag-add" id="neTagAddBtn">➕ Nova etiqueta</button>
                    <input type="text" class="ne-tag-input" id="neTagInput" placeholder="Digite e pressione Enter">
                    <input type="hidden" name="tags" id="neTagsHidden" value="[]">
                </div>

                <div class="ne-side-block">
                    <p class="ne-side-label">Status</p>
                    <select name="status" id="neStatus" class="ne-select">
                        <option value="em_andamento" @selected(!$isSaved || $note->status === 'em_andamento')>🟠 Em andamento</option>
                        <option value="pendente" @selected($isSaved && $note->status === 'pendente')>⚪ Pendente</option>
                        <option value="concluida" @selected($isSaved && $note->status === 'concluida')>🟢 Concluída</option>
                    </select>
                </div>

                <div class="ne-side-block">
                    <p class="ne-side-label">Prioridade</p>
                    <select name="priority" id="nePriority" class="ne-select">
                        <option value="baixa" @selected($isSaved && $note->priority === 'baixa')>🟢 Baixa</option>
                        <option value="media" @selected(!$isSaved || $note->priority === 'media')>🟠 Média</option>
                        <option value="alta" @selected($isSaved && $note->priority === 'alta')>🔴 Alta</option>
                    </select>
                </div>

                <div class="ne-side-block">
                    <p class="ne-side-label">Anexos</p>
                    <div class="ne-dropzone" id="neDropzone">
                        <div class="ne-dropzone-icon">☁️</div>
                        Arraste arquivos aqui<br>ou
                        <br>
                        <button type="button" class="ne-dropzone-btn" id="neSelectFilesBtn">Selecionar Arquivos</button>
                        <input type="file" id="neFileInput" name="attachments[]" multiple
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.gif,.webp" style="display:none;">
                    </div>
                    @if($isSaved && ! empty($note->attachments))
                        <div class="ne-saved-files" aria-label="Arquivos já anexados">
                            @foreach($note->attachments as $attachmentIndex => $attachment)
                                <a class="ne-saved-file" href="{{ secure_url(route('notes.attachments.show', [$note->id, $attachmentIndex], false)) }}"
                                   target="_blank" rel="noopener">
                                    <span aria-hidden="true">📎</span>
                                    <span class="ne-saved-file-name">{{ $attachment['name'] ?? 'Arquivo anexado' }}</span>
                                    <span class="ne-saved-file-action">Ver</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="ne-file-list" id="neFileList"></div>
                </div>

            </aside>

            {{-- COLUNA DIREITA — EDITOR --}}
            <section class="ne-panel ne-editor-panel">
                <div class="ne-plain-editor-header">
                    <div>
                        <p class="ne-editor-title">Conteúdo da Nota</p>
                        <span>Registre as informações principais da nota.</span>
                    </div>
                    <span class="ne-plain-editor-badge">Texto simples</span>
                </div>

                <div class="ne-content-area" id="neContent" contenteditable="{{ $isSaved ? 'false' : 'true' }}"
                     data-placeholder="Digite o conteúdo da sua nota aqui...">{!! $isSaved ? \App\Support\NoteContent::normalizeHtml($note->content) : '' !!}</div>

                <input type="hidden" name="content" id="neContentHidden">

                <div class="ne-footer-row">
                    <span id="neWordCount">0 palavras • 0 caracteres</span>
                </div>

                <div class="ne-tip">
                    💡 <strong>Dica:</strong> Use Ctrl + S para salvar rapidamente sua nota.
                </div>

            </section>

        </div>
    </form>

</div>

<script>
(function () {
    /* ===== Calendário (data de criação) ===== */
    const field = document.getElementById('calendarField');
    const fieldBtn = document.getElementById('calendarFieldBtn');
    const fieldText = document.getElementById('calendarFieldText');
    const popover = document.getElementById('calendarPopover');
    const grid = document.getElementById('calendarGrid');
    const heading = document.getElementById('calendarHeading');
    const prevBtn = document.getElementById('calendarPrevBtn');
    const nextBtn = document.getElementById('calendarNextBtn');
    const hiddenInput = document.getElementById('created_day');

    const monthNames = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
    let selectedDate = null;

    function formatISO(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function formatDisplay(date) {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}/${m}/${y}`;
    }

    function renderCalendar() {
        heading.textContent = `${monthNames[viewDate.getMonth()]} ${viewDate.getFullYear()}`;
        grid.innerHTML = '';

        const firstDayOfMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);
        const startWeekday = firstDayOfMonth.getDay();
        const daysInMonth = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewDate.getFullYear(), viewDate.getMonth(), 0).getDate();

        const totalCells = 42;
        for (let i = 0; i < totalCells; i++) {
            const dayNum = i - startWeekday + 1;
            let cellDate, outOfMonth = false;

            if (dayNum < 1) {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, daysInPrevMonth + dayNum);
                outOfMonth = true;
            } else if (dayNum > daysInMonth) {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, dayNum - daysInMonth);
                outOfMonth = true;
            } else {
                cellDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), dayNum);
            }

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'calendar-day';
            btn.textContent = cellDate.getDate();

            if (outOfMonth) btn.classList.add('outside');
            if (cellDate.getTime() === today.getTime()) btn.classList.add('today');
            if (selectedDate && cellDate.getTime() === selectedDate.getTime()) btn.classList.add('selected');

            btn.addEventListener('click', function () {
                selectedDate = cellDate;
                hiddenInput.value = formatISO(cellDate);
                fieldText.textContent = formatDisplay(cellDate);
                if (outOfMonth) {
                    viewDate = new Date(cellDate.getFullYear(), cellDate.getMonth(), 1);
                }
                closePopover();
                renderCalendar();
            });

            grid.appendChild(btn);
        }
    }

    function openPopover() { popover.classList.add('open'); }
    function closePopover() { popover.classList.remove('open'); }

    fieldBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        popover.classList.contains('open') ? closePopover() : openPopover();
    });
    prevBtn.addEventListener('click', () => { viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() - 1, 1); renderCalendar(); });
    nextBtn.addEventListener('click', () => { viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth() + 1, 1); renderCalendar(); });
    document.addEventListener('click', function (e) { if (!field.contains(e.target)) closePopover(); });

    // pré-seleciona a data da nota ou hoje
    selectedDate = @if($isSaved) new Date('{{ $note->created_day }}T00:00:00') @else today @endif;
    viewDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
    hiddenInput.value = formatISO(selectedDate);
    fieldText.textContent = formatDisplay(selectedDate);
    renderCalendar();

    /* ===== Etiquetas ===== */
    const tagsWrap = document.getElementById('neTags');
    const tagAddBtn = document.getElementById('neTagAddBtn');
    const tagInput = document.getElementById('neTagInput');
    const tagsHidden = document.getElementById('neTagsHidden');
    const initialTags = @json($isSaved ? ($note->tags ?? []) : []);
    const normalizeTag = value => String(value).trim().toLocaleLowerCase('pt-BR');

    // Remove repetições antigas sem alterar a escrita da primeira ocorrência.
    let tags = initialTags.reduce((uniqueTags, tag) => {
        const label = String(tag).trim();
        const alreadyExists = uniqueTags.some(existingTag => normalizeTag(existingTag) === normalizeTag(label));
        if (label && !alreadyExists) uniqueTags.push(label);
        return uniqueTags;
    }, []);

    function renderTags() {
        tagsWrap.innerHTML = '';
        tags.forEach((tag, idx) => {
            const chip = document.createElement('span');
            chip.className = 'ne-tag-chip';

            // textContent evita que um nome de etiqueta seja interpretado como HTML.
            const label = document.createElement('span');
            label.className = 'ne-tag-label';
            label.textContent = tag;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'ne-tag-remove';
            removeButton.dataset.idx = idx;
            removeButton.setAttribute('aria-label', `Remover etiqueta ${tag}`);
            removeButton.textContent = '×';

            chip.append(label, removeButton);
            tagsWrap.appendChild(chip);
        });
        tagsHidden.value = JSON.stringify(tags);
    }
    renderTags();

    tagsWrap.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.ne-tag-remove');
        if (!removeButton || !tagsWrap.contains(removeButton)) return;

        tags.splice(Number(removeButton.dataset.idx), 1);
        renderTags();
    });

    function closeTagInput() {
        tagInput.style.display = 'none';
        tagAddBtn.style.display = 'inline-flex';
    }

    tagAddBtn.addEventListener('click', function () {
        tagAddBtn.style.display = 'none';
        tagInput.style.display = 'inline-block';
        tagInput.value = '';
        tagInput.focus();
    });

    function commitTag() {
        const value = tagInput.value.trim();

        // Limpar antes de ocultar impede que Enter e blur salvem o mesmo valor duas vezes.
        tagInput.value = '';
        closeTagInput();

        const alreadyExists = tags.some(tag => normalizeTag(tag) === normalizeTag(value));
        if (!value || alreadyExists) return;

        tags.push(value);
        renderTags();
    }

    tagInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            commitTag();
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            tagInput.value = '';
            closeTagInput();
        }
    });
    tagInput.addEventListener('blur', commitTag);

    /* ===== Status / Prioridade — bolinha colorida ===== */
    // (mantidos como <select> nativos para acessibilidade; a cor é indicada pelo emoji na option)

    /* ===== Anexos — seleção, arraste e envio real junto com o formulário ===== */
    const dropzone = document.getElementById('neDropzone');
    const selectFilesBtn = document.getElementById('neSelectFilesBtn');
    const fileInput = document.getElementById('neFileInput');
    const fileList = document.getElementById('neFileList');
    const existingAttachmentCount = @json($isSaved ? count($note->attachments ?? []) : 0);
    const maximumAttachments = 5;
    let selectedFiles = [];

    function humanSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function fileKey(file) {
        return `${file.name}:${file.size}:${file.lastModified}`;
    }

    function syncFileInput() {
        const transfer = new DataTransfer();
        selectedFiles.forEach(file => transfer.items.add(file));
        fileInput.files = transfer.files;
    }

    function renderSelectedFiles() {
        fileList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'ne-file-item';

            const icon = document.createElement('span');
            icon.textContent = '📄';
            const name = document.createElement('span');
            name.className = 'ne-file-name';
            name.textContent = file.name;
            const size = document.createElement('span');
            size.className = 'size';
            size.textContent = humanSize(file.size);
            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.setAttribute('aria-label', `Remover ${file.name}`);
            removeButton.textContent = '✕';
            removeButton.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncFileInput();
                renderSelectedFiles();
            });

            item.append(icon, name, size, removeButton);
            fileList.appendChild(item);
        });
    }

    function addFiles(files) {
        if (fileInput.disabled) return;

        const availableSlots = maximumAttachments - existingAttachmentCount - selectedFiles.length;
        if (availableSlots <= 0) {
            window.alert(`Cada nota pode ter no máximo ${maximumAttachments} anexos.`);
            return;
        }

        const knownFiles = new Set(selectedFiles.map(fileKey));
        const newFiles = Array.from(files)
            .filter(file => !knownFiles.has(fileKey(file)))
            .slice(0, availableSlots);

        selectedFiles.push(...newFiles);
        syncFileInput();
        renderSelectedFiles();

        if (Array.from(files).length > newFiles.length) {
            window.alert(`Foram aceitos apenas arquivos novos dentro do limite de ${maximumAttachments} anexos.`);
        }
    }

    selectFilesBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => addFiles(Array.from(fileInput.files)));

    ['dragenter', 'dragover'].forEach(evt => dropzone.addEventListener(evt, e => {
        e.preventDefault(); dropzone.classList.add('drag-over');
    }));
    ['dragleave', 'drop'].forEach(evt => dropzone.addEventListener(evt, e => {
        e.preventDefault(); dropzone.classList.remove('drag-over');
    }));
    dropzone.addEventListener('drop', e => addFiles(e.dataTransfer.files));

    /* ===== Editor de conteúdo ===== */
    const content = document.getElementById('neContent');
    const contentHidden = document.getElementById('neContentHidden');
    const wordCount = document.getElementById('neWordCount');

    function updateCount() {
        const text = content.innerText.replace(/\u00a0/g, ' ').trim();
        const words = text ? text.split(/\s+/).length : 0;
        wordCount.textContent = `${words} palavras • ${text.length} caracteres`;
        contentHidden.value = content.innerHTML
            .replace(/&amp;nbsp;?/gi, ' ')
            .replace(/&nbsp;?/gi, ' ')
            .replace(/\u00a0/g, ' ');
    }

    content.addEventListener('input', updateCount);
    updateCount();

    /* Ctrl+S salva rapidamente */
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            e.preventDefault();
            document.getElementById('noteForm').requestSubmit();
        }
    });

    document.getElementById('noteForm').addEventListener('submit', updateCount);

    @if($isSaved)
    const editableFields = document.querySelectorAll('#noteForm input:not([type="hidden"]), #noteForm select, #noteForm .ne-toolbar-btn, #noteForm .ne-tag-add, #noteForm .ne-dropzone-btn, #calendarFieldBtn');
    const saveBtn = document.querySelector('.ne-meta-save');
    function setEditMode(editing) {
        editableFields.forEach(field => field.disabled = !editing);
        content.setAttribute('contenteditable', editing ? 'true' : 'false');
        saveBtn.hidden = !editing;
        document.querySelector('.note-editor-wrap').classList.toggle('ne-view-mode', !editing);
        if (editing) document.getElementById('title').focus();
    }
    document.getElementById('neEditBtn').addEventListener('click', () => setEditMode(true));

    setEditMode(@json($startEditing));
    @endif
})();
</script>
