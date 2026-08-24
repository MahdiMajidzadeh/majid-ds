@props([
    'date',
    'format' => 'j F Y',
    'fa' => null,
    'ago' => false,
])

@php
use MajidDs\Support\Jalali;
use MajidDs\Support\Persian;

$dt = Persian::toDateTime($date);
@endphp

<time
    datetime="{{ $dt->format(DateTimeInterface::ATOM) }}"
    @if ($ago) title="{{ Jalali::format($dt, 'l j F Y', $fa) }}" @endif
    {{ $attributes }}
    data-mds-jalali-date
>{{ $ago ? Persian::ago($dt, $fa) : Jalali::format($dt, $format, $fa) }}</time>
