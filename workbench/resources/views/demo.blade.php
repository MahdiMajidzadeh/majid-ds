<!DOCTYPE html>
<html lang="{{ $mdsLocale }}" dir="{{ $mdsDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('مجید — کیت رابط کاربری') }}</title>

    @mdsFonts

    <style>{!! file_get_contents(\Orchestra\Testbench\workbench_path('public/demo.css')) !!}</style>

    @livewireStyles
    @fluxAppearance
</head>

@php
$orders = [
    ['name' => __('گوشی موبایل سامسونگ Galaxy S25'), 'seed' => 'phone', 'status' => __('تحویل شده'), 'color' => 'green', 'date' => now()->subDays(2), 'amount' => 42500000, 'original' => 48900000],
    ['name' => __('هدفون بی‌سیم AirSound Pro'), 'seed' => 'headphone', 'status' => __('در حال ارسال'), 'color' => 'blue', 'date' => now()->subDays(1), 'amount' => 1890000, 'original' => null],
    ['name' => __('کتاب صد سال تنهایی'), 'seed' => 'book', 'status' => __('در انتظار پرداخت'), 'color' => 'amber', 'date' => now()->subHours(6), 'amount' => 245000, 'original' => 350000],
    ['name' => __('ساعت هوشمند Fit Band 8'), 'seed' => 'watch', 'status' => __('لغو شده'), 'color' => 'red', 'date' => now()->subHours(2), 'amount' => 3200000, 'original' => null],
];

$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
    items: collect($orders),
    total: 48,
    perPage: 4,
    currentPage: 3,
    options: ['path' => $mdsUrl('/demo')],
);
@endphp

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">

    <div class="mx-auto max-w-5xl space-y-10 px-6 py-10" x-data>

        {{-- ============================== Header ============================== --}}
        <header class="flex items-center justify-between gap-4">
            <flux:brand href="#" :name="__('مجید دیزاین سیستم')">
                <x-slot name="logo">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">{{ __('م') }}</div>
                </x-slot>
            </flux:brand>

            <div class="flex items-center gap-3">
                <flux:button :href="$mdsDocsHref" size="sm" variant="ghost" icon="book-open">{{ __('مستندات') }}</flux:button>

                <flux:button :href="$mdsUrl('/layouts')" size="sm" variant="filled" icon="squares-2x2">{{ __('چیدمان‌های صفحه') }}</flux:button>

                <flux:button :href="$mdsAlt('/demo')" size="sm" variant="ghost" icon="language">{{ $mdsAltLabel }}</flux:button>

                <flux:profile avatar="https://picsum.photos/seed/user/64/64" :name="__('مهدی مجیدزاده')" />

                <flux:tooltip :content="__('حالت تاریک / روشن')">
                    <flux:button variant="subtle" icon="moon" aria-label="{{ __('حالت تاریک') }}" x-on:click="$flux.dark = ! $flux.dark" />
                </flux:tooltip>
            </div>
        </header>

        <flux:text>{{ __('نمایشگاه کامل اجزا: همه اجزای رایگان Flux UI به‌علاوه لایه راست‌چین و فارسی‌محور mds.') }}</flux:text>

        {{--
            Not translated on purpose: this note only exists for the English page,
            and it is about which Persian strings are left on it.
        --}}
        @unless ($mdsFa)
            <flux:callout icon="information-circle" heading="Some component output is Persian-only">
                <flux:callout.text>
                    Digits, separators and the currency label follow <code>config('mds.persian_digits')</code>
                    and <code>config('mds.currency')</code>, so they are Latin here. A handful of strings are
                    baked into the kit with no prop to override them, and stay Persian on this page:
                </flux:callout.text>

                <flux:callout.text class="mt-2">
                    <div class="space-y-1">
                        <div>• <code>mds:discount-badge</code> — the <code dir="rtl">٪</code> sign</div>
                        <div>• <code>mds:countdown</code> — the unit labels <code dir="rtl">روز / ساعت / دقیقه / ثانیه</code> and the expiry text <code dir="rtl">به پایان رسید</code></div>
                        <div>• <code>mds:product-card</code> — <code dir="rtl">ناموجود</code> for an out-of-stock item</div>
                        <div>• <code>mds:jalali-date</code> — the month names, and the relative dates from <code>Persian::ago()</code></div>
                        <div>• the <code>@@toman</code> and <code>@@jalali</code> directives, which are Persian by definition</div>
                    </div>
                </flux:callout.text>
            </flux:callout>
        @endunless

        {{-- ============================== Navbar (ToC) ============================== --}}
        <flux:card size="sm" class="overflow-x-auto !p-2">
            <flux:navbar>
                <flux:navbar.item href="#typography" icon="language">{{ __('تایپوگرافی') }}</flux:navbar.item>
                <flux:navbar.item href="#buttons" icon="cursor-arrow-rays">{{ __('دکمه‌ها') }}</flux:navbar.item>
                <flux:navbar.item href="#badges">{{ __('نشان‌ها') }}</flux:navbar.item>
                <flux:navbar.item href="#avatars">{{ __('آواتار') }}</flux:navbar.item>
                <flux:navbar.item href="#forms" :badge="$mdsNum(10)" badge-color="zinc">{{ __('فرم‌ها') }}</flux:navbar.item>
                <flux:navbar.item href="#overlays">{{ __('منو و مودال') }}</flux:navbar.item>
                <flux:navbar.item href="#command" icon="command-line">{{ __('کامند') }}</flux:navbar.item>
                <flux:navbar.item href="#color-picker" icon="swatch">{{ __('انتخاب رنگ') }}</flux:navbar.item>
                <flux:navbar.item href="#file-upload" icon="cloud-arrow-up">{{ __('بارگذاری فایل') }}</flux:navbar.item>
                <flux:navbar.item href="#composer" icon="chat-bubble-oval-left">{{ __('گفتگو') }}</flux:navbar.item>
                <flux:navbar.item href="#preview-card" icon="link">{{ __('پیش‌نمایش پیوند') }}</flux:navbar.item>
                <flux:navbar.item href="#timeline" icon="clock">{{ __('خط زمانی') }}</flux:navbar.item>
                <flux:navbar.item href="#icons" icon="sparkles">{{ __('آیکون‌ها') }}</flux:navbar.item>
                <flux:navbar.item href="#table">{{ __('جدول') }}</flux:navbar.item>
                <flux:navbar.item href="#mds" :badge="__('جدید')" badge-color="lime">{{ __('اجزای mds') }}</flux:navbar.item>
            </flux:navbar>
        </flux:card>

        {{-- ============================== Typography ============================== --}}
        <flux:card id="typography" class="space-y-4">
            <flux:heading size="lg">{{ __('تایپوگرافی — flux:heading / text / link / separator') }}</flux:heading>

            <div class="space-y-2">
                <flux:heading size="xl">{{ __('عنوان بزرگ — وزیرمتن') }}</flux:heading>
                <flux:heading size="lg">{{ __('عنوان متوسط') }}</flux:heading>
                <flux:heading>{{ __('عنوان معمولی') }}</flux:heading>
                <flux:subheading>{{ __('زیرعنوان برای توضیح بخش‌ها') }}</flux:subheading>
                <flux:text>{{ __('متن بدنه با') }} <flux:link href="#">{{ __('پیوند داخلی') }}</flux:link> {{ __('و اعداد فارسی: قیمت این کالا') }} @toman(2500000) {{ __('است و') }} @jalali('2026-08-20') {{ __('ثبت شده.') }}</flux:text>
            </div>

            <flux:separator :text="__('یا')" />

            <div class="flex items-center gap-4">
                <flux:text class="text-sm">{{ __('جداکننده عمودی:') }}</flux:text>
                <flux:text>{{ __('ورود') }}</flux:text>
                <flux:separator vertical class="h-4" />
                <flux:text>{{ __('ثبت‌نام') }}</flux:text>
            </div>
        </flux:card>

        {{-- ============================== Buttons ============================== --}}
        <flux:card id="buttons" class="space-y-4">
            <flux:heading size="lg">{{ __('دکمه‌ها — flux:button') }}</flux:heading>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="primary">{{ __('پرداخت و ثبت سفارش') }}</flux:button>
                <flux:button variant="filled">{{ __('افزودن به سبد') }}</flux:button>
                <flux:button>{{ __('پیش‌فرض') }}</flux:button>
                <flux:button variant="ghost">{{ __('شبح') }}</flux:button>
                <flux:button variant="subtle">{{ __('ملایم') }}</flux:button>
                <flux:button variant="danger">{{ __('حذف') }}</flux:button>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button size="sm" variant="primary" icon="shopping-cart">{{ __('اندازه کوچک') }}</flux:button>
                <flux:button size="xs">{{ __('خیلی کوچک') }}</flux:button>
                <flux:button icon="truck">{{ __('پیگیری مرسوله') }}</flux:button>
                <flux:button icon:trailing="{{ $mdsForward }}">{{ __('ادامه فرایند خرید') }}</flux:button>
                <flux:button icon="heart" square variant="ghost" aria-label="{{ __('علاقه‌مندی') }}" />
                <flux:button kbd="⌘S" variant="filled">{{ __('ذخیره') }}</flux:button>
            </div>
        </flux:card>

        {{-- ============================== Badges ============================== --}}
        <flux:card id="badges" class="space-y-4">
            <flux:heading size="lg">{{ __('نشان‌ها — flux:badge') }}</flux:heading>

            <div class="flex flex-wrap items-center gap-2">
                @foreach (['zinc' => 'پیش‌فرض', 'red' => 'لغو شده', 'amber' => 'در انتظار', 'lime' => 'ارسال امروز', 'green' => 'تحویل شده', 'teal' => 'اورجینال', 'blue' => 'در حال ارسال', 'indigo' => 'ویژه', 'purple' => 'اشتراک', 'pink' => 'پیشنهاد', 'rose' => 'شگفت‌انگیز'] as $color => $label)
                    <flux:badge :color="$color === 'zinc' ? null : $color">{{ __($label) }}</flux:badge>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:badge size="sm" color="lime">{{ __('کوچک') }}</flux:badge>
                <flux:badge size="lg" color="blue">{{ __('بزرگ') }}</flux:badge>
                <flux:badge rounded color="green" icon="check-circle">{{ __('گِرد با آیکون') }}</flux:badge>
                <flux:badge variant="solid" color="red">{{ __('توپُر') }}</flux:badge>
                <flux:badge variant="solid" color="amber" icon="bolt">{{ __('فروش ویژه') }}</flux:badge>
            </div>
        </flux:card>

        {{-- ============================== Avatars & Icons ============================== --}}
        <flux:card id="avatars" class="space-y-4">
            <flux:heading size="lg">{{ __('آواتار و آیکون — flux:avatar / icon') }}</flux:heading>

            <div class="flex flex-wrap items-center gap-4">
                <flux:avatar src="https://picsum.photos/seed/user/64/64" circle />
                <flux:avatar :initials="__('مم')" circle color="blue" />
                <flux:avatar :name="__('سارا رضایی')" color="auto" circle />
                <flux:avatar :initials="__('فک')" size="lg" color="rose" />
                <flux:avatar icon="user" size="sm" />
                <flux:avatar :initials="__('مم')" circle badge badge:color="green" badge:circle />

                <flux:avatar.group>
                    <flux:avatar circle src="https://picsum.photos/seed/a1/48/48" />
                    <flux:avatar circle src="https://picsum.photos/seed/a2/48/48" />
                    <flux:avatar circle src="https://picsum.photos/seed/a3/48/48" />
                    <flux:avatar circle :initials="$mdsNum(3).'+'" />
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
            <flux:heading size="lg">{{ __('فرم‌ها — flux:input / textarea / select / checkbox / radio / switch / otp') }}</flux:heading>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:input :label="__('جستجو')" :placeholder="__('جستجو در میان کالاها...')" icon="magnifying-glass" clearable kbd="⌘K" />
                <flux:input :label="__('شماره موبایل')" type="tel" :placeholder="__('۰۹۱۲ ۳۴۵ ۶۷۸۹')" :description="__('کد تأیید به این شماره ارسال می‌شود.')" />
                <flux:input :label="__('رمز عبور')" type="password" value="secret123" viewable />
                <flux:input :label="__('کد معرف')" value="MAJID-1405" copyable />
                <flux:input :label="__('ایمیل')" value="not-an-email" invalid />
                <flux:input type="file" :label="__('تصویر کالا')" />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <flux:textarea :label="__('متن دیدگاه')" rows="3" :placeholder="__('نظر خود را درباره این کالا بنویسید...')" />

                <div class="space-y-6">
                    <flux:select :label="__('استان')" :placeholder="__('انتخاب کنید...')">
                        <flux:select.option>{{ __('تهران') }}</flux:select.option>
                        <flux:select.option>{{ __('اصفهان') }}</flux:select.option>
                        <flux:select.option>{{ __('فارس') }}</flux:select.option>
                        <flux:select.option>{{ __('خراسان رضوی') }}</flux:select.option>
                    </flux:select>

                    <flux:field>
                        <flux:label>{{ __('کد تأیید') }}</flux:label>
                        <div dir="ltr" class="flex justify-end">
                            <flux:otp length="5" />
                        </div>
                    </flux:field>
                </div>
            </div>

            <flux:separator />

            <div class="grid gap-6 md:grid-cols-3">
                <flux:checkbox.group :label="__('اعلان‌ها')">
                    <flux:checkbox :label="__('وضعیت سفارش')" :description="__('پیامک در هر مرحله از ارسال')" checked />
                    <flux:checkbox :label="__('تخفیف‌ها و پیشنهادها')" />
                    <flux:checkbox :label="__('خبرنامه هفتگی')" />
                </flux:checkbox.group>

                <flux:radio.group :label="__('روش ارسال')">
                    <flux:radio value="express" :label="__('پیک موتوری')" :description="__('تحویل امروز')" checked />
                    <flux:radio value="post" :label="__('پست پیشتاز')" :description="__('۲ تا ۴ روز کاری')" />
                    <flux:radio value="pickup" :label="__('تحویل حضوری')" />
                </flux:radio.group>

                <div class="space-y-4">
                    <flux:radio.group :label="__('نمایش')" variant="segmented">
                        <flux:radio :label="__('لیست')" checked />
                        <flux:radio :label="__('شبکه')" />
                        <flux:radio :label="__('جدول')" />
                    </flux:radio.group>

                    <flux:field variant="inline">
                        <flux:label>{{ __('فقط کالاهای موجود') }}</flux:label>
                        <flux:switch checked />
                    </flux:field>

                    <flux:field variant="inline">
                        <flux:label>{{ __('ارسال رایگان') }}</flux:label>
                        <flux:switch />
                    </flux:field>
                </div>
            </div>
        </flux:card>

        {{-- ============================== Callouts ============================== --}}
        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('اطلاعیه — flux:callout') }}</flux:heading>

            <div class="space-y-3">
                <flux:callout icon="clock" :heading="__('پرداخت در انتظار تأیید است')" :text="__('نتیجه پرداخت تا لحظاتی دیگر از طرف بانک اعلام می‌شود.')" />
                <flux:callout variant="success" icon="check-circle" :heading="__('سفارش با موفقیت ثبت شد')" :text="__('کد پیگیری: MDS-۱۴۰۵۲۹')" />
                <flux:callout variant="warning" icon="exclamation-triangle" :heading="__('تنها ۲ عدد در انبار باقی مانده')" />
                <flux:callout variant="danger" icon="x-circle" :heading="__('پرداخت ناموفق بود')" :text="__('مبلغ کسرشده تا ۷۲ ساعت آینده به حساب شما باز می‌گردد.')" />
            </div>
        </flux:card>

        {{-- ============================== Overlays: tooltip / dropdown / modal / toast ============================== --}}
        <flux:card id="overlays" class="space-y-6">
            <flux:heading size="lg">{{ __('تول‌تیپ، منو، مودال و توست — flux:tooltip / dropdown / modal / toast') }}</flux:heading>

            <div class="flex flex-wrap items-center gap-3">
                <flux:tooltip :content="__('افزودن به علاقه‌مندی‌ها')">
                    <flux:button icon="heart" square variant="ghost" aria-label="{{ __('علاقه‌مندی') }}" />
                </flux:tooltip>

                <flux:tooltip :content="__('جستجوی سریع')" kbd="⌘K">
                    <flux:button icon="magnifying-glass" square aria-label="{{ __('جستجو') }}" />
                </flux:tooltip>

                <flux:dropdown>
                    <flux:button icon:trailing="chevron-down">{{ __('عملیات سفارش') }}</flux:button>

                    <flux:menu>
                        <flux:menu.item icon="eye">{{ __('مشاهده جزئیات') }}</flux:menu.item>
                        <flux:menu.item icon="printer">{{ __('چاپ فاکتور') }}</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.submenu :heading="__('مرتب‌سازی')" icon="bars-arrow-down">
                            <flux:menu.radio checked>{{ __('جدیدترین') }}</flux:menu.radio>
                            <flux:menu.radio>{{ __('ارزان‌ترین') }}</flux:menu.radio>
                            <flux:menu.radio>{{ __('پرفروش‌ترین') }}</flux:menu.radio>
                        </flux:menu.submenu>
                        <flux:menu.separator />
                        <flux:menu.item variant="danger" icon="trash">{{ __('لغو سفارش') }}</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                <flux:modal.trigger name="confirm-delete">
                    <flux:button variant="danger" icon="trash">{{ __('حذف سفارش (مودال)') }}</flux:button>
                </flux:modal.trigger>

                <flux:modal.trigger name="cart-flyout">
                    <flux:button icon="shopping-cart">{{ __('سبد خرید (فلای‌اوت)') }}</flux:button>
                </flux:modal.trigger>

                <flux:button
                    variant="filled"
                    icon="check-circle"
                    x-on:click="$flux.toast({ heading: '{{ __('انجام شد') }}', text: '{{ __('کالا به سبد خرید اضافه شد.') }}', variant: 'success' })"
                >{{ __('توست موفق') }}</flux:button>

                <flux:button
                    variant="filled"
                    icon="x-circle"
                    x-on:click="$flux.toast({ text: '{{ __('اتصال به درگاه پرداخت برقرار نشد.') }}', variant: 'danger' })"
                >{{ __('توست خطا') }}</flux:button>
            </div>

            <flux:modal name="confirm-delete" class="md:w-96">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">{{ __('سفارش حذف شود؟') }}</flux:heading>
                        <flux:text class="mt-2">{{ __('این عملیات قابل بازگشت نیست و سفارش شما به‌طور کامل لغو می‌شود.') }}</flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />
                        <flux:modal.close>
                            <flux:button variant="ghost">{{ __('انصراف') }}</flux:button>
                        </flux:modal.close>
                        <flux:button variant="danger">{{ __('حذف سفارش') }}</flux:button>
                    </div>
                </div>
            </flux:modal>

            <flux:modal name="cart-flyout" variant="flyout" class="md:w-96">
                <div class="space-y-6">
                    <flux:heading size="lg">{{ __('سبد خرید') }}</flux:heading>

                    <div class="flex items-center justify-between gap-3">
                        <flux:text>{{ __('هدفون بی‌سیم AirSound Pro') }}</flux:text>
                        <mds:quantity :value="1" :min="1" :max="3" size="sm" />
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text>{{ __('جمع کل:') }}</flux:text>
                        <mds:price :amount="1890000" />
                    </div>

                    <flux:button variant="primary" class="w-full">{{ __('ادامه فرایند خرید') }}</flux:button>
                </div>
            </flux:modal>
        </flux:card>

        {{-- ============================== Command palette ============================== --}}
        <flux:card id="command" class="space-y-6">
            <flux:heading size="lg">{{ __('پالت فرمان — mds:command') }}</flux:heading>
            <flux:text>{{ __('نسخه آزاد از کامپوننت Command (که در Flux فقط در نسخه Pro موجود است) — با جستجوی فارسی، ناوبری با کیبورد و میانبر ⌘K.') }}</flux:text>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="space-y-2">
                    <flux:text class="text-sm font-medium">{{ __('درون‌خطی:') }}</flux:text>

                    <mds:command class="max-w-md">
                        <mds:command.input :placeholder="__('جستجوی فرمان...')" clearable />

                        <mds:command.items :empty="__('نتیجه‌ای یافت نشد.')">
                            <mds:command.heading>{{ __('ناوبری') }}</mds:command.heading>
                            <mds:command.item icon="shopping-bag" kbd="⌘O">{{ __('سفارش‌های من') }}</mds:command.item>
                            <mds:command.item icon="heart" kbd="⌘F">{{ __('علاقه‌مندی‌ها') }}</mds:command.item>
                            <mds:command.item icon="map-pin">{{ __('آدرس‌های من') }}</mds:command.item>

                            <mds:command.heading>{{ __('عملیات') }}</mds:command.heading>
                            <mds:command.item icon="truck" kbd="⌘T">{{ __('پیگیری مرسوله') }}</mds:command.item>
                            <mds:command.item icon="chat-bubble-left-right" href="#">{{ __('گفتگو با پشتیبانی') }}</mds:command.item>
                            <mds:command.item icon="arrow-right-start-on-rectangle">{{ __('خروج از حساب') }}</mds:command.item>
                        </mds:command.items>
                    </mds:command>
                </div>

                <div class="space-y-2">
                    <flux:text class="text-sm font-medium">{{ __('درون مودال با میانبر ⌘K:') }}</flux:text>

                    <flux:modal.trigger name="global-search" shortcut="cmd.k">
                        <flux:input as="button" :placeholder="__('جستجو...')" icon="magnifying-glass" kbd="⌘K" class="max-w-md" />
                    </flux:modal.trigger>
                </div>
            </div>

            <flux:modal name="global-search" variant="bare" class="my-[12vh] w-full max-w-[30rem] max-h-screen overflow-y-visible">
                <mds:command>
                    <mds:command.input :placeholder="__('جستجو در همه‌جا...')" closable autofocus />

                    <mds:command.items :empty="__('نتیجه‌ای یافت نشد.')">
                        <mds:command.heading>{{ __('پیشنهادها') }}</mds:command.heading>
                        <mds:command.item icon="fire">{{ __('پیشنهادهای شگفت‌انگیز') }}</mds:command.item>
                        <mds:command.item icon="device-phone-mobile">{{ __('گوشی موبایل') }}</mds:command.item>
                        <mds:command.item icon="book-open">{{ __('کتاب و مجله') }}</mds:command.item>

                        <mds:command.heading>{{ __('حساب کاربری') }}</mds:command.heading>
                        <mds:command.item icon="shopping-bag" kbd="⌘O">{{ __('سفارش‌های من') }}</mds:command.item>
                        <mds:command.item icon="wallet" kbd="⌘W">{{ __('کیف پول') }}</mds:command.item>
                        <mds:command.item icon="cog-6-tooth" kbd="⌘,">{{ __('تنظیمات') }}</mds:command.item>
                    </mds:command.items>
                </mds:command>
            </flux:modal>
        </flux:card>

        {{-- ============================== Color picker ============================== --}}
        <flux:card id="color-picker" class="space-y-6">
            <flux:heading size="lg">{{ __('انتخاب رنگ — mds:color-picker') }}</flux:heading>
            <flux:text>{{ __('نسخه آزاد از کامپوننت Color Picker (در Flux فقط Pro) — بوم اشباع/روشنایی، اسلایدر رنگ و شفافیت، پالت سواچ، قطره‌چکان و شش فرمت خروجی.') }}</flux:text>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <mds:color-picker
                    :label="__('رنگ اصلی')"
                    value="#e11d48"
                    name="brand_color"
                    placeholder="#000000"
                    clearable
                    dropper
                />

                <mds:color-picker
                    :label="__('رنگ پس‌زمینه (rgba)')"
                    :description="__('فرمت خروجی: rgba با اسلایدر شفافیت')"
                    value="rgba(59, 130, 246, 0.5)"
                    format="rgba"
                />

                <mds:color-picker
                    :label="__('سواچ‌های سفارشی')"
                    value="#00c16a"
                    :swatches="[['#ef4444', __('قرمز')], ['#f59e0b', __('کهربایی')], ['#00c16a', __('سبز')], ['#3b82f6', __('آبی')], ['#000000', __('مشکی')]]"
                />
            </div>

            <div class="flex items-center gap-4">
                <flux:text class="text-sm">{{ __('حالت دکمه‌ای:') }}</flux:text>
                <mds:color-picker type="button" value="#8b5cf6" />
                <mds:color-picker type="button" />
            </div>
        </flux:card>

        {{-- ============================== File upload ============================== --}}
        <flux:card id="file-upload" class="space-y-6">
            <flux:heading size="lg">{{ __('بارگذاری فایل — mds:file-upload') }}</flux:heading>
            <flux:text>{{ __('نسخه آزاد از کامپوننت File Upload (در Flux فقط Pro) — کشیدن و رها کردن، انتخاب با کلیک، نوار پیشرفت آپلود Livewire، و فهرست فایل‌ها با حجم فارسی.') }}</flux:text>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <mds:file-upload name="photos" :label="__('بارگذاری تصاویر')" :description="__('می‌توانید چند فایل را همزمان انتخاب کنید.')" accept="image/*" multiple>
                        <mds:file-upload.dropzone
                            :heading="__('فایل‌ها را اینجا رها کنید یا کلیک کنید')"
                            :text="__('JPG، PNG یا GIF تا ۱۰ مگابایت')"
                        />
                    </mds:file-upload>

                    <div class="flex flex-col gap-2">
                        <mds:file-item heading="Profile_pic.jpg" image="https://picsum.photos/seed/phone/80/80" :size="162400" :text="$mdsBytes(162400)">
                            <x-slot name="actions">
                                <mds:file-item.remove :label="__('حذف Profile_pic.jpg')" />
                            </x-slot>
                        </mds:file-item>

                        <mds:file-item :heading="__('قرارداد-فروش.pdf')" :size="2411724" :text="$mdsBytes(2411724)">
                            <x-slot name="actions">
                                <mds:file-item.remove :label="__('حذف قرارداد-فروش.pdf')" />
                            </x-slot>
                        </mds:file-item>

                        <mds:file-item heading="archive.zip" :text="__('حجم فایل بیش از ۱۰ مگابایت است.')" icon="exclamation-triangle" invalid>
                            <x-slot name="actions">
                                <mds:file-item.remove :label="__('حذف archive.zip')" />
                            </x-slot>
                        </mds:file-item>
                    </div>
                </div>

                <div class="space-y-6">
                    <mds:file-upload name="invoice" :label="__('حالت فشرده (inline)')" accept="application/pdf">
                        <mds:file-upload.dropzone
                            :heading="__('فایل را رها کنید یا کلیک کنید')"
                            :text="__('فقط PDF تا ۵ مگابایت')"
                            inline
                        />
                    </mds:file-upload>

                    <mds:file-upload name="attachment" :label="__('با نوار پیشرفت')">
                        <mds:file-upload.dropzone
                            :heading="__('افزودن پیوست')"
                            :text="__('هر فرمتی تا ۲۰ مگابایت')"
                            inline
                            with-progress
                        />
                    </mds:file-upload>

                    <mds:file-upload :label="__('غیرفعال')" disabled>
                        <mds:file-upload.dropzone :heading="__('بارگذاری در دسترس نیست')" :text="__('ابتدا سفارش را ثبت کنید')" inline />
                    </mds:file-upload>

                    <mds:file-upload name="broken" :label="__('با خطای اعتبارسنجی')" :error="__('حجم فایل انتخابی بیش از حد مجاز است.')">
                        <mds:file-upload.dropzone :heading="__('تصویر کالا')" :text="__('JPG یا PNG تا ۲ مگابایت')" inline />
                    </mds:file-upload>
                </div>
            </div>

            <flux:separator :text="__('بارگذارِ سفارشی (آواتار)')" />

            <div class="flex items-center gap-4">
                <mds:file-upload name="avatar" accept="image/*">
                    <div class="relative flex size-20 cursor-pointer items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 transition-colors hover:bg-zinc-200 in-data-dragging:border-accent dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15">
                        <mds:icon icon="user" class="text-zinc-500 dark:text-zinc-400" />

                        <div class="absolute bottom-0 end-0 rounded-full bg-white dark:bg-zinc-800">
                            <mds:icon icon="arrow-up-circle" class="size-5 text-zinc-500 dark:text-zinc-400" />
                        </div>
                    </div>
                </mds:file-upload>

                <flux:text>{!! __('هر HTML دلخواهی داخل <code>&lt;mds:file-upload&gt;</code> رفتار کامل بارگذاری را می‌گیرد؛ با <code>in-data-dragging:</code> و <code>in-data-loading:</code> می‌توانید حالت‌ها را استایل بدهید.') !!}</flux:text>
            </div>
        </flux:card>

        {{-- ============================== Composer ============================== --}}
        <flux:card id="composer" class="space-y-6">
            <flux:heading size="lg">{{ __('گفتگو و پرامپت — mds:composer') }}</flux:heading>
            <flux:text>{{ __('نسخه آزاد از کامپوننت Composer (در Flux فقط Pro) — ورودی چندخطی که با متن رشد می‌کند، نوار عملیات، ارسال با Ctrl/⌘ + Enter و شمارشگر نویسه با ارقام فارسی.') }}</flux:text>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-3">
                    <flux:subheading>{{ __('پرامپت با نوار عملیات') }}</flux:subheading>

                    <form wire:submit="send">
                        <mds:composer :label="__('پیام')" label:sr-only :placeholder="__('چطور می‌توانم کمکتان کنم؟')" :maxlength="500" counter>
                            <x-slot name="footer">
                                <span>{{ __('برای ارسال Ctrl + Enter بزنید.') }}</span>
                            </x-slot>

                            <x-slot name="actionsLeading">
                                <flux:button size="sm" variant="subtle" square :aria-label="__('پیوست فایل')"><mds:icon icon="paper-clip" class="size-4" /></flux:button>
                                <flux:button size="sm" variant="subtle" square :aria-label="__('تنظیمات')"><mds:icon icon="adjustments-horizontal" class="size-4" /></flux:button>
                            </x-slot>

                            <x-slot name="actionsTrailing">
                                <flux:button size="sm" variant="filled" square :aria-label="__('ضبط صدا')"><mds:icon icon="microphone" class="size-4" /></flux:button>
                                <flux:button type="submit" size="sm" variant="primary" square :aria-label="__('ارسال پیام')"><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                            </x-slot>
                        </mds:composer>
                    </form>

                    <flux:subheading>{{ __('با پیش‌نمایش پیوست (اسلات header)') }}</flux:subheading>

                    <mds:composer :placeholder="__('توضیحی برای این تصویر بنویسید...')" rows="2">
                        <x-slot name="header">
                            <mds:file-item :heading="__('گوشی-گلکسی.jpg')" image="https://picsum.photos/seed/phone/80/80" :size="162400" :text="$mdsBytes(162400)" class="w-full max-w-64">
                                <x-slot name="actions">
                                    <mds:file-item.remove :label="__('حذف تصویر')" />
                                </x-slot>
                            </mds:file-item>
                        </x-slot>

                        <x-slot name="actionsTrailing">
                            <flux:button type="submit" size="sm" variant="primary" square :aria-label="__('ارسال پیام')"><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                        </x-slot>
                    </mds:composer>
                </div>

                <div class="space-y-3">
                    <flux:subheading>{{ __('گفتگو با ارسال روی Enter') }}</flux:subheading>

                    <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-white/10">
                        <div class="flex justify-start">
                            <div class="max-w-[80%] rounded-2xl rounded-ss-sm bg-zinc-100 px-3 py-2 text-sm dark:bg-white/10">
                                {{ __('سلام! سفارش من کِی می‌رسد؟') }}
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <div class="max-w-[80%] rounded-2xl rounded-se-sm bg-accent px-3 py-2 text-sm text-accent-foreground">
                                {{ __('مرسوله شما در این تاریخ به دستتان می‌رسد:') }} <mds:jalali-date :date="now()->addDays(2)" dir="auto" />
                            </div>
                        </div>

                        <form wire:submit="send">
                            <mds:composer :label="__('پیام')" label:sr-only rows="1" max-rows="6" submit="enter" inline :placeholder="__('پیام خود را بنویسید...')">
                                <x-slot name="actionsLeading">
                                    <flux:button size="sm" variant="ghost" square :aria-label="__('پیوست فایل')"><mds:icon icon="paper-clip" class="size-4" /></flux:button>
                                </x-slot>

                                <x-slot name="actionsTrailing">
                                    <flux:button type="submit" size="sm" variant="primary" square :aria-label="__('ارسال پیام')"><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                                </x-slot>
                            </mds:composer>
                        </form>
                    </div>

                    <flux:subheading>{{ __('حالت فرم (variant="input") و حالت‌های خطا') }}</flux:subheading>

                    <mds:composer variant="input" :label="__('پیام پشتیبانی')" :description="__('پاسخ حداکثر تا ۲۴ ساعت آینده ارسال می‌شود.')" :placeholder="__('مشکل را توضیح دهید...')" rows="3" max-rows="6">
                        <x-slot name="actionsTrailing">
                            <flux:button type="submit" size="sm" variant="primary">{{ __('ارسال پیام') }}</flux:button>
                        </x-slot>
                    </mds:composer>

                    <mds:composer name="prompt" :label="__('با خطای اعتبارسنجی')" :error="__('نوشتن پیام الزامی است.')" rows="1" inline>
                        <x-slot name="actionsTrailing">
                            <flux:button type="submit" size="sm" variant="primary" square :aria-label="__('ارسال پیام')"><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                        </x-slot>
                    </mds:composer>

                    <mds:composer :label="__('غیرفعال')" disabled rows="1" inline :placeholder="__('ابتدا وارد حساب خود شوید')">
                        <x-slot name="actionsTrailing">
                            <flux:button type="submit" size="sm" variant="primary" square :aria-label="__('ارسال پیام')"><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                        </x-slot>
                    </mds:composer>
                </div>
            </div>
        </flux:card>

        {{-- ============================== Preview card ============================== --}}
        <flux:card id="preview-card" class="space-y-6">
            <flux:heading size="lg">{{ __('پیش‌نمایش پیوند — mds:preview-card') }}</flux:heading>
            <flux:text>{{ __('کارت پیش‌نمایش مقصد یک پیوند — با نگه داشتن نشانگر یا فوکوس صفحه‌کلید باز می‌شود، و خود پیوند همچنان با کلیک کار می‌کند.') }}</flux:text>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <flux:subheading>{{ __('پروفایل پشت یک منشن') }}</flux:subheading>

                    <flux:text>
                        {{ __('این کیت توسط تیم') }}
                        <mds:preview-card>
                            <mds:preview-card.trigger href="#!">@majid_ds</mds:preview-card.trigger>

                            <mds:preview-card.content>
                                <div class="flex items-center justify-between">
                                    <flux:avatar src="https://i.pravatar.cc/48?img=12" />
                                    <flux:button size="sm" variant="primary">{{ __('دنبال کردن') }}</flux:button>
                                </div>

                                <div>
                                    <div class="font-semibold text-zinc-800 dark:text-white">{{ __('مجید دیزاین سیستم') }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">@majid_ds</div>
                                </div>

                                <p>{{ __('کیت رابط کاربری راست‌چین برای Laravel Livewire، روی Flux UI.') }}</p>

                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <span class="font-medium text-zinc-800 dark:text-white">{{ $mdsNum(2481) }}</span> {{ __('دنبال‌کننده') }}
                                </div>
                            </mds:preview-card.content>
                        </mds:preview-card>
                        {{ __('نگه‌داری می‌شود.') }}
                    </flux:text>
                </div>

                <div class="space-y-3">
                    <flux:subheading>{{ __('پیش‌نمایش با تصویر') }}</flux:subheading>

                    <flux:text>
                        {{ __('جلسه بعدی تیم در') }}
                        <mds:preview-card>
                            <mds:preview-card.trigger href="#!">{{ __('خانه ساحلی') }}</mds:preview-card.trigger>

                            <mds:preview-card.content :arrow="false" class="overflow-hidden !p-0">
                                <img src="https://picsum.photos/seed/coast/288/140" alt="" class="h-32 w-full object-cover">

                                <div class="flex flex-col gap-1 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="font-semibold text-zinc-800 dark:text-white">{{ __('خانه ساحلی') }}</div>
                                        <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            <mds:icon icon="star" class="size-4 text-amber-500" />
                                            {{ $mdsNum(4.9, 1) }}
                                        </span>
                                    </div>
                                    <p>{{ __('فضای کار روشن و باز رو به خلیج — جا برای ۲۴ نفر.') }}</p>
                                </div>
                            </mds:preview-card.content>
                        </mds:preview-card>
                        {{ __('برگزار می‌شود.') }}
                    </flux:text>
                </div>
            </div>

            <flux:text class="text-sm">{!! __('کارت فقط با هاور یا فوکوس باز می‌شود — روی لمس هرگز؛ پس محتوای ضروری را در خود صفحه مقصد بگذارید. جهت‌گیری با <code>side</code> و <code>align</code> منطقی است (start/end) و در صفحه‌های راست‌چین خودکار آینه می‌شود.') !!}</flux:text>
        </flux:card>

        {{-- ============================== Timeline ============================== --}}
        <flux:card id="timeline" class="space-y-6">
            <flux:heading size="lg">{{ __('خط زمانی — mds:timeline') }}</flux:heading>
            <flux:text>{{ __('نسخه آزاد از کامپوننت Timeline (در Flux فقط Pro) — عمودی و افقی، وضعیت مرحله‌ها، نشانگرهای رنگی و بلوک‌های تمام‌عرض. ریل زمانی روی محور راست‌چین قرار می‌گیرد.') }}</flux:text>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="space-y-4">
                    <flux:subheading>{{ __('پیش‌فرض') }}</flux:subheading>

                    <mds:timeline>
                        <mds:timeline.item>
                            <mds:timeline.indicator>
                                <flux:icon icon="shopping-bag" variant="micro" />
                            </mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('سفارش ثبت شد') }} <flux:text inline>· @if ($mdsFa)<mds:jalali-date :date="now()->subDays(4)" ago />@else{{ now()->subDays(4)->diffForHumans() }}@endif</flux:text></flux:heading>
                            </mds:timeline.content>
                        </mds:timeline.item>

                        <mds:timeline.item>
                            <mds:timeline.indicator>
                                <flux:icon icon="banknotes" variant="micro" />
                            </mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('پرداخت تأیید شد') }} <flux:badge size="sm" color="lime">{{ __('آنلاین') }}</flux:badge></flux:heading>
                            </mds:timeline.content>
                        </mds:timeline.item>

                        <mds:timeline.item>
                            <mds:timeline.indicator color="green">
                                <flux:icon icon="check" variant="micro" />
                            </mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('تحویل داده شد') }} <flux:text inline>· <mds:jalali-date :date="now()->subDays(1)" /></flux:text></flux:heading>
                            </mds:timeline.content>
                        </mds:timeline.item>
                    </mds:timeline>
                </div>

                <div class="space-y-4">
                    <flux:subheading>{!! __('بزرگ (size="lg") با مرحله‌های شماره‌دار') !!}</flux:subheading>

                    <mds:timeline size="lg" align="start">
                        <mds:timeline.item status="complete">
                            <mds:timeline.indicator>{{ $mdsNum(1) }}</mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('سبد خرید') }}</flux:heading>
                                <flux:text>{{ __('کالاها را بررسی و تعداد را نهایی کنید.') }}</flux:text>
                            </mds:timeline.content>
                        </mds:timeline.item>

                        <mds:timeline.item status="current">
                            <mds:timeline.indicator>{{ $mdsNum(2) }}</mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('آدرس و زمان ارسال') }}</flux:heading>
                                <flux:text>{{ __('نشانی گیرنده و بازه تحویل را انتخاب کنید.') }}</flux:text>
                            </mds:timeline.content>
                        </mds:timeline.item>

                        <mds:timeline.item status="incomplete">
                            <mds:timeline.indicator>{{ $mdsNum(3) }}</mds:timeline.indicator>
                            <mds:timeline.content>
                                <flux:heading>{{ __('پرداخت') }}</flux:heading>
                                <flux:text>{{ __('پرداخت آنلاین یا در محل.') }}</flux:text>
                            </mds:timeline.content>
                        </mds:timeline.item>
                    </mds:timeline>
                </div>
            </div>

            <flux:separator :text="__('افقی + وضعیت مرحله')" />

            <mds:timeline horizontal>
                <mds:timeline.item status="complete">
                    <mds:timeline.indicator>
                        <flux:icon icon="credit-card" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>{{ __('پرداخت شد') }}</flux:heading>
                        <flux:text>@jalali(now()->subDays(2))</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="complete">
                    <mds:timeline.indicator>
                        <flux:icon icon="archive-box" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>{{ __('بسته‌بندی شد') }}</flux:heading>
                        <flux:text>@jalali(now()->subDays(1))</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="current">
                    <mds:timeline.indicator>
                        <flux:icon icon="truck" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>{{ __('در حال ارسال') }}</flux:heading>
                        <flux:text>{{ __('پیک در راه است') }}</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="incomplete">
                    <mds:timeline.indicator>
                        <flux:icon icon="home" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>{{ __('تحویل به مشتری') }}</flux:heading>
                        <flux:text>{{ __('تا فردا') }}</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>

            <flux:separator :text="__('ترازبندی نشانگر با متن بلند (align)')" />

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                @foreach (['start' => 'ابتدا', 'baseline' => 'خط اول متن', 'center' => 'وسط (پیش‌فرض)', 'end' => 'انتها'] as $alignment => $alignmentLabel)
                    <div class="space-y-3">
                        <flux:subheading>{{ __($alignmentLabel) }} — <code>{{ $alignment }}</code></flux:subheading>

                        <mds:timeline :align="$alignment">
                            <mds:timeline.item>
                                <mds:timeline.indicator>{{ $mdsNum(1) }}</mds:timeline.indicator>
                                <mds:timeline.content>
                                    <flux:heading>{{ __('ارسال مدارک') }}</flux:heading>
                                    <flux:text>{{ __('تصویر کارت ملی و آخرین مدرک تحصیلی را بارگذاری کنید تا کارشناسان پرونده شما را بررسی کنند.') }}</flux:text>
                                </mds:timeline.content>
                            </mds:timeline.item>

                            <mds:timeline.item>
                                <mds:timeline.indicator>{{ $mdsNum(2) }}</mds:timeline.indicator>
                                <mds:timeline.content>
                                    <flux:heading>{{ __('بررسی نهایی') }}</flux:heading>
                                </mds:timeline.content>
                            </mds:timeline.item>
                        </mds:timeline>
                    </div>
                @endforeach
            </div>

            <flux:separator :text="__('نشانگر رنگی، بدون قالب (bare) و بلوک')" />

            <div class="grid gap-8 md:grid-cols-2">
                <mds:timeline>
                    <mds:timeline.item>
                        <mds:timeline.indicator color="red">
                            <flux:icon icon="x-mark" variant="micro" />
                        </mds:timeline.indicator>
                        <mds:timeline.content>
                            <flux:heading>{{ __('استقرار ناموفق بود') }}</flux:heading>
                        </mds:timeline.content>
                    </mds:timeline.item>

                    <mds:timeline.item>
                        <mds:timeline.indicator color="amber">
                            <flux:icon icon="exclamation-triangle" variant="micro" />
                        </mds:timeline.indicator>
                        <mds:timeline.content>
                            <flux:heading>{{ __('هشدار صادر شد') }}</flux:heading>
                        </mds:timeline.content>
                    </mds:timeline.item>

                    <mds:timeline.item align="baseline" class="[--mds-timeline-baseline:1.75rem]">
                        <mds:timeline.indicator variant="bare">
                            <flux:icon icon="rocket-launch" class="size-6 text-zinc-400" />
                        </mds:timeline.indicator>
                        <mds:timeline.content>
                            <flux:heading size="lg">{{ __('منتشر شد') }}</flux:heading>
                            <flux:text>{!! __('نشانگر روی خط اول عنوان بزرگ می‌نشیند (align="baseline").') !!}</flux:text>
                        </mds:timeline.content>
                    </mds:timeline.item>
                </mds:timeline>

                <mds:timeline>
                    <mds:timeline.item>
                        <mds:timeline.indicator>
                            <flux:icon icon="chat-bubble-left-right" variant="micro" />
                        </mds:timeline.indicator>
                        <mds:timeline.content>
                            <flux:heading>{{ __('مهدی') }} <flux:text inline>{{ __('دیدگاهی ثبت کرد') }} · @jalali(now()->subDays(3))</flux:text></flux:heading>
                        </mds:timeline.content>
                    </mds:timeline.item>

                    <mds:timeline.item>
                        <mds:timeline.block class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-white/5">
                            <mds:timeline.subgrid class="p-3">
                                <flux:avatar size="xs" circle src="https://picsum.photos/seed/user/64/64" />
                                <div class="space-y-1">
                                    <flux:heading>{{ __('پشتیبانی') }} <flux:text inline>{{ __('پاسخ داد') }}</flux:text></flux:heading>
                                    <flux:text>{{ __('مرسوله شما امروز از انبار تهران ارسال می‌شود.') }}</flux:text>
                                </div>
                            </mds:timeline.subgrid>
                        </mds:timeline.block>
                    </mds:timeline.item>

                    <mds:timeline.item>
                        <mds:timeline.indicator color="green">
                            <flux:icon icon="check" variant="micro" />
                        </mds:timeline.indicator>
                        <mds:timeline.content>
                            <flux:heading>{{ __('تیکت بسته شد') }}</flux:heading>
                        </mds:timeline.content>
                    </mds:timeline.item>
                </mds:timeline>
            </div>
        </flux:card>

        {{-- ============================== Icons ============================== --}}
        <flux:card id="icons" class="space-y-6">
            <flux:heading size="lg">{{ __('آیکون‌ها — mds:icon') }}</flux:heading>
            <flux:text>{!! __('آیکون‌های <a href="https://hugeicons.com" class="underline">Hugeicons</a> جایگزین heroicons در همه اجزای mds شده‌اند. مجموعه رایگان Stroke Rounded (۶٬۲۰۰ آیکون) همراه بسته می‌آید؛ نام‌های heroicons هم از طریق نقشه معادل‌ها کار می‌کنند.') !!}</flux:text>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <flux:subheading>{{ __('نام مستقیم Hugeicons') }}</flux:subheading>

                    <div class="flex flex-wrap items-center gap-4 text-zinc-600 dark:text-zinc-300">
                        @foreach (['shopping-cart-01', 'truck-delivery', 'discount-tag-01', 'wallet-01', 'store-01', 'gift', 'coins-01', 'delivery-box-01'] as $sample)
                            <flux:tooltip :content="$sample">
                                <div><mds:icon :icon="$sample" /></div>
                            </flux:tooltip>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:subheading>{{ __('نام heroicons (نقشه معادل‌ها)') }}</flux:subheading>

                    <div class="flex flex-wrap items-center gap-4 text-zinc-600 dark:text-zinc-300">
                        @foreach (['magnifying-glass', 'shopping-bag', 'credit-card', 'exclamation-triangle', 'heart', 'map-pin', 'trash', 'user'] as $sample)
                            <flux:tooltip :content="$sample.' → '.(\MajidDs\Support\Icons::ALIASES[$sample] ?? $sample)">
                                <div><mds:icon :icon="$sample" /></div>
                            </flux:tooltip>
                        @endforeach
                    </div>
                </div>
            </div>

            <flux:separator />

            <div class="grid gap-6 md:grid-cols-3">
                <div class="space-y-3">
                    <flux:subheading>{{ __('اندازه با کلاس‌های Tailwind') }}</flux:subheading>

                    <div class="flex items-end gap-3 text-zinc-600 dark:text-zinc-300">
                        <mds:icon icon="package" class="size-4" />
                        <mds:icon icon="package" class="size-5" />
                        <mds:icon icon="package" />
                        <mds:icon icon="package" class="size-8" />
                        <mds:icon icon="package" class="size-10" />
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:subheading>{{ __('ضخامت خط (stroke)') }}</flux:subheading>

                    <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-300">
                        <mds:icon icon="notification-01" class="size-8" :stroke="1" />
                        <mds:icon icon="notification-01" class="size-8" />
                        <mds:icon icon="notification-01" class="size-8" :stroke="2" />
                        <mds:icon icon="notification-01" class="size-8" :stroke="3" />
                    </div>
                </div>

                <div class="space-y-3">
                    <flux:subheading>{{ __('رنگ و دسترس‌پذیری') }}</flux:subheading>

                    <div class="flex items-center gap-3">
                        <mds:icon icon="checkmark-circle-02" class="size-8 text-green-500" :label="__('تأیید شد')" />
                        <mds:icon icon="alert-02" class="size-8 text-amber-500" :label="__('هشدار')" />
                        <mds:icon icon="cancel-circle" class="size-8 text-red-500" :label="__('خطا')" />
                        <mds:icon icon="favourite" class="size-8 text-accent" />
                    </div>
                </div>
            </div>

            <flux:callout icon="information-circle" :heading="__('سبک‌های Pro')">
                <flux:callout.text>
                    {!! __("فقط سبک Stroke Rounded رایگان است. برای هشت سبک دیگر، خروجی لایسنس خودتان را در <code>config('mds.icons.sets')</code> ثبت کنید — این بسته هیچ فایل Pro را همراه خود ندارد. با <code>config('mds.icons.default') = 'flux'</code> همه‌چیز به heroicons برمی‌گردد.") !!}
                </flux:callout.text>
            </flux:callout>
        </flux:card>

        {{-- ============================== Breadcrumbs ============================== --}}
        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('مسیر راهنما — flux:breadcrumbs') }}</flux:heading>

            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" icon="home" />
                <flux:breadcrumbs.item href="#">{{ __('کالای دیجیتال') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#">{{ __('گوشی موبایل') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('سامسونگ Galaxy S25') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" separator="slash">{{ __('خانه') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#" separator="slash">{{ __('سفارش‌ها') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash">{{ __('MDS-۱۴۰۵۲۹') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        </flux:card>

        {{-- ============================== Table + Pagination ============================== --}}
        <flux:card id="table" class="space-y-4">
            <flux:heading size="lg">{{ __('جدول و صفحه‌بندی — flux:table / pagination + اجزای mds') }}</flux:heading>

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

            <flux:pagination :paginator="$paginator" />
        </flux:card>

        {{-- ============================== Progress + Skeleton ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('پیشرفت — flux:progress') }}</flux:heading>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <flux:text class="text-sm">{{ __('تکمیل پروفایل — ۳۵٪') }}</flux:text>
                        <flux:progress value="35" max="100" />
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-sm">{{ __('ظرفیت فروش ویژه — ۷۰٪') }}</flux:text>
                        <flux:progress value="70" max="100" color="amber" />
                    </div>
                    <div class="space-y-1">
                        <flux:text class="text-sm">{{ __('امتیاز رضایت — ۹۲٪') }}</flux:text>
                        <flux:progress value="92" max="100" color="green" />
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('اسکلتون — flux:skeleton') }}</flux:heading>

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
            <flux:heading size="lg">{{ __('امتیاز — mds:rating') }}</flux:heading>

            <div class="flex flex-wrap items-center gap-8">
                <mds:rating :value="4.3" :count="126" />
                <mds:rating :value="2.5" size="lg" />
                <mds:rating :value="5" :count="12" size="sm" />
                <mds:rating :value="3.7" :fa="false" />
            </div>

            <flux:separator />

            <div class="flex items-center gap-4">
                <flux:text>{{ __('ثبت امتیاز:') }}</flux:text>
                <mds:rating.input name="score" :value="3" :label="__('امتیاز')" />
            </div>
        </flux:card>

        {{-- ============================== MDS: Quantity + Price ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('تعداد — mds:quantity') }}</flux:heading>

                <div class="flex flex-wrap items-center gap-4">
                    <mds:quantity :value="2" :min="1" :max="5" name="qty" />
                    <mds:quantity :value="1" size="sm" />
                    <mds:quantity :value="3" size="lg" :fa="false" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('قیمت — mds:price / discount-badge') }}</flux:heading>

                <div class="flex flex-wrap items-center gap-8">
                    <mds:price :amount="2500000" :original="3200000" size="lg" />
                    <mds:price :amount="890000" />
                    <mds:price :amount="14500000" :currency="__('rial')" size="sm" />
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
            <flux:heading size="lg">{{ __('مراحل خرید — mds:stepper') }}</flux:heading>

            <mds:stepper :steps="[__('سبد خرید'), __('آدرس و زمان ارسال'), __('پرداخت'), __('تأیید نهایی')]" :current="2" class="w-full" />
        </flux:card>

        {{-- ============================== MDS: Countdown + Jalali ============================== --}}
        <div class="grid gap-10 md:grid-cols-2">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('شمارش معکوس — mds:countdown') }}</flux:heading>

                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <flux:text>{{ __('پیشنهاد شگفت‌انگیز:') }}</flux:text>
                        <mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" />
                    </div>

                    <mds:countdown :until="now()->addDays(2)->addHours(5)" labels size="lg" />

                    <mds:countdown :until="now()->subMinute()" :expired-text="__('این پیشنهاد به پایان رسید')" />
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('تاریخ شمسی — mds:jalali-date') }}</flux:heading>

                <div class="flex flex-col gap-2 text-sm">
                    <div>{{ __('امروز:') }} <mds:jalali-date :date="now()" format="l j F Y" class="font-semibold" /></div>
                    <div>{{ __('عددی:') }} <mds:jalali-date :date="now()" format="Y/m/d" class="font-semibold" /></div>
                    <div>{{ __('ثبت سفارش:') }} <mds:jalali-date :date="now()->subHours(3)" ago class="font-semibold" /></div>
                    <div>{{ __('با دستور بلید:') }} <span class="font-semibold">@jalali('2026-08-20')</span> — <span class="font-semibold">@toman(2500000)</span></div>
                </div>
            </flux:card>
        </div>

        {{-- ============================== MDS: Product cards ============================== --}}
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('کارت کالا — mds:product-card') }}</flux:heading>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <mds:product-card
                    :title="__('گوشی موبایل سامسونگ مدل Galaxy S25 ظرفیت ۲۵۶ گیگابایت')"
                    image="https://picsum.photos/seed/phone/400/400"
                    :amount="42500000"
                    :original="48900000"
                    :rating="4.6"
                    :reviews="342"
                    :badge="__('ارسال امروز')"
                    href="#"
                />

                <mds:product-card
                    :title="__('هدفون بی‌سیم مدل AirSound Pro')"
                    image="https://picsum.photos/seed/headphone/400/400"
                    :amount="1890000"
                    :rating="4.1"
                    :reviews="87"
                    href="#"
                >
                    <flux:button variant="primary" size="sm" class="w-full">{{ __('افزودن به سبد') }}</flux:button>
                </mds:product-card>

                <mds:product-card
                    :title="__('کتاب صد سال تنهایی اثر گابریل گارسیا مارکز')"
                    image="https://picsum.photos/seed/book/400/400"
                    :amount="245000"
                    :original="350000"
                    :rating="4.9"
                    :reviews="1205"
                    :badge="__('پرفروش')"
                    badge-color="amber"
                    href="#"
                />

                <mds:product-card
                    :title="__('ساعت هوشمند مدل Fit Band 8')"
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
                :title="__('سبد خرید شما خالی است')"
                :description="__('هنوز کالایی به سبد خرید خود اضافه نکرده‌اید. از پیشنهادهای شگفت‌انگیز شروع کنید.')"
            >
                <flux:button variant="primary">{{ __('مشاهده پیشنهادها') }}</flux:button>
                <flux:button variant="ghost">{{ __('تاریخچه سفارش‌ها') }}</flux:button>
            </mds:empty-state>
        </flux:card>

        <footer class="pb-8 text-center">
            <flux:text class="text-sm">{{ __('مجید دیزاین سیستم — ساخته‌شده روی Flux UI و Tailwind CSS') }}</flux:text>
        </footer>
    </div>

    <flux:toast />

    @livewireScripts
    @fluxScripts
</body>
</html>
