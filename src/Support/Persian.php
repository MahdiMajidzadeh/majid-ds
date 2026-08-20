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
     */
    public static function money(mixed $amount, ?string $currency = null, int $decimals = 0): string
    {
        $currency ??= config('mds.currency', 'toman');

        $label = static::currencyLabel($currency);

        return trim(static::number($amount, $decimals).' '.$label);
    }

    /**
     * The display label for a currency identifier. Unknown identifiers
     * are treated as literal labels so custom currencies pass through.
     */
    public static function currencyLabel(?string $currency): string
    {
        return match ($currency) {
            'toman' => 'تومان',
            'rial' => 'ریال',
            'none', '', null => '',
            default => $currency,
        };
    }

    /**
     * Format a byte count with Persian units, e.g. "۱۵۹ کیلوبایت".
     */
    public static function fileSize(mixed $bytes, ?bool $persianDigits = null): string
    {
        $persianDigits ??= config('mds.persian_digits', true);

        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت', 'ترابایت'];

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
     * A short human-readable "time ago" phrase in Persian, e.g. "۵ دقیقه پیش".
     */
    public static function ago(mixed $date): string
    {
        $date = static::toDateTime($date);

        $diff = time() - $date->getTimestamp();

        $future = $diff < 0;
        $diff = abs($diff);

        [$amount, $unit] = match (true) {
            $diff < 60 => [null, 'لحظاتی'],
            $diff < 3600 => [intdiv($diff, 60), 'دقیقه'],
            $diff < 86400 => [intdiv($diff, 3600), 'ساعت'],
            $diff < 604800 => [intdiv($diff, 86400), 'روز'],
            $diff < 2629800 => [intdiv($diff, 604800), 'هفته'],
            $diff < 31557600 => [intdiv($diff, 2629800), 'ماه'],
            default => [intdiv($diff, 31557600), 'سال'],
        };

        $phrase = $amount === null ? $unit : static::digits($amount).' '.$unit;

        return $phrase.($future ? ' دیگر' : ' پیش');
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
