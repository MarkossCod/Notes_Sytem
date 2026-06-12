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
    .ed-section-title { font-size:13px; font-weight:700; color:#888; margin-bottom:10px; display:flex; align-items:center; gap:6px; text-transform:uppercase; letter-spacing:.5px; }
    .ed-divider { border:none; border-top:1px solid #f0f0f0; margin:24px 0; }
    .ed-upload-box { border:2px dashed #FFE0B2; border-radius:10px; padding:24px; text-align:center; background:#FFF8F0; cursor:pointer; transition:all .2s; }
    .ed-upload-box:hover { border-color:#FF6D00; background:#FFF3E0; }
    .ed-upload-box input[type=file] { display:none; }
    .ed-preview-imgs { display:flex; flex-wrap:wrap; gap:10px; margin-top:12px; }
    .ed-img-wrap { position:relative; }
    .ed-img-wrap img { width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #FFE0B2; display:block; }
    .ed-img-del { position:absolute; top:-6px; right:-6px; background:#e53935; color:white; border:none; border-radius:50%; width:20px; height:20px; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:0; margin:0; line-height:1; }
    .ed-actions { display:flex; gap:12px; margin-top:28px; }
    .ed-btn-confirm { flex:1; padding:14px; background:#FF6D00; color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; transition:background .2s; box-shadow:0 4px 16px rgba(255,109,0,0.3); }
    .ed-btn-confirm:hover { background:#e06300; }
    .ed-btn-cancel { padding:14px 20px; background:#f5f5f5; color:#555; border:1px solid #e0e0e0; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer; margin-top:0; transition:background .2s; }
    .ed-btn-cancel:hover { background:#e0e0e0; }
    .ed-field { margin-bottom:20px; }
    .ed-file-list { margin-top:10px; display:flex; flex-direction:column; gap:6px; }
    .ed-file-item { display:flex; align-items:center; justify-content:space-between; background:#FFF8F0; border:1px solid #FFE0B2; border-radius:8px; padding:8px 12px; font-size:13px; color:#555; }
    .ed-file-del { background:none; border:none; color:#e53935; cursor:pointer; font-size:16px; padding:0; margin:0; width:auto; }
</style>

<div class="ed-header">
    <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="btn-back" style="margin-bottom:0;">← Voltar para a nota</a>
    <span style="font-size:13px;color:#888;">Editar Divisão — {{ $note->title }}</span>
</div>

<form id="editForm" action="{{ secure_url(route('notes.section.update', [$note->id, $section->id], false)) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="ed-card">

        {{-- TÍTULO --}}
        <div class="ed-field">
            <label class="ed-label">Título da Divisão</label>
            <input class="ed-input" type="text" name="section_title" value="{{ $section->section_title }}" required autocomplete="off"/>
        </div>

        {{-- CONTEÚDO --}}
        <div class="ed-field">
            <div class="ed-section-title">📄 Conteúdo</div>
            <textarea class="ed-textarea" name="section_content">{{ $section->section_content }}</textarea>
        </div>

        <hr class="ed-divider"/>

        {{-- IMAGENS EXISTENTES --}}
        @if($section->images && count($section->images) > 0)
        <div class="ed-field">
            <div class="ed-section-title">🖼️ Imagens Existentes</div>
            <div class="ed-preview-imgs" id="existingImgs">
                @foreach($section->images as $i => $img)
                <div class="ed-img-wrap" id="existing-{{ $i }}">
                    <img src="{{ asset('storage/' . $img) }}" alt="imagem"/>
                    <button type="button" class="ed-img-del" onclick="removerImagemExistente({{ $i }}, '{{ $img }}')">✕</button>
                </div>
                @endforeach
            </div>
            <input type="hidden" name="existing_images" id="existingImagesInput" value="{{ json_encode($section->images) }}"/>
        </div>
        <hr class="ed-divider"/>
        @endif

        {{-- NOVAS IMAGENS --}}
        <div class="ed-field">
            <div class="ed-section-title">🖼️ Adicionar Imagens</div>
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
            <div class="ed-section-title">📎 Arquivos (PDF, DOC, etc)</div>
            <div class="ed-upload-box" onclick="document.getElementById('fileInput').click()">
                <div style="font-size:32px;margin-bottom:8px;">📎</div>
                <div style="font-size:13px;color:#666;">Clique para anexar arquivos</div>
                <div style="font-size:11px;color:#aaa;margin-top:4px;">PDF, DOC, DOCX, XLS, XLSX até 20MB</div>
                <input type="file" id="fileInput" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.txt" onchange="previewArquivos(this)"/>
            </div>
            <div class="ed-file-list" id="filePreview"></div>
        </div>

        <div class="ed-actions">
            <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="ed-btn-cancel">Cancelar</a>
            <button type="submit" class="ed-btn-confirm">✅ Salvar Alterações</button>
        </div>

    </div>
</form>

<script>
let existingImages = @json($section->images ?? []);

function removerImagemExistente(index, path) {
    document.getElementById('existing-' + index).remove();
    existingImages = existingImages.filter(img => img !== path);
    document.getElementById('existingImagesInput').value = JSON.stringify(existingImages);
}

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