<?php

/*
| The guides: the overview page, installation, theming, the directives and
| helpers that have no component of their own, and the two Demo pages that
| embed the workbench's demo cards whole.
*/

/*
| What the demo cards partial needs from its page — the docs-build twin of the
| View::composer in workbench/routes/web.php, minus everything page-level.
| Pagination links point at '#': on a static page a dead route helps nobody.
*/
$demoVars = fn (bool $fa) => [
    'mdsFa' => $fa,
    'mdsForward' => $fa ? 'arrow-left' : 'arrow-right',
    'mdsUrl' => fn (string $path) => '#',
    'mdsNum' => fn (mixed $value, int $decimals = 0) => $fa
        ? MajidDs\Support\Persian::number($value, $decimals)
        : number_format((float) $value, $decimals),
];

// The cards bring their own anchors; list them so the right rail can follow.
$demoAnchors = [
    'typography' => 'Typography',
    'buttons' => 'Buttons',
    'badges' => 'Badges',
    'avatars' => 'Avatars & icons',
    'forms' => 'Forms',
    'overlays' => 'Overlays & toasts',
    'command' => 'Command palette',
    'color-picker' => 'Color picker',
    'file-upload' => 'File upload',
    'composer' => 'Composer',
    'preview-card' => 'Preview card',
    'timeline' => 'Timeline',
    'chart' => 'Charts',
    'icons' => 'Icons',
    'table' => 'Table & pagination',
    'mds' => 'mds components',
];

$demoEmbed = <<<'BLADE'
<div class="space-y-10" x-data>
    @include('demo.cards')
</div>
<flux:toast />
BLADE;

return [
    // ----------------------------------------------------------------- index
    'index' => [
        'group' => 'guides',
        'title' => 'Majid DS',
        'lede' => 'An RTL / Persian-first UI kit for Laravel Livewire, built on top of Flux UI.',
        'sections' => [
            [
                'name' => 'Introduction',
                'lead' => true,
                'text' => 'Majid DS does not replace Flux — it extends it. Every <code>flux:*</code> component keeps working exactly as it does upstream, and an <code>mds:*</code> namespace adds the pieces Flux does not have: Persian typography and digits, Jalali dates, Toman and Rial prices, and the e-commerce components a storefront needs. Previews in these docs render the way an English app would; one config flag turns the whole kit Persian.',
                'code' => <<<'BLADE'
                <mds:product-card
                    title="Samsung Galaxy S25 256GB smartphone"
                    image="https://picsum.photos/seed/phone/400/400"
                    :amount="42500000"
                    :original="48900000"
                    :rating="4.6"
                    :reviews="342"
                    badge="Ships today"
                    href="#"
                    class="max-w-56"
                />
                BLADE,
            ],
            [
                'name' => 'What this adds on top of Flux',
                'text' => 'Everything below is additive. Nothing here changes how a <code>flux:*</code> component behaves.',
                'code' => <<<'BLADE'
                {{-- Money, with the original price struck through --}}
                <mds:price :amount="2500000" :original="3200000" />

                {{-- Dates in the Jalali calendar --}}
                <mds:jalali-date :date="now()" format="l j F Y" />

                {{-- E-commerce --}}
                <mds:quantity :value="2" :min="1" :max="5" />
                <mds:countdown :until="now()->addHours(7)" :days="false" />
                BLADE,
                'render' => <<<'BLADE'
                <div class="w-full max-w-md space-y-4">
                    <mds:price :amount="2500000" :original="3200000" />
                    <div class="text-sm"><mds:jalali-date :date="now()" format="l j F Y" /></div>
                    <div class="flex items-center gap-4">
                        <mds:quantity :value="2" :min="1" :max="5" />
                        <mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" />
                    </div>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Persian-first when you flip the switch',
                'text' => 'The kit was built RTL and Persian first. <code>config(\'mds.persian_digits\')</code> — on by default, off in these docs — switches digits, separators, unit names and every built-in string across the whole page at once; the <code>fa</code> prop does the same per component. The snippet below opts in explicitly — in a real Persian app the config default already covers every component.',
                'rtl' => true,
                'code' => <<<'BLADE'
                <flux:heading>سفارش‌های من</flux:heading>
                @fa(1405)

                <mds:price :amount="2500000" :original="3200000" :fa="true" />
                <mds:jalali-date :date="now()" format="l j F Y" :fa="true" />
                <mds:countdown :until="now()->addHours(7)" :days="false" :fa="true" />
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="w-full max-w-md space-y-4">
                    <flux:heading>سفارش‌های من</flux:heading>
                    <mds:price :amount="2500000" :original="3200000" :fa="true" />
                    <div class="text-sm"><mds:jalali-date :date="now()" format="l j F Y" :fa="true" /></div>
                    <mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" :fa="true" />
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'What is documented here',
                'text' => 'The <strong>Components</strong> group covers every free Flux component that ships with this package — 32 of them, each with live previews. The <strong>mds</strong> group covers this kit\'s own components, including open alternatives to six Flux Pro components: Command, Composer, Color picker, File upload, Timeline and Chart.',
                'note' => '<p><strong>Flux Pro components are not documented here.</strong> Nineteen of the components on <a href="https://fluxui.dev/components">fluxui.dev</a> — Accordion, Autocomplete, Calendar, Carousel, Chart, Composer, Context, Date picker, Editor, Kanban, Pillbox, Popover, Slider, Tabs, Time picker and the rest — ship no code in the free tier, so there is nothing to preview or reference. Six of them have <code>mds:*</code> replacements — Command, Composer, Color picker, File upload, Timeline and Chart; for the others, see Flux\'s own docs.</p>',
            ],
            [
                'name' => 'Try it',
                'text' => 'The demo is the fastest way to see the whole kit at once. It lives in these docs, rendered twice from the same Blade — English left-to-right, and Persian right-to-left.',
                'note' => '<p><a href="guides/demo.html">Demo</a> · <a href="guides/rtl-demo.html">RTL demo</a> · <a href="demo/layouts-en.html">Layout gallery</a></p>',
            ],
        ],
        'related' => ['installation', 'directives', 'grid'],
    ],

    // ---------------------------------------------------------- installation
    'installation' => [
        'group' => 'guides',
        'title' => 'Installation',
        'lede' => 'Composer, one CSS import, and two lines in your layout.',
        'sections' => [
            [
                'name' => 'Install',
                'lead' => true,
                'text' => 'The service provider is auto-discovered. Livewire 3 and <code>livewire/flux</code> come along as dependencies.',
                'code' => 'composer require mahdimajidzadeh/ds',
                'render' => '<div class="w-full max-w-md rounded-lg border border-zinc-200 px-4 py-3 font-mono text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">composer require mahdimajidzadeh/ds</div>',
                'align' => 'stretch',
            ],
            [
                'name' => 'Stylesheet',
                'text' => 'Import the kit\'s layer after Flux\'s. The <code>@source</code> line is not optional: Tailwind v4 skips gitignored paths, so without it none of the utility classes inside the kit\'s Blade views get generated.',
                'code' => <<<'BLADE'
                /* resources/css/app.css */
                @import 'tailwindcss';
                @import '../../vendor/livewire/flux/dist/flux.css';
                @import '../../vendor/mahdimajidzadeh/ds/resources/css/mds.css';

                @source '../../vendor/mahdimajidzadeh/ds/resources/views';

                @custom-variant dark (&:where(.dark, .dark *));
                BLADE,
                'render' => '<div class="w-full text-xs text-zinc-500">resources/css/app.css</div>',
                'align' => 'stretch',
            ],
            [
                'name' => 'Layout',
                'text' => 'Set the document direction and load your font. The kit ships no font and sets none — the <a href="theming.html#fonts">Fonts</a> section of the theming guide shows how to pick a Persian face and either self-host it or load it from Google Fonts.',
                'code' => <<<'BLADE'
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
                BLADE,
                'render' => '<div class="w-full text-xs text-zinc-500">resources/views/components/layouts/app.blade.php</div>',
                'align' => 'stretch',
                'note' => 'That is it. <code>flux:*</code> and <code>mds:*</code> components now work side by side.',
            ],
            [
                'name' => 'Requirements',
                'text' => 'PHP 8.2+, Laravel 11, 12 or 13, Livewire 3, <code>livewire/flux</code> ^2.0 and Tailwind CSS v4.',
            ],
            [
                'name' => 'Configuration',
                'text' => 'Publish the config to change the default currency, turn Persian digits off globally, or register Pro icon styles.',
                'code' => 'php artisan vendor:publish --tag=mds-config',
                'render' => '<div class="w-full max-w-md rounded-lg border border-zinc-200 px-4 py-3 font-mono text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">php artisan vendor:publish --tag=mds-config</div>',
                'align' => 'stretch',
            ],
        ],
        'reference' => [
            ['name' => 'config/mds.php', 'props' => [
                ['currency', 'Default for <code>mds:price</code> and <code>Persian::money()</code>: <code>toman</code>, <code>rial</code>, <code>none</code>, or a literal label.'],
                ['persian_digits', 'When <code>true</code> (the default), output is Persian: digits, separators, and every built-in string — unit labels, empty states, ARIA labels. When <code>false</code>, all of it is English. Every component reads this at render time, so flipping it per request switches the whole page.'],
                ['icons.default', '<code>hugeicons</code> or <code>flux</code> to go back to heroicons.'],
                ['icons.style', 'Hugeicons style used when a component does not ask for one.'],
                ['icons.fallback_style', 'Falls back to Stroke Rounded when a Pro style is not registered.'],
                ['icons.sets', 'Pro style name → directory of SVGs from your own licence.'],
            ]],
            ['name' => 'Publishable tags', 'props' => [
                ['mds-config', 'The config file.'],
                ['mds-assets', 'The CSS layer, into <code>resources/css/vendor/mds.css</code>.'],
                ['mds-views', 'Every component view, into <code>resources/views/mds</code>, for customising.'],
            ]],
        ],
        'related' => ['theming', 'directives'],
    ],

    // --------------------------------------------------------------- theming
    'theming' => [
        'group' => 'guides',
        'title' => 'Theming',
        'lede' => 'One accent token, and both libraries follow.',
        'sections' => [
            [
                'name' => 'Accent color',
                'lead' => true,
                'text' => 'Every <code>mds:*</code> component uses Flux\'s accent tokens, so setting <code>--color-accent</code> once themes both libraries. Nothing in this kit hardcodes a brand color.',
                'code' => <<<'BLADE'
                /* resources/css/app.css */
                @theme {
                    --color-accent: var(--color-rose-600);
                    --color-accent-content: var(--color-rose-600);
                    --color-accent-foreground: var(--color-white);
                }
                BLADE,
                'render' => <<<'BLADE'
                <div class="flex flex-wrap items-center gap-3">
                    <flux:button variant="primary">Primary</flux:button>
                    <flux:badge color="lime">Badge</flux:badge>
                    <mds:discount-badge :percent="25" />
                    <flux:heading accent>Accent heading</flux:heading>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Dark mode',
                'text' => 'Every component in both libraries ships dark variants. Flux drives them from a <code>dark</code> class on the root element, which is why the <a href="installation.html">Installation stylesheet</a> has your <code>app.css</code> declare <code>@custom-variant dark (&:where(.dark, .dark *))</code>. A toggle is one Alpine expression. These reference pages themselves are light-only — see the dark styles in the <a href="rtl-demo.html">demo</a> or the layout gallery, which keep their own toggle.',
                'code' => <<<'BLADE'
                <flux:button variant="subtle" icon="moon" x-data x-on:click="$flux.dark = ! $flux.dark" aria-label="Toggle dark mode" />
                BLADE,
                'render' => '<div class="w-full text-xs text-zinc-500">Wire this into your own layout — these light-only docs would render it inertly.</div>',
                'align' => 'stretch',
            ],
            [
                'name' => 'Fonts',
                'text' => 'The kit ships no font and sets none — typography belongs to your app. Every <code>mds:*</code> component renders in Tailwind\'s <code>font-sans</code>, so this one token styles the whole kit. Point it at a Persian face in your <code>app.css</code> after the kit\'s import; Vazirmatn is the recommended default because Persian and Latin share one family and one scale. Then load the face: <strong>self-host</strong> it (put the <code>woff2</code> files under <code>public/fonts</code> and declare an <code>@font-face</code> — the safe choice for traffic from Iran, where Google Fonts is slow or blocked), or add the Google Fonts <code>&lt;link&gt;</code> to <code>&lt;head&gt;</code>. Any other Persian face works the same way. This documentation loads Vazirmatn for itself; your app decides for itself.',
                'code' => <<<'BLADE'
                /* resources/css/app.css, after the kit's import */
                @theme {
                    --font-sans: 'Vazirmatn', ui-sans-serif, system-ui, sans-serif;
                }

                /* self-hosted (recommended for Iran) … */
                @font-face {
                    font-family: 'Vazirmatn';
                    src: url('/fonts/Vazirmatn[wght].woff2') format('woff2');
                    font-weight: 100 900;
                    font-display: swap;
                }

                /* … or Google Fonts, in <head>:
                <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet"> */
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="space-y-1">
                    <flux:heading size="xl">وزیرمتن</flux:heading>
                    <flux:text>متن بدنه با اعداد فارسی: ۱۴۰۵</flux:text>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Customising a component',
                'text' => 'Publish the views and edit them. Published views win over the package\'s, so you can change one component without forking anything.',
                'code' => 'php artisan vendor:publish --tag=mds-views',
                'render' => '<div class="w-full max-w-md rounded-lg border border-zinc-200 px-4 py-3 font-mono text-xs text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">php artisan vendor:publish --tag=mds-views</div>',
                'align' => 'stretch',
            ],
        ],
        'related' => ['installation', 'directives'],
    ],

    // ------------------------------------------------------------ directives
    'directives' => [
        'group' => 'guides',
        'title' => 'Directives & helpers',
        'lede' => 'The Blade directives and PHP helpers behind the components.',
        // The one page that renders Persian output on purpose: the digit and
        // money directives are Persian by definition, and @jalali's preview
        // should match the Persian comments beside it.
        'env' => ['digits' => true, 'currency' => 'toman'],
        'sections' => [
            [
                'name' => 'Blade directives',
                'lead' => true,
                'text' => 'For values inside a sentence, where a component would be too much. The digit and money directives (<code>@fa</code>, <code>@faNum</code>, <code>@toman</code>, <code>@rial</code>) are Persian by definition — <code>@toman</code> always says تومان; <code>@jalali</code> follows <code>config(\'mds.persian_digits\')</code> like the components do. This page\'s examples read right-to-left, the way these directives are used.',
                'rtl' => true,
                'code' => <<<'BLADE'
                <flux:text>
                    قیمت این کالا @toman(2500000) است و در
                    @jalali('2026-08-20') ثبت شده. کد پیگیری: @fa(140529)
                </flux:text>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Numbers',
                'code' => <<<'BLADE'
                @fa(1405)              {{-- ۱۴۰۵ --}}
                @faNum(2500000)        {{-- ۲٬۵۰۰٬۰۰۰ --}}
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="space-y-1 text-sm">
                    <div>@fa(1405)</div>
                    <div>@faNum(2500000)</div>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Money and dates',
                'code' => <<<'BLADE'
                @toman(2500000)        {{-- ۲٬۵۰۰٬۰۰۰ تومان --}}
                @rial(14500000)        {{-- ۱۴٬۵۰۰٬۰۰۰ ریال --}}
                @jalali('2026-08-20')  {{-- ۲۹ مرداد ۱۴۰۵ --}}
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="space-y-1 text-sm">
                    <div>@toman(2500000)</div>
                    <div>@rial(14500000)</div>
                    <div>@jalali('2026-08-20')</div>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'PHP helpers',
                'text' => 'The same conversions, for use outside a view — in a model accessor, a job, an export.',
                'code' => <<<'BLADE'
                use MajidDs\Support\Persian;
                use MajidDs\Support\Jalali;

                Persian::digits(1405);              // ۱۴۰۵
                Persian::latinDigits('۱۴۰۵');       // 1405
                Persian::number(2500000);           // ۲٬۵۰۰٬۰۰۰
                Persian::money(2500000);            // ۲٬۵۰۰٬۰۰۰ تومان
                Persian::fileSize(162400);          // ۱۵۹ کیلوبایت
                Persian::ago(now()->subHours(3));   // ۳ ساعت پیش

                Jalali::format('2026-08-20', 'l j F Y');  // پنجشنبه ۲۹ مرداد ۱۴۰۵
                Jalali::fromGregorian(2026, 8, 23);       // [1405, 6, 1]
                Jalali::toGregorian(1405, 6, 1);          // [2026, 8, 23]

                // Both helpers can speak English too — Latin digits,
                // transliterated month names, English units:
                Persian::fileSize(162400, false);            // 159 KB
                Persian::ago(now()->subHours(3), false);     // 3 hours ago
                Jalali::format('2026-08-20', 'j F Y', false); // 29 Mordad 1405
                BLADE,
                'render' => '<div class="w-full text-xs text-zinc-500">MajidDs\\Support\\Persian and MajidDs\\Support\\Jalali</div>',
                'align' => 'stretch',
            ],
            [
                'name' => 'Latin digits when you need them',
                'text' => 'Persian digits are display-only. Anything that has to survive a round trip — a form value, a URL, a database column — should hold Latin digits. <code>Persian::latinDigits()</code> is the way back, and components like <code>mds:quantity</code> already keep their hidden input in Latin for exactly this reason.',
            ],
        ],
        'reference' => [
            ['name' => 'Directives', 'props' => [
                ['@fa', 'Persian digits: <code>@fa(1405)</code>.'],
                ['@faNum', 'Persian digits with the ٬ thousands separator.'],
                ['@toman', 'Amount plus تومان. Always Persian.'],
                ['@rial', 'Amount plus ریال. Always Persian.'],
                ['@jalali', 'Jalali date: <code>@jalali($date, $format = \'j F Y\')</code>. Follows <code>mds.persian_digits</code> — Persian names and digits when on, <code>29 Mordad 1405</code> when off.'],
            ]],
            ['name' => 'MajidDs\\Support\\Persian', 'props' => [
                ['digits($value)', 'Latin and Arabic-Indic digits to Persian.'],
                ['latinDigits($value)', 'Persian and Arabic-Indic digits back to Latin.'],
                ['number($value, $decimals = 0)', 'Persian digits with the <code>٬</code> and <code>٫</code> separators.'],
                ['money($amount, $currency = null, $decimals = 0)', 'Formatted amount plus its currency label. Always Persian — it backs <code>@toman</code>; use <code>mds:price</code> for config-aware output.'],
                ['currencyLabel($currency, $persian = null)', 'The label for a currency identifier — تومان/ریال, or <code>Toman</code>/<code>Rial</code> when Persian output is off; unknown values pass through as literals.'],
                ['fileSize($bytes, $persianDigits = null)', 'Byte count: <code>۱۵۹ کیلوبایت</code>, or <code>159 KB</code> when Persian output is off.'],
                ['ago($date, $persian = null)', 'A short relative phrase, past or future — <code>۳ ساعت پیش</code> or <code>3 hours ago</code>.'],
                ['toDateTime($date)', 'Normalises a date-ish value into a <code>DateTimeImmutable</code>.'],
            ]],
            ['name' => 'MajidDs\\Support\\Jalali', 'props' => [
                ['format($date, $format, $persianDigits = null)', 'Formats a date in the Jalali calendar — Persian names and digits, or Latin digits with transliterated names (<code>29 Mordad 1405</code>) when Persian output is off.'],
                ['fromGregorian($y, $m, $d)', 'Gregorian to Jalali, as <code>[y, m, d]</code>.'],
                ['toGregorian($y, $m, $d)', 'Jalali to Gregorian, as <code>[y, m, d]</code>.'],
                ['isLeapYear($year)', 'Whether a Jalali year is a leap year.'],
            ]],
        ],
        'related' => ['price', 'jalali-date', 'installation'],
    ],

    // ------------------------------------------------------------- ai-agents
    'ai-agents' => [
        'group' => 'guides',
        'title' => 'AI agents',
        'lede' => 'Point your coding agent at the kit so it uses the components correctly.',
        'sections' => [
            [
                'name' => 'llms.txt',
                'lead' => true,
                'text' => 'The package ships a machine-oriented version of this reference: the same API surface in compact markdown, with no HTML around it. Point your project\'s <code>CLAUDE.md</code> or <code>AGENTS.md</code> at it and an agent will reach for <code>mds:price</code> instead of hand-rolling a Toman formatter.',
                'code' => <<<'BLADE'
                # CLAUDE.md / AGENTS.md

                ## UI components

                This project uses Majid DS on top of Flux UI. Before writing UI,
                read `vendor/mahdimajidzadeh/ds/llms.txt` — it lists every
                `mds:*` component and its props.

                Rules:
                - Use `mds:price` for money, never a hand-rolled formatter.
                - Use `mds:jalali-date` for dates; do not convert calendars by hand.
                - Persian digits are display-only: keep stored values in Latin.
                BLADE,
                'render' => '<div class="w-full text-xs text-zinc-500">CLAUDE.md</div>',
                'align' => 'stretch',
            ],
            [
                'name' => 'Why it helps',
                'text' => 'Without it, agents reliably reinvent three things this kit already does: Toman formatting, Jalali conversion, and Persian digit handling. All three have edge cases — the <code>٬</code> separator is not a comma, Jalali leap years are not on a four-year cycle, and a Persian digit in a database column is a bug rather than a feature.',
            ],
        ],
        'related' => ['directives', 'index'],
    ],

    // ------------------------------------------------------------------ demo
    'demo' => [
        'group' => 'guides',
        'title' => 'Demo',
        'lede' => 'Every component on one page — the free Flux UI set plus the whole mds layer, in English.',
        'env' => ['locale' => 'en', 'digits' => false, 'currency' => 'Toman'],
        'sections' => [
            [
                'name' => 'Showcase',
                'lead' => true,
                'text' => 'This is the workbench demo, rendered through Blade at build time like every other preview in these docs. Everything on it is live: open the modals, press <kbd>⌘K</kbd>, type in the composer. For the kit in the language it is designed for, see the <a href="rtl-demo.html">RTL demo</a>.',
                'embed' => $demoEmbed,
                'with' => $demoVars(false),
                'anchors' => $demoAnchors,
            ],
        ],
        'related' => ['rtl-demo', 'index'],
    ],

    // -------------------------------------------------------------- rtl-demo
    'rtl-demo' => [
        'group' => 'guides',
        'title' => 'RTL demo',
        'lede' => 'The same showcase right-to-left and in Persian — digits, dates and currency the way a Persian storefront ships them.',
        'env' => ['locale' => 'fa', 'digits' => true, 'currency' => 'toman'],
        'sections' => [
            [
                'name' => 'Showcase',
                'lead' => true,
                'text' => 'The Persian original of the <a href="demo.html">demo</a>: the direction flips to RTL, digits and separators turn Persian, dates go Jalali — all from <code>config()</code> and logical CSS properties, with not one component call changed.',
                'embed' => $demoEmbed,
                'with' => $demoVars(true),
                'rtl' => true,
                'anchors' => $demoAnchors,
            ],
        ],
        'related' => ['demo', 'index'],
    ],
];
