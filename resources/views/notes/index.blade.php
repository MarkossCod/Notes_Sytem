@extends('layout.app')

@section('content')

<h1 class="title">
    Gerenciamento de Chamados
</h1>

<div class="notes-grid">

@foreach($notes as $note)

<div class="card fadeIn">

    <h2>{{ $note->title }}</h2>

    <p>
        Criado em:
        {{ $note->created_day }}
    </p>

    <a href="{{ route('notes.show', $note->id) }}" class="btn">
        Abrir Nota
    </a>

</div>

@endforeach

</div>

@endsection