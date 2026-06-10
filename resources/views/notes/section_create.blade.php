@extends('layout.app')

@section('content')
<style>
    .ed-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .ed-card { background:white; border-radius:16px; padding:28px; box-shadow:0 1px 6px rgba(0,0,0,0.07); }
    .ed-label { font-size:12px; font-weight:700; color:#FF6D00; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; display:block; }
    .ed-input { width:100%; padding:11px 14px; border:2px solid #FFE0B2; border-radius:10px; font-size:14px; color:#1a1a1a; background:#FFF8F0; outline:none; transition:border .2s; }
    .ed-input:focus { border-color:#FF6D00; background:white; }
    .ed-textarea { width:100%; min-height:280px; padding:16px; border:2px solid #FFE0B2; border-radius:10px; font-size:14px; color:#1a1a1a; background:#FFF8F0; outline:none; resize:vertical; font-family:'Courier New', monospace; line-height:1.8; transition:border .2s; }
    .ed-textarea:focus { border-color:#FF6D00; background:white; }
    .ed-toolbar { display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap; }
    .ed-tool { background:#f5f5f5; border:1px solid #e0e0e0; border-radius:6px; padding:5px 12px; font-size:12px; font-weight:600; color:#555; cursor:pointer; transition:all .15s; }
    .ed-tool:hover { background:#FFF3E0; color:#FF6D00; border-color:#FFE0B2; }
    .ed-section-title { font-size:13px; font-weight:700; color:#888; margin-bottom:10px; display:flex; align-items:center; gap:6px; text-transform:uppercase; letter-spacing:.5px; }
    .ed-divider { border:none; border-top:1px solid #f0f0f0; margin:24px 0; }
    .ed-upload-box { border:2px dashed #FFE0B2; border-radius:10px; padding:24px; text-align:center; background:#FFF8F0; cursor:pointer; transition:all .2s; }
    .ed-upload-box:hover { border-color:#FF6D00; background:#FFF3E0; }
    .ed-upload-box input[type=file] { display:none; }
    .ed-preview-imgs { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
    .ed-preview-imgs img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #FFE0B2; }
    .ed-table-wrap { overflow-x:auto; margin-top:8px; }
    .ed-table { width:100%; border-collapse:collapse; font-size:13px; }
    .ed-table th { background:#FF6D00; color:white; padding:9px 14px; text-align:left; font-weight:600; }
    .ed-table td { padding:8px 14px; border-bottom:1px solid #f0f0f0; color:#333; }
    .ed-table td[contenteditable] { outline:none; }
    .ed-table td[contenteditable]:focus { background:#FFF3E0; }
    .ed-table tr:nth-child(even) td { background:#FFF8F0; }
    .ed-actions { display:flex; gap:12px; margin-top:28px; }
    .ed-btn-confirm { flex:1; padding:14px; background:#FF6D00; color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; transition:background .2s; box-shadow:0 4px 16px rgba(255,109,0,0.3); }
    .ed-btn-confirm:hover { background:#e06300; }
    .ed-btn-cancel { padding:14px 20px; background:#f5f5f5; color:#555; border:1px solid #e0e0e0; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; margin-top:0; transition:background .2s; }
    .ed-btn-cancel:hover { background:#e0e0e0; }
    .ed-field { margin-bottom:20px; }
</style>

<div class="ed-header">
    <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="btn-back" style="margin-bottom:0;">← Voltar para a nota</a>
    <span style="font-size:13px;color:#888;">Nova Divisão — {{ $note->title }}</span>
</div>

<form id="sectionForm" action="{{ secure_url(route('notes.section', [$note->id], false)) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    @csrf

    <div class="ed-card">

        {{-- TÍTULO --}}
        <div class="ed-field">
            <label class="ed-label">Título da Divisão</label>
            <input class="ed-input" type="text" name="section_title" placeholder="Ex: Descrição do Problema" required autocomplete="off"/>
        </div>

        {{-- CONTEÚDO --}}
        <div class="ed-field">
            <div class="ed-section-title">📄 Conteúdo</div>
            <div class="ed-toolbar">
                <button type="button" class="ed-tool" onclick="inserirTexto('**', '**')"><strong>B</strong></button>
                <button type="button" class="ed-tool" onclick="inserirTexto('_', '_')"><em>I</em></button>
                <button type="button" class="ed-tool ed-tool-sep" onclick="inserirTexto('\n- ', '')">≡</button>
                <button type="button" class="ed-tool" onclick="inserirTexto('\n---\n', '')">—</button>
                <button type="button" class="ed-tool" onclick="inserirTexto('\n', '')">↵</button>
            </div>
            <textarea class="ed-textarea" id="contentArea" name="section_content" placeholder="Digite o conteúdo da divisão aqui..."></textarea>
        </div>

        <hr class="ed-divider"/>

        {{-- IMAGENS --}}
        <div class="ed-field">
            <div class="ed-section-title">🖼️ Imagens</div>
            <div class="ed-upload-box" onclick="document.getElementById('imgInput').click()">
                <div style="font-size:32px;margin-bottom:8px;">📤</div>
                <div style="font-size:13px;color:#666;">Clique ou arraste imagens aqui</div>
                <div style="font-size:11px;color:#aaa;margin-top:4px;">PNG, JPG, GIF até 10MB cada</div>
                <input type="file" id="imgInput" name="images[]" multiple accept="image/*" onchange="previewImagens(this)"/>
            </div>
            <div class="ed-preview-imgs" id="imgPreview"></div>
        </div>

        <hr class="ed-divider"/>

        {{-- TABELA --}}
        <div class="ed-field">
            <div class="ed-section-title">📊 Tabela / Excel</div>
            <div class="ed-toolbar">
                <button type="button" class="ed-tool" onclick="document.getElementById('xlsxInput').click()">📂 Importar .xlsx</button>
                <button type="button" class="ed-tool" onclick="novaTabela()">➕ Nova tabela</button>
                <button type="button" class="ed-tool" onclick="addLinha()">+ Linha</button>
                <button type="button" class="ed-tool" onclick="addColuna()">+ Coluna</button>
                <input type="file" id="xlsxInput" accept=".xlsx,.xls,.csv" style="display:none" onchange="importarExcel(this)"/>
            </div>
            <div class="ed-table-wrap" id="tableWrap"></div>
            <input type="hidden" name="table_data" id="tableData"/>
        </div>

        <div class="ed-actions">
            <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="ed-btn-cancel">Cancelar</a>
            <button type="button" class="ed-btn-confirm" onclick="confirmarDivisao()">✅ Confirmar Divisão</button>
        </div>

    </div>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let tableHeaders = [];
let tableRows = [];

function inserirTexto(antes, depois) {
    const ta = document.getElementById('contentArea');
    const start = ta.selectionStart;
    const end = ta.selectionEnd;
    const sel = ta.value.substring(start, end);
    ta.value = ta.value.substring(0, start) + antes + sel + depois + ta.value.substring(end);
    ta.focus();
    ta.selectionStart = start + antes.length;
    ta.selectionEnd = start + antes.length + sel.length;
}

function previewImagens(input) {
    const preview = document.getElementById('imgPreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

function novaTabela() {
    tableHeaders = ['Coluna 1', 'Coluna 2', 'Coluna 3'];
    tableRows = [['', '', ''], ['', '', '']];
    renderTabela();
}

function addLinha() {
    if (tableHeaders.length === 0) novaTabela();
    else { tableRows.push(tableHeaders.map(() => '')); renderTabela(); }
}

function addColuna() {
    if (tableHeaders.length === 0) novaTabela();
    else {
        tableHeaders.push('Coluna ' + (tableHeaders.length + 1));
        tableRows.forEach(r => r.push(''));
        renderTabela();
    }
}

function renderTabela() {
    const wrap = document.getElementById('tableWrap');
    if (tableHeaders.length === 0) { wrap.innerHTML = ''; return; }
    let html = '<table class="ed-table"><thead><tr>';
    tableHeaders.forEach((h, i) => {
        html += `<th contenteditable="true" onblur="tableHeaders[${i}]=this.innerText;salvarTabela()">${h}</th>`;
    });
    html += '</tr></thead><tbody>';
    tableRows.forEach((row, ri) => {
        html += '<tr>';
        row.forEach((cell, ci) => {
            html += `<td contenteditable="true" onblur="tableRows[${ri}][${ci}]=this.innerText;salvarTabela()">${cell}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table>';
    wrap.innerHTML = html;
    salvarTabela();
}

function salvarTabela() {
    document.getElementById('tableData').value = JSON.stringify({ headers: tableHeaders, rows: tableRows });
}

function importarExcel(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const wb = XLSX.read(e.target.result, { type: 'binary' });
        const ws = wb.Sheets[wb.SheetNames[0]];
        const data = XLSX.utils.sheet_to_json(ws, { header: 1 });
        if (data.length === 0) return;
        tableHeaders = data[0].map(String);
        tableRows = data.slice(1).map(r => tableHeaders.map((_, i) => String(r[i] ?? '')));
        renderTabela();
    };
    reader.readAsBinaryString(file);
}

function confirmarDivisao() {
    salvarTabela();
    document.getElementById('sectionForm').submit();
}
</script>
@endsection