<?php

namespace MajidDs\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MajidDs\Support\Iran;

/**
 * The field is an Iranian mobile number in any usual spelling — see
 * Iran::mobile(). Store Iran::normalizeMobile($value) afterwards to keep
 * the eleven-digit 09… form.
 */
class IranMobile implements ValidationRule
{
    public function __construct(protected ?string $message = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! Iran::mobile($value)) {
            $fail($this->message ?? (config('mds.persian_digits', true)
                ? ':attribute یک شماره موبایل معتبر نیست.'
                : 'The :attribute is not a valid mobile number.'));
        }
    }
}
