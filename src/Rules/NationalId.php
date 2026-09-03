<?php

namespace MajidDs\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MajidDs\Support\Iran;

/**
 * The field is an Iranian national ID (کد ملی) — see Iran::nationalId().
 * Persian digits and spaces are accepted; the message follows
 * config('mds.persian_digits') unless one is given.
 */
class NationalId implements ValidationRule
{
    public function __construct(protected ?string $message = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! Iran::nationalId($value)) {
            $fail($this->message ?? (config('mds.persian_digits', true)
                ? ':attribute یک کد ملی معتبر نیست.'
                : 'The :attribute is not a valid national ID.'));
        }
    }
}
