@props([
    'data' => [],
    'area' => false,
    'curve' => 'smooth',
    'width' => 120,
    'height' => 28,
])

@php
use MajidDs\Support\Charts;

$values = array_values(array_map(floatval(...), $data));

// The sparkline auto-fits its own range — it shows shape, not scale.
$min = min([0.0, ...$values]);
$top = max([1e-9, ...array_map(fn ($v) => $v - $min, $values)]);

$points = Charts::points(array_map(fn ($v) => $v - $min, $values), 3, 3, $width - 6, $height - 6, $top);
$trace = $curve === 'straight' ? Charts::linePath($points) : Charts::splinePath($points);

$gradientId = 'mds-chart-fade-'.substr(md5(json_encode([$values, $width, $height])), 0, 8);
@endphp

{{-- preserveAspectRatio=none + non-scaling strokes: size it with classes and the line stays 2px crisp. --}}
<svg {{ $attributes }} viewBox="0 0 {{ $width }} {{ $height }}" preserveAspectRatio="none" fill="none" role="img" aria-hidden="true" data-mds-chart-sparkline>
    @if ($points !== [])
        @if ($area)
            <defs>
                <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="currentColor" stop-opacity="0.3" />
                    <stop offset="1" stop-color="currentColor" stop-opacity="0" />
                </linearGradient>
            </defs>
            <path d="{{ Charts::closePath($trace, $points[0][0], $points[count($points) - 1][0], $height - 3) }}" fill="url(#{{ $gradientId }})" />
        @endif
        <path d="{{ $trace }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
    @endif
</svg>
