<?php

namespace MajidDs\Tests\Unit;

use IntlDateFormatter;
use MajidDs\Support\Jalali;
use MajidDs\Tests\TestCase;

class JalaliTest extends TestCase
{
    public function test_known_conversions_from_gregorian(): void
    {
        // Iranian revolution: 1979-02-11 => 1357-11-22...
        $this->assertSame([1357, 11, 22], Jalali::fromGregorian(1979, 2, 11));

        // Nowruz 1403: 2024-03-20 => 1403-01-01...
        $this->assertSame([1403, 1, 1], Jalali::fromGregorian(2024, 3, 20));

        // 2026-08-20 => 1405-05-29...
        $this->assertSame([1405, 5, 29], Jalali::fromGregorian(2026, 8, 20));
    }

    public function test_known_conversions_to_gregorian(): void
    {
        $this->assertSame([1979, 2, 11], Jalali::toGregorian(1357, 11, 22));
        $this->assertSame([2024, 3, 20], Jalali::toGregorian(1403, 1, 1));
    }

    /**
     * A Jalali date is real when it survives a round trip. An impossible one —
     * Esfand 30 in a common year, or a 32nd of anything — is not rejected but
     * rolled forward, so it comes back as a different date.
     */
    private function isRealDate(int $year, int $month, int $day): bool
    {
        return Jalali::fromGregorian(...Jalali::toGregorian($year, $month, $day)) === [$year, $month, $day];
    }

    public function test_esfand_29_and_30_are_anchored_in_both_kinds_of_year(): void
    {
        // 1403 is a leap year: Esfand runs to 30, and Nowruz follows it.
        $this->assertTrue(Jalali::isLeapYear(1403));
        $this->assertSame([2025, 3, 20], Jalali::toGregorian(1403, 12, 30));
        $this->assertSame([1403, 12, 30], Jalali::fromGregorian(2025, 3, 20));
        $this->assertSame([1404, 1, 1], Jalali::fromGregorian(2025, 3, 21));

        // 1404 is a common year: Esfand stops at 29, and Nowruz follows *that*.
        $this->assertFalse(Jalali::isLeapYear(1404));
        $this->assertSame([2026, 3, 20], Jalali::toGregorian(1404, 12, 29));
        $this->assertSame([1404, 12, 29], Jalali::fromGregorian(2026, 3, 20));
        $this->assertSame([1405, 1, 1], Jalali::fromGregorian(2026, 3, 21));

        // Two more leap years, one either side, so a rule that happens to fit
        // 1403 alone cannot pass.
        $this->assertSame([2021, 3, 20], Jalali::toGregorian(1399, 12, 30));
        $this->assertSame([2030, 3, 20], Jalali::toGregorian(1408, 12, 30));
    }

    public function test_esfand_30_exists_in_exactly_the_leap_years(): void
    {
        // The leap rule and the calendar's shape have to agree everywhere, not
        // just on the sampled years the roundtrip happens to visit.
        foreach (range(1380, 1440) as $year) {
            $this->assertSame(
                Jalali::isLeapYear($year),
                $this->isRealDate($year, 12, 30),
                "Esfand 30 of {$year} disagrees with isLeapYear({$year}).",
            );

            // Esfand 29 is in every year, leap or not.
            $this->assertTrue($this->isRealDate($year, 12, 29), "Esfand 29 of {$year} should exist.");
        }
    }

    public function test_month_lengths_follow_the_jalali_calendar(): void
    {
        // Farvardin–Shahrivar have 31 days, Mehr–Bahman 30, Esfand 29 or 30.
        foreach ([1403, 1404] as $year) {
            foreach (range(1, 6) as $month) {
                $this->assertTrue($this->isRealDate($year, $month, 31), "{$year}-{$month} should have 31 days.");
            }

            foreach (range(7, 11) as $month) {
                $this->assertTrue($this->isRealDate($year, $month, 30), "{$year}-{$month} should have 30 days.");
                $this->assertFalse($this->isRealDate($year, $month, 31), "{$year}-{$month} should stop at 30.");
            }
        }
    }

    public function test_the_month_length_changes_between_shahrivar_and_mehr(): void
    {
        // Shahrivar 31 is the last 31-day date of the year; Mehr 1 follows it.
        $this->assertSame([2025, 9, 22], Jalali::toGregorian(1404, 6, 31));
        $this->assertSame([1404, 6, 31], Jalali::fromGregorian(2025, 9, 22));
        $this->assertSame([1404, 7, 1], Jalali::fromGregorian(2025, 9, 23));
    }

    public function test_an_impossible_date_rolls_forward_rather_than_failing(): void
    {
        // Esfand 30 of a common year does not exist. The converter carries the
        // overflow into the next day instead of throwing, which is how PHP's
        // own DateTime treats 2026-02-30 — a view rendering a bad date should
        // not take the page down with it.
        $this->assertFalse(Jalali::isLeapYear(1404));
        $this->assertSame([2026, 3, 21], Jalali::toGregorian(1404, 12, 30));
        $this->assertSame([1405, 1, 1], Jalali::fromGregorian(2026, 3, 21));
    }

    public function test_roundtrip_over_a_wide_date_range(): void
    {
        $date = new \DateTimeImmutable('1950-01-01');
        $end = new \DateTimeImmutable('2100-01-01');

        while ($date < $end) {
            [$jy, $jm, $jd] = Jalali::fromGregorian(
                (int) $date->format('Y'),
                (int) $date->format('n'),
                (int) $date->format('j'),
            );

            $this->assertSame(
                [(int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j')],
                Jalali::toGregorian($jy, $jm, $jd),
                'Roundtrip failed for '.$date->format('Y-m-d'),
            );

            $date = $date->modify('+17 days');
        }
    }

    public function test_conversions_match_php_intl_persian_calendar(): void
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('intl extension not available.');
        }

        $formatter = new IntlDateFormatter(
            'en_US@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'UTC',
            IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd',
        );

        $date = new \DateTimeImmutable('1970-06-15', new \DateTimeZone('UTC'));
        $end = new \DateTimeImmutable('2080-01-01', new \DateTimeZone('UTC'));

        while ($date < $end) {
            [$jy, $jm, $jd] = Jalali::fromGregorian(
                (int) $date->format('Y'),
                (int) $date->format('n'),
                (int) $date->format('j'),
            );

            $expected = $formatter->format($date->getTimestamp());

            $actual = sprintf('%04d-%02d-%02d', $jy, $jm, $jd);

            $this->assertSame($expected, $actual, 'Mismatch with intl for '.$date->format('Y-m-d'));

            $date = $date->modify('+53 days');
        }
    }

    public function test_format_renders_persian_dates(): void
    {
        $this->assertSame('۲۹ مرداد ۱۴۰۵', Jalali::format('2026-08-20', 'j F Y'));
        $this->assertSame('1405/05/29', Jalali::format('2026-08-20', 'Y/m/d', false));
        $this->assertSame('۱۴۰۵/۰۵/۲۹', Jalali::format('2026-08-20', 'Y/m/d'));
    }

    public function test_leap_years(): void
    {
        // Known recent leap years in the 33-year cycle.
        $this->assertTrue(Jalali::isLeapYear(1399));
        $this->assertTrue(Jalali::isLeapYear(1403));
        $this->assertTrue(Jalali::isLeapYear(1408));
        $this->assertFalse(Jalali::isLeapYear(1402));
        $this->assertFalse(Jalali::isLeapYear(1404));
        $this->assertFalse(Jalali::isLeapYear(1405));

        // Must agree with the converters: a leap year is exactly 366 days.
        foreach (range(1390, 1420) as $year) {
            [$gy, $gm, $gd] = Jalali::toGregorian($year, 1, 1);
            [$ny, $nm, $nd] = Jalali::toGregorian($year + 1, 1, 1);

            $days = (new \DateTimeImmutable(sprintf('%d-%02d-%02d', $gy, $gm, $gd)))
                ->diff(new \DateTimeImmutable(sprintf('%d-%02d-%02d', $ny, $nm, $nd)))
                ->days;

            $this->assertSame(Jalali::isLeapYear($year), $days === 366, "year {$year}");
        }
    }

    public function test_format_transliterates_names_when_persian_output_is_off(): void
    {
        // The calendar stays Jalali — only the language of the output changes.
        $this->assertSame('29 Mordad 1405', Jalali::format('2026-08-20', 'j F Y', false));

        // 2026-08-20 is a Thursday.
        $this->assertSame('Thursday', Jalali::format('2026-08-20', 'l', false));
        $this->assertSame('AM', Jalali::format('2026-08-20 09:00', 'A', false));
        $this->assertSame('PM', Jalali::format('2026-08-20 15:00', 'A', false));
    }

    public function test_format_supports_weekday_and_time(): void
    {
        // 2026-08-20 is a Thursday...
        $this->assertSame('پنجشنبه ۲۹ مرداد', Jalali::format('2026-08-20 14:30:00', 'l j F'));
        $this->assertSame('۱۴:۳۰', Jalali::format('2026-08-20 14:30:00', 'H:i'));
    }

    public function test_format_degrades_on_a_malformed_utf8_format(): void
    {
        // preg_split('//u') returns false on invalid UTF-8; that used to reach
        // count() as a TypeError instead of simply producing nothing.
        $this->assertSame('', Jalali::format('2026-08-20', "\xFF"));
    }
}
