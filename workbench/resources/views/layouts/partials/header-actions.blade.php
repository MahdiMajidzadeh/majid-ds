{{-- End-side header actions shared by every layout (left in RTL, right in LTR). --}}
<flux:tooltip :content="__('جستجو (⌘K)')" position="bottom">
    <flux:button variant="subtle" icon="magnifying-glass" square aria-label="{{ __('جستجو') }}" class="max-sm:hidden" />
</flux:tooltip>

<flux:tooltip :content="__('سبد خرید')" position="bottom">
    <flux:button variant="subtle" icon="shopping-cart" square aria-label="{{ __('سبد خرید') }}" />
</flux:tooltip>

<flux:dropdown position="bottom" align="end">
    <flux:button variant="subtle" icon="bell" square aria-label="{{ __('اعلان‌ها') }}" />

    <flux:menu>
        <flux:menu.item icon="truck">{{ __('سفارش شما ارسال شد') }}</flux:menu.item>
        <flux:menu.item icon="tag">{{ __('کالای مورد علاقه‌تان ارزان شد') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>

<flux:dropdown position="bottom" align="end">
    <flux:profile avatar="https://picsum.photos/seed/user/64/64" :name="__('مهدی مجیدزاده')" class="max-sm:hidden" />

    <flux:menu>
        <flux:menu.item icon="user">{{ __('پروفایل من') }}</flux:menu.item>
        <flux:menu.item icon="shopping-bag">{{ __('سفارش‌های من') }}</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">{{ __('خروج از حساب') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>
