@props([
    'route' => null,
    'href' => null,
    'match' => null,
    'size' => 'md', // sm|md|lg
    'activeClass' => 'btn-active',
    'inactiveClass' => '',
])

@php
    $patterns = [];
    if ($route)   { $patterns[] = $route; }
    if ($match)   { $patterns = array_merge($patterns, is_array($match) ? $match : [$match]); }

    $active = false;
    foreach ($patterns as $p) {
        if (request()->routeIs($p) || request()->is($p)) { $active = true; break; }
    }

    if (!$active && $href) {
        $path = parse_url($href, PHP_URL_PATH) ?? $href;
        if ($path) {
            $active = request()->is(ltrim($path, '/')) || request()->is(ltrim($path, '/').'/*');
        }
    }

    $url = $href ?? ($route ? route($route) : '#');
    $sizeClass = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ][$size] ?? '';
@endphp

<a href="{{ $url }}" wire:navigate
   {{ $attributes->class([
        'btn btn-ghost justify-start w-full rounded-lg',
        $sizeClass,
        $active ? $activeClass : $inactiveClass,
   ]) }}>
   {{ $slot }}
</a>
