{{--
    Sidebar navigation body. Everything between the header and the profile:
    a search box, a flat nav list, an expandable group, a spacer that pushes
    the secondary nav to the bottom, and the profile dropdown.
--}}
<flux:sidebar.search :placeholder="__('جستجوی کالا...')" kbd="⌘K" />

<flux:sidebar.nav>
    <flux:sidebar.item href="#" icon="home" current>{{ __('پیشخوان') }}</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="shopping-bag" :badge="$mdsNum(3)" badge-color="lime">{{ __('سفارش‌های من') }}</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="heart">{{ __('علاقه‌مندی‌ها') }}</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="chat-bubble-left-right" :badge="$mdsNum(1)">{{ __('دیدگاه‌ها') }}</flux:sidebar.item>

    <flux:sidebar.group :heading="__('دسته‌بندی‌ها')" icon="squares-2x2" expandable :expanded="true">
        <flux:sidebar.item href="#">{{ __('موبایل و تبلت') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('لوازم خانگی') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('کتاب و لوازم تحریر') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('مد و پوشاک') }}</flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group :heading="__('کیف پول')" icon="credit-card" expandable :expanded="false">
        <flux:sidebar.item href="#">{{ __('موجودی و تراکنش‌ها') }}</flux:sidebar.item>
        <flux:sidebar.item href="#">{{ __('کارت هدیه') }}</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>

<flux:sidebar.spacer />

<flux:sidebar.nav>
    <flux:sidebar.item href="#" icon="cog-6-tooth">{{ __('تنظیمات حساب') }}</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="question-mark-circle">{{ __('راهنما و پشتیبانی') }}</flux:sidebar.item>
</flux:sidebar.nav>

<flux:dropdown position="top" align="start">
    <flux:sidebar.profile avatar="https://picsum.photos/seed/user/64/64" :name="__('مهدی مجیدزاده')" />

    <flux:menu>
        <flux:menu.item icon="user">{{ __('پروفایل من') }}</flux:menu.item>
        <flux:menu.item icon="map-pin">{{ __('آدرس‌های من') }}</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">{{ __('خروج از حساب') }}</flux:menu.item>
    </flux:menu>
</flux:dropdown>
