@props([
    'title',
    'match' => [],      // e.g. ['users.*','orders.*']
    'open' => null,     // force open/closed (null = auto)
])

@php
    $patterns = is_array($match) ? $match : [$match];
    $isMatch = false;
    foreach ($patterns as $p) {
        if (!$p) continue;
        if (request()->routeIs($p) || request()->is($p)) { $isMatch = true; break; }
    }
    $opened = is_null($open) ? $isMatch : (bool) $open;
@endphp

<div {{ $attributes->class([
        'collapse collapse-arrow bg-base-100 rounded-lg border border-base-300/60',
        $opened ? 'collapse-open' : '',
    ]) }}>
    <input type="checkbox" {{ $opened ? 'checked' : '' }} />

    <div class="collapse-title text-sm font-medium flex items-center gap-2">
        @isset($icon)
           {{ $icon }} 
        @endisset
        {{ $title }}
    </div>
    <div class="collapse-content flex flex-col gap-1">
        {{ $slot }}
    </div>
</div>
