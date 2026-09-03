@props([
    'exclusive' => false,
    'transition' => false,
    'name' => null,
    'fa' => null,
])

@php
// The accordion renders no digits and no strings of its own; fa is accepted
// for parity with the rest of the kit and handed to the items via @aware.
$fa ??= config('mds.persian_digits', true);

// The group name every item's <details name> takes when `exclusive`. An
// explicit name is deterministic and survives without JavaScript. Without
// one, the server writes the shared constant `mds-accordion` (so two unnamed
// exclusive accordions on a page act as ONE group until Alpine starts) and
// each item swaps in a per-accordion `$id()` at init — x-id below scopes it.
$group = $exclusive ? ($name ?? 'mds-accordion') : null;
@endphp

<div
    {{ $attributes->class('divide-y divide-zinc-200 dark:divide-white/10') }}
    @if ($exclusive && $name === null) x-data x-id="['mds-accordion']" @endif
    @if ($exclusive) data-mds-accordion-exclusive @endif
    @if ($exclusive && $name !== null) data-mds-accordion-name="{{ $name }}" @endif
    @if ($transition) data-mds-accordion-transition @endif
    data-mds-accordion
>
    {{ $slot }}
</div>
