@props([
    'data' => [],
    'value' => null,
    'label' => null,
    'legend' => true,
    'size' => 160,
    'thickness' => 22,
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Charts;
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$values = array_map(floatval(...), array_values($data));
$names = array_keys($data);
$total = max(array_sum($values), 1e-9);

$c = $size / 2;
$r = $c - 12 - $thickness / 2;

// Round stroke caps overhang the arc ends; shave that plus half the visual
// gap off each side so segments read as separated pills.
$trim = Charts::capDegrees($thickness, $r) + 3;

// The tone ladder: the source's four steps, or an even spread for more.
$count = count($values);
$shades = $count <= 4
    ? array_slice([1.0, 0.7, 0.4, 0.2], 0, max($count, 1))
    : array_map(fn ($i) => round(1 - 0.85 * $i / ($count - 1), 2), range(0, $count - 1));

$centerValue = $value ?? array_sum($values);
$centerText = Persian::auto($centerValue, $fa);

// role="img" makes the SVG's own <text> presentational, so the center
// value has to travel in the accessible name.
$ariaLabel = ($label !== null ? $label.($fa ? '، ' : ', ') : '').$centerText;
@endphp

<div {{ $attributes }} data-mds-chart-stage data-mds-chart-donut>
    <svg viewBox="0 0 {{ $size }} {{ $size }}" fill="none" role="img" aria-label="{{ $ariaLabel }}">
        @php $bearing = 0.0; @endphp
        @foreach ($values as $i => $segment)
            @php
            $sweep = 360 * $segment / $total;
            $from = $bearing + $trim;
            $to = $bearing + $sweep - $trim;
            $bearing += $sweep;
            @endphp
            @if ($segment > 0)
                <path d="{{ Charts::arcPath($c, $c, $r, $from, max($to, $from + 0.5)) }}" stroke="currentColor" stroke-opacity="{{ $shades[$i] }}" stroke-width="{{ $thickness }}" stroke-linecap="round" />
            @endif
        @endforeach

        <text x="{{ $c }}" y="{{ $c - 1 }}" text-anchor="middle" font-size="18" font-weight="700" fill="currentColor">{{ $centerText }}</text>
        @if ($label !== null)
            <text x="{{ $c }}" y="{{ $c + 15 }}" text-anchor="middle" font-size="9" fill="currentColor" fill-opacity="0.55">{{ $label }}</text>
        @endif
    </svg>

    @if ($legend && $names !== [])
        <div data-mds-chart-legend>
            @foreach ($names as $i => $name)
                <span data-mds-chart-legend-item><span data-mds-chart-legend-dot style="opacity: {{ $shades[$i] }}"></span><span data-mds-chart-legend-name>{{ $fa ? Persian::digits($name) : $name }}</span></span>
            @endforeach
        </div>
    @endif
</div>
