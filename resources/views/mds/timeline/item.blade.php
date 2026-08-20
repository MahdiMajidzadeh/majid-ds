@props([
    'status' => null,
    'align' => null,
    'size' => null,
])

{{-- align is inherited from the parent <mds:timeline> unless set on the item... --}}
@aware(['align' => 'center'])

<li
    {{ $attributes }}
    data-mds-timeline-align="{{ $align }}"
    @if ($status) data-mds-timeline-status="{{ $status }}" @endif
    @if ($size) data-mds-timeline-size="{{ $size }}" @endif
    data-mds-timeline-item
>
    <div data-mds-timeline-line="leading"><span></span></div>

    {{ $slot }}

    <div data-mds-timeline-line="trailing"><span></span></div>
</li>
