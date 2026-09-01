@props([
    'image' => null,
    'title' => '',
    'href' => null,
    'amount' => null,
    'original' => null,
    'currency' => null,
    'rating' => null,
    'reviews' => null,
    'badge' => null,
    'badgeColor' => 'lime',
    'unavailable' => false,
    'fa' => null,
])

@php
$fa ??= config('mds.persian_digits', true);

$percent = (! $unavailable && $original !== null && $amount !== null && $original > 0 && $original > $amount)
    ? (int) round((1 - $amount / $original) * 100)
    : null;
@endphp

<flux:card size="sm" {{ $attributes->class('group relative flex flex-col gap-3') }} data-mds-product-card>
    <div class="relative">
        @if ($badge)
            <div class="absolute start-0 top-0 z-10">
                <flux:badge size="sm" :color="$badgeColor">{{ $badge }}</flux:badge>
            </div>
        @endif

        <a @if ($href) href="{{ $href }}" @endif class="block">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    loading="lazy"
                    @class([
                        'aspect-square w-full rounded-lg object-contain transition-transform duration-200 group-hover:scale-[1.03]',
                        'opacity-50 grayscale' => $unavailable,
                    ])
                >
            @else
                <div class="flex aspect-square w-full items-center justify-center rounded-lg bg-zinc-100 text-zinc-300 dark:bg-white/5 dark:text-zinc-600">
                    <mds:icon icon="photo" class="size-10" />
                </div>
            @endif
        </a>
    </div>

    <a @if ($href) href="{{ $href }}" @endif class="block">
        <h3 class="line-clamp-2 min-h-10 text-sm/5 font-medium text-zinc-800 dark:text-white">{{ $title }}</h3>
    </a>

    @if ($rating !== null)
        <mds:rating :value="$rating" :count="$reviews" size="sm" :fa="$fa" />
    @endif

    <div class="mt-auto flex items-center justify-between gap-2">
        @if ($unavailable)
            <span class="text-sm font-medium text-zinc-400 dark:text-zinc-500">{{ $fa ? 'ناموجود' : 'Out of stock' }}</span>
        @else
            @if ($percent !== null)
                <mds:discount-badge :percent="$percent" :fa="$fa" />
            @else
                <span></span>
            @endif

            <mds:price :amount="$amount" :original="$original" :currency="$currency" :fa="$fa" :badge="false" />
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div data-mds-product-card-actions>
            {{ $slot }}
        </div>
    @endif
</flux:card>
