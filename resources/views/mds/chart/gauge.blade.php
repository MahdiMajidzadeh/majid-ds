@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'decimals' => 0,
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Charts;
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$max = max((float) $max, 1e-9);
$ratio = min(max((float) $value / $max, 0), 1);

// A 240° dial, open at the bottom: bearings −120 … +120 around (80, 92).
$cx = 80.0;
$cy = 92.0;
$r = 63.0;
$stroke = 18;

$trim = Charts::capDegrees($stroke, $r);
$start = -120 + $trim;
$end = 120 - $trim;
$split = -120 + 240 * $ratio;

$valueTo = max(min($split - $trim, $end), $start + 0.5);
$restFrom = $split + 4 + $trim;

$text = $fa ? Persian::number($value, $decimals) : number_format((float) $value, $decimals);
@endphp

<div {{ $attributes }} data-mds-chart-stage data-mds-chart-gauge>
    <svg viewBox="0 0 160 130" fill="none" role="img" aria-hidden="true">
        @if ($ratio > 0)
            <path d="{{ Charts::arcPath($cx, $cy, $r, $start, $valueTo) }}" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" />
        @endif

        @if ($restFrom < $end)
            <path d="{{ Charts::arcPath($cx, $cy, $r, $ratio > 0 ? $restFrom : $start, $end) }}" stroke="currentColor" stroke-opacity="0.1" stroke-width="{{ $stroke }}" stroke-linecap="round" />
        @endif

        <text x="{{ $cx }}" y="{{ $cy - 4 }}" text-anchor="middle" font-size="22" font-weight="700" fill="currentColor">{{ $text }}</text>
        @if ($label !== null)
            <text x="{{ $cx }}" y="{{ $cy + 12 }}" text-anchor="middle" font-size="9" fill="currentColor" fill-opacity="0.55">{{ $label }}</text>
        @endif
    </svg>
</div>
