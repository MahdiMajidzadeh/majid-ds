<?php

namespace MajidDs\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MajidDs\Support\Iran;

/**
 * The field is a Sheba (IBAN) number — see Iran::sheba(). The IR prefix
 * may be missing and the groups may be spaced; store
 * Iran::normalizeSheba($value) to keep the canonical 26-character form.
 */
class Sheba implements ValidationRule
{
    public function __construct(protected ?string $message = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! Iran::sheba($value)) {
            $fail($this->message ?? (config('mds.persian_digits', true)
                ? ':attribute یک شماره شبا معتبر نیست.'
                : 'The :attribute is not a valid Sheba number.'));
        }
    }
}
