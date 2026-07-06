@extends('layout.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="title">Olá, {{ session('user_name') }}! 👋</h1>
        <div class="title-underline"></div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-value">{{ $totalNotes }}</div>
        <div class="stat-label">Total de notas</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🧩</div>
        <div class="stat-value">{{ $totalSections }}</div>
        <div class="stat-label">Total de seções</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-value">{{ $recentNotesCount }}</div>
        <div class="stat-label">Notas nos últimos 7 dias</div>
    </div>
</div>

<div class="page-header" style="margin-top:8px;">
    <div>
        <h2 class="title" style="font-size:18px;">Suas Notas</h2>
        <div class="title-underline"></div>
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
            Abrir Nota
        </a>

    </div>
    @endforeach
</div>

<script>
    function filterNotes(query) {
        query = query.toLowerCase();
        document.querySelectorAll('#notesGrid .card').forEach(card => {
            card.style.display = card.dataset.title.includes(query) ? '' : 'none';
        });
    }
    const topSearch = document.getElementById('topSearchInput');
    if (topSearch) {
        topSearch.addEventListener('input', function () {
            filterNotes(this.value);
        });
    }
</script>

@endsection