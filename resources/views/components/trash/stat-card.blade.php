{{--
    COMPONENTE: indicador resumido da Lixeira
    FINALIDADE: apresentar quantidade, retenção, espaço e proteção com a mesma estrutura.
    PROPRIEDADES: icon, value, label e footer são obrigatórias; tone assume "orange" quando não for enviada.
    USO: <x-trash.stat-card ... /> em notes/trash.blade.php.
    AO ALTERAR: ajuste @props quando adicionar uma propriedade e atualize todas as chamadas do componente.
--}}
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
