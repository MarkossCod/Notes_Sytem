@extends('layout.app')

@section('content')

<div class="form-container fadeIn">

    <a href="{{ secure_url(route('notes.index', [], false)) }}" class="btn-back">Voltar</a>

    <h1>Nova Nota</h1>
    <div class="title-underline"></div>

    <form action="{{ secure_url(route('notes.store', [], false)) }}" method="POST" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="title">Título da Nota <span class="required">*</span></label>
            <input type="text"
                   id="title"
                   name="title"
                   placeholder="Digite o título da nota"
                   required>
        </div>

        <div class="form-group">
            <label for="created_day">Dia da Criação <span class="required">*</span></label>
            <input type="date"
                   id="created_day"
                   name="created_day"
                   required>
        </div>

        <button type="submit">Criar Nota</button>

    </form>

</div>

@endsection