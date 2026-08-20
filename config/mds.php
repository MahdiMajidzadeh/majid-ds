<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | Used by the <mds:price> component, the @toman/@rial directives, and
    | Persian::money() when no explicit currency is given. Supported values
    | are "toman", "rial", "none", or any literal label (e.g. "درهم").
    |
    */

    'currency' => 'toman',

    /*
    |--------------------------------------------------------------------------
    | Persian Digits
    |--------------------------------------------------------------------------
    |
    | When enabled, numeric output in components (prices, ratings, counters,
    | Jalali dates) is rendered with Persian digits (۰۱۲۳...). Individual
    | components can override this with their :fa="..." prop.
    |
    */

    'persian_digits' => true,

];
