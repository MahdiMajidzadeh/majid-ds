@props([
    'icon' => null,
    'name' => null,
    'variant' => null,
    'stroke' => null,
    'label' => null,
])

@php
$icon = $name ?? $icon;

// Unlabelled icons are decoration; a label promotes them to real content...
$extra = $label === null
    ? ['aria-hidden' => 'true']
    : ['role' => 'img', 'aria-label' => $label];

$extra['data-mds-icon'] = '';

// Hugeicons hard-codes stroke-width per path (and 453 of the free icons use
// weights other than 1.5 on purpose), so the override is opt-in only...
if ($stroke !== null) {
    $extra['data-mds-icon-stroke'] = '';
    $extra['style'] = trim(($attributes->get('style') ?? '').';--mds-icon-stroke:'.$stroke, '; ');
}

// `variant` takes either a Flux variant (outline/solid/mini/micro) or a
// Hugeicons style (stroke-rounded, solid-sharp, ...) — one prop, so markup
// written against flux:icon keeps working after the swap...
$isStyle = in_array($variant, \MajidDs\Support\Icons::STYLES, true);

// Hugeicons styles mean nothing to flux:icon, and its match() has no default,
// so the fallback always gets one of Flux's four variants...
$fluxVariant = $isStyle || ! $variant ? 'outline' : $variant;

$svg = $icon === null || config('mds.icons.default', 'hugeicons') !== 'hugeicons'
    ? null
    : \MajidDs\Support\Icons::svg($icon, $variant, $attributes->merge($extra)->getAttributes());
@endphp

@if ($svg)
    {!! $svg->toHtml() !!}
@elseif ($icon)
    {{-- No Hugeicons match, or the flux driver is on — heroicons still render... --}}
    <flux:icon :icon="$icon" :variant="$fluxVariant" {{ $attributes->merge($extra) }} />
@endif
