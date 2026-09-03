<?php

namespace MajidDs\Support;

/**
 * Iranian identifiers: the shapes a Persian storefront asks for on every
 * checkout, validated the way the issuing authority defines them. Every
 * method accepts Persian and Arabic-Indic digits and ignores spaces and
 * dashes, so a value can be checked exactly as a user typed it — and the
 * normalize* methods hand back the machine form worth storing.
 * Dependency-free, like the rest of Support.
 */
class Iran
{
    /**
     * Latin digits and nothing else — the raw material every check starts from.
     */
    public static function digits(mixed $value): string
    {
        return (string) preg_replace('/\D+/', '', Persian::latinDigits($value));
    }

    /**
     * An Iranian national ID (کد ملی): ten digits whose last one is a
     * mod-11 check over the first nine. Repeated digits (0000000000,
     * 1111111111, …) satisfy the arithmetic but are never issued.
     */
    public static function nationalId(mixed $value): bool
    {
        $id = static::digits($value);

        if (! preg_match('/^\d{10}$/', $id) || preg_match('/^(\d)\1{9}$/', $id)) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $id[$i] * (10 - $i);
        }

        $remainder = $sum % 11;
        $check = $remainder < 2 ? $remainder : 11 - $remainder;

        return (int) $id[9] === $check;
    }

    /**
     * An Iranian mobile number in any of its usual spellings: 0912…, 912…,
     * +98912…, 0098912…, with or without spaces and dashes.
     */
    public static function mobile(mixed $value): bool
    {
        return static::normalizeMobile($value) !== null;
    }

    /**
     * The storable form of a mobile number — eleven digits starting 09 —
     * or null when the value is not a mobile number at all.
     */
    public static function normalizeMobile(mixed $value): ?string
    {
        $digits = static::digits($value);

        $digits = match (true) {
            str_starts_with($digits, '0098') => substr($digits, 4),
            str_starts_with($digits, '98') && strlen($digits) === 12 => substr($digits, 2),
            default => $digits,
        };

        if (strlen($digits) === 10 && $digits[0] === '9') {
            $digits = '0'.$digits;
        }

        return preg_match('/^09\d{9}$/', $digits) ? $digits : null;
    }

    /**
     * A Sheba (شبا) number — Iran's IBAN: "IR" plus 24 digits, verified with
     * the ISO 13616 mod-97 check. The country prefix may be omitted, and
     * spaces or dashes between the groups are ignored.
     */
    public static function sheba(mixed $value): bool
    {
        return static::normalizeSheba($value) !== null;
    }

    /**
     * The storable form of a Sheba number — "IR" and 24 digits, no spaces —
     * or null when the check fails.
     */
    public static function normalizeSheba(mixed $value): ?string
    {
        $raw = strtoupper((string) preg_replace('/[\s-]+/', '', Persian::latinDigits($value)));

        if (preg_match('/^\d{24}$/', $raw)) {
            $raw = 'IR'.$raw;
        }

        if (! preg_match('/^IR\d{24}$/', $raw)) {
            return null;
        }

        // Move the country code and check digits to the end, spell the
        // letters as numbers (I = 18, R = 27), and the whole thing mod 97 is 1.
        $rearranged = substr($raw, 4).'1827'.substr($raw, 2, 2);
        $mod = 0;

        foreach (str_split($rearranged) as $char) {
            $mod = ($mod * 10 + (int) $char) % 97;
        }

        return $mod === 1 ? $raw : null;
    }

    /**
     * An Iranian bank card number: sixteen digits that pass the Luhn check.
     * Spaces between the four-digit groups are ignored.
     */
    public static function bankCard(mixed $value): bool
    {
        return static::normalizeBankCard($value) !== null;
    }

    /**
     * The storable form of a card number — sixteen digits, no spaces — or
     * null when the Luhn check fails.
     */
    public static function normalizeBankCard(mixed $value): ?string
    {
        $digits = static::digits($value);

        if (! preg_match('/^\d{16}$/', $digits)) {
            return null;
        }

        $sum = 0;

        foreach (str_split(strrev($digits)) as $i => $char) {
            $n = (int) $char;

            if ($i % 2 === 1) {
                $n *= 2;

                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
        }

        return $sum % 10 === 0 ? $digits : null;
    }
}
