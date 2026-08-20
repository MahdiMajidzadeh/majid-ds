@props([
    'channel' => 'hue',
])

@if ($channel === 'alpha')
    <input
        type="range"
        min="0"
        max="100"
        step="1"
        dir="ltr"
        {{ $attributes->class('mds-range mds-checker w-full') }}
        x-bind:value="Math.round(a * 100)"
        x-on:input="a = $event.target.value / 100; empty = false; commit()"
        x-bind:style="{ backgroundImage: `linear-gradient(to right, transparent, ${hex}), repeating-conic-gradient(#e4e4e7 0% 25%, #ffffff 0% 50%)`, backgroundSize: 'auto, 10px 10px' }"
        aria-label="شفافیت"
        data-mds-color-picker-slider="alpha"
    >
@else
    <input
        type="range"
        min="0"
        max="360"
        step="1"
        dir="ltr"
        {{ $attributes->class('mds-range w-full') }}
        style="background: linear-gradient(to right, #f00, #ff0, #0f0, #0ff, #00f, #f0f, #f00)"
        x-bind:value="Math.round(h)"
        x-on:input="h = +$event.target.value; empty = false; commit()"
        aria-label="رنگ"
        data-mds-color-picker-slider="hue"
    >
@endif
