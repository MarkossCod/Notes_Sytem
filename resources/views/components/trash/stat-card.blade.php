{{-- Responsabilidade: renderiza um indicador reutilizavel da Lixeira a partir das propriedades recebidas. --}}
@props(['icon', 'value', 'label', 'footer', 'tone' => 'orange'])

{{-- As propriedades mantem estrutura e cores consistentes em todos os indicadores. --}}
<article class="trash-stat-card">
    <div class="trash-stat-main">
        <span class="trash-stat-icon trash-stat-icon--{{ $tone }}" aria-hidden="true">{{ $icon }}</span>
        <div>
            <strong>{{ $value }}</strong>
            <span>{{ $label }}</span>
        </div>
    </div>
    <div class="trash-stat-footer">{{ $footer }}</div>
</article>
