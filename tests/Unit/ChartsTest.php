<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Support\Charts;
use MajidDs\Tests\TestCase;

class ChartsTest extends TestCase
{
    public function test_coordinates_are_trimmed_and_never_negative_zero(): void
    {
        $this->assertSame('12.35', Charts::n(12.345));
        $this->assertSame('12', Charts::n(12.000));
        $this->assertSame('0', Charts::n(-0.0001));
    }

    public function test_points_map_values_onto_the_plot(): void
    {
        $points = Charts::points([0, 50, 100], 10, 5, 100, 80, 100);

        $this->assertSame([10.0, 85.0], $points[0]);
        $this->assertSame([60.0, 45.0], $points[1]);
        $this->assertSame([110.0, 5.0], $points[2]);
    }

    public function test_spline_stays_monotone_between_points(): void
    {
        // A monotone rise must produce a curve that never dips: every control
        // point's y has to sit inside its segment's y range.
        $points = Charts::points([10, 20, 80, 85], 0, 0, 300, 100, 100);
        $path = Charts::splinePath($points);

        preg_match_all('/C([\d.]+) ([\d.]+) ([\d.]+) ([\d.]+) ([\d.]+) ([\d.]+)/', $path, $curves, PREG_SET_ORDER);

        $this->assertCount(3, $curves);

        $cursor = $points[0][1];

        foreach ($curves as $c) {
            [$hi, $lo] = [max($cursor, (float) $c[6]), min($cursor, (float) $c[6])];

            $this->assertGreaterThanOrEqual($lo - 0.01, (float) $c[2]);
            $this->assertLessThanOrEqual($hi + 0.01, (float) $c[2]);
            $this->assertGreaterThanOrEqual($lo - 0.01, (float) $c[4]);
            $this->assertLessThanOrEqual($hi + 0.01, (float) $c[4]);

            $cursor = (float) $c[6];
        }
    }

    public function test_two_points_fall_back_to_a_straight_line(): void
    {
        $this->assertSame('M0 100 L50 0', Charts::splinePath([[0, 100], [50, 0]]));
    }

    public function test_arc_path_runs_clockwise_from_twelve(): void
    {
        // 0° is up, 90° is right; a 180°+ sweep sets the large-arc flag.
        $this->assertSame([80.0, 20.0], array_map(round(...), Charts::polarPoint(80, 80, 60, 0)));
        $this->assertSame([140.0, 80.0], array_map(round(...), Charts::polarPoint(80, 80, 60, 90)));

        $this->assertStringContainsString(' A60 60 0 0 1 ', Charts::arcPath(80, 80, 60, 0, 90));
        $this->assertStringContainsString(' A60 60 0 1 1 ', Charts::arcPath(80, 80, 60, 0, 270));
    }

    public function test_bar_path_rounds_only_the_asked_corners(): void
    {
        $pill = Charts::barPath(0, 0, 16, 40, [8, 8, 8, 8]);
        $bottomOnly = Charts::barPath(0, 0, 16, 40, [0, 0, 8, 8]);

        $this->assertSame(4, substr_count($pill, 'A8 8 0 0 1'));
        $this->assertSame(2, substr_count($bottomOnly, 'A8 8 0 0 1'));

        // Radii larger than the box can hold are clamped to half its size.
        $this->assertStringContainsString('A2 2 0 0 1', Charts::barPath(0, 0, 16, 4, [8, 8, 8, 8]));
    }

    public function test_nice_max_lands_on_clean_ticks(): void
    {
        $this->assertSame(100.0, Charts::niceMax(84));
        $this->assertSame(100.0, Charts::niceMax(95));
        $this->assertSame(200.0, Charts::niceMax(160));
        $this->assertSame(8.0, Charts::niceMax(6));
        $this->assertSame(4.0, Charts::niceMax(0));

        $this->assertSame([0.0, 25.0, 50.0, 75.0, 100.0], Charts::ticks(84));
    }

    public function test_cap_degrees_measure_the_round_cap_overhang(): void
    {
        $this->assertEqualsWithDelta(11.06, Charts::capDegrees(22, 57), 0.01);
    }
}
