{{--
    A Sheba (IBAN) number. The mask supplies the IR prefix and the spacing,
    so a user types the 24 digits alone; the bound value is the spaced
    form, which the Sheba rule accepts and Iran::normalizeSheba() reduces
    to IR + 24 digits. No `only` — the mask owns the shape.
--}}
<mds:input
    ltr
    {{ $attributes->merge([
        'mask' => 'IR99 9999 9999 9999 9999 9999 99',
        'inputmode' => 'numeric',
        'autocomplete' => 'off',
        'placeholder' => 'IR00 0000 0000 0000 0000 0000 00',
        'data-mds-input-sheba' => '',
    ]) }}
/>
