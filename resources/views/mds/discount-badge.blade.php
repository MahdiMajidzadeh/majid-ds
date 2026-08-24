@props([
    'percent' => null,
    'amount' => null,
    'original' => null,
    'size' => null,
    'fa' => null,
])

@php
$fa ??= config('mds.persian_digits', true);

if ($percent === null && $amount !== null && $original !== null && $original > 0) {
    $percent = (int) round((1 - $amount / $original) * 100);
}

$percent = (int) $percent;

$classes = match ($size) {
    'sm' => 'px-1.5 py-px text-[10px]',
    'lg' => 'px-2.5 py-1 text-sm',
    default => 'px-2 py-0.5 text-xs',
};
@endphp

<span
    {{ $attributes->class("inline-flex items-center justify-center rounded-full bg-red-500 font-bold text-white tabular-nums {$classes}") }}
    aria-label="{{ $fa ? \MajidDs\Support\Persian::digits($percent).' درصد تخفیف' : $percent.'% off' }}"
    data-mds-discount-badge
>{{ $fa ? \MajidDs\Support\Persian::digits($percent).'٪' : $percent.'%' }}</span>
