{{--
    VIEW: criação de nota
    FINALIDADE: reutilizar notes/editor.blade.php no modo de criação, passando note como null.
    DADOS DISPONÍVEIS: $categories é preparado por NoteController@create e utilizado dentro do editor incluído.
    AO ALTERAR: mudanças no formulário devem ser feitas no editor compartilhado para manter criação e edição consistentes.
--}}
@extends('layout.app')

@section('content')
    {{-- Reutiliza o editor sem uma nota para ativar o modo de criacao. --}}
    @include('notes.editor', ['note' => null])
@endsection
