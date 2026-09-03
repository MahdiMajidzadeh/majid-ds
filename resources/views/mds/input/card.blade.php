{{--
    A bank card number, grouped in fours as it reads on the card. The
    grouping is Flux's mask, so the bound value carries the spaces; the
    BankCard rule ignores them and Iran::normalizeBankCard() strips them.
    No `only` here — a mask owns the value's shape — but the digits still
    arrive Latin through x-mds-digits.
--}}
<mds:input
    ltr
    {{ $attributes->merge([
        'mask' => '9999 9999 9999 9999',
        'inputmode' => 'numeric',
        'autocomplete' => 'cc-number',
        'icon' => 'credit-card',
        'placeholder' => '0000 0000 0000 0000',
        'data-mds-input-card' => '',
    ]) }}
/>
