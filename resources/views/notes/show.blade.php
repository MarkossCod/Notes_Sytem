{{--
    VIEW: visualização e edição de uma nota existente
    FINALIDADE: carregar o editor compartilhado e manter o modal que confirma o envio da nota para a Lixeira.
    DADOS RECEBIDOS: $note e $categories são preparados por NoteController@show.
    EXCLUSÃO: o formulário DELETE chama NoteController@destroy, que usa exclusão lógica e permite restauração.
    AO ALTERAR: mantenha o modal fora do contêiner principal e preserve noteDeleteForm/neDeleteBtn usados pelo JavaScript.
--}}
@extends('layout.app')

@section('content')
    {{-- Reutiliza o editor com os dados persistidos da nota selecionada. --}}
    @include('notes.editor', ['note' => $note])
@endsection

@section('modals')
{{-- O modal fica fora do contêiner animado para cobrir toda a viewport. --}}
<form id="noteDeleteForm" action="{{ secure_url(route('notes.destroy', [$note->id], false)) }}" method="POST" hidden>
    @csrf
    @method('DELETE')
</form>

<div id="noteDeleteModal" class="modal-overlay note-delete-overlay" role="dialog" aria-modal="true" aria-labelledby="noteDeleteTitle">
    <div class="note-delete-confirm-modal" role="document">
        <span class="note-delete-confirm-icon" aria-hidden="true">🗑️</span>
        <h2 id="noteDeleteTitle">Mover nota para a Lixeira?</h2>
        <p>A nota <strong>“{{ $note->title }}”</strong> será removida da sua lista e poderá ser restaurada pela página Lixeira.</p>
        <div class="note-delete-confirm-actions">
            <button type="button" class="ne-btn" data-close-note-delete>Cancelar</button>
            <button type="button" class="ne-btn ne-btn-danger-solid" id="noteDeleteConfirmBtn">Mover para a Lixeira</button>
        </div>
    </div>
</div>

<script>
(function () {
    // Controla a confirmação antes de enviar a nota para a Lixeira.
    const modal = document.getElementById('noteDeleteModal');
    const openButton = document.getElementById('neDeleteBtn');
    const confirmButton = document.getElementById('noteDeleteConfirmBtn');

    function closeNoteDeleteModal() {
        modal.classList.remove('modal-active');
    }

    openButton.addEventListener('click', () => modal.classList.add('modal-active'));
    confirmButton.addEventListener('click', () => document.getElementById('noteDeleteForm').submit());
    modal.addEventListener('click', event => {
        if (event.target === modal || event.target.closest('[data-close-note-delete]')) {
            closeNoteDeleteModal();
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeNoteDeleteModal();
    });
})();
</script>
@endsection
