<?php

namespace MajidDs\Support;

use DateTimeInterface;

class Jalali
{
    public const MONTHS = [
        1 => 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند',
    ];

    /**
     * Keyed by PHP's `w` format (0 = Sunday).
     */
    public const WEEKDAYS = [
        0 => 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه',
    ];

    /**
     * Convert a Gregorian date to Jalali. Returns [year, month, day].
     */
    public static function fromGregorian(int $gy, int $gm, int $gd): array
    {
        $daysInMonths = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

        $days = 355666 + (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $gd + $daysInMonths[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /**
     * Convert a Jalali date to Gregorian. Returns [year, month, day].
     */
    public static function toGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;

        $days = -355668 + (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + $jd
            + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;

        $isLeap = (($gy % 4 === 0) && ($gy % 100 !== 0)) || ($gy % 400 === 0);

        $monthLengths = [0, 31, $isLeap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        for ($gm = 1; $gm <= 12; $gm++) {
            if ($gd <= $monthLengths[$gm]) {
                break;
            }

            $gd -= $monthLengths[$gm];
        }

        return [$gy, $gm, $gd];
    }

    /**
     * Format a date in the Jalali calendar.
     *
     * Supported tokens: Y y n m j d F l D  and time passthroughs  H G h g i s A a v
     * Backslash escapes a token. Anything else is emitted literally.
     */
    public static function format(mixed $date, string $format = 'j F Y', ?bool $persianDigits = null): string
    {
        $persianDigits ??= (bool) config('mds.persian_digits', true);

        $date = Persian::toDateTime($date);

        [$jy, $jm, $jd] = static::fromGregorian(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j'),
        );

        $out = '';
        $chars = preg_split('//u', $format, -1, PREG_SPLIT_NO_EMPTY);

        for ($i = 0, $len = count($chars); $i < $len; $i++) {
            $char = $chars[$i];

            if ($char === '\\' && $i + 1 < $len) {
                $out .= $chars[++$i];

                continue;
            }

            $out .= match ($char) {
                'Y' => (string) $jy,
                'y' => str_pad((string) ($jy % 100), 2, '0', STR_PAD_LEFT),
                'n' => (string) $jm,
                'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
                'j' => (string) $jd,
                'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
                'F' => static::MONTHS[$jm],
                'l', 'D' => static::WEEKDAYS[(int) $date->format('w')],
                'H', 'G', 'h', 'g', 'i', 's', 'v' => $date->format($char),
                'A', 'a' => ((int) $date->format('G')) < 12 ? 'قبل‌ازظهر' : 'بعدازظهر',
                default => $char,
            };
        }

        return $persianDigits ? Persian::digits($out) : $out;
    }
}
