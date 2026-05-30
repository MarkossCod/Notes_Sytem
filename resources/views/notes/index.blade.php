@extends('layout.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="title">Gerenciamento de Notas</h1>
        <div class="title-underline"></div>
    </div>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Buscar notas...">
        <span class="search-icon">🔍</span>
    </div>
</div>

<div class="notes-grid" id="notesGrid">
    @foreach($notes as $note)
    <div class="card fadeIn" data-title="{{ strtolower($note->title) }}">

        <h2>{{ $note->title }}</h2>

        <p class="card-label">Criado em:</p>
        <div class="card-meta">
            <span class="card-date">
                📅 {{ \Carbon\Carbon::parse($note->created_day)->format('d/m/Y') }}
            </span>
            <span class="card-badge">
                👥 {{ $note->sections->count() }} divisões
            </span>
        </div>

        <a href="{{ secure_url(route('notes.show', [$note->id], false)) }}" class="btn">
            Abrir Nota →
        </a>

    </div>
    @endforeach
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#notesGrid .card').forEach(card => {
            card.style.display = card.dataset.title.includes(query) ? '' : 'none';
        });
    });
</script>

@endsection