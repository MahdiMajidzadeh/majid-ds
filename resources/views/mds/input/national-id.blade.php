{{--
    An Iranian national ID (کد ملی): ten digits, no grouping, the check
    digit verified server-side by the NationalId rule.
--}}
<mds:input
    only
    ltr
    {{ $attributes->merge([
        'maxlength' => 10,
        'autocomplete' => 'off',
        'placeholder' => '0012345678',
        'data-mds-input-national-id' => '',
    ]) }}
/>
