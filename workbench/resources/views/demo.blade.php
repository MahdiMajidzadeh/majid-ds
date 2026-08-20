<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مجید — کیت رابط کاربری</title>

    @mdsFonts

    <style>{!! file_get_contents(\Orchestra\Testbench\workbench_path('public/demo.css')) !!}</style>

    @livewireStyles
    @fluxAppearance
</head>

@php
$orders = [
    ['name' => 'گوشی موبایل سامسونگ Galaxy S25', 'seed' => 'phone', 'status' => 'تحویل شده', 'color' => 'green', 'date' => now()->subDays(2), 'amount' => 42500000, 'original' => 48900000],
    ['name' => 'هدفون بی‌سیم AirSound Pro', 'seed' => 'headphone', 'status' => 'در حال ارسال', 'color' => 'blue', 'date' => now()->subDays(1), 'amount' => 1890000, 'original' => null],
    ['name' => 'کتاب صد سال تنهایی', 'seed' => 'book', 'status' => 'در انتظار پرداخت', 'color' => 'amber', 'date' => now()->subHours(6), 'amount' => 245000, 'original' => 350000],
    ['name' => 'ساعت هوشمند Fit Band 8', 'seed' => 'watch', 'status' => 'لغو شده', 'color' => 'red', 'date' => now()->subHours(2), 'amount' => 3200000, 'original' => null],
];

$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
    items: collect($orders),
    total: 48,
    perPage: 4,
    currentPage: 3,
    options: ['path' => '/demo'],
);
@endphp

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">

    <div class="mx-auto max-w-5xl space-y-10 px-6 py-10" x-data>

        {{-- ============================== Header ============================== --}}
        <header class="flex items-center justify-between gap-4">
            <flux:brand href="#" name="مجید دیزاین سیستم">
                <x-slot name="logo">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">م</div>
                </x-slot>
            </flux:brand>

            <div class="flex items-center gap-3">
                <flux:button href="/layouts" size="sm" variant="filled" icon="squares-2x2">چیدمان‌های صفحه</flux:button>

                <flux:profile avatar="https://picsum.photos/seed/user/64/64" name="مهدی مجیدزاده" />

                <flux:tooltip content="حالت تاریک / روشن">
                    <flux:button variant="subtle" icon="moon" aria-label="حالت تاریک" x-on:click="$flux.dark = ! $flux.dark" />
                </flux:tooltip>
            </div>
        </header>

        <flux:text>نمایشگاه کامل اجزا: همه اجزای رایگان Flux UI به‌علاوه لایه راست‌چین و فارسی‌محور mds.</flux:text>

        {{-- ============================== Navbar (ToC) ============================== --}}
        <flux:card size="sm" class="overflow-x-auto !p-2">
            <flux:navbar>
                <flux:navbar.item href="#typography" icon="language">تایپوگرافی</flux:navbar.item>
                <flux:navbar.item href="#buttons" icon="cursor-arrow-rays">دکمه‌ها</flux:navbar.item>
                <flux:navbar.item href="#badges">نشان‌ها</flux:navbar.item>
                <flux:navbar.item href="#avatars">آواتار</flux:navbar.item>
                <flux:navbar.item href="#forms" badge="۱۰" badge-color="zinc">فرم‌ها</flux:navbar.item>
                <flux:navbar.item href="#overlays">منو و مودال</flux:navbar.item>
                <flux:navbar.item href="#command" icon="command-line">کامند</flux:navbar.item>
                <flux:navbar.item href="#color-picker" icon="swatch">انتخاب رنگ</flux:navbar.item>
                <flux:navbar.item href="#table">جدول</flux:navbar.item>
                <flux:navbar.item href="#mds" badge="جدید" badge-color="lime">اجزای mds</flux:navbar.item>
            </flux:navbar>
        </flux:card>

        {{-- ============================== Typography ============================== --}}
        <flux:card id="typography" class="space-y-4">
            <flux:heading size="lg">تایپوگرافی — flux:heading / text / link / separator</flux:heading>

            <div class="space-y-2">
                <flux:heading size="xl">عنوان بزرگ — وزیرمتن</flux:heading>
                <flux:heading size="lg">عنوان متوسط</flux:heading>
                <flux:heading>عنوان معمولی</flux:heading>
                <flux:subheading>زیرعنوان برای توضیح بخش‌ها</flux:subheading>
                <flux:text>متن بدنه با <flux:link href="#">پیوند داخلی</flux:link> و اعداد فارسی: قیمت این کالا @toman(2500000) است و @jalali('2026-08-20') ثبت شده.</flux:text>
            </div>

            <flux:separator text="یا" />

            <div class="flex items-center gap-4">
                <flux:text class="text-sm">جداکننده عمودی:</flux:text>
                <flux:text>ورود</flux:text>
                <flux:separator vertical class="h-4" />
                <flux:text>ثبت‌نام</flux:text>
            </div>
        </flux:card>

        {{-- ============================== Buttons ============================== --}}
        <flux:card id="buttons" class="space-y-4">
            <flux:heading size="lg">دکمه‌ها — flux:button</flux:heading>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="primary">پرداخت و ثبت سفارش</flux:button>
                <flux:button variant="filled">افزودن به سبد</flux:button>
                <flux:button>پیش‌فرض</flux:button>
                <flux:button variant="ghost">شبح</flux:button>
                <flux:button variant="subtle">ملایم</flux:button>
                <flux:button variant="danger">حذف</flux:button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button size="sm" variant="primary" icon="shopping-cart">اندازه کوچک</flux:button>
                <flux:button size="xs">خیلی کوچک</flux:button>
                <flux:button icon="truck">پیگیری مرسوله</flux:button>
                <flux:button icon:trailing="arrow-left">ادامه فرایند خرید</flux:button>
                <flux:button icon="heart" square variant="ghost" aria-label="علاقه‌مندی" />
                <flux:button kbd="⌘S" variant="filled">ذخیره</flux:button>
            </div>
        </flux:card>

        {{-- ============================== Badges ============================== --}}
        <flux:card id="badges" class="space-y-4">
            <flux:heading size="lg">نشان‌ها — flux:badge</flux:heading>

            <div class="flex flex-wrap items-center gap-2">
                @foreach (['zinc' => 'پیش‌فرض', 'red' => 'لغو شده', 'amber' => 'در انتظار', 'lime' => 'ارسال امروز', 'green' => 'تحویل شده', 'teal' => 'اورجینال', 'blue' => 'در حال ارسال', 'indigo' => 'ویژه', 'purple' => 'اشتراک', 'pink' => 'پیشنهاد', 'rose' => 'شگفت‌انگیز'] as $color => $label)
                    <flux:badge :color="$color === 'zinc' ? null : $color">{{ $label }}</flux:badge>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:badge size="sm" color="lime">کوچک</flux:badge>
                <flux:badge size="lg" color="blue">بزرگ</flux:badge>
                <flux:badge rounded color="green" icon="check-circle">گِرد با آیکون</flux:badge>
                <flux:badge variant="solid" color="red">توپُر</flux:badge>
                <flux:badge variant="solid" color="amber" icon="bolt">فروش ویژه</flux:badge>
            </div>
        </flux:card>

        {{-- ============================== Avatars & Icons ============================== --}}
        <flux:card id="avatars" class="space-y-4">
            <flux:heading size="lg">آواتار و آیکون — flux:avatar / icon</flux:heading>

            <div class="flex flex-wrap items-center gap-4">
                <flux:avatar src="https://picsum.photos/seed/user/64/64" circle />
                <flux:avatar initials="مم" circle color="blue" />
                <flux:avatar name="سارا رضایی" color="auto" circle />
                <flux:avatar initials="فک" size="lg" color="rose" />
                <flux:avatar icon="user" size="sm" />
                <flux:avatar initials="مم" circle badge badge:color="green" badge:circle />

                <flux:avatar.group>
                    <flux:avatar circle src="https://picsum.photos/seed/a1/48/48" />
                    <flux:avatar circle src="https://picsum.photos/seed/a2/48/48" />
                    <flux:avatar circle src="https://picsum.photos/seed/a3/48/48" />
                    <flux:avatar circle initials="۳+" />
                </flux:avatar.group>
            </div>

            <flux:separator />

            <div class="flex flex-wrap items-center gap-4 text-zinc-600 dark:text-zinc-300">
                <flux:icon icon="shopping-cart" />
                <flux:icon icon="heart" variant="solid" class="text-red-500" />
                <flux:icon icon="truck" />
                <flux:icon icon="credit-card" />
                <flux:icon icon="gift" />
                <flux:icon icon="star" variant="solid" class="text-amber-400" />
                <flux:icon icon="bell" variant="mini" />
                <flux:icon icon="magnifying-glass" variant="micro" />
                <flux:icon icon="loading" />
            </div>
        </flux:card>

        {{-- ============================== Forms ============================== --}}
        <flux:card id="forms" class="space-y-6">
            <flux:heading size="lg">فرم‌ها — flux:input / textarea / select / checkbox / radio / switch / otp</flux:heading>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:input label="جستجو" placeholder="جستجو در میان کالاها..." icon="magnifying-glass" clearable kbd="⌘K" />
                <flux:input label="شماره موبایل" type="tel" placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" description="کد تأیید به این شماره ارسال می‌شود." />
                <flux:input label="رمز عبور" type="password" value="secret123" viewable />
                <flux:input label="کد معرف" value="MAJID-1405" copyable />
                <flux:input label="ایمیل" value="not-an-email" invalid />
                <flux:input type="file" label="تصویر کالا" />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:textarea label="متن دیدگاه" rows="3" placeholder="نظر خود را درباره این کالا بنویسید..." />

                <div class="space-y-6">
                    <flux:select label="استان" placeholder="انتخاب کنید...">
                        <flux:select.option>تهران</flux:select.option>
                        <flux:select.option>اصفهان</flux:select.option>
                        <flux:select.option>فارس</flux:select.option>
                        <flux:select.option>خراسان رضوی</flux:select.option>
                    </flux:select>

                    <flux:field>
                        <flux:label>کد تأیید</flux:label>
                        <div dir="ltr" class="flex justify-end">
                            <flux:otp length="5" />
                        </div>
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            <div class="grid gap-6 md:grid-cols-3">
                <flux:checkbox.group label="اعلان‌ها">
                    <flux:checkbox label="وضعیت سفارش" description="پیامک در هر مرحله از ارسال" checked />
                    <flux:checkbox label="تخفیف‌ها و پیشنهادها" />
                    <flux:checkbox label="خبرنامه هفتگی" />
                </flux:checkbox.group>

                <flux:radio.group label="روش ارسال">
                    <flux:radio value="express" label="پیک موتوری" description="تحویل امروز" checked />
                    <flux:radio value="post" label="پست پیشتاز" description="۲ تا ۴ روز کاری" />
                    <flux:radio value="pickup" label="تحویل حضوری" />
                </flux:radio.group>

                <div class="space-y-4">
                    <flux:radio.group label="نمایش" variant="segmented">
                        <flux:radio label="لیست" checked />
                        <flux:radio label="شبکه" />
                        <flux:radio label="جدول" />
                    </flux:radio.group>

                    <flux:field variant="inline">
                        <flux:label>فقط کالاهای موجود</flux:label>
                        <flux:switch checked />
                    </flux:field>

                    <flux:field variant="inline">
                        <flux:label>ارسال رایگان</flux:label>
                        <flux:switch />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        {{-- ============================== Callouts ============================== --}}
        <flux:card class="space-y-4">
            <flux:heading size="lg">اطلاعیه — flux:callout</flux:heading>

            <div class="space-y-3">
                <flux:callout icon="clock" heading="پرداخت در انتظار تأیید است" text="نتیجه پرداخت تا لحظاتی دیگر از طرف بانک اعلام می‌شود." />
                <flux:callout variant="success" icon="check-circle" heading="سفارش با موفقیت ثبت شد" text="کد پیگیری: MDS-۱۴۰۵۲۹" />
                <flux:callout variant="warning" icon="exclamation-triangle" heading="تنها ۲ عدد در انبار باقی مانده" />
                <flux:callout variant="danger" icon="x-circle" heading="پرداخت ناموفق بود" text="مبلغ کسرشده تا ۷۲ ساعت آینده به حساب شما باز می‌گردد." />
            </div>
        </flux:card>

        {{-- ============================== Overlays: tooltip / dropdown / modal / toast ============================== --}}
        <flux:card id="overlays" class="space-y-6">
            <flux:heading size="lg">تول‌تیپ، منو، مودال و توست — flux:tooltip / dropdown / modal / toast</flux:heading>

            <div class="flex flex-wrap items-center gap-3">
                <flux:tooltip content="افزودن به علاقه‌مندی‌ها">
                    <flux:button icon="heart" square variant="ghost" aria-label="علاقه‌مندی" />
                </flux:tooltip>

                <flux:tooltip content="جستجوی سریع" kbd="⌘K">
                    <flux:button icon="magnifying-glass" square aria-label="جستجو" />
                </flux:tooltip>

                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down">عملیات سفارش</flux:button>

                    <flux:menu>
                        <flux:menu.item icon="eye">مشاهده جزئیات</flux:menu.item>
                        <flux:menu.item icon="printer">چاپ فاکتور</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.submenu heading="مرتب‌سازی" icon="bars-arrow-down">
                            <flux:menu.radio checked>جدیدترین</flux:menu.radio>
                            <flux:menu.radio>ارزان‌ترین</flux:menu.radio>
                            <flux:menu.radio>پرفروش‌ترین</flux:menu.radio>
                        </flux:menu.submenu>
                        <flux:menu.separator />
                        <flux:menu.item variant="danger" icon="trash">لغو سفارش</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                <flux:modal.trigger name="confirm-delete">
                    <flux:button variant="danger" icon="trash">حذف سفارش (مودال)</flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="cart-flyout">
                    <flux:button icon="shopping-cart">سبد خرید (فلای‌اوت)</flux:button>
                </flux:modal.trigger>

                <flux:button
                    variant="filled"
                    icon="check-circle"
                    x-on:click="$flux.toast({ heading: 'انجام شد', text: 'کالا به سبد خرید اضافه شد.', variant: 'success' })"
                >توست موفق</flux:button>

                <flux:button
                    variant="filled"
                    icon="x-circle"
                    x-on:click="$flux.toast({ text: 'اتصال به درگاه پرداخت برقرار نشد.', variant: 'danger' })"
                >توست خطا</flux:button>
            </div>

            <flux:modal name="confirm-delete" class="md:w-96">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">سفارش حذف شود؟</flux:heading>
                        <flux:text class="mt-2">این عملیات قابل بازگشت نیست و سفارش شما به‌طور کامل لغو می‌شود.</flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">انصراف</flux:button>
                        </flux:modal.close>
                        <flux:button variant="danger">حذف سفارش</flux:button>
                    </div>
                </div>
            </flux:modal>

            <flux:modal name="cart-flyout" variant="flyout" class="md:w-96">
                <div class="space-y-6">
                    <flux:heading size="lg">سبد خرید</flux:heading>

                    <div class="flex items-center justify-between gap-3">
                        <flux:text>هدفون بی‌سیم AirSound Pro</flux:text>
                        <mds:quantity :value="1" :min="1" :max="3" size="sm" />
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text>جمع کل:</flux:text>
                        <mds:price :amount="1890000" />
                    </div>

                    <flux:button variant="primary" class="w-full">ادامه فرایند خرید</flux:button>
                </div>
            </flux:modal>
        </flux:card>

        {{-- ============================== Command palette ============================== --}}
        <flux:card id="command" class="space-y-6">
            <flux:heading size="lg">پالت فرمان — mds:command</flux:heading>
            <flux:text>نسخه آزاد از کامپوننت Command (که در Flux فقط در نسخه Pro موجود است) — با جستجوی فارسی، ناوبری با کیبورد و میانبر ⌘K.</flux:text>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="space-y-2">
                    <flux:text class="text-sm font-medium">درون‌خطی:</flux:text>

                    <mds:command class="max-w-md">
                        <mds:command.input placeholder="جستجوی فرمان..." clearable />

                        <mds:command.items>
                            <mds:command.heading>ناوبری</mds:command.heading>
                            <mds:command.item icon="shopping-bag" kbd="⌘O">سفارش‌های من</mds:command.item>
                            <mds:command.item icon="heart" kbd="⌘F">علاقه‌مندی‌ها</mds:command.item>
                            <mds:command.item icon="map-pin">آدرس‌های من</mds:command.item>

                            <mds:command.heading>عملیات</mds:command.heading>
                            <mds:command.item icon="truck" kbd="⌘T">پیگیری مرسوله</mds:command.item>
                            <mds:command.item icon="chat-bubble-left-right" href="#">گفتگو با پشتیبانی</mds:command.item>
                            <mds:command.item icon="arrow-right-start-on-rectangle">خروج از حساب</mds:command.item>
                        </mds:command.items>
                    </mds:command>
                </div>

                <div class="space-y-2">
                    <flux:text class="text-sm font-medium">درون مودال با میانبر ⌘K:</flux:text>

                    <flux:modal.trigger name="global-search" shortcut="cmd.k">
                        <flux:input as="button" placeholder="جستجو..." icon="magnifying-glass" kbd="⌘K" class="max-w-md" />
                    </flux:modal.trigger>
                </div>
            </div>

            <flux:modal name="global-search" variant="bare" class="my-[12vh] w-full max-w-[30rem] max-h-screen overflow-y-visible">
                <mds:command>
                    <mds:command.input placeholder="جستجو در همه‌جا..." closable autofocus />

                    <mds:command.items>
                        <mds:command.heading>پیشنهادها</mds:command.heading>
                        <mds:command.item icon="fire">پیشنهادهای شگفت‌انگیز</mds:command.item>
                        <mds:command.item icon="device-phone-mobile">گوشی موبایل</mds:command.item>
                        <mds:command.item icon="book-open">کتاب و مجله</mds:command.item>

                        <mds:command.heading>حساب کاربری</mds:command.heading>
                        <mds:command.item icon="shopping-bag" kbd="⌘O">سفارش‌های من</mds:command.item>
                        <mds:command.item icon="wallet" kbd="⌘W">کیف پول</mds:command.item>
                        <mds:command.item icon="cog-6-tooth" kbd="⌘,">تنظیمات</mds:command.item>
                    </mds:command.items>
                </mds:command>
            </flux:modal>
        </flux:card>

        {{-- ============================== Color picker ============================== --}}
        <flux:card id="color-picker" class="space-y-6">
            <flux:heading size="lg">انتخاب رنگ — mds:color-picker</flux:heading>
            <flux:text>نسخه آزاد از کامپوننت Color Picker (در Flux فقط Pro) — بوم اشباع/روشنایی، اسلایدر رنگ و شفافیت، پالت سواچ، قطره‌چکان و شش فرمت خروجی.</flux:text>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <mds:color-picker
                    label="رنگ اصلی"
                    value="#e11d48"
                    name="brand_color"
                    placeholder="#000000"
                    clearable
                    dropper
                />

                <mds:color-picker
                    label="رنگ پس‌زمینه (rgba)"
                    description="فرمت خروجی: rgba با اسلایدر شفافیت"
                    value="rgba(59, 130, 246, 0.5)"
                    format="rgba"
                />

                <mds:color-picker
                    label="سواچ‌های سفارشی"
                    value="#00c16a"
                    :swatches="[['#ef4444', 'قرمز'], ['#f59e0b', 'کهربایی'], ['#00c16a', 'سبز'], ['#3b82f6', 'آبی'], ['#000000', 'مشکی']]"
                />
            </div>

            <div class="flex items-center gap-4">
                <flux:text class="text-sm">حالت دکمه‌ای:</flux:text>
                <mds:color-picker type="button" value="#8b5cf6" />
                <mds:color-picker type="button" />
            </div>
        </flux:card>

        {{-- ============================== Breadcrumbs ============================== --}}
        <flux:card class="space-y-4">
            <flux:heading size="lg">مسیر راهنما — flux:breadcrumbs</flux:heading>

            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" icon="home" />
                <flux:breadcrumbs.item href="#">کالای دیجیتال</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#">گوشی موبایل</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>سامسونگ Galaxy S25</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" separator="slash">خانه</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#" separator="slash">سفارش‌ها</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash">MDS-۱۴۰۵۲۹</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </flux:card>

        {{-- ============================== Table + Pagination ============================== --}}
        <flux:card id="table" class="space-y-4">
            <flux:heading size="lg">جدول و صفحه‌بندی — flux:table / pagination + اجزای mds</flux:heading>

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

            <flux:pagination :paginator="$paginator" />
        </flux:card>

        {{-- ============================== Progress + Skeleton ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">پیشرفت — flux:progress</flux:heading>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <flux:text class="text-sm">تکمیل پروفایل — ۳۵٪</flux:text>
                        <flux:progress value="35" max="100" />
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-sm">ظرفیت فروش ویژه — ۷۰٪</flux:text>
                        <flux:progress value="70" max="100" color="amber" />
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-sm">امتیاز رضایت — ۹۲٪</flux:text>
                        <flux:progress value="92" max="100" color="green" />
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">اسکلتون — flux:skeleton</flux:heading>

                <flux:skeleton.group animate="shimmer" class="space-y-3">
                    <flux:skeleton class="aspect-video w-full rounded-lg" />
                    <flux:skeleton class="h-4 w-3/4" />
                    <flux:skeleton class="h-4 w-1/2" />
                    <div class="flex items-center gap-2">
                        <flux:skeleton class="size-9 rounded-full" />
                        <flux:skeleton class="h-4 w-24" />
                    </div>
                </flux:skeleton.group>
            </flux:card>
        </div>

        {{-- ============================== MDS: Rating ============================== --}}
        <flux:card id="mds" class="space-y-4">
            <flux:heading size="lg">امتیاز — mds:rating</flux:heading>

            <div class="flex flex-wrap items-center gap-8">
                <mds:rating :value="4.3" :count="126" />
                <mds:rating :value="2.5" size="lg" />
                <mds:rating :value="5" :count="12" size="sm" />
                <mds:rating :value="3.7" :fa="false" />
            </div>

            <flux:separator />

            <div class="flex items-center gap-4">
                <flux:text>ثبت امتیاز:</flux:text>
                <mds:rating.input name="score" :value="3" />
            </div>
        </flux:card>

        {{-- ============================== MDS: Quantity + Price ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">تعداد — mds:quantity</flux:heading>

                <div class="flex flex-wrap items-center gap-4">
                    <mds:quantity :value="2" :min="1" :max="5" name="qty" />
                    <mds:quantity :value="1" size="sm" />
                    <mds:quantity :value="3" size="lg" :fa="false" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">قیمت — mds:price / discount-badge</flux:heading>

                <div class="flex flex-wrap items-center gap-8">
                    <mds:price :amount="2500000" :original="3200000" size="lg" />
                    <mds:price :amount="890000" />
                    <mds:price :amount="14500000" currency="rial" size="sm" />
                    <mds:price :amount="1200000" :original="1500000" :fa="false" />
                </div>

                <div class="flex items-center gap-2">
                    <mds:discount-badge :percent="10" size="sm" />
                    <mds:discount-badge :percent="25" />
                    <mds:discount-badge :amount="80000" :original="100000" size="lg" />
                </div>
            </flux:card>
        </div>

        {{-- ============================== MDS: Stepper ============================== --}}
        <flux:card class="space-y-6">
            <flux:heading size="lg">مراحل خرید — mds:stepper</flux:heading>

            <mds:stepper :steps="['سبد خرید', 'آدرس و زمان ارسال', 'پرداخت', 'تأیید نهایی']" :current="2" class="w-full" />
        </flux:card>

        {{-- ============================== MDS: Countdown + Jalali ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">شمارش معکوس — mds:countdown</flux:heading>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <flux:text>پیشنهاد شگفت‌انگیز:</flux:text>
                        <mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" />
                    </div>

                    <mds:countdown :until="now()->addDays(2)->addHours(5)" labels size="lg" />

                    <mds:countdown :until="now()->subMinute()" expired-text="این پیشنهاد به پایان رسید" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">تاریخ شمسی — mds:jalali-date</flux:heading>

                <div class="flex flex-col gap-2 text-sm">
                    <div>امروز: <mds:jalali-date :date="now()" format="l j F Y" class="font-semibold" /></div>
                    <div>عددی: <mds:jalali-date :date="now()" format="Y/m/d" class="font-semibold" /></div>
                    <div>ثبت سفارش: <mds:jalali-date :date="now()->subHours(3)" ago class="font-semibold" /></div>
                    <div>با دستور بلید: <span class="font-semibold">@jalali('2026-08-20')</span> — <span class="font-semibold">@toman(2500000)</span></div>
                </div>
            </flux:card>
        </div>

        {{-- ============================== MDS: Product cards ============================== --}}
        <div class="space-y-4">
            <flux:heading size="lg">کارت کالا — mds:product-card</flux:heading>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <mds:product-card
                    title="گوشی موبایل سامسونگ مدل Galaxy S25 ظرفیت ۲۵۶ گیگابایت"
                    image="https://picsum.photos/seed/phone/400/400"
                    :amount="42500000"
                    :original="48900000"
                    :rating="4.6"
                    :reviews="342"
                    badge="ارسال امروز"
                    href="#"
                />

                <mds:product-card
                    title="هدفون بی‌سیم مدل AirSound Pro"
                    image="https://picsum.photos/seed/headphone/400/400"
                    :amount="1890000"
                    :rating="4.1"
                    :reviews="87"
                    href="#"
                >
                    <flux:button variant="primary" size="sm" class="w-full">افزودن به سبد</flux:button>
                </mds:product-card>

                <mds:product-card
                    title="کتاب صد سال تنهایی اثر گابریل گارسیا مارکز"
                    image="https://picsum.photos/seed/book/400/400"
                    :amount="245000"
                    :original="350000"
                    :rating="4.9"
                    :reviews="1205"
                    badge="پرفروش"
                    badge-color="amber"
                    href="#"
                />

                <mds:product-card
                    title="ساعت هوشمند مدل Fit Band 8"
                    image="https://picsum.photos/seed/watch/400/400"
                    unavailable
                    href="#"
                />
            </div>
        </div>

        {{-- ============================== MDS: Empty state ============================== --}}
        <flux:card>
            <mds:empty-state
                icon="shopping-cart"
                title="سبد خرید شما خالی است"
                description="هنوز کالایی به سبد خرید خود اضافه نکرده‌اید. از پیشنهادهای شگفت‌انگیز شروع کنید."
            >
                <flux:button variant="primary">مشاهده پیشنهادها</flux:button>
                <flux:button variant="ghost">تاریخچه سفارش‌ها</flux:button>
            </mds:empty-state>
        </flux:card>

        <footer class="pb-8 text-center">
            <flux:text class="text-sm">مجید دیزاین سیستم — ساخته‌شده روی Flux UI و Tailwind CSS</flux:text>
        </footer>
    </div>

    <flux:toast />

    @livewireScripts
    @fluxScripts
</body>
</html>
