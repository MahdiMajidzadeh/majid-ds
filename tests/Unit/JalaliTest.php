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
