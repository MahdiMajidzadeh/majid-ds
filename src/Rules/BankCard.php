<?php

namespace MajidDs\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MajidDs\Support\Iran;

/**
 * The field is a sixteen-digit bank card number that passes the Luhn
 * check — see Iran::bankCard(). Spaces between the groups are ignored;
 * store Iran::normalizeBankCard($value) to keep the digits alone.
 */
class BankCard implements ValidationRule
{
    public function __construct(protected ?string $message = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! Iran::bankCard($value)) {
            $fail($this->message ?? (config('mds.persian_digits', true)
                ? ':attribute یک شماره کارت معتبر نیست.'
                : 'The :attribute is not a valid card number.'));
        }
    }
}
