@props(['status'])

@php
    $s = strtolower((string) $status);
    $map = [
        'open'     => 'badge-info',
        'pending'  => 'badge-warning',
        'approved' => 'badge-success',
        'rejected' => 'badge-error',
        // fallback:
        'default'  => 'badge-ghost',
    ];
    $cls = $map[$s] ?? $map['default'];
@endphp

<span {{ $attributes->merge(['class' => "badge $cls"]) }}>
    {{ ucfirst($s) }}
</span>