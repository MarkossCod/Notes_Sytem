{{-- Responsabilidade: inicia o editor compartilhado sem uma nota existente para ativar o modo de criacao. --}}
@extends('layout.app')

@section('content')
    {{-- Reutiliza o editor sem uma nota para ativar o modo de criacao. --}}
    @include('notes.editor', ['note' => null])
@endsection
