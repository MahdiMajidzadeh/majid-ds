# Majid DS

**An RTL / Persian-first UI kit for Laravel Livewire, built on top of [Flux UI](https://fluxui.dev).**

[![CI](https://github.com/MahdiMajidzadeh/majid-ds/actions/workflows/ci.yml/badge.svg)](https://github.com/MahdiMajidzadeh/majid-ds/actions/workflows/ci.yml)

Majid DS does not replace Flux — it extends it. You keep every `<flux:*>` component exactly as it is, and gain an `<mds:*>` namespace with the pieces Flux doesn't have: Persian typography and digits, Jalali dates, Toman and Rial prices, and the e-commerce components a storefront needs.

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
| Typography | Font-agnostic: every component renders in your `--font-sans`, so one token styles the kit. Set a Persian face — see [Fonts](#fonts). |
| Icons | [Hugeicons](https://hugeicons.com) throughout, with heroicon names still resolving through an alias map |
| Numbers | Persian digits and separators wherever a number is shown, and Latin machine values in every hidden input |
| Money | Toman and Rial, with the currency label and the discount maths handled for you |
| Dates | A dependency-free Jalali calendar, checked against PHP's `intl` Persian calendar across 110 years |
| Forms | Persian and Arabic-Indic digits rewritten to Latin as the user types, plus Iranian identifier checks — national ID, mobile, Sheba, bank card — as validation rules |
| E-commerce | Product cards, quantity steppers, prices, discount badges, ratings, checkout steppers, deal countdowns |
| Pro alternatives | **All nineteen** of Flux's Pro-only components, rebuilt open — including a Jalali calendar and date picker rather than translated Gregorian ones |

Everything is RTL-first, built with logical properties so it works left-to-right too, supports dark mode, and follows Flux's accent tokens: set `--color-accent` once and both libraries follow.

## Documentation

The component reference is deliberately not in this file. It lives in two places, both kept honest by the test suite so neither can quietly go stale:

- **[The docs site](https://mahdimajidzadeh.github.io/majid-ds/)** — one page per component, laid out like [fluxui.dev](https://fluxui.dev/components/callout): grouped nav, live previews and prop tables. 74 pages covering every free Flux component bundled here, the layout grid, and the whole `mds:*` layer. It also ships in the repo, so [docs/index.html](docs/index.html) opens straight from disk.
- **[llms.txt](llms.txt)** — the same API in one compact, machine-readable file: props, slots, behaviour and the Livewire contract for each component. Point your project's `CLAUDE.md` or `AGENTS.md` at `vendor/mahdimajidzadeh/ds/llms.txt` so coding agents use the kit correctly.

Two demos render the whole kit at once: [RTL demo](docs/guides/rtl-demo.html) in Persian, [demo](docs/guides/demo.html) in English, plus a [layout gallery](docs/demo/layouts.html).

**Flux Pro components are not documented here — because the kit replaces them.** Nineteen components on fluxui.dev ship no code in the free tier, so there is nothing of Flux's to preview or reference. All nineteen have an open `mds:*` version instead, each with its own page.

## Requirements

- PHP 8.2+, Laravel 12 or 13
- Livewire 3 or 4, and `livewire/flux` ^2.0 (installed automatically)
- Tailwind CSS v4

Laravel 11 was supported until its releases were withdrawn behind Composer's security advisories: every 11.x version of `laravel/framework` and `illuminate/support` is now blocked, so a fresh install cannot resolve it at all. Keeping the constraint would have been a promise nothing could satisfy.

## Installation

```bash
composer require mahdimajidzadeh/ds
```

**1. CSS** — in `resources/css/app.css`, import the Majid DS layer after Flux:

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/mahdimajidzadeh/ds/resources/css/mds.css';

/* Tailwind v4 skips gitignored paths, so point it at the kit's Blade views
   or the utility classes inside them never get generated. */
@source '../../vendor/mahdimajidzadeh/ds/resources/views';

@custom-variant dark (&:where(.dark, .dark *));

/* The kit sets no font — choose the Persian face your app ships (see Fonts). */
@theme {
    --font-sans: 'Vazirmatn', ui-sans-serif, system-ui, sans-serif;
}
```

**2. Layout** — set the document direction and load your font:

```blade
<html lang="fa" dir="rtl">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    @fluxAppearance
</head>
<body>
    ...
    @fluxScripts
</body>
</html>
```

That's it. `<flux:*>` and `<mds:*>` components now work side by side.

### Fonts

The kit ships no font and sets none — typography belongs to your app. Every `mds:*` component renders in Tailwind's
`font-sans`, so one token styles the whole kit. For Persian text point it at a Persian face in your `app.css`, after the
kit's import; [Vazirmatn](https://github.com/rastikerdar/vazirmatn) (SIL OFL) is the recommended default because Persian
and Latin share one family and one scale:

```css
@theme {
    --font-sans: 'Vazirmatn', ui-sans-serif, system-ui, sans-serif;
}
```

Then load the face, one of two ways:

- **Self-hosted** — recommended for production traffic from Iran, where Google Fonts is slow or blocked, and it avoids a
  third-party request everywhere else. Put the `woff2` files under `public/fonts` and declare them:

  ```css
  @font-face {
      font-family: 'Vazirmatn';
      src: url('/fonts/Vazirmatn[wght].woff2') format('woff2');
      font-weight: 100 900;
      font-display: swap;
  }
  ```

- **Google Fonts** — a single `<link>` in `<head>`, as in the layout in step 2 above.

Any other Persian face (IRANSansX, Yekan Bakh, Sahel…) works the same way: name it in `--font-sans` and load it.

### Content Security Policy

The interactive components register their Alpine behaviour in inline `<script>` blocks. Under a policy with
`script-src 'nonce-…'` the kit needs the nonce, and it reads the one you register with Laravel — the same registry
Livewire's own tags read, and Flux takes it as an option — so one line per request covers the page:

```php
// in your CSP middleware
$nonce = Vite::useCspNonce(); // generates one; or pass your own

$response = $next($request);
$response->headers->set('Content-Security-Policy', "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval'; style-src 'self' 'unsafe-inline'");
```

```blade
@fluxAppearance(['nonce' => Vite::cspNonce()])
...
@fluxScripts(['nonce' => Vite::cspNonce()])

<script @mdsNonce>…</script>   {{-- your own inline scripts can carry the same nonce --}}
```

Every `<script>` the kit renders carries `@mdsNonce`, which echoes `Mds::cspNonce()` or nothing. Two more things a
strict policy needs: `'unsafe-eval'` in `script-src`, because Alpine's standard build (the one Livewire bundles)
evaluates `x-data` expressions, and `'unsafe-inline'` in `style-src`, because the kit renders inline `style`
attributes for chart geometry, colour swatches and hidden-until-Alpine states. Nonces cover scripts only.

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
use MajidDs\Support\{Persian, Jalali, Iran};

Mds::toman(2500000);                       // ۲٬۵۰۰٬۰۰۰ تومان
Mds::jalali(now(), 'Y/m/d');               // ۱۴۰۵/۰۵/۲۹
Mds::ago(now()->subHours(3));              // ۳ ساعت پیش
Mds::fileSize(162400);                     // ۱۵۹ کیلوبایت
Persian::digits('order 123');              // order ۱۲۳
Persian::latinDigits('۰۹۱۲');              // 0912
Jalali::fromGregorian(2026, 8, 20);        // [1405, 5, 29]
Jalali::toGregorian(1405, 5, 29);          // [2026, 8, 20]
Iran::normalizeMobile('+98 912 345 6789'); // 09123456789
Iran::nationalId('0013542877');            // true
```

The Jalali implementation is dependency-free and is tested against PHP's `intl` Persian calendar across a 110-year range.

The Iranian identifiers also ship as validation rules — `NationalId`, `IranMobile`, `Sheba` and `BankCard` — each accepting Persian digits and the spaced forms the inputs produce, with a Persian or English message chosen by `config('mds.persian_digits')`.

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

Components also work with standard component syntax: `<x-mds::rating>` ≡ `<mds:rating>`. That spelling is *required* for `wire:key`, which Livewire's precompiler rewrites before the namespaced tag is compiled.

## Development

```bash
composer install
npm install

composer check          # analyse + test — the local loop; formatting is enforced on CI
composer fix            # apply Pint formatting
npm run demo:css        # compile the demo stylesheet, once
npm run demo:serve      # then open http://127.0.0.1:8720/demo
```

`docs/` is a committed static site — GitHub Pages serves it as-is, with no build step — so regenerate it whenever a view, the CSS or a docs page changes:

```bash
npm run docs            # the 74 reference pages + docs/assets/site.css
npm run pages           # the 18 layout-gallery pages in docs/demo/
```

Both builders are deterministic, so a rebuild that dirties git means something actually changed — which is what lets CI rebuild and fail on a dirty tree.

### The workbench demo

Two RTL demos ship in the workbench: a component showcase at `/demo`, and a layout gallery at `/layouts` covering every arrangement Flux's grid supports — header-only, full-height sidebar, header above sidebar, a collapsible rail, off-canvas on mobile, a three-column aside, sticky regions, and container width control. Layout is decided by the order of the children: a `<flux:sidebar>` before `<flux:header>` claims the full height, a `<flux:header>` first spans the full width, and in RTL the sidebar lands on the right with no extra classes.

Every route also exists under `/en`, rendered left-to-right in English. Both locales are what `npm run pages` snapshots into `docs/`.

The Persian copy in the workbench views **is** the translation key, so the Blade stays readable in the language the kit is designed for:

```blade
<flux:button :href="$mdsUrl('/layouts')" icon="squares-2x2">{{ __('چیدمان‌های صفحه') }}</flux:button>
```

[`workbench/lang/en.json`](workbench/lang/en.json) maps each Persian string to English via Laravel's JSON translations. An unmapped key falls through to itself, so `fa` needs no translation file at all.

Locale also drives the kit's own output. Every `mds:*` component reads `config('mds.persian_digits')` and `config('mds.currency')` at render time, so the route sets them once per locale and the whole page switches — digits, separators, the currency label and every built-in string: `۲٬۵۰۰٬۰۰۰ تومان` becomes `2,500,000 Toman`, `ناموجود` becomes `Out of stock`, and Jalali month names transliterate. The only Persian the config cannot switch is the digit and money directives (`@fa`, `@faNum`, `@toman`, `@rial`), which are Persian by definition. The docs build counts the Persian left on each English page, so a new untranslated string shows up in the build output.

## License

MIT for this package. Note that `livewire/flux` has its own license; the free component set is used here, and Flux Pro features remain subject to Flux's licensing.
