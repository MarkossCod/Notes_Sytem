{{--
    VIEW: Painel de atividades
    FINALIDADE: exibir indicadores, gráfico de movimentações, progresso das notas e histórico recente do usuário.
    DADOS RECEBIDOS: $summary contém totais; $chart contém a série; $recentActivities contém o histórico; $period define 7, 30 ou 90 dias.
    ORIGEM DOS DADOS: PanelController@index. O formato do gráfico e os filtros do histórico são aplicados no navegador.
    AO ALTERAR: mantenha o objeto chart compatível com renderChart() e prefira calcular novas métricas no controlador.
--}}
@extends('layout.app')

@section('content')
<main class="panel-page">
    {{-- O periodo selecionado controla todos os indicadores de movimentacao. --}}
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

    {{-- Os indicadores atuais complementam o historico apresentado abaixo. --}}
    <section class="panel-stats" aria-label="Indicadores principais">
        <x-panel.stat-card icon="📝" :value="$summary['notes']" label="Notas ativas" :hint="$summary['latest_note_date'] ? 'Última criada em '.$summary['latest_note_date'] : 'Nenhuma nota criada'" />
        <x-panel.stat-card icon="✓" :value="$summary['completed']" label="Notas concluídas" :hint="$summary['completion_rate'].'% de conclusão'" tone="green" />
        <x-panel.stat-card icon="▰" :value="$summary['categories']" label="Categorias" :hint="$summary['latest_category_date'] ? 'Última criada em '.$summary['latest_category_date'] : 'Nenhuma categoria criada'" tone="purple" />
        <x-panel.stat-card icon="⌫" :value="$summary['trash']" label="Na lixeira" hint="Itens recuperáveis" tone="red" />
    </section>

    <section class="panel-grid">
        {{-- O grafico responsivo suporta barras, linha e area sem dependencia externa. --}}
        <article class="panel-card panel-chart-card">
            <div class="panel-card-heading">
                <div>
                    <span>Movimentações</span>
                    <h2>Atividade nos últimos {{ $period }} dias</h2>
                </div>
                <div class="panel-chart-heading-actions">
                    <label class="panel-chart-metric">
                        <span>Dados exibidos</span>
                        <select id="panelChartMetric" aria-label="Informação exibida no gráfico">
                            <option value="movements">Movimentações</option>
                            <option value="notes">Notas criadas</option>
                            <option value="categories">Categorias criadas</option>
                        </select>
                    </label>
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
                    <strong id="panelChartTotal" data-totals='@json($chartTotals)'>{{ $chartTotals['movements'] }}</strong>
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
                <p class="panel-chart-caption" id="panelChartCaption">
                    Movimentações {{ $chartGranularity === 'weekly' ? 'agrupadas por semana.' : 'apresentadas por dia.' }}
                </p>
            </div>
        </article>

        {{-- O progresso converte o status das notas em um indicador direto de conclusao. --}}
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

    {{-- O historico pode ser filtrado localmente sem descartar os dados recentes recebidos. --}}
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
    // Limpa o SVG, calcula a escala e chama o desenho de barras, linha ou área. O formato escolhido é salvo no localStorage.
    (() => {
        const chart = document.getElementById('panelMovementChart');
        if (!chart) return;

        const svg = chart.querySelector('svg');
        const series = JSON.parse(chart.dataset.series || '[]');
        const typeButtons = [...document.querySelectorAll('[data-chart-type]')];
        const metricSelect = document.getElementById('panelChartMetric');
        const totalLabel = document.getElementById('panelChartTotal');
        const caption = document.getElementById('panelChartCaption');
        const totals = JSON.parse(totalLabel.dataset.totals || '{}');
        const allowedTypes = ['bars', 'line', 'area'];
        const allowedMetrics = ['movements', 'notes', 'categories'];
        const metricLabels = {
            movements: ['Movimentações', 'movimentação', 'movimentações'],
            notes: ['Notas criadas', 'nota criada', 'notas criadas'],
            categories: ['Categorias criadas', 'categoria criada', 'categorias criadas'],
        };
        const savedType = localStorage.getItem('notes-panel-chart-type');
        const savedMetric = localStorage.getItem('notes-panel-chart-metric');
        const initialType = allowedTypes.includes(savedType) ? savedType : 'bars';
        let currentMetric = allowedMetrics.includes(savedMetric) ? savedMetric : 'movements';
        const namespace = 'http://www.w3.org/2000/svg';

        const createSvgElement = (tag, attributes = {}, text = '') => {
            const element = document.createElementNS(namespace, tag);
            Object.entries(attributes).forEach(([name, value]) => element.setAttribute(name, value));
            if (text !== '') element.textContent = text;
            return element;
        };

        const addTitle = (element, point, metric) => {
            const total = Number(point[metric] || 0);
            element.appendChild(createSvgElement(
                'title',
                {},
                `${point.tooltip}: ${total} ${total === 1 ? metricLabels[metric][1] : metricLabels[metric][2]}`,
            ));
        };

        const renderChart = (type, metric = currentMetric) => {
            svg.replaceChildren();
            currentMetric = metric;

            const width = 900;
            const height = 230;
            const padding = { top: 18, right: 14, bottom: 34, left: 34 };
            const plotWidth = width - padding.left - padding.right;
            const plotHeight = height - padding.top - padding.bottom;
            const baseline = padding.top + plotHeight;
            const maximum = Math.max(1, ...series.map((point) => Number(point[metric] || 0)));
            const xPosition = (index) => series.length === 1
                ? padding.left + (plotWidth / 2)
                : padding.left + ((plotWidth / Math.max(1, series.length - 1)) * index);
            const yPosition = (total) => padding.top + plotHeight - ((Number(total) / maximum) * plotHeight);

            // As guias horizontais mantem os tres formatos visualmente comparaveis.
            [0, .5, 1].forEach((ratio) => {
                const y = padding.top + (plotHeight * ratio);
                svg.appendChild(createSvgElement('line', { x1: padding.left, y1: y, x2: width - padding.right, y2: y, class: 'panel-chart-grid-line' }));
                svg.appendChild(createSvgElement('text', { x: padding.left - 8, y: y + 3, class: 'panel-chart-axis-value' }, String(Math.round(maximum * (1 - ratio)))));
            });

            const points = series.map((point, index) => `${xPosition(index)},${yPosition(point[metric] || 0)}`).join(' ');

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
                        cy: yPosition(point[metric] || 0),
                        r: Number(point[metric] || 0) > 0 ? 4 : 2.4,
                        class: 'panel-chart-marker',
                    });
                    addTitle(marker, point, metric);
                    svg.appendChild(marker);
                });
            }

            if (type === 'bars') {
                const slotWidth = plotWidth / Math.max(1, series.length);
                const barWidth = Math.max(5, Math.min(28, slotWidth * .56));

                series.forEach((point, index) => {
                    const value = Number(point[metric] || 0);
                    const barHeight = value === 0 ? 0 : Math.max(4, baseline - yPosition(value));
                    const bar = createSvgElement('rect', {
                        x: xPosition(index) - (barWidth / 2),
                        y: baseline - barHeight,
                        width: barWidth,
                        height: barHeight,
                        rx: Math.min(5, barWidth / 3),
                        class: 'panel-chart-bar',
                    });
                    addTitle(bar, point, metric);
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
            const intervalLabel = chart.dataset.granularity === 'weekly' ? 'agrupadas por semana' : 'apresentadas por dia';
            chart.setAttribute('aria-label', `Gráfico de ${type === 'bars' ? 'barras' : type === 'line' ? 'linha' : 'área'} com ${metricLabels[metric][0].toLowerCase()} ${intervalLabel}.`);
            totalLabel.textContent = totals[metric] || 0;
            caption.textContent = `${metricLabels[metric][0]} ${intervalLabel}.`;
            metricSelect.value = metric;
            typeButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.chartType === type)));
        };

        typeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.dataset.chartType;
                localStorage.setItem('notes-panel-chart-type', type);
                renderChart(type, currentMetric);
            });
        });

        metricSelect.addEventListener('change', () => {
            currentMetric = metricSelect.value;
            localStorage.setItem('notes-panel-chart-metric', currentMetric);
            renderChart(chart.dataset.type || initialType, currentMetric);
        });

        renderChart(initialType, currentMetric);
    })();

    // Mostra somente itens cujo data-group corresponde ao filtro; "all" restaura todos sem consultar o backend.
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
