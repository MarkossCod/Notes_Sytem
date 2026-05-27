@extends('layout.app')

@section('content')

<div class="note-page">

    <h1>{{ $note->title }}</h1>
    <p>Data: {{ $note->created_day }}</p>

    <div class="sections">
        @foreach($note->sections as $section)
            <div class="section-card {{ $section->completed ? 'section-completed' : '' }}">
                <h2>{{ $section->section_title }}</h2>
                <p>{{ $section->section_content }}</p>

                <div class="section-actions">

                    {{-- Botão Editar --}}
                    <a href="{{ route('notes.section.edit', [$note->id, $section->id]) }}"
                       class="btn-edit">
                        Editar
                    </a>

                    {{-- Botão Concluir --}}
                    <form action="{{ route('notes.section.complete', [$note->id, $section->id]) }}"
                          method="POST" style="display:inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-complete">
                            {{ $section->completed ? 'Reabrir' : 'Concluir' }}
                        </button>
                    </form>

                </div>
            </div>
        @endforeach
    </div>

    <hr>

    {{-- Formulário de Adicionar / Editar --}}
    @isset($editingSection)
        <h2>Editar Divisão</h2>
        <form action="{{ route('notes.section.update', [$note->id, $editingSection->id]) }}"
              method="POST">
            @csrf
            @method('PUT')

            <input type="text"
                   name="section_title"
                   value="{{ $editingSection->section_title }}"
                   placeholder="Título da Divisão"
                   required>

            <textarea name="section_content"
                      placeholder="Conteúdo">{{ $editingSection->section_content }}</textarea>

            <button type="submit">Salvar</button>
            <a href="{{ route('notes.show', $note->id) }}" class="btn-cancel">Cancelar</a>
        </form>
    @else
        <h2>Adicionar Divisão</h2>
        <form action="{{ route('notes.section', $note->id) }}" method="POST">
            @csrf

            <input type="text"
                   name="section_title"
                   placeholder="Título da Divisão"
                   required>

            <textarea name="section_content"
                      placeholder="Conteúdo"></textarea>

            <button type="submit">Adicionar</button>
        </form>
    @endisset

</div>

@endsection