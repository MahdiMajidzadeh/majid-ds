<?php

/*
| The guides: the overview page, installation, theming, and the directives and
| helpers that have no component of their own.
*/

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
                'text' => 'Majid DS does not replace Flux — it extends it. Every <code>flux:*</code> component keeps working exactly as it does upstream, and an <code>mds:*</code> namespace adds the pieces Flux does not have: Persian typography and digits, Jalali dates, Toman and Rial prices, and the e-commerce components a Persian storefront needs.',
                'rtl' => true,
                'code' => <<<'BLADE'
                <mds:product-card
                    title="گوشی موبایل سامسونگ مدل Galaxy S25"
                    image="https://picsum.photos/seed/phone/400/400"
                    :amount="42500000"
                    :original="48900000"
                    :rating="4.6"
                    :reviews="342"
                    badge="ارسال امروز"
                    href="#"
                    class="max-w-56"
                />
                BLADE,
            ],
            [
                'name' => 'What this adds on top of Flux',
                'text' => 'Everything below is additive. Nothing here changes how a <code>flux:*</code> component behaves.',
                'code' => <<<'BLADE'
                {{-- Persian typography and digits --}}
                <flux:heading>سفارش‌های من</flux:heading>
                @fa(1405)

                {{-- Money --}}
                <mds:price :amount="2500000" :original="3200000" />

                {{-- Jalali dates --}}
                <mds:jalali-date :date="now()" format="l j F Y" />

                {{-- E-commerce --}}
                <mds:quantity :value="2" :min="1" :max="5" />
                <mds:countdown :until="now()->addHours(7)" :days="false" />
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="w-full max-w-md space-y-4">
                    <flux:heading>سفارش‌های من</flux:heading>
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
                'name' => 'What is documented here',
                'text' => 'The <strong>Components</strong> group covers every free Flux component that ships with this package — 30 of them, each with live previews. The <strong>mds</strong> group covers this kit\'s own components, including open alternatives to five Flux Pro components: Command, Composer, Color picker, File upload and Timeline.',
                'note' => '<p><strong>Flux Pro components are not documented here.</strong> Nineteen of the components on <a href="https://fluxui.dev/components">fluxui.dev</a> — Accordion, Autocomplete, Calendar, Carousel, Chart, Composer, Context, Date picker, Editor, Kanban, Pillbox, Popover, Slider, Tabs, Time picker and the rest — ship no code in the free tier, so there is nothing to preview or reference. Five of them have <code>mds:*</code> replacements — Command, Composer, Color picker, File upload and Timeline; for the others, see Flux\'s own docs.</p>',
            ],
            [
                'name' => 'Try it',
                'text' => 'The demo is the fastest way to see the whole kit at once. It is rendered twice — Persian right-to-left and English left-to-right — from the same Blade.',
                'note' => '<p><a href="demo/demo-en.html">Component gallery</a> · <a href="demo/layouts-en.html">Layout gallery</a> · <a href="demo/demo.html">نمایشگاه اجزا (فارسی)</a></p>',
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
                'text' => 'Set the document direction and load the font. <code>@mdsFonts</code> emits the Vazirmatn <code>&lt;link&gt;</code> tags — self-host them for production traffic from Iran.',
                'code' => <<<'BLADE'
                <html lang="fa" dir="rtl">
                <head>
                    @mdsFonts
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
                ['persian_digits', 'When <code>true</code>, numeric output uses Persian digits. Every component reads this at render time, so flipping it per request switches the whole page.'],
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
                'text' => 'Every component in both libraries ships dark variants. Flux drives them from a <code>dark</code> class on the root element, which is why the kit\'s CSS declares <code>@custom-variant dark (&:where(.dark, .dark *))</code>. The toggle in this page\'s top bar is doing exactly that.',
                'code' => <<<'BLADE'
                <flux:button variant="subtle" icon="moon" x-data x-on:click="$flux.dark = ! $flux.dark" aria-label="Toggle dark mode" />
                BLADE,
            ],
            [
                'name' => 'Typography',
                'text' => 'The kit points Tailwind\'s <code>--font-sans</code> at Vazirmatn, so Persian and Latin text share one family, one scale and one weight ramp. Override it like any other theme token.',
                'code' => <<<'BLADE'
                @theme {
                    --font-sans: 'IRANSansX', 'Vazirmatn', ui-sans-serif, sans-serif;
                }
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
        'sections' => [
            [
                'name' => 'Blade directives',
                'lead' => true,
                'text' => 'For values inside a sentence, where a component would be too much.',
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
                @jalali(now())         {{-- ۱ شهریور ۱۴۰۵ --}}
                BLADE,
                'render' => <<<'BLADE'
                <div dir="rtl" class="space-y-1 text-sm">
                    <div>@toman(2500000)</div>
                    <div>@rial(14500000)</div>
                    <div>@jalali(now())</div>
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

                Jalali::format(now(), 'l j F Y');    // یکشنبه ۱ شهریور ۱۴۰۵
                Jalali::fromGregorian(2026, 8, 23);  // [1405, 6, 1]
                Jalali::toGregorian(1405, 6, 1);     // [2026, 8, 23]
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
                ['@toman', 'Amount plus تومان.'],
                ['@rial', 'Amount plus ریال.'],
                ['@jalali', 'Jalali date: <code>@jalali($date, $format = \'j F Y\')</code>.'],
                ['@mdsFonts', 'The Vazirmatn <code>&lt;link&gt;</code> tags, for <code>&lt;head&gt;</code>.'],
            ]],
            ['name' => 'MajidDs\\Support\\Persian', 'props' => [
                ['digits($value)', 'Latin and Arabic-Indic digits to Persian.'],
                ['latinDigits($value)', 'Persian and Arabic-Indic digits back to Latin.'],
                ['number($value, $decimals = 0)', 'Persian digits with the <code>٬</code> and <code>٫</code> separators.'],
                ['money($amount, $currency = null, $decimals = 0)', 'Formatted amount plus its currency label.'],
                ['currencyLabel($currency)', 'The label for a currency identifier; unknown values pass through as literals.'],
                ['fileSize($bytes, $persianDigits = null)', 'Byte count in Persian units.'],
                ['ago($date)', 'A short relative phrase, past or future.'],
                ['toDateTime($date)', 'Normalises a date-ish value into a <code>DateTimeImmutable</code>.'],
            ]],
            ['name' => 'MajidDs\\Support\\Jalali', 'props' => [
                ['format($date, $format, $persianDigits = null)', 'Formats a date in the Jalali calendar.'],
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
];
