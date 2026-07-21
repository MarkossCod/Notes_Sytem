{{-- Responsabilidade: renderiza um indicador reutilizavel do Painel com icone, valor, rotulo, dica e tonalidade. --}}
{{-- A dica e opcional; a tonalidade assume laranja quando nao for informada. --}}
<article class="panel-stat-card panel-stat-card--{{ $tone ?? 'orange' }}">
    <span class="panel-stat-icon" aria-hidden="true">{{ $icon }}</span>
    <div>
        <strong>{{ $value }}</strong>
        <span>{{ $label }}</span>
        @isset($hint)
            <small>{{ $hint }}</small>
        @endisset
    </div>
</article>
