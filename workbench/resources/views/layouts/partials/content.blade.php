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
    ['label' => 'سفارش‌های امروز', 'value' => 128, 'trend' => '+۱۲٪', 'color' => 'green', 'icon' => 'shopping-bag'],
    ['label' => 'فروش امروز', 'value' => null, 'amount' => 184500000, 'trend' => '+۸٪', 'color' => 'green', 'icon' => 'banknotes'],
    ['label' => 'مرجوعی‌ها', 'value' => 7, 'trend' => '−۳٪', 'color' => 'red', 'icon' => 'arrow-uturn-left'],
    ['label' => 'میانگین رضایت', 'value' => null, 'rating' => 4.6, 'trend' => 'ثابت', 'color' => 'zinc', 'icon' => 'star'],
];

$orders = [
    ['name' => 'گوشی موبایل سامسونگ Galaxy S25', 'seed' => 'phone', 'status' => 'تحویل شده', 'color' => 'green', 'date' => now()->subDays(2), 'amount' => 42500000, 'original' => 48900000],
    ['name' => 'هدفون بی‌سیم AirSound Pro', 'seed' => 'headphone', 'status' => 'در حال ارسال', 'color' => 'blue', 'date' => now()->subDay(), 'amount' => 1890000, 'original' => null],
    ['name' => 'کتاب صد سال تنهایی', 'seed' => 'book', 'status' => 'در انتظار پرداخت', 'color' => 'amber', 'date' => now()->subHours(6), 'amount' => 245000, 'original' => 350000],
    ['name' => 'ساعت هوشمند Fit Band 8', 'seed' => 'watch', 'status' => 'لغو شده', 'color' => 'red', 'date' => now()->subHours(2), 'amount' => 3200000, 'original' => null],
];

$products = [
    ['title' => 'گوشی موبایل سامسونگ مدل Galaxy S25 ظرفیت ۲۵۶ گیگابایت', 'seed' => 'phone', 'amount' => 42500000, 'original' => 48900000, 'rating' => 4.6, 'reviews' => 342, 'badge' => 'ارسال امروز'],
    ['title' => 'هدفون بی‌سیم مدل AirSound Pro', 'seed' => 'headphone', 'amount' => 1890000, 'original' => null, 'rating' => 4.1, 'reviews' => 87, 'badge' => null],
    ['title' => 'کتاب صد سال تنهایی اثر گابریل گارسیا مارکز', 'seed' => 'book', 'amount' => 245000, 'original' => 350000, 'rating' => 4.9, 'reviews' => 1205, 'badge' => 'پرفروش'],
    ['title' => 'ساعت هوشمند مدل Fit Band 8', 'seed' => 'watch', 'amount' => 3200000, 'original' => null, 'rating' => 4.3, 'reviews' => 54, 'badge' => null],
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
                            @fa($stat['value'])
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
            <flux:heading size="lg">آخرین سفارش‌ها</flux:heading>

            <div class="flex items-center gap-2">
                <flux:button size="sm" variant="filled" icon="funnel">فیلترها</flux:button>
                <flux:button size="sm" icon="arrow-down-tray">خروجی اکسل</flux:button>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>کالا</flux:table.column>
                <flux:table.column>وضعیت</flux:table.column>
                <flux:table.column sortable sorted direction="desc">تاریخ ثبت</flux:table.column>
                <flux:table.column align="end">مبلغ</flux:table.column>
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
                            <mds:jalali-date :date="$order['date']" ago />
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
                    پیشنهاد شگفت‌انگیز
                @else
                    ادامه پیشنهادها ({{ Mds::fa($section) }})
                @endif
            </flux:heading>

            @if ($section === 1)
                <div class="flex items-center gap-2">
                    <flux:text class="text-sm">پایان تا:</flux:text>
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
