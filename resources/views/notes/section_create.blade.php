@extends('layout.app')
@section('content')
<div class="ed-header">
    <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="btn-back" style="margin-bottom:0;">Voltar para a nota</a>
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

        {{-- ARQUIVOS --}}
        <div class="ed-field">
            <div class="ed-section-title">📎 Arquivos (PDF, DOC, XLS, etc)</div>
            <div class="ed-upload-box" onclick="document.getElementById('fileInput').click()">
                <div style="font-size:32px;margin-bottom:8px;">📎</div>
                <div style="font-size:13px;color:#666;">Clique para anexar arquivos</div>
                <div style="font-size:11px;color:#aaa;margin-top:4px;">PDF, DOC, DOCX, XLS, XLSX, TXT até 20MB</div>
                <input type="file" id="fileInput" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" onchange="previewArquivos(this)"/>
            </div>
            <div class="ed-file-list" id="filePreview"></div>
        </div>

        <div class="ed-actions">
            <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="ed-btn-cancel">Cancelar</a>
            <button type="submit" class="ed-btn-confirm">✅ Confirmar Divisão</button>
        </div>

    </div>
</form>

<script>
function previewImagens(input) {
    const preview = document.getElementById('imgPreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.className = 'ed-img-wrap';
            wrap.innerHTML = `<img src="${e.target.result}" alt="preview"/>
                <button type="button" class="ed-img-del" onclick="this.parentElement.remove()">✕</button>`;
            preview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}

function previewArquivos(input) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const item = document.createElement('div');
        item.className = 'ed-file-item';
        item.innerHTML = `<span>📄 ${file.name}</span>
            <button type="button" class="ed-file-del" onclick="this.parentElement.remove()">✕</button>`;
        preview.appendChild(item);
    });
}
</script>
@endsection