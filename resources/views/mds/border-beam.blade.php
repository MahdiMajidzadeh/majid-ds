@props([
    'color' => null,
    'length' => 10,
    'thickness' => 1,
    'speed' => 5,
    'delay' => 0,
    'revealOn' => null,
    'reveal' => null,
    'showOnTouch' => false,
    'pressScale' => false,
])

@php
/*
| `reveal-on` takes one keyword or several — "hover", "press", "hover press",
| or the same as an array. Anything else is dropped rather than passed into a
| selector, so a typo simply leaves the beam always-on instead of silently
| matching nothing.
*/
$revealOn = match (true) {
    $revealOn === null => [],
    is_array($revealOn) => $revealOn,
    default => preg_split('/[\s,]+/', trim((string) $revealOn), -1, PREG_SPLIT_NO_EMPTY) ?: [],
};

$revealOn = array_values(array_intersect(['hover', 'press'], $revealOn));

// A controlled `reveal` also hides the beam by default — with an empty reveal
// list, so no interaction can show it and only the boolean drives it.
$conditional = $revealOn !== [] || $reveal !== null;

$vars = [
    '--mds-beam-color: '.($color ?? 'var(--color-accent, currentColor)'),
    '--mds-beam-length: '.(float) $length.'%',
    '--mds-beam-thickness: '.(float) $thickness.'px',
    '--mds-beam-speed: '.(float) $speed.'s',
    '--mds-beam-delay: '.(float) $delay.'s',
];
@endphp

<div
    {{ $attributes->class('relative')->merge(['style' => implode('; ', $vars)]) }}
    @if ($conditional) data-mds-beam-reveal="{{ implode(' ', $revealOn) }}" @endif
    @if ($reveal) data-mds-beam-shown @endif
    @if ($showOnTouch) data-mds-beam-touch @endif
    @if ($pressScale) data-mds-beam-press-scale @endif
    data-mds-border-beam
>
    {{ $slot }}

    {{-- Decoration only: never announced, never in the way of a click. --}}
    <span class="mds-beam" aria-hidden="true"></span>
</div>
