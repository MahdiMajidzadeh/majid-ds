{{--
    An Iranian mobile number: eleven digits starting 09, typed on a phone
    keypad and kept left-to-right. Digits only — the storable form is the
    digits themselves. The cap is 14, not 11, so a pasted +98… or 0098…
    number survives intact for the IranMobile rule to accept and
    Iran::normalizeMobile() to reduce; an 11 cap would silently drop its
    last digit and post a wrong number instead of an invalid one.
--}}
<mds:input
    only
    ltr
    {{ $attributes->merge([
        'type' => 'tel',
        'maxlength' => 14,
        'autocomplete' => 'tel-national',
        'placeholder' => '09123456789',
        'data-mds-input-mobile' => '',
    ]) }}
/>
