# Majid DS

**An RTL / Persian-first UI kit for Laravel Livewire, built on top of [Flux UI](https://fluxui.dev).**

Majid DS does not replace Flux — it extends it. You keep every `<flux:*>` component exactly as it is, and gain an `<mds:*>` namespace with the pieces Flux doesn't have: Persian typography and digits, Jalali dates, Toman/Rial prices, and e-commerce components (product cards, quantity steppers, deal countdowns, checkout steppers, ratings).

```blade
<mds:product-card
    title="گوشی موبایل سامسونگ مدل Galaxy S25"
    image="/images/phone.jpg"
    :amount="42500000"
    :original="48900000"
    :rating="4.6"
    :reviews="342"
    badge="ارسال امروز"
    href="/products/galaxy-s25"
/>
```

## What you get on top of Flux

| Area | What Majid DS adds |
|---|---|
| Typography | Vazirmatn font wired into Tailwind's `--font-sans` (`@mdsFonts` directive for the `<link>` tags) |
| Numbers | Persian digits (۰۱۲۳), Persian separators (٬ / ٫), `@fa` / `@faNum` directives |
| Money | `<mds:price>`, `@toman` / `@rial` directives, configurable default currency |
| Dates | Dependency-free Jalali calendar (`<mds:jalali-date>`, `@jalali`, relative "۳ ساعت پیش") |
| E-commerce | `<mds:product-card>`, `<mds:quantity>`, `<mds:discount-badge>`, `<mds:countdown>` |
| Flows | `<mds:stepper>` (checkout steps), `<mds:rating>` / `<mds:rating.input>`, `<mds:empty-state>` |
| Pro alternatives | `<mds:command>` (⌘K palette) and `<mds:color-picker>` — open versions of Flux Pro-only components |

All components are RTL-first (built with logical properties, so they also work LTR), support dark mode, and follow Flux's accent color tokens — customize `--color-accent` once and both libraries follow.

## Requirements

- PHP 8.2+, Laravel 11+
- Livewire 3 and `livewire/flux` ^2.0 (installed automatically)
- Tailwind CSS v4

## Installation

```bash
composer require mahdimajidzadeh/majid-ds
```

**1. CSS** — in `resources/css/app.css`, import the Majid DS layer after Flux:

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/mahdimajidzadeh/majid-ds/resources/css/mds.css';

@custom-variant dark (&:where(.dark, .dark *));
```

**2. Layout** — set the document direction and load the font:

```blade
<html lang="fa" dir="rtl">
<head>
    @mdsFonts   {{-- Vazirmatn <link> tags; self-host for production traffic from Iran --}}
    @fluxAppearance
</head>
<body>
    ...
    @fluxScripts
</body>
</html>
```

That's it. `<flux:*>` and `<mds:*>` components now work side by side.

## Components

### `<mds:price>`

```blade
<mds:price :amount="2500000" />                          {{-- ۲٬۵۰۰٬۰۰۰ تومان --}}
<mds:price :amount="2500000" :original="3200000" />      {{-- + strikethrough & ۲۲٪ badge --}}
<mds:price :amount="14500000" currency="rial" size="sm" />
<mds:price :amount="1200000" :fa="false" />              {{-- Latin digits --}}
```

Props: `amount`, `original`, `currency` (`toman` | `rial` | `none` | literal label), `decimals`, `size` (`sm`|`lg`), `fa`, `badge`.

### `<mds:quantity>`

Cart quantity stepper. Works with plain forms (`name`) and Livewire (`wire:model` is forwarded to the hidden input, which always holds Latin digits):

```blade
<mds:quantity wire:model="qty" :min="1" :max="5" />
<mds:quantity name="quantity" :value="2" size="lg" />
```

### `<mds:rating>` and `<mds:rating.input>`

```blade
<mds:rating :value="4.3" :count="126" />        {{-- display, supports half stars --}}
<mds:rating.input wire:model="score" />          {{-- interactive, forwards wire:model --}}
```

### `<mds:product-card>`

Composes `flux:card`, `mds:rating`, `mds:price`, and `mds:discount-badge`. The slot renders an actions area (e.g. an add-to-cart button):

```blade
<mds:product-card title="..." image="..." :amount="1890000" :rating="4.1" :reviews="87" href="...">
    <flux:button variant="primary" size="sm" class="w-full">افزودن به سبد</flux:button>
</mds:product-card>

<mds:product-card title="..." image="..." unavailable />   {{-- grayscale + ناموجود --}}
```

Other props: `original`, `currency`, `badge` + `badge-color` (top corner `flux:badge`), `fa`.

### `<mds:stepper>`

```blade
<mds:stepper :steps="['سبد خرید', 'آدرس و زمان ارسال', 'پرداخت', 'تأیید نهایی']" :current="2" />
```

`current` is 1-based; earlier steps render as completed (check icon), later ones as upcoming.

### `<mds:countdown>`

```blade
<mds:countdown :until="$deal->ends_at" :days="false" />         {{-- ۰۷:۴۱:۵۵ (wall-clock, LTR) --}}
<mds:countdown :until="$deal->ends_at" labels size="lg" />      {{-- Digikala-style labeled boxes --}}
<mds:countdown :until="$deal->ends_at" expired-text="این پیشنهاد به پایان رسید" />
```

Server-rendered initial value (no blank flash), then ticks with Alpine.

### `<mds:jalali-date>`

```blade
<mds:jalali-date :date="now()" />                        {{-- ۲۹ مرداد ۱۴۰۵ --}}
<mds:jalali-date :date="now()" format="l j F Y" />       {{-- پنجشنبه ۲۹ مرداد ۱۴۰۵ --}}
<mds:jalali-date :date="now()" format="Y/m/d" />         {{-- ۱۴۰۵/۰۵/۲۹ --}}
<mds:jalali-date :date="$order->created_at" ago />       {{-- ۳ ساعت پیش --}}
```

Renders a semantic `<time datetime="...">`. Format tokens: `Y y n m j d F l D` plus time passthroughs `H G h g i s A a`.

### `<mds:command>`

An open implementation of Flux Pro's command palette, API-compatible with `flux:command`. Client-side filtering (with Persian text normalization: Arabic ي/ك and Arabic-Indic digits match their Persian forms), arrow-key navigation, Enter to select, and group headings that hide while searching:

```blade
<mds:command>
    <mds:command.input placeholder="جستجوی فرمان..." clearable />

    <mds:command.items>
        <mds:command.heading>ناوبری</mds:command.heading>
        <mds:command.item icon="shopping-bag" kbd="⌘O" wire:click="...">سفارش‌های من</mds:command.item>
        <mds:command.item icon="heart" href="/wishlist">علاقه‌مندی‌ها</mds:command.item>
    </mds:command.items>
</mds:command>
```

For a global ⌘K palette, pair it with Flux's modal (the `shortcut` prop and `bare` variant are free):

```blade
<flux:modal.trigger name="search" shortcut="cmd.k">
    <flux:input as="button" placeholder="جستجو..." icon="magnifying-glass" kbd="⌘K" />
</flux:modal.trigger>

<flux:modal name="search" variant="bare" class="my-[12vh] w-full max-w-[30rem]">
    <mds:command>
        <mds:command.input placeholder="جستجو در همه‌جا..." closable autofocus />
        <mds:command.items>...</mds:command.items>
    </mds:command>
</flux:modal>
```

Props — `command.input`: `icon` (default `magnifying-glass`), `clearable`, `closable` (closes the containing modal); `command.item`: `icon`, `icon-variant`, `kbd`, `href` (renders `<a>` instead of `<button>`); `command.items`: `empty` (no-results text, default «نتیجه‌ای یافت نشد.»).

### `<mds:color-picker>`

An open implementation of Flux Pro's color picker, API-compatible with `flux:color-picker`. Saturation/value canvas, hue slider, alpha slider (for alpha formats), hex input, swatch palette, and an EyeDropper button:

```blade
<mds:color-picker label="رنگ اصلی" wire:model="color" clearable dropper />

<mds:color-picker type="button" value="#8b5cf6" />                {{-- chip-only trigger --}}

<mds:color-picker format="rgba" value="rgba(59, 130, 246, 0.5)" /> {{-- adds an alpha slider --}}

<mds:color-picker :swatches="[['#ef4444', 'قرمز'], '#3b82f6']" />  {{-- custom palette --}}
<mds:color-picker :swatches="false" />                             {{-- no palette --}}
```

Props: `value`, `name`, `wire:model` (forwarded to the hidden input; emits `input` events), `format` (`hex` default, `hexa`, `rgb`, `rgba`, `hsl`, `hsla` — pasted colors in any of these forms are parsed and normalized), `type` (`input` | `button`), `label`, `description`, `placeholder`, `clearable`, `dropper` (hidden automatically where the EyeDropper API is unsupported), `size` (`sm`), `disabled`, `invalid`.

The popover layout is composable — pass a slot to build your own from `color-picker.area`, `color-picker.slider` (`channel="hue|alpha"`), `color-picker.input`, `color-picker.swatches` / `color-picker.swatch`, and `color-picker.dropper`:

```blade
<mds:color-picker type="button">
    <div class="flex flex-col gap-3">
        <mds:color-picker.input placeholder="#000000" />
        <mds:color-picker.area />
        <mds:color-picker.slider channel="hue" />
    </div>
</mds:color-picker>
```

The canvas and sliders are intentionally left-to-right even on RTL pages (the universal convention for color controls); labels and field layout follow the page direction.

### `<mds:discount-badge>` and `<mds:empty-state>`

```blade
<mds:discount-badge :percent="20" />
<mds:discount-badge :amount="80000" :original="100000" />   {{-- computes ۲۰٪ --}}

<mds:empty-state icon="shopping-cart" title="سبد خرید شما خالی است" description="...">
    <flux:button variant="primary">مشاهده پیشنهادها</flux:button>
</mds:empty-state>
```

## Helpers, directives, facade

```blade
@fa(123)             {{-- ۱۲۳ --}}
@faNum(2500000)      {{-- ۲٬۵۰۰٬۰۰۰ --}}
@toman(2500000)      {{-- ۲٬۵۰۰٬۰۰۰ تومان --}}
@rial(990)           {{-- ۹۹۰ ریال --}}
@jalali('2026-08-20') {{-- ۲۹ مرداد ۱۴۰۵ --}}
```

```php
use MajidDs\Mds;
use MajidDs\Support\{Persian, Jalali};

Mds::toman(2500000);                       // ۲٬۵۰۰٬۰۰۰ تومان
Mds::jalali(now(), 'Y/m/d');               // ۱۴۰۵/۰۵/۲۹
Mds::ago(now()->subHours(3));              // ۳ ساعت پیش
Persian::digits('order 123');              // order ۱۲۳
Persian::latinDigits('۰۹۱۲');              // 0912
Jalali::fromGregorian(2026, 8, 20);        // [1405, 5, 29]
Jalali::toGregorian(1405, 5, 29);          // [2026, 8, 20]
```

The Jalali implementation is dependency-free and is tested against PHP's `intl` Persian calendar across a 110-year range.

## Configuration

```bash
php artisan vendor:publish --tag=mds-config
```

```php
return [
    'currency' => 'toman',        // default currency for prices
    'persian_digits' => true,     // global default; override per component with :fa="..."
];
```

## Customizing components

Publish the views and edit them — app-level views take precedence, exactly like Flux's own publish flow:

```bash
php artisan vendor:publish --tag=mds-views   # -> resources/views/mds/
```

Components can also be used with standard component syntax: `<x-mds::rating>` ≡ `<mds:rating>`.

## Demo & development

A full RTL demo page ships in the workbench:

```bash
composer install
npm install
npm run demo:css        # compile the demo stylesheet (Tailwind v4)
npm run demo:serve      # then open http://127.0.0.1:8720/demo
```

Run the test suite:

```bash
composer test
```

## License

MIT for this package. Note that `livewire/flux` has its own license; the free component set is used here, and Flux Pro features remain subject to Flux's licensing.
