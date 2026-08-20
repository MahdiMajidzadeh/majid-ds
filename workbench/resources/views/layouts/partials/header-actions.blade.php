{{-- Right-hand (start-hand, in RTL: left) header actions shared by every layout. --}}
<flux:tooltip content="جستجو (⌘K)" position="bottom">
    <flux:button variant="subtle" icon="magnifying-glass" square aria-label="جستجو" class="max-sm:hidden" />
</flux:tooltip>

<flux:tooltip content="سبد خرید" position="bottom">
    <flux:button variant="subtle" icon="shopping-cart" square aria-label="سبد خرید" />
</flux:tooltip>

<flux:dropdown position="bottom" align="end">
    <flux:button variant="subtle" icon="bell" square aria-label="اعلان‌ها" />

    <flux:menu>
        <flux:menu.item icon="truck">سفارش شما ارسال شد</flux:menu.item>
        <flux:menu.item icon="tag">کالای مورد علاقه‌تان ارزان شد</flux:menu.item>
    </flux:menu>
</flux:dropdown>

<flux:dropdown position="bottom" align="end">
    <flux:profile avatar="https://picsum.photos/seed/user/64/64" name="مهدی مجیدزاده" class="max-sm:hidden" />

    <flux:menu>
        <flux:menu.item icon="user">پروفایل من</flux:menu.item>
        <flux:menu.item icon="shopping-bag">سفارش‌های من</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">خروج از حساب</flux:menu.item>
    </flux:menu>
</flux:dropdown>
