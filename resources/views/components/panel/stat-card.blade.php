{{-- Presents one dashboard metric in a reusable, consistent card. --}}
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
