@props([
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the language of the role description; the "n of total" label is
// written by the parent's Alpine component, which knows the slide's index.
$fa ??= config('mds.persian_digits', true);
@endphp

{{-- The width is a share of the track, minus its share of the gaps between
     slides — both come down from the track as custom properties. --}}
<div
    {{ $attributes->class('min-w-0 shrink-0 snap-start') }}
    style="flex: 0 0 calc((100% - (var(--mds-carousel-per-view, 1) - 1) * var(--mds-carousel-gap, 0px)) / var(--mds-carousel-per-view, 1))"
    role="group"
    aria-roledescription="{{ $fa ? 'اسلاید' : 'slide' }}"
    data-mds-carousel-item
>
    {{ $slot }}
</div>
