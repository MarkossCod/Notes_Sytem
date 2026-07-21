{{--
    COMPONENTE: indicador resumido do Painel
    FINALIDADE: evitar a repetição da mesma estrutura visual nos quatro indicadores principais.
    DADOS RECEBIDOS: $icon, $value e $label são obrigatórios; $hint e $tone são opcionais.
    USO: <x-panel.stat-card ... /> em panel/index.blade.php.
    AO ALTERAR: mantenha os nomes das propriedades; novas cores também precisam de uma classe correspondente no CSS.
--}}
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
