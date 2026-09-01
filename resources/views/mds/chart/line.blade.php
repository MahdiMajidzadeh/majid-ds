@props([
    'data' => [],
    'labels' => [],
    'baseline' => [],
    'area' => false,
    'dots' => true,
    'curve' => 'smooth',
    'axis' => true,
    'max' => null,
    'width' => 360,
    'height' => 170,
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Charts;
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$values = array_values(array_map(floatval(...), $data));
$second = array_values(array_map(floatval(...), $baseline));

// One shared y scale: the axis ceiling covers both series unless pinned.
$top = $max !== null ? max((float) $max, 1e-9) : Charts::niceMax(max([1.0, ...$values, ...$second]));
$ticks = [0.0, $top / 4, $top / 2, $top * 3 / 4, $top];

$x0 = $axis ? 34 : 6;
$y0 = 10;
$plotW = $width - $x0 - 8;
$plotH = $height - $y0 - ($axis && $labels !== [] ? 24 : 10);

$points = Charts::points($values, $x0 + 6, $y0, $plotW - 12, $plotH, $top);
$basePoints = Charts::points($second, $x0 + 6, $y0, $plotW - 12, $plotH, $top);

$trace = fn ($pts) => $curve === 'straight' ? Charts::linePath($pts) : Charts::splinePath($pts);

$tick = fn (float $t) => $fa
    ? Persian::number($t, $t == floor($t) ? 0 : 1)
    : number_format($t, $t == floor($t) ? 0 : 1);

// Deterministic gradient id: unique per shape, stable across rebuilds, and a
// same-data collision references an identical definition — harmless.
$gradientId = 'mds-chart-fade-'.substr(md5(json_encode([$values, $width, $height])), 0, 8);
@endphp

<div {{ $attributes }} data-mds-chart-stage data-mds-chart-line>
    <svg viewBox="0 0 {{ $width }} {{ $height }}" fill="none" role="img" aria-hidden="true">
        @if ($area && $points !== [])
            <defs>
                <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="currentColor" stop-opacity="0.3" />
                    <stop offset="1" stop-color="currentColor" stop-opacity="0" />
                </linearGradient>
            </defs>
        @endif

        @if ($axis)
            @foreach ($ticks as $t)
                @php $ty = Charts::n($y0 + $plotH - $plotH * ($t / $top)); @endphp
                <line x1="{{ $x0 }}" y1="{{ $ty }}" x2="{{ $x0 + $plotW }}" y2="{{ $ty }}" stroke="currentColor" stroke-opacity="0.07" stroke-dasharray="3 3" />
                <text x="{{ $x0 - 8 }}" y="{{ $ty }}" text-anchor="end" dominant-baseline="middle" font-size="10" fill="currentColor" fill-opacity="0.45">{{ $tick($t) }}</text>
            @endforeach
        @endif

        @if ($area && $points !== [])
            <path d="{{ Charts::closePath($trace($points), $points[0][0], $points[count($points) - 1][0], $y0 + $plotH) }}" fill="url(#{{ $gradientId }})" />
        @endif

        @if ($basePoints !== [])
            <path d="{{ $trace($basePoints) }}" stroke="currentColor" stroke-opacity="0.4" stroke-width="2" stroke-dasharray="4 4" stroke-linecap="round" stroke-linejoin="round" />
        @endif

        @if ($points !== [])
            <path d="{{ $trace($points) }}" stroke="currentColor" stroke-width="{{ $area ? '2.5' : '3' }}" stroke-linecap="round" stroke-linejoin="round" />
        @endif

        @if ($dots)
            @foreach ($points as [$px, $py])
                <circle cx="{{ Charts::n($px) }}" cy="{{ Charts::n($py) }}" r="4" fill="currentColor" stroke-width="2" data-mds-chart-dot />
            @endforeach
        @endif

        @if ($axis)
            @foreach (array_values($labels) as $i => $categoryLabel)
                @if (isset($points[$i]))
                    <text x="{{ Charts::n($points[$i][0]) }}" y="{{ $height - 6 }}" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity="0.45">{{ $fa ? Persian::digits($categoryLabel) : $categoryLabel }}</text>
                @endif
            @endforeach
        @endif
    </svg>
</div>
