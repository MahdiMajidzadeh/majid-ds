@props([
    'data' => [],
    'max' => 100,
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Charts;
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$values = array_map(floatval(...), array_values($data));
$names = array_keys($data);
$n = max(count($values), 3);
$max = max((float) $max, 1e-9);

// A wide viewBox: the side labels sit outside the web and need the room.
$cx = 110.0;
$cy = 78.0;
$r = 50.0;

// First axis at 12 o'clock, the rest clockwise.
$bearing = fn (int $i) => $i * 360 / $n;
$vertex = fn (int $i, float $radius) => Charts::polarPoint($cx, $cy, $radius, $bearing($i));

$ring = function (float $radius) use ($n, $vertex) {
    $points = array_map(fn ($i) => $vertex($i, $radius), range(0, $n - 1));

    return implode(' ', array_map(fn ($p) => Charts::n($p[0]).','.Charts::n($p[1]), $points));
};

$shape = implode(' ', array_map(
    fn ($i) => implode(',', array_map(Charts::n(...), $vertex($i, $r * min($values[$i] ?? 0, $max) / $max))),
    array_keys(array_pad($values, $n, 0)),
));
@endphp

<div {{ $attributes }} data-mds-chart-stage data-mds-chart-radar>
    <svg viewBox="0 0 220 150" fill="none" role="img" aria-hidden="true">
        @foreach ([1, 2, 3, 4] as $level)
            <polygon points="{{ $ring($r * $level / 4) }}" stroke="currentColor" stroke-opacity="0.08" />
        @endforeach

        @foreach (range(0, $n - 1) as $i)
            @php [$sx, $sy] = $vertex($i, $r); @endphp
            <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ Charts::n($sx) }}" y2="{{ Charts::n($sy) }}" stroke="currentColor" stroke-opacity="0.08" />
        @endforeach

        @if ($values !== [])
            <polygon points="{{ $shape }}" fill="currentColor" fill-opacity="0.15" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        @endif

        @foreach ($names as $i => $name)
            @php
            [$lx, $ly] = $vertex($i, $r + 10);
            $sin = sin(deg2rad($bearing($i)));
            $cos = cos(deg2rad($bearing($i)));
            $anchor = abs($sin) < 0.3 ? 'middle' : ($sin > 0 ? 'start' : 'end');
            $ly += $cos > 0.7 ? -2 : ($cos < -0.7 ? 8 : 3);
            @endphp
            <text x="{{ Charts::n($lx) }}" y="{{ Charts::n($ly) }}" text-anchor="{{ $anchor }}" font-size="9" fill="currentColor" fill-opacity="0.45">{{ $fa ? Persian::digits($name) : $name }}</text>
        @endforeach
    </svg>
</div>
