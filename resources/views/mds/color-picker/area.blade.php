@props([
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in labels' language; inherited from the picker.
$fa ??= config('mds.persian_digits', true);
@endphp

<div
    {{ $attributes->class('relative h-36 w-full cursor-crosshair touch-none rounded-lg border border-black/10 dark:border-white/10') }}
    dir="ltr"
    x-bind:style="`background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, hsl(${h}, 100%, 50%))`"
    x-on:pointerdown.prevent="areaDown($event)"
    role="group"
    aria-label="{{ $fa ? 'اشباع و روشنایی' : 'Saturation and brightness' }}"
    data-mds-color-picker-area
>
    <div
        class="pointer-events-none absolute size-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-md"
        x-bind:style="`left: ${s}%; top: ${100 - v}%; background: ${hex}`"
    ></div>

    {{--
        The area sets two values at once, which no single ARIA role describes —
        role="slider" would be a lie, and dragging is pointer-only. One native
        range per axis instead: real keyboard support, and each announces its
        own name and value. Visually hidden; the thumb above is what you see.
    --}}
    <input
        type="range"
        class="sr-only"
        min="0"
        max="100"
        step="1"
        x-bind:value="Math.round(s)"
        x-on:input="s = +$event.target.value; empty = false; commit()"
        aria-label="{{ $fa ? 'اشباع' : 'Saturation' }}"
        data-mds-color-picker-axis="saturation"
    >

    <input
        type="range"
        class="sr-only"
        min="0"
        max="100"
        step="1"
        x-bind:value="Math.round(v)"
        x-on:input="v = +$event.target.value; empty = false; commit()"
        aria-label="{{ $fa ? 'روشنایی' : 'Brightness' }}"
        data-mds-color-picker-axis="brightness"
    >
</div>
