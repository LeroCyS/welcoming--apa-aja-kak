@props(['urgency' => null])
@php
    $map = ['normal' => 'sage', 'important' => 'sand', 'urgent' => 'terracotta'];
    $label = \App\Models\Conversation::URGENCY_LABELS[$urgency] ?? \App\Models\Conversation::UNASSESSED_LABEL;
@endphp
<x-badge :tone="$map[$urgency] ?? 'neutral'" dot {{ $attributes }}>{{ $label }}</x-badge>
