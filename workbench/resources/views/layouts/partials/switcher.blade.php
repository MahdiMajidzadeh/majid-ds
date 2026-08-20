{{-- Floating layout switcher — fixed, so it stays out of the layout grid. --}}
<div class="fixed bottom-4 left-1/2 z-30 -translate-x-1/2 print:hidden">
    <div class="flex items-center gap-1 rounded-full border border-zinc-200 bg-white/90 p-1.5 shadow-lg backdrop-blur-sm dark:border-zinc-600 dark:bg-zinc-800/90">
        <flux:tooltip content="فهرست همه چیدمان‌ها" position="top">
            <flux:button href="/layouts" variant="ghost" size="sm" icon="squares-2x2" square aria-label="فهرست چیدمان‌ها" />
        </flux:tooltip>

        <flux:separator vertical class="h-5" />

        <flux:dropdown position="top" align="center">
            <flux:button variant="ghost" size="sm" icon:trailing="chevron-up">
                {{ isset($layout) ? $layout['title'] : 'انتخاب چیدمان' }}
            </flux:button>

            <flux:navmenu class="max-h-[70dvh] overflow-y-auto">
                @foreach ($mdsLayouts as $item)
                    <flux:navmenu.item :href="$item['url']" :icon="$item['icon']">{{ $item['title'] }}</flux:navmenu.item>
                @endforeach

                <flux:navmenu.separator />

                <flux:navmenu.item href="/demo" icon="swatch">نمایشگاه اجزا</flux:navmenu.item>
            </flux:navmenu>
        </flux:dropdown>

        <flux:separator vertical class="h-5" />

        <flux:tooltip content="حالت تاریک / روشن" position="top">
            <flux:button variant="ghost" size="sm" icon="moon" square aria-label="حالت تاریک" x-data x-on:click="$flux.dark = ! $flux.dark" />
        </flux:tooltip>
    </div>
</div>
