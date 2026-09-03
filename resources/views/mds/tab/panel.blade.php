@props([
    'name' => null,
    'fa' => null,
])

@aware(['value' => null, 'fa' => null])

@php
// fa is accepted for symmetry with the other parts; a panel has no built-in text.
$fa ??= config('mds.persian_digits', true);

$name = (string) $name;

if ($name === '') {
    throw new \InvalidArgumentException('mds:tab.panel needs a name — the tab that controls it is matched by it.');
}

// Hidden on the first paint unless it belongs to the active tab, so the page
// is right before Alpine runs. The tablist rendered before this panel and
// left the active tab's name on the per-request stack; a panel placed BEFORE
// its tablist finds nothing there and falls back to the group's `value` —
// without one it stays visible until Alpine decides.
$registry = app()->bound('mds.tabs') ? app('mds.tabs') : ['pending' => [], 'active' => []];

$hidden = $registry['active'] !== []
    ? end($registry['active']) !== $name
    : ($value !== null && (string) $value !== $name);

$slug = preg_replace('/[^\pL\pN_-]+/u', '-', $name);
@endphp

<div
    {{ $attributes->merge([
        'id' => 'mds-tabpanel-'.$slug,
        'aria-labelledby' => 'mds-tab-'.$slug,
    ])->class('mt-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent') }}
    role="tabpanel"
    tabindex="0"
    @if ($hidden) hidden @endif
    x-bind:hidden="! isActive(@js($name))"
    data-mds-tab-panel="{{ $name }}"
>
    {{ $slot }}
</div>
