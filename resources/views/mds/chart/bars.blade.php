@props([
    'data' => [],
    'labels' => [],
    'secondary' => [],
    'horizontal' => false,
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

$num = fn ($n) => Persian::decimal($n, $fa);

// Each item is a value — or an array of layer values, which stacks it.
$series = array_values(array_map(
    fn ($item) => array_values(array_map(floatval(...), is_array($item) ? $item : [$item])),
    $data,
));
$second = array_values(array_map(floatval(...), $secondary));

$totals = array_map(array_sum(...), $series);
$dataMax = max([1.0, ...$totals, ...$second]);

// The stacked shade ladder: solid base, lighter tones upward.
$shades = fn (int $layers) => match (true) {
    $layers <= 1 => [1.0],
    $layers === 2 => [1.0, 0.35],
    $layers === 3 => [1.0, 0.5, 0.2],
    default => array_map(fn ($i) => round(1 - 0.85 * $i / ($layers - 1), 2), range(0, $layers - 1)),
};
@endphp

@if ($horizontal)
    {{-- Rows are plain HTML, so a funnel follows the page direction — bars grow from the inline start. --}}
    <div {{ $attributes }} data-mds-chart-stage data-mds-chart-bars-rows>
        @foreach ($totals as $i => $value)
            @php $top = $max !== null ? (float) $max : $dataMax; @endphp
            <div data-mds-chart-row>
                @if (isset($labels[$i]))<span data-mds-chart-row-label>{{ $fa ? Persian::digits($labels[$i]) : $labels[$i] }}</span>@endif
                <span data-mds-chart-track><span data-mds-chart-fill style="inline-size: {{ Charts::n(min(100, $value / max($top, 1e-9) * 100)) }}%"></span></span>
                <span data-mds-chart-row-value>{{ $num($value) }}</span>
            </div>
        @endforeach
    </div>
@else
    @php
    $top = $max !== null ? max((float) $max, 1e-9) : Charts::niceMax($dataMax);
    $ticks = [0.0, $top / 4, $top / 2, $top * 3 / 4, $top];

    $x0 = $axis ? 34 : 6;
    $y0 = 10;
    $plotW = $width - $x0 - 8;
    $plotH = $height - $y0 - ($axis && $labels !== [] ? 24 : 10);
    $baseline = $y0 + $plotH;

    $n = max(count($series), 1);
    $slotW = $plotW / $n;
    $grouped = $second !== [];
    $barW = min($grouped ? 12 : 16, $slotW * ($grouped ? 0.3 : 0.4));
    $radius = min(8, $barW / 2);

    $tick = fn (float $t) => Persian::decimal($t, $fa);
    @endphp

    <div {{ $attributes }} data-mds-chart-stage data-mds-chart-bars>
        <svg viewBox="0 0 {{ $width }} {{ $height }}" fill="none" role="img" aria-label="{{ $fa ? 'نمودار ستونی' : 'Bar chart' }}">
            @if ($axis)
                @foreach ($ticks as $t)
                    @php $ty = Charts::n($y0 + $plotH - $plotH * ($t / $top)); @endphp
                    <line x1="{{ $x0 }}" y1="{{ $ty }}" x2="{{ $x0 + $plotW }}" y2="{{ $ty }}" stroke="currentColor" stroke-opacity="0.07" stroke-dasharray="2 2" />
                    <text x="{{ $x0 - 8 }}" y="{{ $ty }}" text-anchor="end" dominant-baseline="middle" font-size="10" fill="currentColor" fill-opacity="0.45">{{ $tick($t) }}</text>
                @endforeach
            @endif

            @foreach ($series as $i => $layers)
                @php $cx = $x0 + $slotW * ($i + 0.5); @endphp

                @php $x = $grouped ? $cx - $barW - 2 : $cx - $barW / 2; @endphp
                @php $y = $baseline; @endphp

                @foreach ($layers as $layer => $value)
                    @php
                    $h = $plotH * min($value, $top) / $top;
                    $y -= $h;
                    $last = count($layers) - 1;
                    // Only the stack's outer ends are rounded; a single layer is a full pill.
                    $radii = match (true) {
                        $last === 0 => [$radius, $radius, $radius, $radius],
                        $layer === 0 => [0, 0, $radius, $radius],
                        $layer === $last => [$radius, $radius, 0, 0],
                        default => [0, 0, 0, 0],
                    };
                    @endphp
                    <path d="{{ Charts::barPath($x, $y, $barW, $h, $radii) }}" fill="currentColor" fill-opacity="{{ $shades(count($layers))[$layer] }}" />
                @endforeach

                @if ($grouped && isset($second[$i]))
                    @php $h = $plotH * min($second[$i], $top) / $top; @endphp
                    <path d="{{ Charts::barPath($cx + 2, $baseline - $h, $barW, $h, [$radius, $radius, $radius, $radius]) }}" fill="currentColor" fill-opacity="0.2" />
                @endif

                @if ($axis && isset($labels[$i]))
                    <text x="{{ Charts::n($cx) }}" y="{{ $height - 6 }}" text-anchor="middle" font-size="10" fill="currentColor" fill-opacity="0.45">{{ $fa ? Persian::digits($labels[$i]) : $labels[$i] }}</text>
                @endif
            @endforeach
        </svg>
    </div>
@endif
