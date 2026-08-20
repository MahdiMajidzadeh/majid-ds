{{--
    Sidebar navigation body. Everything between the header and the profile:
    a search box, a flat nav list, an expandable group, a spacer that pushes
    the secondary nav to the bottom, and the profile dropdown.
--}}
<flux:sidebar.search placeholder="جستجوی کالا..." kbd="⌘K" />

<flux:sidebar.nav>
    <flux:sidebar.item href="#" icon="home" current>پیشخوان</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="shopping-bag" badge="۳" badge-color="lime">سفارش‌های من</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="heart">علاقه‌مندی‌ها</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="chat-bubble-left-right" badge="۱">دیدگاه‌ها</flux:sidebar.item>

    <flux:sidebar.group heading="دسته‌بندی‌ها" icon="squares-2x2" expandable :expanded="true">
        <flux:sidebar.item href="#">موبایل و تبلت</flux:sidebar.item>
        <flux:sidebar.item href="#">لوازم خانگی</flux:sidebar.item>
        <flux:sidebar.item href="#">کتاب و لوازم تحریر</flux:sidebar.item>
        <flux:sidebar.item href="#">مد و پوشاک</flux:sidebar.item>
    </flux:sidebar.group>

    <flux:sidebar.group heading="کیف پول" icon="credit-card" expandable :expanded="false">
        <flux:sidebar.item href="#">موجودی و تراکنش‌ها</flux:sidebar.item>
        <flux:sidebar.item href="#">کارت هدیه</flux:sidebar.item>
    </flux:sidebar.group>
</flux:sidebar.nav>

<flux:sidebar.spacer />

<flux:sidebar.nav>
    <flux:sidebar.item href="#" icon="cog-6-tooth">تنظیمات حساب</flux:sidebar.item>
    <flux:sidebar.item href="#" icon="question-mark-circle">راهنما و پشتیبانی</flux:sidebar.item>
</flux:sidebar.nav>

<flux:dropdown position="top" align="start">
    <flux:sidebar.profile avatar="https://picsum.photos/seed/user/64/64" name="مهدی مجیدزاده" />

    <flux:menu>
        <flux:menu.item icon="user">پروفایل من</flux:menu.item>
        <flux:menu.item icon="map-pin">آدرس‌های من</flux:menu.item>
        <flux:menu.separator />
        <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">خروج از حساب</flux:menu.item>
    </flux:menu>
</flux:dropdown>
