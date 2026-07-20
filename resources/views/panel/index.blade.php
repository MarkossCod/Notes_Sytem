@extends('layout.app')

@section('content')
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
        {{-- The responsive chart supports multiple formats without external dependencies. --}}
        <article class="panel-card panel-chart-card">
            <div class="panel-card-heading">
                <div>
                    <span>Movimentações</span>
                    <h2>Atividade nos últimos {{ $period }} dias</h2>
                </div>
                <div class="panel-chart-heading-actions">
                    <div class="panel-chart-types" role="group" aria-label="Formato do gráfico">
                        <button type="button" data-chart-type="bars" aria-pressed="true" title="Gráfico de barras">
                            <span aria-hidden="true">▥</span> Barras
                        </button>
                        <button type="button" data-chart-type="line" aria-pressed="false" title="Gráfico de linha">
                            <span aria-hidden="true">⌁</span> Linha
                        </button>
                        <button type="button" data-chart-type="area" aria-pressed="false" title="Gráfico de área">
                            <span aria-hidden="true">◒</span> Área
                        </button>
                    </div>
                    <strong>{{ $summary['movements'] }}</strong>
                </div>
            </div>

            <div class="panel-chart-wrap">
                <div class="panel-chart"
                     id="panelMovementChart"
                     data-series='@json($chart)'
                     data-granularity="{{ $chartGranularity }}"
                     role="img"
                     aria-label="Gráfico de movimentações {{ $chartGranularity === 'weekly' ? 'semanais' : 'diárias' }}">
                    <svg viewBox="0 0 900 230" preserveAspectRatio="none" aria-hidden="true"></svg>
                </div>
                <p class="panel-chart-caption">
                    {{ $chartGranularity === 'weekly' ? 'Dados agrupados por semana para melhorar a leitura dos 90 dias.' : 'Dados apresentados por dia.' }}
                </p>
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
    // Renders the movement series in the format selected by the user and remembers that preference.
    (() => {
        const chart = document.getElementById('panelMovementChart');
        if (!chart) return;

        const svg = chart.querySelector('svg');
        const series = JSON.parse(chart.dataset.series || '[]');
        const typeButtons = [...document.querySelectorAll('[data-chart-type]')];
        const allowedTypes = ['bars', 'line', 'area'];
        const savedType = localStorage.getItem('notes-panel-chart-type');
        const initialType = allowedTypes.includes(savedType) ? savedType : 'bars';
        const namespace = 'http://www.w3.org/2000/svg';

        const createSvgElement = (tag, attributes = {}, text = '') => {
            const element = document.createElementNS(namespace, tag);
            Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
            if (text !== '') element.textContent = text;
            return element;
        };

        const addTitle = (element, point) => {
            element.appendChild(createSvgElement(
                'title',
                {},
                `${point.tooltip}: ${point.total} movimentação${point.total === 1 ? '' : 'ões'}`,
            ));
        };

        const renderChart = (type) => {
            svg.replaceChildren();

            const width = 900;
            const height = 230;
            const padding = { top: 18, right: 14, bottom: 34, left: 34 };
            const plotWidth = width - padding.left - padding.right;
            const plotHeight = height - padding.top - padding.bottom;
            const baseline = padding.top + plotHeight;
            const maximum = Math.max(1, ...series.map((point) => Number(point.total)));
            const xPosition = (index) => series.length === 1
                ? padding.left + (plotWidth / 2)
                : padding.left + ((plotWidth / Math.max(1, series.length - 1)) * index);
            const yPosition = (total) => padding.top + plotHeight - ((Number(total) / maximum) * plotHeight);

            // Horizontal guides keep all three formats easy to compare.
            [0, .5, 1].forEach((ratio) => {
                const y = padding.top + (plotHeight * ratio);
                svg.appendChild(createSvgElement('line', { x1: padding.left, y1: y, x2: width - padding.right, y2: y, class: 'panel-chart-grid-line' }));
                svg.appendChild(createSvgElement('text', { x: padding.left - 8, y: y + 3, class: 'panel-chart-axis-value' }, String(Math.round(maximum * (1 - ratio)))));
            });

            const points = series.map((point, index) => `${xPosition(index)},${yPosition(point.total)}`).join(' ');

            if (type === 'area' && series.length) {
                svg.appendChild(createSvgElement('polygon', {
                    points: `${padding.left},${baseline} ${points} ${width - padding.right},${baseline}`,
                    class: 'panel-chart-area',
                }));
            }

            if ((type === 'line' || type === 'area') && series.length) {
                svg.appendChild(createSvgElement('polyline', { points, class: 'panel-chart-line' }));
                series.forEach((point, index) => {
                    const marker = createSvgElement('circle', {
                        cx: xPosition(index),
                        cy: yPosition(point.total),
                        r: Number(point.total) > 0 ? 4 : 2.4,
                        class: 'panel-chart-marker',
                    });
                    addTitle(marker, point);
                    svg.appendChild(marker);
                });
            }

            if (type === 'bars') {
                const slotWidth = plotWidth / Math.max(1, series.length);
                const barWidth = Math.max(5, Math.min(28, slotWidth * .56));

                series.forEach((point, index) => {
                    const barHeight = Number(point.total) === 0 ? 0 : Math.max(4, baseline - yPosition(point.total));
                    const bar = createSvgElement('rect', {
                        x: xPosition(index) - (barWidth / 2),
                        y: baseline - barHeight,
                        width: barWidth,
                        height: barHeight,
                        rx: Math.min(5, barWidth / 3),
                        class: 'panel-chart-bar',
                    });
                    addTitle(bar, point);
                    svg.appendChild(bar);
                });
            }

            const labelStep = Math.max(1, Math.ceil(series.length / 6));
            series.forEach((point, index) => {
                if (index !== 0 && index !== series.length - 1 && index % labelStep !== 0) return;
                svg.appendChild(createSvgElement('text', {
                    x: xPosition(index),
                    y: height - 9,
                    class: 'panel-chart-axis-label',
                }, point.label));
            });

            chart.dataset.type = type;
            chart.setAttribute('aria-label', `Gráfico de ${type === 'bars' ? 'barras' : type === 'line' ? 'linha' : 'área'} com movimentações ${chart.dataset.granularity === 'weekly' ? 'semanais' : 'diárias'}.`);
            typeButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.chartType === type)));
        };

        typeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.dataset.chartType;
                localStorage.setItem('notes-panel-chart-type', type);
                renderChart(type);
            });
        });

        renderChart(initialType);
    })();

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
