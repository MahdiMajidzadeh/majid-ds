<?php

namespace MajidDs;

use Illuminate\Foundation\Vite;
use MajidDs\Support\Jalali;
use MajidDs\Support\Persian;

class MdsManager
{
    public function fa(mixed $value): string
    {
        return Persian::digits($value);
    }

    public function number(mixed $value, int $decimals = 0): string
    {
        return Persian::number($value, $decimals);
    }

    public function money(mixed $amount, ?string $currency = null, int $decimals = 0): string
    {
        return Persian::money($amount, $currency, $decimals);
    }

    public function toman(mixed $amount): string
    {
        return Persian::money($amount, 'toman');
    }

    public function rial(mixed $amount): string
    {
        return Persian::money($amount, 'rial');
    }

    public function jalali(mixed $date, string $format = 'j F Y', ?bool $persianDigits = null): string
    {
        return Jalali::format($date, $format, $persianDigits);
    }

    public function fileSize(mixed $bytes, ?bool $persianDigits = null): string
    {
        return Persian::fileSize($bytes, $persianDigits);
    }

    public function ago(mixed $date, ?bool $persian = null): string
    {
        return Persian::ago($date, $persian);
    }

    /**
     * The Content-Security-Policy nonce for the kit's inline <script> blocks.
     *
     * Read from the registry the app already fills for Laravel — Vite::useCspNonce(),
     * which Livewire's own script tags read too — so one call in the app's CSP
     * middleware covers the whole page. Null when no nonce is registered; the
     *
     * @mdsNonce directive then emits nothing.
     */
    public function cspNonce(): ?string
    {
        if (! app()->bound(Vite::class)) {
            return null;
        }

        return app(Vite::class)->cspNonce();
    }
}
