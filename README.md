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
| Typography | Font-agnostic: every component renders in your `--font-sans`. Set a Persian face (Vazirmatn is the recommended default) — see [Fonts](#fonts). |
| Icons | [Hugeicons](https://hugeicons.com) via `<mds:icon>`, replacing heroicons across every `mds:*` component (heroicon names still work) |
| Numbers | Persian digits (۰۱۲۳), Persian separators (٬ / ٫), `@fa` / `@faNum` directives |
| Forms | `<mds:input>` — Flux's input that rewrites Persian/Arabic digits to Latin as the user types, so `wire:model` always receives machine values |
| Money | `<mds:price>`, `@toman` / `@rial` directives, configurable default currency |
| Dates | Dependency-free Jalali calendar (`<mds:jalali-date>`, `@jalali`, relative "۳ ساعت پیش") |
| E-commerce | `<mds:product-card>`, `<mds:quantity>`, `<mds:discount-badge>`, `<mds:countdown>` |
| Flows | `<mds:stepper>` (checkout steps), `<mds:rating>` / `<mds:rating.input>`, `<mds:empty-state>`, `<mds:preview-card>` |
| Pro alternatives | **All nineteen** of Flux's Pro-only components, rebuilt open: `<mds:command>` (⌘K palette), `<mds:composer>`, `<mds:color-picker>`, `<mds:file-upload>`, `<mds:timeline>`, `<mds:chart>`, `<mds:popover>`, `<mds:accordion>`, `<mds:slider>`, `<mds:time-picker>`, `<mds:tabs>`, `<mds:autocomplete>`, `<mds:carousel>`, `<mds:context>`, `<mds:pillbox>`, `<mds:editor>`, `<mds:calendar>` (Jalali), `<mds:kanban>` and `<mds:date-picker>` |

All components are RTL-first (built with logical properties, so they also work LTR), support dark mode, and follow Flux's accent color tokens — customize `--color-accent` once and both libraries follow.

## Documentation

- **Reference docs**: [docs/index.html](docs/index.html) — one page per component, laid out like [fluxui.dev](https://fluxui.dev/components/callout): grouped nav, live previews, prop tables. 74 pages covering every free Flux component that ships with this package, the layout grid, and the whole `mds:*` layer.
- **Live demo**: the component showcase lives inside the docs — Persian RTL at [docs/guides/rtl-demo.html](docs/guides/rtl-demo.html), English LTR at [docs/guides/demo.html](docs/guides/demo.html). The layout gallery is pre-rendered to static HTML from the workbench: Persian at [docs/demo/layouts.html](docs/demo/layouts.html), English at [docs/demo/layouts-en.html](docs/demo/layouts-en.html), with a language switcher on every page.
- **AI-agent docs**: [llms.txt](llms.txt) — the same API surface in compact, machine-oriented markdown. Point your project's `CLAUDE.md`/`AGENTS.md` at `vendor/mahdimajidzadeh/ds/llms.txt` so coding agents use the kit correctly.

The whole `docs/` folder is a static site: **Settings → Pages → Source: `main` / `/docs`**
publishes it at `https://mahdimajidzadeh.github.io/majid-ds/`, docs and demo alike. No
build step runs on GitHub — the pages are committed, so regenerate them when things change:

```bash
npm run docs            # rebuilds the 74 reference pages + docs/assets/site.css
npm run pages           # rebuilds the 18 layout-gallery pages in docs/demo/
```

Both sites share one stylesheet, `docs/assets/site.css`, built from
[workbench/resources/css/site-input.css](workbench/resources/css/site-input.css) — so a
component CSS change can never ship to the docs but not the demo. The same input also
compiles to `workbench/public/demo.css` for `npm run demo:serve`.

### What the docs cover

| Group | Pages |
|---|---|
| Guides | Overview, Installation, Theming, Directives & helpers, AI agents |
| Layouts | The layout grid, Header, Sidebar, Aside |
| Components | The 32 free Flux components bundled with this package |
| mds components | All 31 `mds:*` components, including the nineteen Flux Pro alternatives |

**Flux Pro components are not documented — because the kit replaces them.** Nineteen of
the components on fluxui.dev ship no code in the free tier, so there is nothing to preview
or reference from Flux itself. All nineteen now have an open `mds:*` version built here:
Accordion, Autocomplete, Calendar, Carousel, Chart, Color picker, Command, Composer,
Context, Date picker, Editor, File upload, Kanban, Pillbox, Popover, Slider, Tabs, Time
picker and Timeline — each with its own page in the mds group.

### How the docs are built

[bin/build-docs.php](bin/build-docs.php) assembles the pages from the content in
[bin/docs/](bin/docs) — one PHP file per group, holding each page's prose, examples and
prop tables. The output is plain static HTML: nothing at read time needs PHP, Blade or a
server.

Each example's preview is its own snippet, rendered once through Blade at build time.
That is deliberate: Flux computes its class strings at render time (a `match` over
colour, size and variant), so transcribing that markup by hand would give previews that
only resemble the components. Rendering it means the snippet you copy and the preview
above it are provably the same thing — and because the real `flux.js` is loaded, the
dropdowns, modals, tooltips and countdowns in the docs actually work.

`bin/build-pages.php` is the equivalent tool for the layout gallery: it boots the
workbench app on a throwaway port, crawls `/layouts` and its `/en` counterpart, and
writes flat `.html` files into `docs/demo/` with every link and asset rewritten to a
relative path — so the site works from a project subpath, a custom domain, or straight
off the file system. The component showcase needs no crawl: the docs builder renders
the workbench's demo cards (`workbench/resources/views/demo/cards.blade.php`) straight
into the Demo and RTL demo pages under Guides.

## Requirements

- PHP 8.2+, Laravel 11+
- Livewire 3 and `livewire/flux` ^2.0 (installed automatically)
- Tailwind CSS v4

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
The `@mdsFonts` directive that used to emit the Google Fonts tags is gone — replace it with one of these.

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

## Components

Every component has its own reference page with live previews and a prop table,
and [llms.txt](llms.txt) carries the same API in one machine-readable file —
props, slots, behaviour and the Livewire contract for each. Both are generated
or test-enforced, so this table stays an index rather than a fourth copy of the
API that quietly goes stale.

<!-- mds:components (generated by `npm run docs` — edit bin/docs/mds.php, not this) -->

| Component | What it is |
|---|---|
| [`<mds:icon>`](docs/mds/mds-icon.html) | Hugeicons, replacing heroicons across every mds component |
| [`<mds:input>`](docs/mds/mds-input.html) | Flux's input, storing Latin digits whatever keyboard typed them |
| [`<mds:price>`](docs/mds/price.html) | Money in Toman or Rial — separators, currency label and discount badge from one amount |
| [`<mds:discount-badge>`](docs/mds/discount-badge.html) | A percentage-off pill |
| [`<mds:quantity>`](docs/mds/quantity.html) | A cart quantity stepper |
| [`<mds:rating>`](docs/mds/rating.html) | A star rating, read-only or as an input |
| [`<mds:product-card>`](docs/mds/product-card.html) | A whole product tile: image, title, price, rating and a badge |
| [`<mds:stepper>`](docs/mds/stepper.html) | Checkout steps, with the completed ones ticked |
| [`<mds:countdown>`](docs/mds/countdown.html) | A live countdown to a deadline |
| [`<mds:jalali-date>`](docs/mds/jalali-date.html) | Jalali (Shamsi) dates, with no external calendar dependency |
| [`<mds:empty-state>`](docs/mds/empty-state.html) | What to show when there is nothing to show |
| [`<mds:preview-card>`](docs/mds/preview-card.html) | A hover preview of a link's destination, shown beside it |
| [`<mds:command>`](docs/mds/command.html) | A ⌘K command palette — an open version of a Flux Pro component |
| [`<mds:composer>`](docs/mds/composer.html) | A chat / prompt input with an action bar — an open version of a Flux Pro component |
| [`<mds:color-picker>`](docs/mds/color-picker.html) | A colour picker — an open version of a Flux Pro component |
| [`<mds:file-upload>`](docs/mds/file-upload.html) | Drag-and-drop uploads — an open version of a Flux Pro component |
| [`<mds:timeline>`](docs/mds/timeline.html) | An event timeline — an open version of a Flux Pro component |
| [`<mds:chart>`](docs/mds/chart.html) | Monochrome dashboard charts, server-rendered as SVG — an open answer to a Flux Pro component |
| [`<mds:popover>`](docs/mds/popover.html) | A panel anchored to the button that opens it — an open version of a Flux Pro component |
| [`<mds:accordion>`](docs/mds/accordion.html) | Collapsible sections built on native details/summary — an open version of a Flux Pro component |
| [`<mds:slider>`](docs/mds/slider.html) | A one- or two-thumb range slider — an open version of a Flux Pro component |
| [`<mds:time-picker>`](docs/mds/time-picker.html) | A typable time field over a list of times — an open version of a Flux Pro component |
| [`<mds:tabs>`](docs/mds/tabs.html) | Tabs and tab panels — an open version of a Flux Pro component |
| [`<mds:autocomplete>`](docs/mds/autocomplete.html) | A text field with suggestions — an open version of a Flux Pro component |
| [`<mds:carousel>`](docs/mds/carousel.html) | A slide strip with controls, dots and autoplay — an open version of a Flux Pro component |
| [`<mds:context>`](docs/mds/context.html) | A right-click menu around any content — an open version of a Flux Pro component |
| [`<mds:pillbox>`](docs/mds/pillbox.html) | A multi-select whose chosen values become removable pills — an open version of a Flux Pro component |
| [`<mds:editor>`](docs/mds/editor.html) | A rich-text field — an open version of a Flux Pro component |
| [`<mds:calendar>`](docs/mds/calendar.html) | A Jalali date-picker grid — an open version of a Flux Pro component, on the Iranian calendar |
| [`<mds:kanban>`](docs/mds/kanban.html) | A drag-and-drop board that also works entirely from the keyboard — an open version of a Flux Pro component |
| [`<mds:date-picker>`](docs/mds/date-picker.html) | A date field you can type into, with a Jalali calendar in a popover — an open version of a Flux Pro component |

<!-- /mds:components -->

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
Mds::fileSize(162400);                     // ۱۵۹ کیلوبایت
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

Two RTL demos ship in the workbench — a component showcase and a layout gallery:

```bash
composer install
npm install
npm run demo:css        # compile the demo stylesheet (Tailwind v4)
npm run demo:serve      # then open http://127.0.0.1:8720/demo
```

| Route | What it shows |
|---|---|
| `/demo` | Every free Flux component plus the whole `<mds:*>` set |
| `/layouts` | Gallery of all page layouts, with a wireframe and snippet for each |
| `/layouts/header` | Top navbar only — header, main and footer all contained |
| `/layouts/sidebar` | Full-height sidebar (sidebar before header) |
| `/layouts/sidebar-header` | Full-width header above the sidebar (header first) |
| `/layouts/collapsible` | Sidebar that collapses to an icon rail on desktop |
| `/layouts/mobile` | Sidebar fixed on desktop, off-canvas below `lg` |
| `/layouts/aside` | Three columns: sidebar, main and a sticky `flux:aside` |
| `/layouts/sticky` | Sticky header, sidebar and aside with a long scrolling main |
| `/layouts/container` | `container` prop and `flux:container` width control |

Every route above also exists under `/en` — `/en/demo`, `/en/layouts/aside` and so
on — rendered left-to-right in English. Both locales are what `npm run pages`
snapshots into `docs/` (18 pages); see [Documentation](#documentation) for
publishing them.

### How the two locales work

The Persian copy in the workbench views **is** the translation key, so the Blade
stays readable in the language the kit is designed for:

```blade
<flux:button :href="$mdsUrl('/layouts')" icon="squares-2x2">{{ __('چیدمان‌های صفحه') }}</flux:button>
```

[`workbench/lang/en.json`](workbench/lang/en.json) maps each Persian string to
English via Laravel's JSON translations. An unmapped key falls through to itself,
so `fa` needs no translation file at all.

Locale also drives the kit's own output. Every `mds:*` component reads
`config('mds.persian_digits')` and `config('mds.currency')` at render time, so the
route sets them once per locale and the whole page switches — digits, separators,
the currency label, and every built-in string: `۲٬۵۰۰٬۰۰۰ تومان` becomes
`2,500,000 Toman`, `ناموجود` becomes `Out of stock`, the countdown's
`روز / ساعت / دقیقه / ثانیه` become `days / hours / min / sec`, Jalali month names
transliterate (`۲۹ مرداد` → `29 Mordad`), and `Persian::ago()` speaks English —
without touching a single component call.

The only Persian the config cannot switch is the digit and money directives
(`@fa`, `@faNum`, `@toman`, `@rial`), which are Persian by definition — `@jalali`
follows the config, and `mds:price` is the config-aware way to write money. The
docs build counts the Persian left on each English page, so a new untranslated
string shows up in the build output.

Every layout page carries a floating switcher, so you can jump between them (and
toggle dark mode) without going back to the gallery. Layout arrangement is decided
by the order of the children: a `<flux:sidebar>` placed before `<flux:header>`
claims the full height, a `<flux:header>` placed first spans the full width — in
RTL the sidebar lands on the right with no extra classes.

Run the test suite:

```bash
composer test
```

## License

MIT for this package. Note that `livewire/flux` has its own license; the free component set is used here, and Flux Pro features remain subject to Flux's licensing.
