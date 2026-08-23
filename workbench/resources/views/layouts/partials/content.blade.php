{{--
    Shared page body so every layout shows the same content and only the frame
    changes. Optional: $sections (how many product rows to repeat, for testing
    scroll behaviour of sticky areas), $showTable, $showStats.
--}}
@php
$sections = $sections ?? 1;
$showTable = $showTable ?? true;
$showStats = $showStats ?? true;

$stats = [
    ['label' => __('سفارش‌های امروز'), 'value' => 128, 'trend' => __('+۱۲٪'), 'color' => 'green', 'icon' => 'shopping-bag'],
    ['label' => __('فروش امروز'), 'value' => null, 'amount' => 184500000, 'trend' => __('+۸٪'), 'color' => 'green', 'icon' => 'banknotes'],
    ['label' => __('مرجوعی‌ها'), 'value' => 7, 'trend' => __('−۳٪'), 'color' => 'red', 'icon' => 'arrow-uturn-left'],
    ['label' => __('میانگین رضایت'), 'value' => null, 'rating' => 4.6, 'trend' => __('ثابت'), 'color' => 'zinc', 'icon' => 'star'],
];

$orders = [
    ['name' => __('گوشی موبایل سامسونگ Galaxy S25'), 'seed' => 'phone', 'status' => __('تحویل شده'), 'color' => 'green', 'date' => now()->subDays(2), 'amount' => 42500000, 'original' => 48900000],
    ['name' => __('هدفون بی‌سیم AirSound Pro'), 'seed' => 'headphone', 'status' => __('در حال ارسال'), 'color' => 'blue', 'date' => now()->subDay(), 'amount' => 1890000, 'original' => null],
    ['name' => __('کتاب صد سال تنهایی'), 'seed' => 'book', 'status' => __('در انتظار پرداخت'), 'color' => 'amber', 'date' => now()->subHours(6), 'amount' => 245000, 'original' => 350000],
    ['name' => __('ساعت هوشمند Fit Band 8'), 'seed' => 'watch', 'status' => __('لغو شده'), 'color' => 'red', 'date' => now()->subHours(2), 'amount' => 3200000, 'original' => null],
];

$products = [
    ['title' => __('گوشی موبایل سامسونگ مدل Galaxy S25 ظرفیت ۲۵۶ گیگابایت'), 'seed' => 'phone', 'amount' => 42500000, 'original' => 48900000, 'rating' => 4.6, 'reviews' => 342, 'badge' => __('ارسال امروز')],
    ['title' => __('هدفون بی‌سیم مدل AirSound Pro'), 'seed' => 'headphone', 'amount' => 1890000, 'original' => null, 'rating' => 4.1, 'reviews' => 87, 'badge' => null],
    ['title' => __('کتاب صد سال تنهایی اثر گابریل گارسیا مارکز'), 'seed' => 'book', 'amount' => 245000, 'original' => 350000, 'rating' => 4.9, 'reviews' => 1205, 'badge' => __('پرفروش')],
    ['title' => __('ساعت هوشمند مدل Fit Band 8'), 'seed' => 'watch', 'amount' => 3200000, 'original' => null, 'rating' => 4.3, 'reviews' => 54, 'badge' => null],
];
@endphp

@if ($showStats)
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <flux:card size="sm" class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:subheading class="text-xs">{{ $stat['label'] }}</flux:subheading>
                    <flux:icon :icon="$stat['icon']" class="size-4 text-zinc-400" />
                </div>

                <div class="flex items-end justify-between gap-2">
                    <flux:heading size="lg">
                        @if (isset($stat['amount']))
                            <mds:price :amount="$stat['amount']" size="sm" />
                        @elseif (isset($stat['rating']))
                            <mds:rating :value="$stat['rating']" size="sm" :count="912" />
                        @else
                            {{ $mdsNum($stat['value']) }}
                        @endif
                    </flux:heading>

                    <flux:badge size="sm" :color="$stat['color']">{{ $stat['trend'] }}</flux:badge>
                </div>
            </flux:card>
        @endforeach
    </div>
@endif

@if ($showTable)
    <flux:card class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading size="lg">{{ __('آخرین سفارش‌ها') }}</flux:heading>

            <div class="flex items-center gap-2">
                <flux:button size="sm" variant="filled" icon="funnel">{{ __('فیلترها') }}</flux:button>
                <flux:button size="sm" icon="arrow-down-tray">{{ __('خروجی اکسل') }}</flux:button>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('کالا') }}</flux:table.column>
                <flux:table.column>{{ __('وضعیت') }}</flux:table.column>
                <flux:table.column sortable sorted direction="desc">{{ __('تاریخ ثبت') }}</flux:table.column>
                <flux:table.column align="end">{{ __('مبلغ') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($orders as $order)
                    <flux:table.row>
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="sm" src="https://picsum.photos/seed/{{ $order['seed'] }}/48/48" />
                            {{ $order['name'] }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$order['color']">{{ $order['status'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($mdsFa)
                                <mds:jalali-date :date="$order['date']" ago />
                            @else
                                {{ $order['date']->diffForHumans() }}
                            @endif
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <mds:price :amount="$order['amount']" :original="$order['original']" size="sm" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
@endif

@for ($section = 1; $section <= $sections; $section++)
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:heading size="lg">
                @if ($section === 1)
                    {{ __('پیشنهاد شگفت‌انگیز') }}
                @else
                    {{ __('ادامه پیشنهادها') }} ({{ $mdsNum($section) }})
                @endif
            </flux:heading>

            @if ($section === 1)
                <div class="flex items-center gap-2">
                    <flux:text class="text-sm">{{ __('پایان تا:') }}</flux:text>
                    <mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" />
                </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($products as $product)
                <mds:product-card
                    :title="$product['title']"
                    image="https://picsum.photos/seed/{{ $product['seed'] }}{{ $section }}/400/400"
                    :amount="$product['amount']"
                    :original="$product['original']"
                    :rating="$product['rating']"
                    :reviews="$product['reviews']"
                    :badge="$product['badge']"
                    href="#"
                />
            @endforeach
        </div>
    </div>
@endfor
