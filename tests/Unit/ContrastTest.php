<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Tests\TestCase;

/**
 * The coloured chips — timeline indicators, the discount badge — pair a
 * Tailwind background with a text colour by hand, and 16 of the 18 indicator
 * hues once shipped white on a 500 at under 4.5:1 (yellow and lime near
 * 1.9:1). Every pair here is held to WCAG AA against the real palette, so a
 * new hue, a changed shade, or a Tailwind palette shift fails the build.
 */
class ContrastTest extends TestCase
{
    private const AA = 4.5;

    public function test_timeline_indicator_chips_meet_aa_in_both_modes(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/timeline/indicator.blade.php');

        preg_match_all("/'([a-z]+)' => '([^']+)',/", $source, $arms, PREG_SET_ORDER);

        $this->assertCount(18, $arms, 'Expected the eighteen colour arms.');

        foreach ($arms as [, $hue, $classes]) {
            foreach ($this->pairs($classes) as $mode => [$bg, $text]) {
                $ratio = $this->contrast($bg, $text);

                $this->assertGreaterThanOrEqual(
                    self::AA,
                    $ratio,
                    sprintf('timeline.indicator color="%s" (%s): %s on %s is %.2f:1, under AA.', $hue, $mode, $text, $bg, $ratio),
                );
            }
        }
    }

    public function test_discount_badge_meets_aa_in_both_modes(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/discount-badge.blade.php');

        preg_match('/class\("([^"]+)"\)/', $source, $m);

        $this->assertNotEmpty($m, 'Could not find the badge class string.');

        $pairs = $this->pairs($m[1]);

        $this->assertArrayHasKey('light', $pairs);
        $this->assertArrayHasKey('dark', $pairs, 'The badge used to have no dark: variant at all.');

        foreach ($pairs as $mode => [$bg, $text]) {
            $ratio = $this->contrast($bg, $text);

            $this->assertGreaterThanOrEqual(self::AA, $ratio, sprintf('discount-badge (%s): %s on %s is %.2f:1, under AA.', $mode, $text, $bg, $ratio));
        }
    }

    /**
     * The light and dark (bg, text) pairs named in a class string.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function pairs(string $classes): array
    {
        $pairs = [];

        if (preg_match('/(?<!:)\bbg-([a-z]+-\d+)\b/', $classes, $bg) && preg_match('/(?<!:)\btext-(white|[a-z]+-\d+)\b/', $classes, $text)) {
            $pairs['light'] = [$bg[1], $text[1]];
        }

        if (preg_match('/dark:bg-([a-z]+-\d+)\b/', $classes, $bg) && preg_match('/dark:text-(white|[a-z]+-\d+)\b/', $classes, $text)) {
            $pairs['dark'] = [$bg[1], $text[1]];
        }

        return $pairs;
    }

    private function contrast(string $a, string $b): float
    {
        $la = $this->luminance($a);
        $lb = $this->luminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /**
     * WCAG relative luminance from a Tailwind swatch name. Tailwind v4 defines
     * its palette in OKLCH; this converts to linear sRGB (clipping to gamut as
     * a browser does) and weights the channels.
     */
    private function luminance(string $swatch): float
    {
        if ($swatch === 'white') {
            return 1.0;
        }

        $this->assertArrayHasKey($swatch, self::PALETTE, "No palette entry for {$swatch} — add it from node_modules/tailwindcss/theme.css.");

        [$L, $C, $h] = self::PALETTE[$swatch];

        $a = $C * cos(deg2rad($h));
        $b = $C * sin(deg2rad($h));

        $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

        $clip = fn (float $c): float => min(1.0, max(0.0, $c));

        $r = $clip(+4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s);
        $g = $clip(-1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s);
        $bl = $clip(-0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $bl;
    }

    /**
     * OKLCH [L (0..1), C, h] per swatch — the shades the chips use, copied from
     * tailwindcss 4.3.3 theme.css so the test needs no node_modules.
     *
     * @var array<string, array{0: float, 1: float, 2: float}>
     */
    private const PALETTE = [
        'amber-400' => [0.828, 0.189, 84.429],
        'amber-500' => [0.769, 0.188, 70.08],
        'amber-600' => [0.666, 0.179, 58.318],
        'amber-950' => [0.279, 0.077, 45.635],
        'blue-400' => [0.707, 0.165, 254.624],
        'blue-500' => [0.623, 0.214, 259.815],
        'blue-600' => [0.546, 0.245, 262.881],
        'blue-950' => [0.282, 0.091, 267.935],
        'cyan-400' => [0.789, 0.154, 211.53],
        'cyan-500' => [0.715, 0.143, 215.221],
        'cyan-600' => [0.609, 0.126, 221.723],
        'cyan-950' => [0.302, 0.056, 229.695],
        'emerald-400' => [0.765, 0.177, 163.223],
        'emerald-500' => [0.696, 0.17, 162.48],
        'emerald-600' => [0.596, 0.145, 163.225],
        'emerald-950' => [0.262, 0.051, 172.552],
        'fuchsia-400' => [0.740, 0.238, 322.16],
        'fuchsia-500' => [0.667, 0.295, 322.15],
        'fuchsia-600' => [0.591, 0.293, 322.896],
        'fuchsia-950' => [0.293, 0.136, 325.661],
        'green-400' => [0.792, 0.209, 151.711],
        'green-500' => [0.723, 0.219, 149.579],
        'green-600' => [0.627, 0.194, 149.214],
        'green-950' => [0.266, 0.065, 152.934],
        'indigo-400' => [0.673, 0.182, 276.935],
        'indigo-500' => [0.585, 0.233, 277.117],
        'indigo-600' => [0.511, 0.262, 276.966],
        'indigo-950' => [0.257, 0.09, 281.288],
        'lime-400' => [0.841, 0.238, 128.85],
        'lime-500' => [0.768, 0.233, 130.85],
        'lime-600' => [0.648, 0.2, 131.684],
        'lime-950' => [0.274, 0.072, 132.109],
        'orange-400' => [0.750, 0.183, 55.934],
        'orange-500' => [0.705, 0.213, 47.604],
        'orange-600' => [0.646, 0.222, 41.116],
        'orange-950' => [0.266, 0.079, 36.259],
        'pink-400' => [0.718, 0.202, 349.761],
        'pink-500' => [0.656, 0.241, 354.308],
        'pink-600' => [0.592, 0.249, 0.584],
        'pink-950' => [0.284, 0.109, 3.907],
        'purple-400' => [0.714, 0.203, 305.504],
        'purple-500' => [0.627, 0.265, 303.9],
        'purple-600' => [0.558, 0.288, 302.321],
        'purple-950' => [0.291, 0.149, 302.717],
        'red-400' => [0.704, 0.191, 22.216],
        'red-500' => [0.637, 0.237, 25.331],
        'red-600' => [0.577, 0.245, 27.325],
        'red-950' => [0.258, 0.092, 26.042],
        'rose-400' => [0.712, 0.194, 13.428],
        'rose-500' => [0.645, 0.246, 16.439],
        'rose-600' => [0.586, 0.253, 17.585],
        'rose-950' => [0.271, 0.105, 12.094],
        'sky-400' => [0.746, 0.16, 232.661],
        'sky-500' => [0.685, 0.169, 237.323],
        'sky-600' => [0.588, 0.158, 241.966],
        'sky-950' => [0.293, 0.066, 243.157],
        'teal-400' => [0.777, 0.152, 181.912],
        'teal-500' => [0.704, 0.14, 182.503],
        'teal-600' => [0.600, 0.118, 184.704],
        'teal-950' => [0.277, 0.046, 192.524],
        'violet-400' => [0.702, 0.183, 293.541],
        'violet-500' => [0.606, 0.25, 292.717],
        'violet-600' => [0.541, 0.281, 293.009],
        'violet-950' => [0.283, 0.141, 291.089],
        'yellow-400' => [0.852, 0.199, 91.936],
        'yellow-500' => [0.795, 0.184, 86.047],
        'yellow-600' => [0.681, 0.162, 75.834],
        'yellow-950' => [0.286, 0.066, 53.813],
        'zinc-400' => [0.705, 0.015, 286.067],
        'zinc-500' => [0.552, 0.016, 285.938],
        'zinc-600' => [0.442, 0.017, 285.786],
        'zinc-950' => [0.141, 0.005, 285.823],
    ];
}
