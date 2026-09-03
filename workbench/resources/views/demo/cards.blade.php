{{--
    The demo content itself — every card between the page header and the
    footer. Shared by two renderers:

    - workbench demo.blade.php includes it (variables arrive from the
      View::composer in workbench/routes/web.php);
    - bin/build-docs.php renders it into the Demo / RTL demo docs pages,
      passing the same variables explicitly.

    It therefore must not touch anything page-level (no <html>, no header,
    no $mdsAlt/$mdsDocsHref) and may only rely on: $mdsFa, $mdsNum,
    $mdsForward, $mdsUrl — plus __() and the mds config.
--}}

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

{{--
    Not translated on purpose: this note only exists for the English page,
    and it is about which Persian strings are left on it.
--}}
@unless ($mdsFa)
    <flux:callout icon="information-circle" heading="The whole kit follows one config flag">
        <flux:callout.text>
            <code>config('mds.persian_digits')</code> is off on this page, so every component speaks
            English: Latin digits and separators, <code>%</code>, English unit labels, transliterated
            Jalali month names, <code>Out of stock</code>, <code>159 KB</code>. The only Persian left
            comes from the digit and money directives (<code>@@fa</code>, <code>@@faNum</code>,
            <code>@@toman</code>, <code>@@rial</code>), which are Persian by definition —
            <code>@@jalali</code> and every component follow the config; reach for
            <code>mds:price</code> when money should too.
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
        <flux:navbar.item href="#chart" icon="chart-bar">{{ __('نمودار') }}</flux:navbar.item>
        <flux:navbar.item href="#calendar" icon="calendar">{{ __('تقویم') }}</flux:navbar.item>
        <flux:navbar.item href="#editor" icon="pencil-square">{{ __('ویرایشگر متن') }}</flux:navbar.item>
        <flux:navbar.item href="#pillbox" icon="squares-2x2">{{ __('چندانتخابی') }}</flux:navbar.item>
        <flux:navbar.item href="#context" icon="bars-3">{{ __('منوی راست‌کلیک') }}</flux:navbar.item>
        <flux:navbar.item href="#carousel">{{ __('اسلایدشو') }}</flux:navbar.item>
        <flux:navbar.item href="#autocomplete" icon="magnifying-glass">{{ __('تکمیل خودکار') }}</flux:navbar.item>
        <flux:navbar.item href="#tabs">{{ __('زبانه‌ها') }}</flux:navbar.item>
        <flux:navbar.item href="#time-picker" icon="clock">{{ __('انتخاب ساعت') }}</flux:navbar.item>
        <flux:navbar.item href="#slider" icon="adjustments-horizontal">{{ __('اسلایدر') }}</flux:navbar.item>
        <flux:navbar.item href="#accordion">{{ __('آکاردئون') }}</flux:navbar.item>
        <flux:navbar.item href="#popover" icon="chat-bubble-oval-left">{{ __('پاپ‌اور') }}</flux:navbar.item>
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
                <mds:file-item heading="Profile_pic.jpg" image="https://picsum.photos/seed/phone/80/80" :size="162400">
                    <x-slot name="actions">
                        <mds:file-item.remove :label="__('حذف Profile_pic.jpg')" />
                    </x-slot>
                </mds:file-item>

                <mds:file-item :heading="__('قرارداد-فروش.pdf')" :size="2411724">
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
                    <mds:file-item :heading="__('گوشی-گلکسی.jpg')" image="https://picsum.photos/seed/phone/80/80" :size="162400" class="w-full max-w-64">
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

{{-- ============================== MDS: Chart ============================== --}}
<flux:card id="chart" class="space-y-6">
    <flux:heading size="lg">{{ __('نمودار — mds:chart') }}</flux:heading>
    <flux:text>{{ __('نمودارهای تک‌رنگ داشبورد، رندرشده در سرور به‌صورت SVG بدون هیچ کتابخانه‌ای — نسخه آزاد از کامپوننت Chart (در Flux فقط Pro). ارقام فارسی، قیف و هیت‌مپ راست‌به‌چپ.') }}</flux:text>

    <div class="grid gap-4 sm:grid-cols-2">
        <mds:chart :label="__('درآمد ماهانه')" :value="48920" :unit="__('هزار تومان')" delta="+14.2%" :footer-start="__('اسپارک‌لاین گرد')" :footer-end="__('شش ماه اخیر')">
            <mds:chart.sparkline :data="[30, 45, 35, 60, 50, 85, 75, 95]" area class="h-16" />
        </mds:chart>

        <mds:chart :label="__('فروش ماهانه')" :badge="__('اسپلاین')" :value="84" :unit="__('هزار سفارش')" :footer-start="__('خط‌چین: سال گذشته')" :footer-end="__('اوج در مرداد')">
            <mds:chart.line
                :data="[24, 45, 38, 65, 52, 84]"
                :baseline="[18, 32, 29, 48, 41, 62]"
                :labels="[__('فروردین'), __('اردیبهشت'), __('خرداد'), __('تیر'), __('مرداد'), __('شهریور')]"
                area
            />
        </mds:chart>

        <mds:chart :label="__('فروش فصلی')" :badge="__('سه لایه')" :value="160" :unit="__('میلیارد')">
            <mds:chart.bars
                :data="[[30, 25, 20], [45, 35, 25], [60, 40, 30], [75, 50, 35]]"
                :labels="[__('بهار'), __('تابستان'), __('پاییز'), __('زمستان')]"
            />
        </mds:chart>

        <mds:chart :label="__('قیف فروش')" :badge="__('چهار مرحله')" value="24%" :unit="__('نرخ تبدیل')">
            <mds:chart.bars horizontal :data="[100, 68, 42, 24]" :labels="[__('بازدید'), __('سبد خرید'), __('پرداخت'), __('خرید')]" />
        </mds:chart>

        <mds:chart :label="__('سهم دسته‌ها')" :badge="__('دونات')" :footer-start="__('چهار دسته')" :footer-end="__('سهم از فروش')">
            <mds:chart.donut
                :data="[__('موبایل') => 45, __('لوازم خانگی') => 30, __('کتاب') => 15, __('دیگر') => 10]"
                value="100%"
                :label="__('کل فروش')"
            />
        </mds:chart>

        <mds:chart :label="__('رضایت مشتری')" :badge="__('عقربه‌ای')" value="84%" :unit="__('شاخص عملکرد')">
            <mds:chart.gauge :value="84" :label="__('هدف محقق شد')" />
        </mds:chart>

        <mds:chart :label="__('اهداف عملیات')" :badge="__('سنجش')" :footer-start="__('نشانگر: هدف')" :footer-end="__('سه سنجه')">
            <mds:chart.bullet :items="[
                ['label' => __('ارسال به‌موقع'), 'value' => 82, 'target' => 75],
                ['label' => __('پاسخ‌گویی'), 'value' => 65, 'target' => 80],
                ['label' => __('دسترس‌پذیری'), 'value' => 95, 'target' => 90],
            ]" />
        </mds:chart>

        <mds:chart :label="__('امتیاز فروشنده')" :badge="__('رادار')" :value="85" :unit="__('از صد')">
            <mds:chart.radar :data="[
                __('سرعت ارسال') => 90,
                __('کیفیت') => 75,
                __('قیمت') => 85,
                __('پاسخ‌گویی') => 95,
                __('بسته‌بندی') => 80,
            ]" />
        </mds:chart>

        <mds:chart :label="__('فعالیت سفارش‌ها')" :badge="__('چهارده هفته')" :value="807" :unit="__('سفارش')" :footer-start="__('هر ستون یک هفته')" :footer-end="__('پررنگ‌تر: بیشتر')">
            <mds:chart.heatmap
                :data="array_map(fn ($i) => ($i * 7) % 13, range(1, 98))"
                :labels="[__('تیر'), __('مرداد'), __('شهریور')]"
                color="accent"
            />
        </mds:chart>
    </div>

    <flux:text size="sm">{{ __('رنگ جوهر همه نمودارها currentColor است؛ با یک کلاس text-accent کل نمودار رنگ می‌گیرد.') }}</flux:text>
</flux:card>

{{-- ============================== MDS: Popover ============================== --}}
<flux:card id="popover" class="space-y-4">
    <flux:heading size="lg">{{ __('پاپ‌اور — mds:popover') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Popover (که در Flux فقط در نسخه Pro موجود است) — پنلی که به دکمه‌اش لنگر می‌اندازد: جای‌گذاری منطقی و راست‌چین، برگشت فوکوس به دکمه و بسته شدن با Escape.') }}</flux:text>

    <div class="flex flex-wrap items-center gap-3">
        <mds:popover position="bottom" align="start" arrow>
            <mds:popover.trigger>
                <flux:button icon="bell">{{ __('اعلان‌ها') }}</flux:button>
            </mds:popover.trigger>

            <mds:popover.content closable class="w-72">
                <flux:heading size="sm">{{ __('اعلان‌ها') }}</flux:heading>
                <flux:text class="mt-2 text-sm">{{ __('سفارش شما ارسال شد') }}</flux:text>
                <flux:separator class="my-3" />
                <flux:link href="#!">{{ __('مشاهده همه اعلان‌ها') }}</flux:link>
            </mds:popover.content>
        </mds:popover>

        <mds:popover position="bottom" align="end">
            <mds:popover.trigger>
                <flux:button icon="funnel" variant="subtle">{{ __('فیلترها') }}</flux:button>
            </mds:popover.trigger>

            <mds:popover.content class="w-64">
                <flux:heading size="sm" class="mb-3">{{ __('فیلترها') }}</flux:heading>

                <div class="space-y-2">
                    <flux:checkbox :label="__('فقط کالاهای موجود')" checked />
                    <flux:checkbox :label="__('ارسال رایگان')" />
                    <flux:checkbox :label="__('کالاهای تخفیف‌دار')" />
                </div>

                <flux:button size="sm" variant="primary" class="mt-4 w-full">{{ __('اعمال فیلتر') }}</flux:button>
            </mds:popover.content>
        </mds:popover>

        <mds:popover hover position="top" align="center" arrow>
            <mds:popover.trigger>
                <flux:button icon="question-mark-circle" variant="ghost">{{ __('راهنمای سایز') }}</flux:button>
            </mds:popover.trigger>

            <mds:popover.content class="w-64">
                <flux:heading size="sm">{{ __('اندازه‌گیری چگونه انجام می‌شود؟') }}</flux:heading>
                <flux:text class="mt-2 text-sm">{{ __('دور سینه روی لباس صاف و دو سانتی‌متر پایین‌تر از حلقه آستین اندازه گرفته می‌شود. بین دو سایز، سایز بزرگ‌تر را انتخاب کنید.') }}</flux:text>
            </mds:popover.content>
        </mds:popover>
    </div>

    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('پنل سوم با نگه‌داشتن نشانگر یا با فوکوس کیبورد هم باز می‌شود؛ کلیک آن را ثابت نگه می‌دارد.') }}</flux:text>
</flux:card>

{{-- ============================== MDS: Accordion ============================== --}}
<flux:card id="accordion" class="space-y-4">
    <flux:heading size="lg">{{ __('آکاردئون — mds:accordion') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Accordion (که در Flux فقط در نسخه Pro موجود است) — روی details و summary بومی مرورگر، پس باز و بسته شدن، کیبورد و جستجوی درون‌صفحه بدون جاوااسکریپت هم کار می‌کند.') }}</flux:text>

    <div class="grid gap-8 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('پرسش‌های پرتکرار (هر بار یک بخش، با انیمیشن):') }}</flux:text>

            <mds:accordion exclusive transition>
                <mds:accordion.item :heading="__('ارسال و تحویل')" expanded>
                    {{ __('سفارش‌ها حداکثر تا دو روز کاری ارسال می‌شوند و در بیشتر شهرها کمتر از یک هفته به دستتان می‌رسد.') }}
                </mds:accordion.item>

                <mds:accordion.item :heading="__('مرجوع کردن کالا')">
                    {{ __('تا هفت روز پس از تحویل، بدون پرسش. هزینه ارسال مرجوعی با ماست.') }}
                </mds:accordion.item>

                <mds:accordion.item :heading="__('گارانتی کالاها')">
                    {{ __('همه کالاهای دیجیتال دو سال گارانتی دارند که رسیدگی به آن با خود ما است.') }}
                </mds:accordion.item>

                <mds:accordion.item :heading="__('حساب سازمانی')" disabled>
                    {{ __('به‌زودی') }}
                </mds:accordion.item>
            </mds:accordion>
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('عنوان دلخواه با نشان و آیکون:') }}</flux:text>

            <mds:accordion transition>
                <mds:accordion.item expanded>
                    <mds:accordion.heading>
                        <span class="flex items-center gap-2">
                            <mds:icon icon="truck" variant="micro" class="size-4 text-zinc-400" />
                            {{ __('سفارش ۱۴۸۱') }}
                            <flux:badge size="sm" color="green">{{ __('در حال ارسال') }}</flux:badge>
                        </span>
                    </mds:accordion.heading>

                    <mds:accordion.content>
                        <div class="flex items-center justify-between gap-3">
                            <span>{{ __('یکشنبه تحویل پست شد.') }}</span>
                            <flux:button size="sm" variant="subtle">{{ __('پیگیری مرسوله') }}</flux:button>
                        </div>
                    </mds:accordion.content>
                </mds:accordion.item>

                <mds:accordion.item>
                    <mds:accordion.heading>
                        <span class="flex items-center gap-2">
                            <mds:icon icon="clock" variant="micro" class="size-4 text-zinc-400" />
                            {{ __('سفارش ۱۴۷۷') }}
                            <flux:badge size="sm" color="zinc">{{ __('تحویل شده') }}</flux:badge>
                        </span>
                    </mds:accordion.heading>

                    <mds:accordion.content>{{ __('چهارشنبه هفته گذشته تحویل داده و امضا شد.') }}</mds:accordion.content>
                </mds:accordion.item>
            </mds:accordion>
        </div>
    </div>
</flux:card>

{{-- ============================== MDS: Slider ============================== --}}
<flux:card id="slider" class="space-y-6">
    <flux:heading size="lg">{{ __('اسلایدر — mds:slider') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Slider (در Flux فقط Pro) — تک‌دستگیره یا بازه‌ای، با خوانش زنده به ارقام فارسی، تیک‌های راهنما و کلمپ سمت سرور.') }}</flux:text>

    <div class="grid gap-8 md:grid-cols-2">
        <mds:slider
            :label="__('میزان صدا')"
            :description="__('با کلیدهای جهت‌دار هم جابه‌جا می‌شود.')"
            :value="60"
            :step="5"
            :format="__('{value}٪')"
            show-value
        />

        <mds:slider
            :label="__('بازه قیمت')"
            range
            :min="0"
            :max="5000000"
            :step="250000"
            :value="[500000, 3000000]"
            :format="__('{value} تومان')"
            show-value
        />

        <mds:slider
            :label="__('روشنایی صفحه')"
            :min="1"
            :max="5"
            :value="3"
            ticks
            show-value
        />

        <mds:slider
            :label="__('حجم بسته اینترنتی')"
            :description="__('برای تغییر، ابتدا وارد حساب کاربری شوید.')"
            :value="10"
            :max="50"
            size="sm"
            disabled
        />
    </div>
</flux:card>

{{-- ============================== MDS: Time-Picker ============================== --}}
<flux:card id="time-picker" class="space-y-6">
    <flux:heading size="lg">{{ __('انتخاب ساعت — mds:time-picker') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Time Picker (در Flux فقط Pro) — فهرست ساعت‌ها با گام دلخواه، تایپ آزاد با ارقام فارسی، و مقدار ماشینی همیشه ۲۴ساعته.') }}</flux:text>

    <div class="grid gap-6 md:grid-cols-3">
        <mds:time-picker
            :label="__('ساعت تحویل')"
            :description="__('بازه‌های نیم‌ساعته، از ۹ تا ۲۱.')"
            value="10:30"
            min="09:00"
            max="21:00"
            clearable
        />

        <mds:time-picker
            :label="__('شروع نوبت')"
            value="14:15"
            :step="15"
            min="08:00"
            max="18:00"
        />

        <mds:time-picker
            :label="__('یادآوری')"
            :placeholder="__('ساعت را انتخاب کنید')"
            hours="12"
            clearable
        />
    </div>
</flux:card>

{{-- ============================== MDS: Tabs ============================== --}}
<flux:card id="tabs" class="space-y-4">
    <flux:heading size="lg">{{ __('زبانه‌ها — mds:tabs') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Tabs (که در Flux فقط در نسخه Pro موجود است) — با الگوی کامل WAI-ARIA، ناوبری با کلیدهای جهت‌دار که در چیدمان راست‌به‌چپ برعکس می‌شوند، و زبانه فعالِ درست در همان اولین رندر سرور.') }}</flux:text>

    <div class="space-y-2">
        <flux:text class="text-sm font-medium">{{ __('پیش‌فرض، با آیکون:') }}</flux:text>

        <mds:tab.group>
            <mds:tabs :label="__('بخش‌های محصول')">
                <mds:tab name="specs" icon="document-text">{{ __('مشخصات فنی') }}</mds:tab>
                <mds:tab name="reviews" icon="star">{{ __('نظرات کاربران') }}</mds:tab>
                <mds:tab name="qna" icon="chat-bubble-left-right">{{ __('پرسش و پاسخ') }}</mds:tab>
                <mds:tab name="seller" icon="truck" disabled>{{ __('فروشنده') }}</mds:tab>
            </mds:tabs>

            <mds:tab.panel name="specs">{{ __('وزن، ابعاد و مدت گارانتی.') }}</mds:tab.panel>
            <mds:tab.panel name="reviews">{{ __('آنچه خریداران درباره این کالا نوشته‌اند.') }}</mds:tab.panel>
            <mds:tab.panel name="qna">{{ __('پرسش خود را از فروشنده بپرسید.') }}</mds:tab.panel>
            <mds:tab.panel name="seller">{{ __('این بخش فعلاً در دسترس نیست.') }}</mds:tab.panel>
        </mds:tab.group>
    </div>

    <div class="space-y-2">
        <flux:text class="text-sm font-medium">{{ __('قطعه‌ای و کوچک، با زبانه آغازین انتخاب‌شده:') }}</flux:text>

        <mds:tab.group value="month">
            <mds:tabs variant="segmented" size="sm" :label="__('بازه زمانی')">
                <mds:tab name="week">{{ __('این هفته') }}</mds:tab>
                <mds:tab name="month">{{ __('این ماه') }}</mds:tab>
                <mds:tab name="year">{{ __('امسال') }}</mds:tab>
            </mds:tabs>

            <mds:tab.panel name="week">{{ __('سفارش‌های ۷ روز گذشته.') }}</mds:tab.panel>
            <mds:tab.panel name="month">{{ __('سفارش‌های ۳۰ روز گذشته.') }}</mds:tab.panel>
            <mds:tab.panel name="year">{{ __('سفارش‌های ۱۲ ماه گذشته.') }}</mds:tab.panel>
        </mds:tab.group>
    </div>

    <div class="space-y-2">
        <flux:text class="text-sm font-medium">{{ __('قرصی، متصل به Livewire:') }}</flux:text>

        <mds:tab.group>
            <mds:tabs variant="pills" wire:model="tab" name="tab" :label="__('حساب کاربری')">
                <mds:tab name="profile" icon="user">{{ __('نمایه') }}</mds:tab>
                <mds:tab name="notifications" icon="bell">{{ __('اعلان‌ها') }}</mds:tab>
                <mds:tab name="security" icon="lock-closed">{{ __('امنیت') }}</mds:tab>
            </mds:tabs>

            <mds:tab.panel name="profile">{{ __('نام، تصویر و نشانی عمومی شما.') }}</mds:tab.panel>
            <mds:tab.panel name="notifications">{{ __('ایمیل و پیامک‌هایی که دریافت می‌کنید.') }}</mds:tab.panel>
            <mds:tab.panel name="security">{{ __('گذرواژه و ورودهای فعال.') }}</mds:tab.panel>
        </mds:tab.group>
    </div>
</flux:card>

{{-- ============================== MDS: Autocomplete ============================== --}}
<flux:card id="autocomplete" class="space-y-6">
    <flux:heading size="lg">{{ __('تکمیل خودکار — mds:autocomplete') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Autocomplete (که در Flux فقط در نسخه Pro موجود است) — یک ورودی متنی معمولی با فهرست پیشنهاد، جستجوی فارسی و ناوبری با کیبورد.') }}</flux:text>

    <div class="grid gap-8 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('متن آزاد با پیشنهاد شهرها:') }}</flux:text>

            <mds:autocomplete
                :label="__('شهر')"
                :description="__('با کلیدهای بالا و پایین در فهرست حرکت کنید و با Enter انتخاب کنید.')"
                :placeholder="__('نام شهر را بنویسید...')"
                :empty="__('شهری با این نام پیدا نشد.')"
                icon="map-pin"
                clearable
            >
                <mds:autocomplete.item>{{ __('تهران') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('مشهد') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('اصفهان') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('کرج') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('شیراز') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('تبریز') }}</mds:autocomplete.item>
                <mds:autocomplete.item disabled>{{ __('کیش (خارج از محدوده ارسال)') }}</mds:autocomplete.item>
            </mds:autocomplete>
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('فقط یکی از گزینه‌ها، با حداقل دو حرف:') }}</flux:text>

            <mds:autocomplete
                :label="__('جستجوی کالا')"
                :description="__('اگر متن با هیچ کالایی نخواند، هنگام خروج از فیلد پاک می‌شود.')"
                :placeholder="__('حداقل دو حرف بنویسید...')"
                :empty="__('کالایی با این نام پیدا نشد.')"
                icon="magnifying-glass"
                :min-chars="2"
                strict
                clearable
            >
                <mds:autocomplete.item>{{ __('گوشی موبایل سامسونگ Galaxy S25') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('هدفون بی‌سیم AirSound Pro') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('ساعت هوشمند Fit Band 8') }}</mds:autocomplete.item>
                <mds:autocomplete.item>{{ __('شارژر سریع ۶۵ وات') }}</mds:autocomplete.item>
            </mds:autocomplete>
        </div>
    </div>

    <flux:callout icon="information-circle">
        <flux:callout.text>{{ __('مقدار همیشه متن است، نه شناسه — پس با wire:model.live می‌توانید فهرست را روی سرور بسازید؛ کامپوننت پس از هر morph فهرست خود را دوباره می‌خواند.') }}</flux:callout.text>
    </flux:callout>
</flux:card>

{{-- ============================== MDS: Carousel ============================== --}}
<flux:card id="carousel" class="space-y-6">
    <flux:heading size="lg">{{ __('اسلایدشو — mds:carousel') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Carousel (که در Flux فقط در نسخه Pro موجود است) — روی scroll snap ساخته شده، پس روی موبایل با انگشت کشیده می‌شود و در صفحه راست‌به‌چپ بدون هیچ تنظیمی درست حرکت می‌کند.') }}</flux:text>

    <div class="space-y-2">
        <flux:text class="text-sm font-medium">{{ __('بنر اصلی، با چرخش خودکار:') }}</flux:text>

        <mds:carousel :label="__('پیشنهاد شگفت‌انگیز')" autoplay :interval="4000" aspect="aspect-[21/9]">
            <mds:carousel.item>
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-bl from-sky-500 to-indigo-600 text-2xl font-semibold text-white">{{ __('جشنواره پاییزه') }}</div>
            </mds:carousel.item>
            <mds:carousel.item>
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-bl from-amber-500 to-rose-600 text-2xl font-semibold text-white">{{ __('ارسال رایگان') }}</div>
            </mds:carousel.item>
            <mds:carousel.item>
                <div class="flex h-full w-full items-center justify-center bg-gradient-to-bl from-emerald-500 to-teal-700 text-2xl font-semibold text-white">{{ __('تازه‌های دیجیتال') }}</div>
            </mds:carousel.item>
        </mds:carousel>
    </div>

    <div class="space-y-2">
        <flux:text class="text-sm font-medium">{{ __('نوار کالاها، سه اسلاید در هر نما:') }}</flux:text>

        <mds:carousel :label="__('پیشنهاد ویژه برای شما')" per-view="3" gap="gap-4" :loop="false">
            <mds:carousel.item>
                <mds:product-card :title="__('گوشی موبایل سامسونگ Galaxy S25')" :amount="61990000" :original="68000000" :rating="4.6" :reviews="1240" />
            </mds:carousel.item>
            <mds:carousel.item>
                <mds:product-card :title="__('هدفون بی‌سیم AirSound Pro')" :amount="18900000" :rating="4.8" :reviews="512" :badge="__('جدید')" />
            </mds:carousel.item>
            <mds:carousel.item>
                <mds:product-card :title="__('ساعت هوشمند Fit Band 8')" :amount="3450000" :original="3990000" :rating="4.2" :reviews="88" />
            </mds:carousel.item>
            <mds:carousel.item>
                <mds:product-card :title="__('شارژر سریع ۶۵ وات')" :amount="1290000" :rating="4.4" :reviews="203" />
            </mds:carousel.item>
        </mds:carousel>
    </div>

    <flux:callout icon="information-circle">
        <flux:callout.text>{{ __('نوار اسلایدها با Tab قابل تمرکز است: کلیدهای چپ و راست بر اساس چیزی که می‌بینید کار می‌کنند، پس در صفحه راست‌به‌چپ کلید راست یعنی اسلاید قبلی. چرخش خودکار با نشانگر ماوس، تمرکز کیبورد یا تب پنهان متوقف می‌شود.') }}</flux:callout.text>
    </flux:callout>
</flux:card>

{{-- ============================== MDS: Context ============================== --}}
<flux:card id="context" class="space-y-4">
    <flux:heading size="lg">{{ __('منوی راست‌کلیک — mds:context') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Context (در Flux فقط Pro) — یک flux:menu معمولی که با راست‌کلیک، کلید Shift+F10 یا لمس طولانی کنار نشانگر باز می‌شود.') }}</flux:text>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('روی کارت راست‌کلیک کنید:') }}</flux:text>

            <mds:context>
                <flux:card class="w-64">
                    <flux:heading>{{ __('گزارش فصلی.pdf') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('۲٫۴ مگابایت · برای گزینه‌ها راست‌کلیک کنید') }}</flux:text>
                </flux:card>

                <mds:context.menu>
                    <flux:menu>
                        <flux:menu.item icon="pencil-square">{{ __('ویرایش') }}</flux:menu.item>
                        <flux:menu.item icon="document-duplicate">{{ __('تکثیر') }}</flux:menu.item>
                        <flux:menu.item icon="share">{{ __('هم‌رسانی') }}</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item icon="trash" variant="danger">{{ __('حذف') }}</flux:menu.item>
                    </flux:menu>
                </mds:context.menu>
            </mds:context>
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('با کیبورد: Tab تا تصویر، سپس Shift+F10:') }}</flux:text>

            <mds:context focusable class="inline-block rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent">
                <img src="https://picsum.photos/seed/context/240/150" alt="{{ __('نمونه تصویر') }}" class="block h-[150px] w-60 rounded-lg object-cover">

                <mds:context.menu>
                    <flux:menu>
                        <flux:menu.item icon="arrow-down-tray">{{ __('ذخیره تصویر') }}</flux:menu.item>
                        <flux:menu.item icon="clipboard">{{ __('کپی پیوند') }}</flux:menu.item>
                        <flux:menu.item icon="star">{{ __('انتخاب به‌عنوان کاور') }}</flux:menu.item>
                    </flux:menu>
                </mds:context.menu>
            </mds:context>
        </div>
    </div>

    <flux:text class="text-sm">{!! __('منوی راست‌کلیک یک میان‌بر است، نه تنها راه: همان عملیات را جایی دیدنی هم بگذارید — یک <code>flux:dropdown</code> یا دکمه‌های ردیف.') !!}</flux:text>
</flux:card>

{{-- ============================== MDS: Pillbox ============================== --}}
<flux:card id="pillbox" class="space-y-4">
    <flux:heading size="lg">{{ __('جعبه برچسب — mds:pillbox') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Pillbox (که در Flux فقط در نسخه Pro موجود است) — انتخاب چندتایی با پیل‌های قابل حذف، جستجوی فارسی و سقف انتخاب.') }}</flux:text>

    <div class="grid gap-8 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('حداکثر سه دسته‌بندی:') }}</flux:text>

            <mds:pillbox
                :label="__('دسته‌بندی‌ها')"
                :placeholder="__('انتخاب کنید...')"
                :value="['mobile']"
                max="3"
                clearable
            >
                <mds:pillbox.option value="mobile">{{ __('گوشی موبایل') }}</mds:pillbox.option>
                <mds:pillbox.option value="books">{{ __('کتاب و مجله') }}</mds:pillbox.option>
                <mds:pillbox.option value="home">{{ __('لوازم خانگی') }}</mds:pillbox.option>
                <mds:pillbox.option value="beauty">{{ __('زیبایی و سلامت') }}</mds:pillbox.option>
                <mds:pillbox.option value="fashion">{{ __('مد و پوشاک') }}</mds:pillbox.option>
                <mds:pillbox.option value="sports">{{ __('ورزش و سفر') }}</mds:pillbox.option>
            </mds:pillbox>
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('برچسب‌های محصول:') }}</flux:text>

            <mds:pillbox
                :label="__('برچسب‌های محصول')"
                :description="__('برچسب‌ها در صفحه محصول کنار عنوان نمایش داده می‌شوند.')"
                :placeholder="__('جستجو...')"
                :empty="__('برچسبی پیدا نشد.')"
                :value="['free-shipping', 'original']"
            >
                <mds:pillbox.option value="free-shipping">{{ __('ارسال رایگان') }}</mds:pillbox.option>
                <mds:pillbox.option value="original">{{ __('اصل و اورجینال') }}</mds:pillbox.option>
                <mds:pillbox.option value="warranty">{{ __('گارانتی') }}</mds:pillbox.option>
                <mds:pillbox.option value="in-stock">{{ __('موجود در انبار') }}</mds:pillbox.option>
                <mds:pillbox.option value="discounted" disabled>{{ __('تخفیف‌دار') }}</mds:pillbox.option>
            </mds:pillbox>
        </div>
    </div>
</flux:card>

{{-- ============================== MDS: Editor ============================== --}}
<flux:card id="editor" class="space-y-4">
    <flux:heading size="lg">{{ __('ویرایشگر متن — mds:editor') }}</flux:heading>

    <flux:text>{{ __('نسخه آزاد از کامپوننت Editor (در Flux فقط Pro) — بدون هیچ کتابخانه جاوااسکریپت بیرونی: یک سطح contenteditable، نوار ابزار استاندارد ARIA، و پاک‌سازی HTML چسبانده‌شده تا فهرست مجاز.') }}</flux:text>

    <mds:editor
        name="story"
        :label="__('توضیحات محصول')"
        :description="__('فقط قالب‌بندی‌های زیر نگه داشته می‌شوند؛ بقیه هنگام چسباندن حذف می‌شود.')"
        :placeholder="__('داستان محصول را بنویسید...')"
        dir="rtl"
        rows="7"
        wire:model="story"
        :value="'<h2>'.__('هدفون بی‌سیم').'</h2><p>'.__('صدای شفاف، باتری ۳۰ ساعته و اتصال پایدار.').'</p><ul><li>'.__('حذف نویز فعال').'</li><li>'.__('شارژ سریع').'</li></ul>'"
    />

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('نوار ابزار کوتاه:') }}</flux:text>

            <mds:editor
                :label="__('یادداشت کوتاه')"
                label:sr-only
                :placeholder="__('یک یادداشت بنویسید...')"
                toolbar="bold italic | link unlink"
                dir="rtl"
                rows="3"
            />
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('نوار ابزار دلخواه:') }}</flux:text>

            <mds:editor :label="__('نظر شما')" label:sr-only dir="rtl" rows="3">
                <mds:editor.toolbar :label="__('قالب‌بندی')">
                    <mds:editor.button command="bold" />
                    <mds:editor.button command="h3" />
                    <mds:editor.button command="quote" />
                    <mds:editor.button command="direction" />
                    <mds:editor.button command="clear" :label="__('از نو')" />
                </mds:editor.toolbar>

                <mds:editor.content :placeholder="__('نظرتان را بنویسید...')" rows="3" dir="rtl" />
            </mds:editor>
        </div>
    </div>

    <flux:text class="text-sm">{{ __('میان‌برها: Ctrl/⌘ به همراه B پررنگ، I مورب، U زیرخط و K پیوند. در نوار ابزار، کلیدهای جهت‌دار به ترتیب دیداری حرکت می‌کنند.') }}</flux:text>
</flux:card>

{{-- ============================== MDS: Calendar ============================== --}}
<flux:card id="calendar" class="space-y-6">
    <flux:heading size="lg">{{ __('تقویم — mds:calendar') }}</flux:heading>
    <flux:text>{{ __('نسخه آزاد از کامپوننت Calendar (که در Flux فقط در نسخه Pro موجود است) — و برخلاف آن، شمسی: ماه‌های فروردین تا اسفند، هفته‌ای که با شنبه شروع می‌شود و سال کبیسه‌ی درست. مقداری که به Livewire می‌رسد همیشه تاریخ میلادی ISO است.') }}</flux:text>

    <div class="grid gap-8 md:grid-cols-2">
        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('یک روز، با محدوده و روزهای تعطیل:') }}</flux:text>

            <mds:calendar
                :label="__('روز تحویل')"
                value="2026-08-24"
                min="2026-08-16"
                max="2026-09-12"
                :unavailable="['2026-08-28', '2026-09-04']"
                week-numbers
                with-today
            />
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('بازه‌ی اقامت، دو ماه کنار هم:') }}</flux:text>

            <mds:calendar
                :label="__('بازه‌ی اقامت')"
                mode="range"
                :months="2"
                fixed-weeks
                size="sm"
                :value="['2026-08-26', '2026-09-08']"
            />
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('چند روز دلخواه:') }}</flux:text>

            <mds:calendar
                :label="__('روزهای انتخابی')"
                mode="multiple"
                :value="['2026-08-20', '2026-08-25', '2026-08-26']"
            />
        </div>

        <div class="space-y-2">
            <flux:text class="text-sm font-medium">{{ __('انتخاب ماه و سال از هدر:') }}</flux:text>

            <mds:calendar :label="__('تاریخ تولد')" selectable-header value="1993-06-15" />
        </div>
    </div>

    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.text>{{ __('کلیدهای جهت‌دار روی شبکه کار می‌کنند: چپ و راست یک روز — به همان سمتی که فلش نشان می‌دهد، حتی در صفحه‌ی راست‌چین — بالا و پایین یک هفته، Home و End ابتدا و انتهای هفته، PageUp و PageDown یک ماه و با Shift یک سال.') }}</flux:callout.text>
    </flux:callout>
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

{{-- ============================== MDS: Input ============================== --}}
<flux:card class="space-y-4">
    <flux:heading size="lg">{{ __('ورودی ارقام — mds:input') }}</flux:heading>

    <flux:text>{{ __('همان flux:input، با این تفاوت که ارقام فارسی و عربی هنگام تایپ یا چسباندن به لاتین تبدیل می‌شوند — مقداری که به سرور می‌رسد همیشه ماشینی است.') }}</flux:text>

    <div class="grid gap-6 md:grid-cols-2">
        <mds:input.mobile :label="__('شماره موبایل')" :description="__('با هر صفحه‌کلیدی تایپ کنید؛ مقدار لاتین ذخیره می‌شود.')" />
        <mds:input.national-id :label="__('کد ملی')" :description="__('ده رقم؛ رقم کنترل در سرور با قانون NationalId بررسی می‌شود.')" />
        <mds:input.card :label="__('شماره کارت')" :description="__('۱۶ رقم؛ گروه‌بندی نمایشی است و قانون BankCard فاصله‌ها را نادیده می‌گیرد.')" />
        <mds:input.sheba :label="__('شماره شبا')" :description="__('IR و ۲۴ رقم؛ گروه‌بندی نمایشی است و قانون Sheba فاصله‌ها را نادیده می‌گیرد.')" />
        <mds:input only ltr :label="__('کد تأیید')" maxlength="5" placeholder="12345" class="md:w-40" />
        <mds:input :label="__('آدرس')" :placeholder="__('خیابان ولیعصر، پلاک ۱۲')" />
    </div>
</flux:card>

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
