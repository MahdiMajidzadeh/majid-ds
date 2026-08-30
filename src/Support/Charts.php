<?php

namespace MajidDs\Support;

/**
 * SVG path geometry for the mds:chart family. Pure math, no dependencies —
 * every method returns strings or plain arrays ready to interpolate into an
 * SVG attribute. Coordinates are rounded so the docs builder's rebuilds stay
 * byte-identical.
 */
class Charts
{
    /**
     * Format a coordinate for an SVG attribute: at most two decimals, no
     * trailing zeros, and never "-0".
     */
    public static function n(float $value): string
    {
        $s = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $s === '-0' ? '0' : $s;
    }

    /**
     * Map a series onto plot coordinates: index across [$x0 .. $x0+$width],
     * value up from the baseline ($y0+$height is the zero line, SVG y grows
     * downward). A single point lands mid-plot.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    public static function points(array $values, float $x0, float $y0, float $width, float $height, float $max, float $min = 0): array
    {
        $values = array_values($values);
        $n = count($values);
        $span = max($max - $min, 1e-9);

        return array_map(function ($i, $value) use ($n, $x0, $y0, $width, $height, $min, $span) {
            $x = $n > 1 ? $x0 + $width * $i / ($n - 1) : $x0 + $width / 2;
            $y = $y0 + $height - $height * (((float) $value - $min) / $span);

            return [$x, $y];
        }, array_keys($values), $values);
    }

    /**
     * A straight polyline through the points.
     *
     * @param array<int, array{0: float, 1: float}> $points
     */
    public static function linePath(array $points): string
    {
        $d = '';

        foreach (array_values($points) as $i => [$x, $y]) {
            $d .= ($i === 0 ? 'M' : ' L').static::n($x).' '.static::n($y);
        }

        return $d;
    }

    /**
     * A monotone cubic spline through the points (Fritsch–Carlson tangents,
     * the curve d3 calls curveMonotoneX): smooth, but it never overshoots the
     * data's range — the signature rounded-but-honest chart line.
     *
     * @param array<int, array{0: float, 1: float}> $points
     */
    public static function splinePath(array $points): string
    {
        $points = array_values($points);
        $n = count($points);

        if ($n < 3) {
            return static::linePath($points);
        }

        // Secant slope of each segment, then a tangent per point.
        $h = [];
        $s = [];

        for ($i = 0; $i < $n - 1; $i++) {
            $h[$i] = max($points[$i + 1][0] - $points[$i][0], 1e-9);
            $s[$i] = ($points[$i + 1][1] - $points[$i][1]) / $h[$i];
        }

        $m = [];

        for ($i = 1; $i < $n - 1; $i++) {
            if ($s[$i - 1] * $s[$i] <= 0) {
                // A local extremum — a flat tangent keeps the curve inside the data.
                $m[$i] = 0.0;
            } else {
                $m[$i] = 3 * ($h[$i - 1] + $h[$i]) / ((2 * $h[$i] + $h[$i - 1]) / $s[$i - 1] + ($h[$i] + 2 * $h[$i - 1]) / $s[$i]);
            }
        }

        $m[0] = static::endTangent($s[0], $m[1]);
        $m[$n - 1] = static::endTangent($s[$n - 2], $m[$n - 2]);

        $d = 'M'.static::n($points[0][0]).' '.static::n($points[0][1]);

        for ($i = 0; $i < $n - 1; $i++) {
            $t = $h[$i] / 3;

            $d .= ' C'.static::n($points[$i][0] + $t).' '.static::n($points[$i][1] + $m[$i] * $t)
                .' '.static::n($points[$i + 1][0] - $t).' '.static::n($points[$i + 1][1] - $m[$i + 1] * $t)
                .' '.static::n($points[$i + 1][0]).' '.static::n($points[$i + 1][1]);
        }

        return $d;
    }

    /**
     * One-sided endpoint tangent, kept sign-consistent with its segment so
     * the first and last curve pieces stay monotone too.
     */
    protected static function endTangent(float $secant, float $neighbor): float
    {
        $t = (3 * $secant - $neighbor) / 2;

        return $secant * $t <= 0 ? 0.0 : $t;
    }

    /**
     * Close an open line/spline path down to a baseline for an area fill.
     */
    public static function closePath(string $path, float $left, float $right, float $baseline): string
    {
        return $path.' L'.static::n($right).' '.static::n($baseline).' L'.static::n($left).' '.static::n($baseline).' Z';
    }

    /**
     * A point on a circle. Bearings are degrees clockwise from 12 o'clock —
     * 0 is up, 90 is right — which reads the way a dial does.
     *
     * @return array{0: float, 1: float}
     */
    public static function polarPoint(float $cx, float $cy, float $r, float $bearing): array
    {
        $rad = deg2rad($bearing);

        return [$cx + $r * sin($rad), $cy - $r * cos($rad)];
    }

    /**
     * A circular arc from one bearing to another, clockwise, meant to be
     * stroked (donut segments and gauge dials are stroked arcs with round
     * caps — that is where the pill ends come from).
     */
    public static function arcPath(float $cx, float $cy, float $r, float $from, float $to): string
    {
        [$x1, $y1] = static::polarPoint($cx, $cy, $r, $from);
        [$x2, $y2] = static::polarPoint($cx, $cy, $r, $to);

        $large = ($to - $from) > 180 ? 1 : 0;

        return 'M'.static::n($x1).' '.static::n($y1)
            .' A'.static::n($r).' '.static::n($r).' 0 '.$large.' 1 '.static::n($x2).' '.static::n($y2);
    }

    /**
     * How many degrees a round stroke cap adds past the end of an arc —
     * subtract it from each end to keep visual gaps honest.
     */
    public static function capDegrees(float $strokeWidth, float $r): float
    {
        return rad2deg(($strokeWidth / 2) / max($r, 1e-9));
    }

    /**
     * A rectangle with per-corner radii [top-start, top-end, bottom-end,
     * bottom-start], each clamped to what the box can hold — the pill bars
     * and the stacked bars' outer-ends-only rounding.
     *
     * @param array{0: float, 1: float, 2: float, 3: float} $radii
     */
    public static function barPath(float $x, float $y, float $w, float $h, array $radii): string
    {
        $clamp = fn (float $r) => max(0, min($r, $w / 2, $h / 2));

        [$tl, $tr, $br, $bl] = array_map($clamp, $radii);

        $d = 'M'.static::n($x + $tl).' '.static::n($y);
        $d .= ' H'.static::n($x + $w - $tr);
        $d .= $tr > 0 ? ' A'.static::n($tr).' '.static::n($tr).' 0 0 1 '.static::n($x + $w).' '.static::n($y + $tr) : '';
        $d .= ' V'.static::n($y + $h - $br);
        $d .= $br > 0 ? ' A'.static::n($br).' '.static::n($br).' 0 0 1 '.static::n($x + $w - $br).' '.static::n($y + $h) : '';
        $d .= ' H'.static::n($x + $bl);
        $d .= $bl > 0 ? ' A'.static::n($bl).' '.static::n($bl).' 0 0 1 '.static::n($x).' '.static::n($y + $h - $bl) : '';
        $d .= ' V'.static::n($y + $tl);
        $d .= $tl > 0 ? ' A'.static::n($tl).' '.static::n($tl).' 0 0 1 '.static::n($x + $tl).' '.static::n($y) : '';

        return $d.' Z';
    }

    /**
     * A "nice" axis ceiling: the smallest step from {1, 2, 2.5, 5} × 10^k
     * that covers max/4, times 4 — so ticks land on clean numbers (84 → 100
     * in steps of 25) and never on awkward fractions.
     */
    public static function niceMax(float $max): float
    {
        if ($max <= 0) {
            return 4;
        }

        $target = $max / 4;
        $magnitude = pow(10, floor(log10($target)));

        foreach ([1, 2, 2.5, 5, 10] as $candidate) {
            if ($candidate * $magnitude >= $target - 1e-9) {
                return $candidate * $magnitude * 4;
            }
        }

        return $magnitude * 40;
    }

    /**
     * The tick values 0..niceMax($max), low to high.
     *
     * @return array<int, float>
     */
    public static function ticks(float $max): array
    {
        $top = static::niceMax($max);

        return [0.0, $top / 4, $top / 2, $top * 3 / 4, $top];
    }
}
