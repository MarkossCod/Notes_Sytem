@extends('layout.app')

@section('content')
@php($chartMax = max(1, (int) $chart->max('total')))

<main class="panel-page">
    {{-- Header controls the period used by every movement indicator. --}}
    <header class="panel-header">
        <div>
            <span class="panel-kicker">Visão geral</span>
            <h1>Painel de atividades</h1>
            <p>Acompanhe seus resultados e as movimentações realizadas no sistema.</p>
        </div>
        <nav class="panel-periods" aria-label="Período do painel">
            @foreach([7, 30, 90] as $days)
                <a href="{{ secure_url(route('panel.index', ['period' => $days], false)) }}"
                   class="{{ $period === $days ? 'is-active' : '' }}">{{ $days }} dias</a>
            @endforeach
        </nav>
    </header>

    {{-- Current-state indicators complement the historical activity data. --}}
    <section class="panel-stats" aria-label="Indicadores principais">
        <x-panel.stat-card icon="📝" :value="$summary['notes']" label="Notas ativas" hint="Disponíveis na sua lista" />
        <x-panel.stat-card icon="✓" :value="$summary['completed']" label="Notas concluídas" :hint="$summary['completion_rate'].'% de conclusão'" tone="green" />
        <x-panel.stat-card icon="▰" :value="$summary['categories']" label="Categorias" hint="Organização criada" tone="purple" />
        <x-panel.stat-card icon="⌫" :value="$summary['trash']" label="Na lixeira" hint="Itens recuperáveis" tone="red" />
    </section>

    <section class="panel-grid">
        {{-- The responsive bar chart visualizes movement volume without external dependencies. --}}
        <article class="panel-card panel-chart-card">
            <div class="panel-card-heading">
                <div>
                    <span>Movimentações</span>
                    <h2>Atividade nos últimos {{ $period }} dias</h2>
                </div>
                <strong>{{ $summary['movements'] }}</strong>
            </div>

            <div class="panel-chart" role="img" aria-label="Gráfico de movimentações diárias">
                @foreach($chart as $point)
                    <div class="panel-chart-column" title="{{ $point['label'] }}: {{ $point['total'] }} movimentação(ões)">
                        <span class="panel-chart-value">{{ $point['total'] ?: '' }}</span>
                        <i style="--panel-bar-height: {{ max(5, (int) round(($point['total'] / $chartMax) * 100)) }}%"></i>
                        @if($loop->first || $loop->last || ($period <= 7) || ($loop->iteration % max(1, (int) floor($period / 5)) === 0))
                            <small>{{ $point['label'] }}</small>
                        @else
                            <small aria-hidden="true">&nbsp;</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </article>

        {{-- Completion widget translates note status into a direct productivity indicator. --}}
        <article class="panel-card panel-progress-card">
            <div class="panel-card-heading">
                <div>
                    <span>Desempenho</span>
                    <h2>Progresso das notas</h2>
                </div>
            </div>
            <div class="panel-progress-ring" style="--panel-progress: {{ $summary['completion_rate'] }}">
                <div><strong>{{ $summary['completion_rate'] }}%</strong><span>concluído</span></div>
            </div>
            <p><strong>{{ $summary['completed'] }}</strong> de <strong>{{ $summary['notes'] }}</strong> notas foram concluídas.</p>
        </article>
    </section>

    {{-- Activity timeline can be filtered in place while keeping all recent data available. --}}
    <section class="panel-card panel-activity-card">
        <div class="panel-activity-header">
            <div>
                <span class="panel-kicker">Histórico</span>
                <h2>Movimentações recentes</h2>
            </div>
            <div class="panel-filters" role="group" aria-label="Filtrar movimentações">
                <button type="button" class="is-active" data-panel-filter="all">Todas <em>{{ $recentActivities->count() }}</em></button>
                <button type="button" data-panel-filter="notas">Notas <em>{{ $groupTotals['notas'] ?? 0 }}</em></button>
                <button type="button" data-panel-filter="categorias">Categorias <em>{{ $groupTotals['categorias'] ?? 0 }}</em></button>
                <button type="button" data-panel-filter="lixeira">Lixeira <em>{{ $groupTotals['lixeira'] ?? 0 }}</em></button>
                <button type="button" data-panel-filter="acessos">Acessos <em>{{ $groupTotals['acessos'] ?? 0 }}</em></button>
                @if(session('user_role') === 'admin')
                    <button type="button" data-panel-filter="administracao">Admin <em>{{ $groupTotals['administracao'] ?? 0 }}</em></button>
                @endif
            </div>
        </div>

        <div class="panel-timeline" id="panelTimeline">
            @forelse($recentActivities as $activity)
                <article class="panel-activity-item" data-panel-group="{{ $activity->group }}">
                    <span class="panel-activity-icon panel-activity-icon--{{ $activity->group }}" aria-hidden="true">{{ $activity->icon }}</span>
                    <div>
                        <strong>{{ $activity->label }}</strong>
                        <p>{{ $activity->description }}</p>
                    </div>
                    <time datetime="{{ $activity->created_at->toIso8601String() }}">
                        {{ $activity->created_at->isToday() ? 'Hoje' : $activity->created_at->format('d/m/Y') }}
                        <small>{{ $activity->created_at->format('H:i') }}</small>
                    </time>
                </article>
            @empty
                <div class="panel-empty">
                    <span>📊</span>
                    <h3>Seu histórico começa aqui</h3>
                    <p>Crie ou edite uma nota para visualizar as primeiras movimentações.</p>
                    <a href="{{ secure_url(route('notes.create', [], false)) }}">Criar uma nota</a>
                </div>
            @endforelse
        </div>

        <div class="panel-filter-empty" id="panelFilterEmpty" hidden>
            Nenhuma movimentação recente neste filtro.
        </div>
    </section>
</main>

<script>
    // Filters the rendered timeline without another server request.
    document.querySelectorAll('[data-panel-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.panelFilter;
            let visibleItems = 0;

            document.querySelectorAll('[data-panel-filter]').forEach((item) => item.classList.toggle('is-active', item === button));
            document.querySelectorAll('[data-panel-group]').forEach((item) => {
                const isVisible = filter === 'all' || item.dataset.panelGroup === filter;
                item.hidden = !isVisible;
                visibleItems += isVisible ? 1 : 0;
            });

            document.getElementById('panelFilterEmpty').hidden = visibleItems !== 0;
        });
    });
</script>
@endsection
