# CLAUDE.md

Majid DS (`mahdimajidzadeh/ds`) — an RTL/Persian-first UI kit for Laravel Livewire,
layered **on top of** Flux UI (free tier), never replacing it. It adds an `<mds:*>`
Blade namespace next to `<flux:*>`: Persian digits/money, Jalali dates, Hugeicons,
e-commerce components, and open versions of Flux Pro-only components (command,
composer, color-picker, file-upload, timeline, chart).

**[llms.txt](llms.txt) is the complete API reference — read it first.** This file
only covers what llms.txt doesn't: how to work on the package itself.

## Commands

```bash
composer check                            # lint + analyse + test, in that order
composer test                             # full suite (~3s, keep it green)
composer fix                              # apply Pint formatting (composer lint just checks)
composer analyse                          # PHPStan/Larastan, level 7 over src/
vendor/bin/phpunit --filter composer      # one component's tests
npm run docs                              # regenerate the 74 reference pages + docs/assets/site.css
npm run pages                             # regenerate the 18 static layout-gallery pages in docs/demo/
npm run demo:serve                        # live workbench at :8720 (needs demo:css once)
```

Pint runs the `laravel` preset and skips Blade views. `src/MdsTagCompiler.php`
is excluded from it on purpose: the file is a deliberate verbatim fork of
Flux's own compiler, and staying byte-comparable with upstream is what makes
a re-sync diff readable. `TagCompilerTest` enforces that — it tokenises both
copies and fails the moment Flux's three `compile*Tags()` methods stop
matching ours, so an upstream syntax change surfaces as a failing test rather
than as `<mds:*>` tags silently reaching the browser as text. The same
reasoning is why PHPStan sits at level 7 — level 8 only flags
`preg_replace_callback`'s nullable return in that mirrored file, which
upstream Flux has too.

`phpunit.xml.dist` is the committed test config and is strict on purpose —
skipped, risky, output, and any deprecation/notice/warning raised from `src/`
all fail the run (vendor noise is scoped out by `<source>`). A `markTestSkipped`
is therefore never a quiet fallback: fix the environment or fix the test. Drop
a local `phpunit.xml` beside it to override; it is gitignored.

`docs/` is a **committed** static site (GitHub Pages serves it as-is, no CI build).
Any change to a component's view, CSS, or docs content is not done until
`npm run docs` and (if the demo shows it) `npm run pages` have been re-run —
and until `llms.txt` describes it. That file is the API contract agents
generate code against, and `LlmsTxtTest` fails the build when a prop, helper,
config key or directive exists in code but not there (see step 5 below). It
checks names, not prose: when behaviour changes — keyboard handling, what an
`error` renders, an ARIA role — update the sentence too.

## Map

| Path | What |
|---|---|
| `src/MajidDsServiceProvider.php` | directives (`@fa`, `@toman`, `@jalali`, `@mdsNonce`), component path, publishing — no font, the app owns typography |
| `src/MdsTagCompiler.php` | compiles `<mds:x>` → Blade components, mirrors Flux's own compiler — rarely touched |
| `src/Support/{Persian,Jalali,Icons,Charts,Iran}.php` | dependency-free helpers; `Icons::ALIASES`/`OVERRIDES` map heroicon names to Hugeicons; `Charts` is the SVG geometry behind `mds:chart.*`; `Iran` checks and normalizes national IDs, mobiles, Sheba and card numbers |
| `src/Rules/` | Laravel validation rules over `Iran` (`NationalId`, `IranMobile`, `Sheba`, `BankCard`), bilingual messages by `mds.persian_digits` |
| `resources/views/mds/` | the components — anonymous Blade views, subcomponents in subdirs (`command/item.blade.php` = `<mds:command.item>`) |
| `resources/css/mds.css` | the kit's CSS layer; component rules MUST stay in `@layer components` (unlayered rules beat Tailwind utilities) |
| `bin/docs/` | docs content as PHP arrays, one file per group (`mds.php`, `flux.php`, `nav.php`…); `bin/build-docs.php` renders each snippet through real Blade |
| `workbench/` | the bilingual demo app (testbench); `bin/build-pages.php` crawls its layout gallery into `docs/demo/`, and `demo/cards.blade.php` (the showcase content) is rendered into the docs' Demo pages by `build-docs.php` |
| `tests/Feature/ComponentsTest.php` | render-a-Blade-string, assert-substrings tests |

## Component conventions (follow the neighbors)

- `@props([...])` with kebab-case attrs → camelCase vars (`max-rows` → `$maxRows`).
  Flux's colon syntax (`label:sr-only`) can't be a PHP variable — read it off
  `$attributes->has()` then `except()` it out (see `composer/index.blade.php`).
- `wire:model` is forwarded to the real control (`<input>`/`<textarea>`);
  everything else lands on the wrapper: `$attributes->whereDoesntStartWith('wire:model')`.
- `$fa ??= config('mds.persian_digits', true)` — every numeric output respects it,
  and so does every built-in string (labels, empty states, aria-labels): Persian
  when on, English when off. A new hardcoded string must ship both languages,
  and both belong in the `microcopy()` table in `ComponentsTest` — one row feeds
  the Persian and the English sweep, so neither language can fall behind.
- Validation: explicit `error` prop wins, else fall back to `$errors->first($name)`.
  Don't use `flux:error` (it needs the session bag); copy its markup instead.
- RTL-first via logical properties (`ms-`, `end-`, `border-inline-start`) — never
  `ml-`/`left-`. Dark mode via `dark:` variants. Accent via `--color-accent` tokens.
- Root elements carry `data-mds-<name>` attributes — tests and user CSS hook on them.
- Interactivity is Alpine, inlined in an `@once <script @mdsNonce>` block registering
  `Alpine.data('mdsX', ...)`. Hidden inputs hold Latin-digit machine values and
  dispatch bubbling `input` events; Persian rendering is display-only. Every
  `<script>` tag takes `@mdsNonce` — `CspNonceTest` fails the build when one is bare.
- Icons go through `<mds:icon>`. A heroicon name used anywhere in the kit must
  resolve: add it to `Icons::ALIASES` if Hugeicons lacks the name — `IconsTest`
  fails the build if an alias target doesn't exist in the bundled free set.

## The docs/demo pipeline

- One CSS input (`workbench/resources/css/site-input.css`) compiles to both
  `docs/assets/site.css` and `workbench/public/demo.css` — a component CSS change
  can never ship to docs but not demo. Docs chrome is scoped under `html.docs`.
- Tailwind scans the **generated** docs pages for utilities, so a class used only
  in a new docs example appears in site.css after the next `npm run docs`.
- Demo i18n: the Persian string in the Blade **is** the translation key;
  `workbench/lang/en.json` maps it to English. Every new `__('...')` needs an
  entry, and keys are global — check for collisions first (`ارسال` already means
  "Shipping", which is why send buttons use `ارسال پیام`).
- `bin/docs/nav.php` must list every page and every page must be in the nav —
  the builder hard-fails on missing pages and warns on orphans. The nav ships
  as data (`docs/assets/nav.js`, a JS-wrapped JSON because `fetch()` is blocked
  on `file://`) and pages render the sidebar client-side, so a nav change
  rewrites one asset, not all 74 pages.
- Rebuilds are byte-identical: both builders pin the clock (`Date::setTestNow`
  in `build-docs.php`; the `MDS_TEST_NOW` env var for the crawled demo server)
  and the crawler pins the unused Livewire CSRF token. A rebuild that dirties
  git means something actually changed. The pinned instant is 2026-08-24 10:00,
  so countdown previews read as expired past it — static pages always did.

## Adding a component (the full checklist)

1. View(s) in `resources/views/mds/<name>/` (or a single `<name>.blade.php`).
2. Tests in `tests/Feature/ComponentsTest.php` — render + assert. A control
   that takes `wire:model` goes through `assertBindingReachesControl()`, which
   checks both halves of the contract at once: the binding matches the real
   control's own tag, and appears exactly once, so a wrapper that quietly
   keeps a copy fails. Cover the error-bag fallback too.
3. Docs page in `bin/docs/mds.php` + nav entry in `bin/docs/nav.php`
   (sections use `rtl => true` for Persian previews, `align => 'stretch'` for
   full-width ones; `related` cross-links both ways).
4. Demo card in `workbench/resources/views/demo/cards.blade.php` + its navbar
   ToC item + `en.json` keys (the partial is shared: the workbench `/demo` page
   includes it, and the docs' Demo / RTL demo pages embed it at build time).
5. `llms.txt` section (props · slots · behavior · Livewire contract) and a
   README section; bump the page/component counts in README and
   `bin/docs/guides.php` if it's a Pro alternative. `LlmsTxtTest` enforces the
   llms.txt half: every `@props` key of every view must appear in that
   component's section, every `### <mds:*>` heading must have a view, every
   public static method on the Support classes and every config key and
   directive must be named — so a new prop, helper or key fails
   `composer check` until llms.txt says so.
6. `npm run docs && npm run pages && vendor/bin/phpunit`.

## Verifying visually

The generated pages run without PHP — open `docs/mds/<name>.html` or
`docs/guides/demo.html` / `docs/guides/rtl-demo.html` straight from disk
(`file://`), screenshot the `.docs-preview` / `.docs-embed` elements. The
reference docs are light-only (no toggle, `color-scheme: light`); to check a
component's dark styles, add the class by hand —
`document.documentElement.classList.add('dark')` — or use the layout-gallery
pages in `docs/demo/` or the live workbench, which keep their own toggle.
Alpine is live on these pages, so keyboard/submit behavior is testable there
too.

## Don'ts

- Don't hand-write Flux markup in docs previews — previews are rendered through
  Blade at build time precisely because Flux computes classes at render time.
- Don't edit anything in `docs/` by hand; it's all generated.
- Don't ship Hugeicons Pro SVGs — only the free Stroke Rounded set is bundled;
  Pro styles come from the app's own licensed export via `config('mds.icons.sets')`.
- Don't add runtime dependencies — Jalali/Persian helpers are dependency-free on purpose.
