<?php

namespace MajidDs\Support;

use DateTimeImmutable;
use DateTimeInterface;

class Persian
{
    /**
     * Convert Latin digits (and Arabic-Indic digits) to Persian digits.
     */
    public static function digits(mixed $value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
            '٠' => '۰', '١' => '۱', '٢' => '۲', '٣' => '۳', '٤' => '۴',
            '٥' => '۵', '٦' => '۶', '٧' => '۷', '٨' => '۸', '٩' => '۹',
        ]);
    }

    /**
     * Convert Persian/Arabic-Indic digits back to Latin digits.
     */
    public static function latinDigits(mixed $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * Format a number with Persian digits, the Persian thousands
     * separator (٬) and decimal separator (٫).
     */
    public static function number(mixed $value, int $decimals = 0): string
    {
        return static::digits(number_format((float) $value, $decimals, '٫', '٬'));
    }

    /**
     * Format an amount of money with its currency label, e.g. "۲٬۵۰۰٬۰۰۰ تومان".
     * Persian by definition (it backs @toman / @rial) — for output that follows
     * the config, use mds:price.
     */
    /**
     * A number in the caller's language: Persian digits and separators, or
     * Latin ones. The shape every component wants when `fa` decides the
     * language rather than the call site.
     */
    public static function format(mixed $value, int $decimals = 0, ?bool $persian = null): string
    {
        $persian ??= (bool) config('mds.persian_digits', true);

        return $persian
            ? static::number($value, $decimals)
            : number_format((float) $value, $decimals);
    }

    /**
     * A measurement: whole numbers stay whole, fractions keep one place.
     * Chart axes and tallies read better without a trailing «٫۰».
     */
    public static function decimal(mixed $value, ?bool $persian = null): string
    {
        $number = (float) $value;

        return static::format($number, $number == floor($number) ? 0 : 1, $persian);
    }

    /**
     * A value that may already be a formatted string — «۱۰۰٪», "3.2k" — in
     * which case only its digits are converted, never its shape.
     */
    public static function auto(mixed $value, ?bool $persian = null): string
    {
        $persian ??= (bool) config('mds.persian_digits', true);

        if (is_int($value) || is_float($value)) {
            return static::format($value, 0, $persian);
        }

        return $persian ? static::digits($value) : (string) $value;
    }

    public static function money(mixed $amount, ?string $currency = null, int $decimals = 0): string
    {
        $currency ??= config('mds.currency', 'toman');

        $label = static::currencyLabel($currency, true);

        return trim(static::number($amount, $decimals).' '.$label);
    }

    /**
     * The display label for a currency identifier — تومان/ریال, or Toman/Rial
     * when Persian output is off. Unknown identifiers are treated as literal
     * labels so custom currencies pass through.
     */
    public static function currencyLabel(?string $currency, ?bool $persian = null): string
    {
        $persian ??= config('mds.persian_digits', true);

        return match ($currency) {
            'toman' => $persian ? 'تومان' : 'Toman',
            'rial' => $persian ? 'ریال' : 'Rial',
            'none', '', null => '',
            default => $currency,
        };
    }

    /**
     * Format a byte count, e.g. "۱۵۹ کیلوبایت" — or "159 KB" when Persian
     * output is off, since the Persian unit words have no place in Latin text.
     */
    public static function fileSize(mixed $bytes, ?bool $persianDigits = null): string
    {
        $persianDigits ??= config('mds.persian_digits', true);

        $units = $persianDigits
            ? ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت']
            : ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max(0.0, (float) $bytes);
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        // Whole numbers, byte counts, and values of 100+ read better without a fraction...
        $decimals = ($unit === 0 || $bytes >= 100 || $bytes === floor($bytes)) ? 0 : 1;

        $number = $persianDigits
            ? static::number($bytes, $decimals)
            : number_format($bytes, $decimals);

        return $number.' '.$units[$unit];
    }

    /**
     * A short human-readable "time ago" phrase, e.g. "۵ دقیقه پیش" — or
     * "5 minutes ago" when Persian output is off.
     */
    public static function ago(mixed $date, ?bool $persian = null): string
    {
        $persian ??= config('mds.persian_digits', true);

        $date = static::toDateTime($date);

        // now() rather than time(): identical on a live clock, but it honors
        // Date::setTestNow() — the docs builder pins the clock through it.
        $diff = now()->getTimestamp() - $date->getTimestamp();

        $future = $diff < 0;
        $diff = abs($diff);

        [$amount, $unit] = match (true) {
            $diff < 60 => [null, $persian ? 'لحظاتی' : 'moments'],
            $diff < 3600 => [intdiv($diff, 60), $persian ? 'دقیقه' : 'minute'],
            $diff < 86400 => [intdiv($diff, 3600), $persian ? 'ساعت' : 'hour'],
            $diff < 604800 => [intdiv($diff, 86400), $persian ? 'روز' : 'day'],
            $diff < 2629800 => [intdiv($diff, 604800), $persian ? 'هفته' : 'week'],
            $diff < 31557600 => [intdiv($diff, 2629800), $persian ? 'ماه' : 'month'],
            default => [intdiv($diff, 31557600), $persian ? 'سال' : 'year'],
        };

        if ($persian) {
            $phrase = $amount === null ? $unit : static::digits($amount).' '.$unit;

            return $phrase.($future ? ' دیگر' : ' پیش');
        }

        if ($amount === null) {
            return $future ? 'in a moment' : 'just now';
        }

        $phrase = $amount.' '.$unit.($amount === 1 ? '' : 's');

        return $future ? 'in '.$phrase : $phrase.' ago';
    }

    /**
     * Normalize a date-ish value into a DateTimeImmutable.
     */
    public static function toDateTime(mixed $date): DateTimeImmutable
    {
        return match (true) {
            $date instanceof DateTimeImmutable => $date,
            $date instanceof DateTimeInterface => DateTimeImmutable::createFromInterface($date),
            is_int($date) => (new DateTimeImmutable('@'.$date))->setTimezone(new \DateTimeZone(config('app.timezone', 'UTC'))),
            default => new DateTimeImmutable((string) $date),
        };
    }
}
