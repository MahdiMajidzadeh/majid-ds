@props([
    'name' => null,
    'icon' => null,
    'iconTrailing' => null,
    'disabled' => false,
    'fa' => null,
])

@aware(['value' => null, 'variant' => 'default', 'size' => null, 'fa' => null])

@php
// fa is accepted for symmetry with the other parts; a tab has no built-in text.
$fa ??= config('mds.persian_digits', true);

$name = (string) $name;

if ($name === '') {
    throw new \InvalidArgumentException('mds:tab needs a name — the panel it controls is matched by it.');
}

// Flux writes `icon:trailing`, which is not a name a PHP variable can hold —
// read it off the attribute bag too, then keep it out of the markup...
$iconTrailing ??= $attributes->get('icon:trailing');
$attributes = $attributes->except(['icon:trailing']);

$disabled = (bool) $disabled;

// Is this the active tab, for the first paint? With a `value` on the group,
// only that tab is. Without one, the first ENABLED tab of the list is — and
// since the tabs render in order, "no enabled tab has been queued yet" is
// exactly "I am the first". The tablist resolves the same rule once it has
// the whole list (see tabs.blade.php); this queue is how it gets it.
$registry = app()->bound('mds.tabs') ? app('mds.tabs') : ['pending' => [], 'active' => []];

$enabledSoFar = array_filter($registry['pending'], fn (array $tab) => ! $tab[1]) !== [];

$active = $value !== null
    ? ((string) $value === $name && ! $disabled)
    : (! $disabled && ! $enabledSoFar);

$registry['pending'][] = [$name, $disabled];

app()->instance('mds.tabs', $registry);

// Deterministic ids, derived from the name so the panel can compute the
// same pair without JavaScript. Letters of any script survive — a Persian
// tab name is a valid id fragment; whitespace and punctuation collapse.
$slug = preg_replace('/[^\pL\pN_-]+/u', '-', $name);

$variant = in_array($variant, ['segmented', 'pills'], true) ? $variant : 'default';
$small = $size === 'sm';

$classes = match ($variant) {
    'segmented' => [
        'rounded-md text-zinc-500 enabled:hover:text-zinc-800 dark:text-white/70 dark:enabled:hover:text-white',
        'data-selected:bg-white data-selected:text-zinc-800 data-selected:shadow-xs dark:data-selected:bg-zinc-700 dark:data-selected:text-white',
        $small ? 'px-2.5 py-1 text-xs' : 'px-3 py-1.5 text-sm',
    ],
    'pills' => [
        'rounded-full text-zinc-500 enabled:hover:bg-zinc-800/5 enabled:hover:text-zinc-800 dark:text-white/70 dark:enabled:hover:bg-white/10 dark:enabled:hover:text-white',
        'data-selected:bg-accent/10 data-selected:text-accent-content dark:data-selected:bg-accent/20',
        $small ? 'px-2.5 py-1 text-xs' : 'px-3 py-1.5 text-sm',
    ],
    default => [
        'border-b-2 border-transparent px-1 text-zinc-500 enabled:hover:text-zinc-800 dark:text-white/70 dark:enabled:hover:text-white',
        'data-selected:border-accent data-selected:text-zinc-800 dark:data-selected:text-white',
        $small ? 'h-8 text-xs' : 'h-10 text-sm',
    ],
};
@endphp

<button
    {{ $attributes->merge([
        'id' => 'mds-tab-'.$slug,
        'aria-controls' => 'mds-tabpanel-'.$slug,
    ])->class([
        'inline-flex shrink-0 cursor-pointer items-center gap-2 whitespace-nowrap font-medium transition-colors motion-reduce:transition-none',
        'focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-accent',
        'disabled:cursor-not-allowed disabled:opacity-50',
        ...$classes,
    ]) }}
    type="button"
    role="tab"
    aria-selected="{{ $active ? 'true' : 'false' }}"
    tabindex="{{ $active ? '0' : '-1' }}"
    @if ($active) data-selected @endif
    @if ($disabled) disabled @endif
    x-on:click="select(@js($name))"
    x-bind:aria-selected="isActive(@js($name)) ? 'true' : 'false'"
    x-bind:tabindex="isActive(@js($name)) ? 0 : -1"
    x-bind:data-selected="isActive(@js($name)) ? '' : false"
    data-mds-tab="{{ $name }}"
>
    @if ($icon)
        <mds:icon :icon="$icon" variant="micro" class="size-4 shrink-0" />
    @endif

    <span>{{ $slot }}</span>

    @if ($iconTrailing)
        <mds:icon :icon="$iconTrailing" variant="micro" class="size-4 shrink-0" />
    @endif
</button>
