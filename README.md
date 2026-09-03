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
| Pro alternatives | `<mds:command>` (⌘K palette), `<mds:composer>` (chat/prompt input), `<mds:color-picker>`, `<mds:file-upload>`, `<mds:timeline>`, and `<mds:chart>` — open versions of Flux Pro-only components |

All components are RTL-first (built with logical properties, so they also work LTR), support dark mode, and follow Flux's accent color tokens — customize `--color-accent` once and both libraries follow.

## Documentation

- **Reference docs**: [docs/index.html](docs/index.html) — one page per component, laid out like [fluxui.dev](https://fluxui.dev/components/callout): grouped nav, live previews, prop tables. 61 pages covering every free Flux component that ships with this package, the layout grid, and the whole `mds:*` layer.
- **Live demo**: the component showcase lives inside the docs — Persian RTL at [docs/guides/rtl-demo.html](docs/guides/rtl-demo.html), English LTR at [docs/guides/demo.html](docs/guides/demo.html). The layout gallery is pre-rendered to static HTML from the workbench: Persian at [docs/demo/layouts.html](docs/demo/layouts.html), English at [docs/demo/layouts-en.html](docs/demo/layouts-en.html), with a language switcher on every page.
- **AI-agent docs**: [llms.txt](llms.txt) — the same API surface in compact, machine-oriented markdown. Point your project's `CLAUDE.md`/`AGENTS.md` at `vendor/mahdimajidzadeh/ds/llms.txt` so coding agents use the kit correctly.

The whole `docs/` folder is a static site: **Settings → Pages → Source: `main` / `/docs`**
publishes it at `https://mahdimajidzadeh.github.io/majid-ds/`, docs and demo alike. No
build step runs on GitHub — the pages are committed, so regenerate them when things change:

```bash
npm run docs            # rebuilds the 61 reference pages + docs/assets/site.css
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
| mds components | All 17 `mds:*` components, including the six Flux Pro alternatives |

**Flux Pro components are not documented.** Nineteen of the components on fluxui.dev —
Accordion, Autocomplete, Calendar, Carousel, Chart, Composer, Context, Date picker,
Editor, Kanban, Pillbox, Popover, Slider, Tabs, Time picker and the rest — ship no code
in the free tier, so there is nothing to preview or reference. Six of them have `mds:*`
replacements (Command, Composer, Color picker, File upload, Timeline, Chart); for the
others, see Flux's own docs.

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

### `<mds:price>`

```blade
<mds:price :amount="2500000" />                          {{-- ۲٬۵۰۰٬۰۰۰ تومان --}}
<mds:price :amount="2500000" :original="3200000" />      {{-- + strikethrough & ۲۲٪ badge --}}
<mds:price :amount="14500000" currency="rial" size="sm" />
<mds:price :amount="1200000" :fa="false" />              {{-- Latin digits --}}
```

Props: `amount`, `original`, `currency` (`toman` | `rial` | `none` | literal label), `decimals`, `size` (`sm`|`lg`), `fa`, `badge`.

### `<mds:input>`

Flux's input with digit normalisation. Persian keyboards type `۰۱۲۳` and Arabic ones `٠١٢٣`; bound to a plain input, that is what
`wire:model` posts. `<mds:input>` puts the `x-mds-digits` directive on the control, which rewrites them to Latin as they are typed,
pasted or dropped — before the browser applies the keystroke, so the field fires one `input` event that is already Latin. Every
`flux:input` prop passes through:

```blade
<mds:input wire:model.live="mobile" label="Mobile number" type="tel" only ltr />
<mds:input only label="Verification code" maxlength="5" />
<mds:input label="Address" placeholder="خیابان ولیعصر، پلاک ۱۲" />
```

Props: `only` (digits alone, and `inputmode="numeric"` for mobile keyboards), `ltr` (keep the control left-to-right inside an RTL
form), plus everything `flux:input` accepts. Leave `only` off when you pass a `mask`; the mask owns the value's shape.

Four presets cover the numbers every Persian checkout asks for, each paired with a validation rule that checks what the issuing
authority defines — the national ID's check digit, the mobile prefixes, the IBAN mod-97 check, Luhn for cards. The rules accept
Persian digits and the spaced forms the inputs produce; the `Iran` helpers return the canonical value to store:

```blade
<mds:input.mobile wire:model="mobile" label="Mobile number" />          {{-- 11 digits, 09… --}}
<mds:input.national-id wire:model="national_id" label="National ID" />  {{-- 10 digits --}}
<mds:input.card wire:model="card" label="Card number" />                {{-- masked 6037 9911 0000 0003 --}}
<mds:input.sheba wire:model="sheba" label="Sheba" />                    {{-- masked IR06 0620 0000 … --}}
```

```php
use MajidDs\Rules\{IranMobile, NationalId, BankCard, Sheba};
use MajidDs\Support\Iran;

$this->validate(['mobile' => ['required', new IranMobile], 'card' => ['nullable', new BankCard]]);

$user->mobile = Iran::normalizeMobile($this->mobile);   // "09123456789"
$user->card = Iran::normalizeBankCard($this->card);     // 16 digits, no spaces
```

Messages are Persian or English by `config('mds.persian_digits')`, or pass your own: `new Sheba('Please check the Sheba number.')`.

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

<mds:product-card title="..." image="..." unavailable />   {{-- grayscale + ناموجود / "Out of stock" --}}
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

Props — `command.input`: `icon` (default `magnifying-glass`), `clearable`, `closable` (closes the containing modal); `command.item`: `icon`, `icon-variant`, `kbd`, `href` (renders `<a>` instead of `<button>`); `command.items`: `empty` (no-results text, default «نتیجه‌ای یافت نشد.» — or "No results found." when Persian output is off).

### `<mds:composer>`

An open implementation of Flux Pro's composer, API-compatible with `flux:composer`. A textarea that grows with the text, an action bar around it, and `Ctrl`/`⌘` + `Enter` to submit the enclosing form — the input every chat and AI-prompt screen needs:

```blade
<form wire:submit="send">
    <mds:composer wire:model="message" label="پیام" label:sr-only placeholder="پیام خود را بنویسید...">
        <x-slot name="actionsLeading">
            <flux:button size="sm" variant="subtle" square><mds:icon icon="paper-clip" class="size-4" /></flux:button>
        </x-slot>

        <x-slot name="actionsTrailing">
            <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
        </x-slot>
    </mds:composer>
</form>
```

Four slots wrap the input: `header` (attachment previews, a reply-to line), `footer`, `actionsLeading` and `actionsTrailing`. A fifth, `input`, replaces the textarea itself — that is where Flux drops its Pro editor, and where any rich-text control goes.

```blade
<mds:composer rows="1" max-rows="6" inline submit="enter" ... />   {{-- one row, actions beside it, Enter sends --}}
<mds:composer variant="input" label="پیام پشتیبانی" ... />          {{-- form-control corner radius --}}
<mds:composer :maxlength="500" counter ... />                       {{-- «۱۲ / ۵۰۰» under the action bar --}}
```

`rows` is the height it starts at and `max-rows` the height it stops growing at; past that the input scrolls. `submit="enter"` promotes the bare `Enter` to sending and leaves `Shift` + `Enter` as the newline — `Ctrl`/`⌘` + `Enter` sends either way, and an open IME composition keeps its `Enter`. Sending calls `form.requestSubmit()`, so `wire:submit` and native validation behave exactly as they do on a click.

The counter counts characters rather than bytes — «سلام» is ۴ — and renders in Persian digits unless you pass `:fa="false"`. `disabled` makes the whole box `inert`, action buttons included, and validation errors come from the bag for `name` (or from an explicit `error`), the same as every other field in the kit.

Props: `name`, `value`, `placeholder`, `label` (+ `label:sr-only`), `description` (+ `description:sr-only`), `rows`, `max-rows`, `maxlength`, `counter`, `inline`, `variant`, `submit`, `autofocus`, `dir` (`auto` follows what is being typed), `disabled`, `invalid`, `error`, `fa`. `wire:model` lands on the textarea; everything else lands on the wrapper.

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

### `<mds:file-upload>` and `<mds:file-item>`

An open implementation of Flux Pro's file upload, API-compatible with `flux:file-upload`. Drag-and-drop, click-to-browse, a compact `inline` variant, and a progress bar driven by Livewire's own upload events. Under the chrome it is a real `<input type="file">`, so `wire:model`, plain form posts, and keyboard access all work:

```blade
<mds:file-upload wire:model="photos" label="بارگذاری تصاویر" multiple accept="image/*">
    <mds:file-upload.dropzone text="JPG، PNG یا GIF تا ۱۰ مگابایت" with-progress />
</mds:file-upload>

<div class="mt-3 flex flex-col gap-2">
    @foreach ($photos as $index => $photo)
        <mds:file-item
            :heading="$photo->getClientOriginalName()"
            :image="$photo->temporaryUrl()"
            :size="$photo->getSize()"
        >
            <x-slot name="actions">
                <mds:file-item.remove wire:click="removePhoto({{ $index }})" />
            </x-slot>
        </mds:file-item>
    @endforeach
</div>
```

`<mds:file-item :size="162400" />` renders «۱۵۹ کیلوبایت» — sizes go through `Persian::fileSize()`, and file names get `dir="auto"` so Latin names stay readable in an RTL list. Pass `invalid` for rejected files.

Props — `file-upload`: `name` (`[]` appended when `multiple`), `multiple`, `accept`, `label`, `description`, `error` (falls back to `$errors->first($name)` when omitted), `invalid`, `disabled`, `fa`; `file-upload.dropzone`: `heading`, `text`, `icon` (default `cloud-arrow-up`), `inline`, `with-progress`; `file-item`: `heading`, `text`, `image`, `size`, `icon`, `invalid`, `fa`, plus an `actions` slot; `file-item.remove`: `icon`, `label` (its `aria-label`).

Any markup in the slot inherits the upload behavior, so custom uploaders are just HTML. The wrapper carries `data-dragging` and `data-loading` (target them with Tailwind's `in-data-dragging:` / `in-data-loading:`) and sets `--mds-file-upload-progress` (`42%`) and `--mds-file-upload-progress-as-string` (`'۴۲٪'`) for custom progress UIs:

```blade
<mds:file-upload wire:model="avatar" accept="image/*">
    <div class="size-20 rounded-full bg-zinc-100 in-data-dragging:border-accent in-data-loading:opacity-50">
        <flux:icon icon="user" variant="solid" />
    </div>
</mds:file-upload>
```

### `<mds:preview-card>`

A hover preview of a link's destination (inspired by [Appica UI's Preview Card](https://appica.dev/ui/components/react/preview-card)) — the profile behind an @mention, the venue behind a place name. The trigger is a real `<a>` that still navigates on click; hovering or focusing it opens a card beside it:

```blade
<p>
    این کیت توسط
    <mds:preview-card>
        <mds:preview-card.trigger href="/team">@majid_ds</mds:preview-card.trigger>

        <mds:preview-card.content>
            <div class="flex items-center justify-between">
                <flux:avatar src="/img/team.jpg" />
                <flux:button size="sm" variant="primary">دنبال کردن</flux:button>
            </div>
            <div class="font-semibold">مجید دیزاین سیستم</div>
            <p>کیت رابط کاربری راست‌چین برای Laravel Livewire.</p>
        </mds:preview-card.content>
    </mds:preview-card>
    نگه‌داری می‌شود.
</p>
```

Position with `side` (`top` / `bottom` / `start` / `end`) and `align` (`start` / `center` / `end`) — logical values, so placement mirrors automatically on RTL pages, including RTL islands inside LTR pages. The card flips when it runs out of room, clamps to the viewport, repositions on scroll, and its arrow keeps pointing at the link (`:arrow="false"` for a flatter card that sits closer). Hover waits `delay` (600ms) to open and `close-delay` (300ms) to close; keyboard focus opens immediately; `Escape` closes.

The popup ships inside `<template x-teleport="body">` — the template keeps a block-level card legal inside a `<p>` (an HTML parser would otherwise split the paragraph and strand the popup outside the component), and the teleport plays the portal role, so `overflow: hidden` ancestors never clip it. The preview never opens on touch and is not announced to screen readers — it is supplementary by design, so keep anything essential on the linked page.

Props — `preview-card`: `delay`, `close-delay`; `preview-card.trigger`: `href` plus any anchor attribute; `preview-card.content`: `side`, `align`, `side-offset` (default 10, or 6 without the arrow), `arrow`.

### `<mds:icon>`

Majid DS uses [Hugeicons](https://hugeicons.com) instead of heroicons. Every `mds:*` component's `icon` prop routes through `<mds:icon>`, so the whole kit shares one icon language:

```blade
<mds:icon icon="shopping-cart-01" />                       {{-- a Hugeicons name --}}
<mds:icon icon="magnifying-glass" />                       {{-- a heroicon name, aliased --}}
<mds:icon icon="truck-delivery" class="size-4 text-accent" />
<mds:icon icon="notification-01" :stroke="2" />            {{-- thicker strokes --}}
<mds:icon icon="alert-02" label="هشدار" />                 {{-- role="img" + aria-label --}}
```

The free **Stroke Rounded** set (6,200 icons) ships with the `afatmustafa/blade-hugeicons` dependency, so it works out of the box. Sizing and colour are plain Tailwind classes; unlabelled icons are `aria-hidden`, and a `label` promotes them to `role="img"`.

**Heroicon names keep working.** Two maps handle the vocabulary gap: `ALIASES` translates heroicon names with no Hugeicons counterpart (`magnifying-glass` → `search-01`, `x-mark` → `cancel-01`, `cloud-arrow-up` → `cloud-upload`), and `OVERRIDES` handles the six names Hugeicons *also* has but draws differently — its `arrow-*` are chevrons rather than arrows, its `moon` is full rather than crescent, and its `map-pin` is a pin on a map. A literal Hugeicons name always beats an alias, so `icon="heart"` gets you Hugeicons' own heart. Anything neither map covers falls back to `flux:icon`, which still renders heroicons — so nothing breaks.

If you only ever use `<mds:icon>` and want a hard guarantee that heroicons never render — for example to keep the heroicons set out of the page entirely — set `'strict' => true` under `icons` in `config/mds.php`. An unmapped name then renders nothing instead of falling back.

`:stroke` is opt-in on purpose: 478 of the free icons vary their stroke weight deliberately, and forcing one width would flatten them.

**Pro styles.** Only Stroke Rounded is free. The other eight styles are never bundled — register your own licensed export and they resolve by name:

```php
// config/mds.php
'icons' => [
    'default' => 'hugeicons',            // 'flux' switches the whole kit back to heroicons
    'style' => 'stroke-rounded',
    'sets' => [
        'solid-rounded' => resource_path('svg/hugeicons/solid-rounded'),
    ],
],
```

```blade
<mds:icon icon="user" variant="solid-rounded" />   {{-- a Hugeicons style --}}
<mds:icon icon="user" variant="micro" />           {{-- a Flux variant, mapped --}}
```

`variant` takes either vocabulary, so markup written against `flux:icon` keeps rendering. Licensing: the free set is published to npm as MIT and redistributed by `afatmustafa/blade-hugeicons`; note that Hugeicons' own [licence agreement](https://hugeicons.com/license-agreement) claims to cover the free versions too, so if that matters to you, read both before shipping. Pro files stay in your app either way.

### `<mds:timeline>`

An open implementation of Flux Pro's timeline, API-compatible with `flux:timeline`. A CSS-grid rail with connector lines, step statuses, coloured indicators, and full-width blocks. The rail sits on the **right** in RTL, and a horizontal timeline flows right-to-left:

```blade
<mds:timeline horizontal>
    <mds:timeline.item status="complete">
        <mds:timeline.indicator><flux:icon icon="credit-card" variant="micro" /></mds:timeline.indicator>
        <mds:timeline.content>
            <flux:heading>پرداخت شد</flux:heading>
            <flux:text>@jalali($order->paid_at)</flux:text>
        </mds:timeline.content>
    </mds:timeline.item>

    <mds:timeline.item status="current">
        <mds:timeline.indicator><flux:icon icon="truck" variant="micro" /></mds:timeline.indicator>
        <mds:timeline.content><flux:heading>در حال ارسال</flux:heading></mds:timeline.content>
    </mds:timeline.item>

    <mds:timeline.item status="incomplete">
        <mds:timeline.indicator><flux:icon icon="home" variant="micro" /></mds:timeline.indicator>
        <mds:timeline.content><flux:heading>تحویل به مشتری</flux:heading></mds:timeline.content>
    </mds:timeline.item>
</mds:timeline>
```

`status` drives the rail as well as the dot: `complete` paints the connector up to the next indicator in your accent colour, `current` gets an accent ring, `incomplete` is muted and dims its content.

```blade
{{-- Numbered checkout steps --}}
<mds:timeline size="lg" align="start">
    <mds:timeline.item status="complete">
        <mds:timeline.indicator>@fa(1)</mds:timeline.indicator>
        <mds:timeline.content>
            <flux:heading>سبد خرید</flux:heading>
            <flux:text>کالاها را بررسی و تعداد را نهایی کنید.</flux:text>
        </mds:timeline.content>
    </mds:timeline.item>
</mds:timeline>

{{-- A full-width block, re-aligned to the rail's columns --}}
<mds:timeline.item>
    <mds:timeline.block class="rounded-xl border border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-white/5">
        <mds:timeline.subgrid class="p-3">
            <flux:avatar size="xs" circle src="..." />
            <flux:text>مرسوله شما امروز از انبار تهران ارسال می‌شود.</flux:text>
        </mds:timeline.subgrid>
    </mds:timeline.block>
</mds:timeline.item>
```

Props — `timeline`: `horizontal`, `align` (`start` | `baseline` | `center` | `end`), `size` (`lg`); `timeline.item`: `status` (`complete` | `current` | `incomplete`), plus `align` / `size` overrides; `timeline.indicator`: `variant="bare"` (icon-only marker), `status`, `color` (the 17 Tailwind hues plus `zinc`, and it outranks `status`). `timeline.content`, `timeline.block`, and `timeline.subgrid` take no props.

Geometry is four CSS variables: `--mds-timeline-item-gap`, `--mds-timeline-content-gap`, `--mds-timeline-indicator-size`, and `--mds-timeline-baseline` (the first line-height `align="baseline"` centres on — raise it for large headings). Set them with an arbitrary class:

```blade
<mds:timeline class="[--mds-timeline-item-gap:3rem]">...</mds:timeline>
```

### `<mds:chart>`

Monochrome dashboard charts in the [Mono Charts](https://github.com/Subhan-code/Monocharts) style (MIT), server-rendered as SVG — no chart library, no script, no payload. An open answer to Flux Pro's `flux:chart`, with a different, batteries-included API: where Flux ships a client-side line-chart toolkit, `mds:chart` ships finished dashboard cards. `<mds:chart>` is the card chrome (label, badge, big stat, delta, footer); the `<mds:chart.*>` stages draw the charts and also work standalone anywhere:

```blade
<mds:chart label="فروش ماهانه" :value="84" unit="هزار سفارش" delta="+14.2%">
    <mds:chart.line :data="[24, 45, 38, 65, 52, 84]" :labels="['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور']" area />
</mds:chart>

<mds:chart.bars :data="[[30, 25, 20], [45, 35, 25]]" />        {{-- arrays stack into tone layers --}}
<mds:chart.bars horizontal :data="[100, 68, 42, 24]" :labels="['بازدید', 'سبد خرید', 'پرداخت', 'خرید']" />
<mds:chart.donut :data="['موبایل' => 45, 'کتاب' => 30, 'دیگر' => 25]" value="100%" />
<mds:chart.gauge :value="84" label="هدف محقق شد" />
<mds:chart.bullet :items="[['label' => 'ارسال به‌موقع', 'value' => 82, 'target' => 75]]" />
<mds:chart.radar :data="['سرعت' => 90, 'کیفیت' => 75, 'قیمت' => 85]" />
<mds:chart.heatmap :data="$dailyOrders" :labels="['تیر', 'مرداد', 'شهریور']" color="accent" />
<mds:chart.sparkline :data="[30, 45, 35, 60, 85]" area class="h-16" />  {{-- bare svg, KPI recipe --}}
```

Every chart is one ink: the stage renders in `currentColor` at fixed opacity steps, so `class="text-accent"` recolors a whole chart, and dark mode needs nothing. Lines are monotone splines (smooth, never overshooting the data), bars are pills, donut and gauge segments end in round caps, axis ticks land on "nice" numbers — with Persian digits per `fa`, like everything else in the kit. The SVG plot plane is always LTR (the color-picker precedent); the HTML stages — bullet, heatmap, and horizontal funnel bars — follow the page direction, so funnels grow rightward in RTL and the heatmap's weeks read like the calendar.

Props — `chart`: `label`, `badge`, `value`, `unit`, `delta` (leading `-` turns it red), `footer-start` / `footer-end`, `fa` (inherited by the stages inside); `chart.line`: `data`, `labels`, `baseline` (dashed twin series), `area`, `dots`, `curve` (`smooth` | `straight`), `axis`, `max`, `width` / `height`; `chart.bars`: `data` (numbers, or arrays that stack), `secondary`, `labels`, `horizontal`, `axis`, `max`; `chart.donut`: `data` (label ⇒ value), `value` / `label` (center), `legend`, `size`, `thickness`; `chart.gauge`: `value`, `max`, `label`, `decimals`; `chart.radar`: `data`, `max`; `chart.bullet`: `items`, `max`, `unit`; `chart.heatmap`: `data`, `rows`, `labels`, `color` (`accent`), `unit`, `callout`; `chart.sparkline`: `data`, `area`, `curve`, `width` / `height`.

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
