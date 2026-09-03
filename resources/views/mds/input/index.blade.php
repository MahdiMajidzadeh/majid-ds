@props([
    'only' => false,
    'ltr' => false,
])

@php
// Flux's input, with x-mds-digits on the control. Everything else — label,
// description, icons, clearable, copyable, viewable, mask, wire:model and
// the validation state read from the error bag — is Flux's own: the
// attribute bag is handed over whole, and Flux puts it on the <input>.
//
// `only` keeps digits alone (phone numbers, verification codes, card
// numbers) and asks mobile keyboards for a numeric layout; `ltr` marks the
// control so mds.css keeps it left-to-right inside an RTL form. Both are
// defaults: a caller's own inputmode or data-ltr wins.
$defaults = [
    $only ? 'x-mds-digits.only' : 'x-mds-digits' => '',
    'data-mds-input' => '',
];

if ($only) {
    $defaults['inputmode'] = 'numeric';
}

if ($ltr) {
    $defaults['data-ltr'] = '';
}
@endphp

@include('mds::partials.digits')

<flux:input {{ $attributes->merge($defaults) }} />
