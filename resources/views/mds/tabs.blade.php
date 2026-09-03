@props([
    'variant' => 'default',
    'size' => null,
    'label' => null,
    'name' => null,
    'fa' => null,
])

@aware(['value' => null, 'fa' => null])

@php
// fa picks the built-in label's language; inherited from the group.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'زبانه‌ها' : 'Tabs';

$variant = in_array($variant, ['segmented', 'pills'], true) ? $variant : 'default';
$size = $size === 'sm' ? 'sm' : 'default';

// The tabs in the slot have already rendered and queued themselves, in
// order, on the per-request registry. Take the queue, decide the active tab
// the way each tab decided for itself — the group's `value` when it names an
// enabled tab, else the first enabled one — and leave the answer on the
// stack for the panels, which render next. The group pops it.
$registry = app()->bound('mds.tabs') ? app('mds.tabs') : ['pending' => [], 'active' => []];

$enabled = array_map(
    fn (array $tab) => $tab[0],
    array_filter($registry['pending'], fn (array $tab) => ! $tab[1]),
);

$active = $value !== null && in_array((string) $value, $enabled, true)
    ? (string) $value
    : (array_values($enabled)[0] ?? null);

$registry['pending'] = [];
$registry['active'][] = $active;

app()->instance('mds.tabs', $registry);

$classes = match ($variant) {
    // A track the active tab sits on as a raised pill...
    'segmented' => 'inline-flex max-w-full gap-1 rounded-lg bg-zinc-800/5 p-1 dark:bg-white/10',
    'pills' => 'flex gap-1',
    // The rule under the tabs is an inset shadow rather than a border: the
    // list scrolls (overflow-x), which would clip a tab's underline hanging
    // one pixel below it, while a child's border still paints over an inset
    // shadow of its parent — so the active underline covers the rule...
    default => 'flex gap-4 shadow-[inset_0_-1px_0_0_var(--color-zinc-200)] dark:shadow-[inset_0_-1px_0_0_rgb(255_255_255/0.1)]',
};
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([
        $classes,
        'overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden',
    ]) }}
    x-on:keydown="keydown($event)"
    role="tablist"
    aria-orientation="horizontal"
    aria-label="{{ $label }}"
    data-mds-tabs
    data-mds-tabs-variant="{{ $variant }}"
    data-mds-tabs-size="{{ $size }}"
>
    {{-- The bound control: Livewire reads the active tab's name from here, and every change dispatches `input` on it. --}}
    <input
        type="hidden"
        x-ref="tabsInput"
        value="{{ $active }}"
        @if ($name) name="{{ $name }}" @endif
        data-mds-tabs-input
        {{ $attributes->whereStartsWith('wire:model') }}
    >

    {{ $slot }}
</div>
