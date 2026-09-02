<?php

namespace MajidDs;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string fa(mixed $value)
 * @method static string number(mixed $value, int $decimals = 0)
 * @method static string money(mixed $amount, ?string $currency = null, int $decimals = 0)
 * @method static string toman(mixed $amount)
 * @method static string rial(mixed $amount)
 * @method static string jalali(mixed $date, string $format = 'j F Y', ?bool $persianDigits = null)
 * @method static string ago(mixed $date, ?bool $persian = null)
 * @method static string fileSize(mixed $bytes, ?bool $persianDigits = null)
 * @method static string|null cspNonce()
 *
 * @see MdsManager
 */
class Mds extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mds';
    }
}
