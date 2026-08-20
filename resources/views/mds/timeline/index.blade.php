@props([
    'horizontal' => false,
    'align' => 'center',
    'size' => null,
])

<ol
    {{ $attributes }}
    data-mds-timeline-align="{{ $align }}"
    @if ($size) data-mds-timeline-size="{{ $size }}" @endif
    @if ($horizontal) data-mds-timeline-horizontal @endif
    data-mds-timeline
>
    {{ $slot }}
</ol>
