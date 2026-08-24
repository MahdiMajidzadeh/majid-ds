<div
    {{ $attributes->class('relative h-36 w-full cursor-crosshair touch-none rounded-lg border border-black/10 dark:border-white/10') }}
    dir="ltr"
    x-bind:style="`background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, hsl(${h}, 100%, 50%))`"
    x-on:pointerdown.prevent="areaDown($event)"
    role="slider"
    aria-label="{{ config('mds.persian_digits', true) ? 'اشباع و روشنایی' : 'Saturation and brightness' }}"
    data-mds-color-picker-area
>
    <div
        class="pointer-events-none absolute size-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-md"
        x-bind:style="`left: ${s}%; top: ${100 - v}%; background: ${hex}`"
    ></div>
</div>
