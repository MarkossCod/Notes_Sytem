@props(['icon', 'value', 'label', 'footer', 'tone' => 'orange'])

{{-- Reusable summary card for trash metrics. --}}
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
