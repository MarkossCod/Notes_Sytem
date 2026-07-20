@extends('layout.app')

@section('content')
    {{-- Reutiliza o editor sem uma nota para ativar o modo de criacao. --}}
    @include('notes.editor', ['note' => null])
@endsection
