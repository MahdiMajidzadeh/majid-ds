<?php

/*
| The mds:* layer — everything this package adds on top of Flux.
|
| The kit is RTL/Persian-first, but these reference pages render with
| config('mds.persian_digits') off and config('mds.currency') set to the
| literal "Toman" — what the kit produces in an app configured for English:
| Latin digits and English built-in microcopy. Each page closes with one
| Persian section showing the same components as a Persian app renders them.
*/

$pages = [];

// ------------------------------------------------------------------ mds:icon

$pages['mds-icon'] = [
    'group' => 'mds',
    'title' => 'mds:icon',
    'lede' => 'Hugeicons, replacing heroicons across every mds component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The free Stroke Rounded set (6,200 icons) ships with the package. Names come straight from <a href="https://hugeicons.com">hugeicons.com</a>.',
            'code' => <<<'BLADE'
            <mds:icon icon="shopping-cart-01" />
            <mds:icon icon="truck-delivery" />
            <mds:icon icon="discount-tag-01" />
            <mds:icon icon="wallet-01" />
            <mds:icon icon="store-01" />
            BLADE,
        ],
        [
            'name' => 'heroicon names still work',
            'text' => 'An alias map means you can keep writing heroicon names and get the Hugeicons equivalent, so switching an existing app over is a one-line config change rather than a find-and-replace.',
            'code' => <<<'BLADE'
            <mds:icon icon="magnifying-glass" />
            <mds:icon icon="shopping-bag" />
            <mds:icon icon="credit-card" />
            <mds:icon icon="exclamation-triangle" />
            BLADE,
        ],
        [
            'name' => 'Size and color',
            'text' => 'Size with Tailwind classes; the glyph inherits <code>currentColor</code>.',
            'code' => <<<'BLADE'
            <div class="flex items-end gap-3 text-zinc-600 dark:text-zinc-300">
                <mds:icon icon="package" class="size-4" />
                <mds:icon icon="package" class="size-6" />
                <mds:icon icon="package" class="size-8" />
                <mds:icon icon="package" class="size-10 text-accent" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Stroke width',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3 text-zinc-600 dark:text-zinc-300">
                <mds:icon icon="notification-01" class="size-8" :stroke="1" />
                <mds:icon icon="notification-01" class="size-8" />
                <mds:icon icon="notification-01" class="size-8" :stroke="2" />
                <mds:icon icon="notification-01" class="size-8" :stroke="3" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Accessible label',
            'text' => 'An icon carrying meaning on its own needs a label; a decorative one should stay unlabelled so screen readers skip it.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3">
                <mds:icon icon="checkmark-circle-02" class="size-8 text-green-500" label="Confirmed" />
                <mds:icon icon="alert-02" class="size-8 text-amber-500" label="Warning" />
                <mds:icon icon="cancel-circle" class="size-8 text-red-500" label="Error" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Pro styles',
            'text' => 'Only Stroke Rounded is free. For the other eight styles, register your own licensed export — this package ships no Pro files.',
            'code' => <<<'BLADE'
            // config/mds.php
            'icons' => [
                'default' => 'hugeicons',
                'style' => 'stroke-rounded',
                'sets' => [
                    'solid-rounded' => resource_path('svg/hugeicons/solid-rounded'),
                ],
            ],
            BLADE,
            'render' => '<div class="text-xs text-zinc-500">config/mds.php</div>',
            'note' => 'Set <code>config(\'mds.icons.default\')</code> to <code>flux</code> to go back to heroicons everywhere.',
        ],
        [
            'name' => 'Strict mode (no heroicons fallback)',
            'text' => 'By default, a name neither Hugeicons nor the alias map covers still renders — as a heroicon, via <code>flux:icon</code>. If a page only ever calls <code>mds:icon</code> and you want a hard guarantee that heroicons never render, set <code>strict</code> instead of relying on every call site using a name Hugeicons actually has.',
            'code' => <<<'BLADE'
            // config/mds.php
            'icons' => [
                'strict' => true,
            ],
            BLADE,
            'render' => '<div class="text-xs text-zinc-500">config/mds.php</div>',
            'note' => 'An unmapped name then renders nothing instead of falling back to <code>flux:icon</code>.',
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'An icon has no digits or text of its own, so nothing here reads <code>config(\'mds.persian_digits\')</code> and the glyph never mirrors. On an RTL page only the accessible label needs translating.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3">
                <mds:icon icon="checkmark-circle-02" class="size-8 text-green-500" label="تأیید شد" />
                <mds:icon icon="alert-02" class="size-8 text-amber-500" label="هشدار" />
                <mds:icon icon="truck-delivery" class="size-8 text-zinc-500" label="در حال ارسال" />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:icon', 'props' => [
            ['icon', 'Hugeicons name, or a heroicon name via the alias map.'],
            ['variant', 'Hugeicons style. Falls back to Stroke Rounded when a Pro style is not registered.'],
            ['stroke', 'Stroke width. Default: <code>1.5</code>.'],
            ['label', 'Accessible label. Without it the icon is <code>aria-hidden</code>.'],
        ]],
    ],
    'related' => ['icon'],
];

// ----------------------------------------------------------------- mds:input

$pages['mds-input'] = [
    'group' => 'mds',
    'title' => 'mds:input',
    'lede' => 'Flux\'s input, storing Latin digits whatever keyboard typed them.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Persian keyboards type <code>۰۱۲۳</code> and Arabic ones <code>٠١٢٣</code>; bound to a plain input, that is what <code>wire:model</code> posts. <code>mds:input</code> is <code>flux:input</code> with the <code>x-mds-digits</code> directive on the control: digits are rewritten to Latin as they are typed, pasted or dropped, so the value the server receives is always the machine form. Every <code>flux:input</code> prop and attribute passes straight through.',
            'code' => <<<'BLADE'
            <mds:input label="Mobile number" placeholder="Try typing ۰۹۱۲ here" />
            BLADE,
        ],
        [
            'name' => 'Digits only',
            'text' => 'With <code>only</code> everything that is not a digit is dropped too — phone numbers, verification codes, card numbers — and mobile keyboards are asked for a numeric layout through <code>inputmode="numeric"</code>. A caller\'s own <code>inputmode</code> wins.',
            'code' => <<<'BLADE'
            <mds:input only label="Verification code" placeholder="12345" maxlength="5" class="w-40" />
            BLADE,
        ],
        [
            'name' => 'Everything Flux\'s input does',
            'text' => 'Label, description, icons, <code>clearable</code>, <code>copyable</code>, <code>viewable</code>, <code>mask</code>, <code>invalid</code> and the validation state read from the error bag are all Flux\'s own. Only the digit handling is added. A <code>mask</code> decides the shape of the value itself, so leave <code>only</code> off when you use one — the digits still arrive Latin.',
            'code' => <<<'BLADE'
            <div class="grid gap-6 sm:grid-cols-2">
                <mds:input only label="Card number" icon="credit-card" clearable description="16 digits, no spaces." />
                <mds:input label="Postal code" mask="99999-99999" placeholder="12345-67890" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Iranian formats',
            'align' => 'stretch',
            'text' => 'Four presets for the numbers every Persian checkout asks for. <code>mds:input.mobile</code> and <code>mds:input.national-id</code> are digits-only fields with the right length, keyboard and autofill hints. <code>mds:input.card</code> and <code>mds:input.sheba</code> group the digits the way they read on the card and the bank letter, through Flux\'s mask — so their bound value carries the spaces (and Sheba\'s <code>IR</code>), and the matching rule ignores them. Every preset is a default the caller\'s own attribute overrides.',
            'code' => <<<'BLADE'
            <div class="grid gap-6 sm:grid-cols-2">
                <mds:input.mobile label="Mobile number" />
                <mds:input.national-id label="National ID" />
                <mds:input.card label="Card number" />
                <mds:input.sheba label="Sheba (IBAN)" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Validating on the server',
            'text' => 'The checks the issuing authorities define — the national ID\'s mod-11 check digit, the mobile prefixes, the IBAN mod-97 check, Luhn for cards — ship as validation rules under <code>MajidDs\Rules</code>, with a Persian or English message by <code>config(\'mds.persian_digits\')</code> and an optional custom one. Each accepts Persian digits and the spaced forms the inputs produce; the <code>MajidDs\Support\Iran</code> helpers behind them return the canonical value to store.',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label>Mobile number</flux:label>
                <mds:input.mobile name="mobile" value="0912345678" />
                <flux:error name="mobile" />
            </flux:field>
            BLADE,
            'render' => <<<'BLADE'
            <flux:field>
                <flux:label>Mobile number</flux:label>
                <mds:input.mobile name="mobile" value="0912345678" invalid />
                <flux:error message="The mobile number is not a valid mobile number." />
            </flux:field>
            BLADE,
            'note' => '<pre><code>use MajidDs\Rules\{IranMobile, NationalId, BankCard, Sheba};
use MajidDs\Support\Iran;

$request->validate([
    \'mobile\'      => [\'required\', new IranMobile],
    \'national_id\' => [\'required\', new NationalId],
    \'card\'        => [\'nullable\', new BankCard],
    \'sheba\'       => [\'nullable\', new Sheba(\'Please check the Sheba number.\')],
]);

$user->mobile = Iran::normalizeMobile($request->mobile);   // 09123456789
$user->card   = Iran::normalizeBankCard($request->card);   // 16 digits, no spaces
$user->sheba  = Iran::normalizeSheba($request->sheba);     // IR + 24 digits</code></pre>',
        ],
        [
            'name' => 'With Livewire',
            'text' => 'Because the digits are rewritten <em>before</em> the browser applies the keystroke, the field fires a single <code>input</code> event, already Latin — a <code>wire:model.live</code> request never carries a Persian digit. Text committed through an on-screen keyboard\'s composition step cannot be intercepted that early; a second pass normalises it on <code>input</code> and re-announces the value.',
            'code' => <<<'BLADE'
            <mds:input only wire:model="mobile" label="Mobile number" type="tel" />
            BLADE,
        ],
        [
            'name' => 'Inside an RTL form',
            'rtl' => true,
            'text' => 'Numbers read left-to-right even on a right-to-left page. <code>ltr</code> marks the control so the kit\'s stylesheet keeps it LTR and end-aligned inside an RTL form — the same rule that already applies to <code>type="tel"</code>. The field shows Latin digits by design: what is on screen is exactly what will be posted.',
            'code' => <<<'BLADE'
            <div class="grid gap-6 sm:grid-cols-2">
                <mds:input only ltr label="شماره موبایل" placeholder="۰۹۱۲ ۳۴۵ ۶۷۸۹" description="با هر صفحه‌کلیدی تایپ کنید؛ مقدار لاتین ذخیره می‌شود." />
                <mds:input label="آدرس" placeholder="خیابان ولیعصر، پلاک ۱۲" />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:input', 'props' => [
            ['only', 'Keep digits alone and ask for a numeric keyboard (<code>inputmode="numeric"</code>). Default: <code>false</code>.'],
            ['ltr', 'Mark the control <code>data-ltr</code> so it stays left-to-right inside an RTL form. Default: <code>false</code>.'],
            ['…', 'Every <code>flux:input</code> prop and attribute — <code>label</code>, <code>description</code>, <code>placeholder</code>, <code>icon</code>, <code>clearable</code>, <code>copyable</code>, <code>viewable</code>, <code>mask</code>, <code>type</code>, <code>invalid</code>, <code>wire:model</code>, <code>name</code> — passes through unchanged.'],
        ]],
        ['name' => 'mds:input.mobile · mds:input.national-id · mds:input.card · mds:input.sheba', 'props' => [
            ['mobile', '<code>only ltr type="tel" maxlength="14" autocomplete="tel-national"</code>. Value: the digits — 09…, or a pasted 98…/0098… form the rule accepts and <code>Iran::normalizeMobile()</code> reduces. Rule: <code>IranMobile</code>.'],
            ['national-id', '<code>only ltr maxlength="10" autocomplete="off"</code>. Value: the ten digits. Rule: <code>NationalId</code>.'],
            ['card', '<code>ltr mask="9999 9999 9999 9999" autocomplete="cc-number" icon="credit-card"</code>. Value: the grouped digits. Rule: <code>BankCard</code>; store <code>Iran::normalizeBankCard()</code>.'],
            ['sheba', '<code>ltr mask="IR99 9999 9999 9999 9999 9999 99"</code>. Value: the grouped form with its IR prefix. Rule: <code>Sheba</code>; store <code>Iran::normalizeSheba()</code>.'],
            ['…', 'Every preset is a default — a caller\'s attribute of the same name replaces it — and every <code>flux:input</code> prop passes through as on <code>mds:input</code>.'],
        ]],
    ],
    'related' => ['input', 'quantity'],
];

// ----------------------------------------------------------------- mds:price

$pages['price'] = [
    'group' => 'mds',
    'title' => 'mds:price',
    'lede' => 'Money in Toman or Rial — separators, currency label and discount badge from one amount.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Grouping separators and the currency label, from one amount. These docs render the kit configured for English — Latin digits, <code>Toman</code>; the last section shows the Persian output a Persian-configured app gets by default.',
            'code' => '<mds:price :amount="2500000" />',
        ],
        [
            'name' => 'With an original price',
            'text' => 'Pass <code>original</code> and you get the strikethrough and the discount badge for free — the percentage is computed, not passed in.',
            'code' => '<mds:price :amount="2500000" :original="3200000" size="lg" />',
        ],
        [
            'name' => 'Currency',
            'text' => '<code>toman</code> and <code>rial</code> render تومان / ریال in Persian mode and <code>Toman</code> / <code>Rial</code> in English mode, <code>none</code> drops the label, and any other string is used verbatim.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:price :amount="2500000" />
                <mds:price :amount="890000" currency="none" />
                <mds:price :amount="890000" currency="AED" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:price :amount="2500000" size="sm" />
                <mds:price :amount="2500000" />
                <mds:price :amount="2500000" size="lg" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Decimals and no badge',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:price :amount="1250.75" :decimals="2" />
                <mds:price :amount="2500000" :original="3200000" :badge="false" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the digits, the <code>٬</code> separator and the currency label to Persian; <code>:fa="true"</code> does it for a single price. The <code>@toman</code> and <code>@rial</code> directives are Persian by definition — money inside a Persian sentence.',
            'code' => <<<'BLADE'
            <div class="flex flex-col items-center gap-6">
                <div class="flex flex-wrap items-center gap-8">
                    <mds:price :amount="2500000" :original="3200000" size="lg" :fa="true" currency="toman" />
                    <mds:price :amount="14500000" :fa="true" currency="rial" />
                </div>

                <flux:text>قیمت این کالا @toman(2500000) است.</flux:text>
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:price', 'props' => [
            ['amount', 'The price. Default: <code>0</code>.'],
            ['original', 'Pre-discount price; adds the strikethrough and the badge.'],
            ['currency', '<code>toman</code>, <code>rial</code> (the Persian words), <code>none</code>, or a literal label. Default: <code>config(\'mds.currency\')</code>.'],
            ['decimals', 'Decimal places. Default: <code>0</code>.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['fa', 'Persian digits, separators and built-in labels. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['badge', 'Shows the discount badge when <code>original</code> is set. Default: <code>true</code>.'],
        ]],
        ['name' => '@toman / @rial', 'text' => 'Blade directives for a formatted amount inline, e.g. <code>@toman($order->total)</code>. Persian digits and labels by definition — they exist for Persian sentences.'],
    ],
    'related' => ['discount-badge', 'product-card', 'chart'],
];

// -------------------------------------------------------- mds:discount-badge

$pages['discount-badge'] = [
    'group' => 'mds',
    'title' => 'mds:discount-badge',
    'lede' => 'A percentage-off pill.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<mds:discount-badge :percent="25" />',
        ],
        [
            'name' => 'From two prices',
            'text' => 'Give it the amounts instead of the percentage and it works the arithmetic out.',
            'code' => '<mds:discount-badge :amount="80000" :original="100000" />',
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3">
                <mds:discount-badge :percent="10" size="sm" />
                <mds:discount-badge :percent="25" />
                <mds:discount-badge :percent="40" size="lg" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — renders the badge with Persian digits and the <code>٪</code> sign, and its accessible label as «۲۵ درصد تخفیف» rather than "25% off". <code>:fa="true"</code> does the same for a single badge.',
            'code' => '<mds:discount-badge :percent="25" :fa="true" />',
        ],
    ],
    'reference' => [
        ['name' => 'mds:discount-badge', 'props' => [
            ['percent', 'The percentage, when you already have it.'],
            ['amount', 'Discounted price; used with <code>original</code>.'],
            ['original', 'Pre-discount price.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['fa', 'Persian digits and built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
    ],
    'related' => ['price', 'badge'],
];

// -------------------------------------------------------------- mds:quantity

$pages['quantity'] = [
    'group' => 'mds',
    'title' => 'mds:quantity',
    'lede' => 'A cart quantity stepper.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<mds:quantity :value="2" :min="1" :max="5" name="qty" />',
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4">
                <mds:quantity :value="1" size="sm" />
                <mds:quantity :value="2" />
                <mds:quantity :value="3" size="lg" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Bounds and step',
            'text' => 'The buttons disable themselves at the bounds.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4">
                <mds:quantity :value="1" :min="1" :max="3" />
                <mds:quantity :value="10" :step="5" :min="5" :max="50" />
            </div>
            BLADE,
        ],
        [
            'name' => 'With Livewire',
            'text' => 'The hidden input always holds Latin digits, whatever the display shows — so the value that reaches your component is a number, not a Persian string.',
            'code' => '<mds:quantity wire:model.live="quantity" :min="1" :max="10" />',
            'render' => '<mds:quantity :value="2" :min="1" :max="10" />',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the displayed digits and the built-in strings to Persian: the buttons are labeled افزایش تعداد and کاهش تعداد for screen readers. <code>:fa="true"</code> does it for a single stepper.',
            'code' => '<mds:quantity :value="2" :min="1" :max="10" :fa="true" />',
        ],
    ],
    'reference' => [
        ['name' => 'mds:quantity', 'props' => [
            ['value', 'Starting quantity.'],
            ['min', 'Lower bound. Default: <code>1</code>.'],
            ['max', 'Upper bound.'],
            ['step', 'Increment. Default: <code>1</code>.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['name', 'Field name for a plain form.'],
            ['fa', 'Persian digits and built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['increment-label', 'Accessible label for the plus button. Default: "Increase quantity", or افزایش تعداد in Persian.'],
            ['decrement-label', 'Accessible label for the minus button. Default: "Decrease quantity", or کاهش تعداد in Persian.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['price', 'product-card'],
];

// ---------------------------------------------------------------- mds:rating

$pages['rating'] = [
    'group' => 'mds',
    'title' => 'mds:rating',
    'lede' => 'A star rating, read-only or as an input.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Half stars are supported, and the review count comes along.',
            'code' => '<mds:rating :value="4.3" :count="126" />',
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:rating :value="4.6" size="sm" />
                <mds:rating :value="4.6" />
                <mds:rating :value="4.6" size="lg" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Without the number',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:rating :value="5" :count="12" />
                <mds:rating :value="2.5" :show-value="false" />
            </div>
            BLADE,
        ],
        [
            'name' => 'As an input',
            'text' => '<code>mds:rating.input</code> is a real radio group under the hood, so it works in a plain form and with the keyboard. Its default group label follows the config language — "Rating" here, امتیاز in Persian.',
            'code' => '<mds:rating.input name="score" :value="3" />',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the value and count to Persian digits and the built-in labels to Persian; <code>:fa="true"</code> does it for a single rating.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center gap-8">
                <mds:rating :value="4.3" :count="126" :fa="true" />
                <mds:rating :value="3.7" :count="1205" :fa="true" />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:rating', 'props' => [
            ['value', 'The rating. Default: <code>0</code>.'],
            ['max', 'Number of stars. Default: <code>5</code>.'],
            ['count', 'Review count shown after the stars.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['show-value', 'Shows the numeric value. Default: <code>true</code>.'],
            ['fa', 'Persian digits. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
        ['name' => 'mds:rating.input', 'props' => [
            ['value', 'Pre-selected rating.'],
            ['max', 'Number of stars. Default: <code>5</code>.'],
            ['name', 'Field name for a plain form.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['label', 'Accessible group label. Default: "Rating", or امتیاز in Persian (follows the config).'],
            ['reverse', 'Flips Arrow Left/Right. By default Right is always <em>next</em>, in RTL and LTR alike; set this when Right should follow the visual order of an RTL star row.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['product-card', 'price'],
];

// ---------------------------------------------------------- mds:product-card

$pages['product-card'] = [
    'group' => 'mds',
    'title' => 'mds:product-card',
    'lede' => 'A whole product tile: image, title, price, rating and a badge.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <mds:product-card
                title="Samsung Galaxy S25 smartphone, 256 GB"
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
            'name' => 'With an action',
            'text' => 'Anything in the slot lands under the price.',
            'code' => <<<'BLADE'
            <mds:product-card
                title="AirSound Pro wireless headphones"
                image="https://picsum.photos/seed/headphone/400/400"
                :amount="1890000"
                :rating="4.1"
                :reviews="87"
                href="#"
                class="max-w-56"
            >
                <flux:button variant="primary" size="sm" class="w-full">Add to cart</flux:button>
            </mds:product-card>
            BLADE,
        ],
        [
            'name' => 'Out of stock',
            'text' => '<code>unavailable</code> drops the price and shows the out-of-stock label instead — "Out of stock" here, ناموجود in Persian.',
            'code' => <<<'BLADE'
            <mds:product-card
                title="Fit Band 8 smart watch"
                image="https://picsum.photos/seed/watch/400/400"
                unavailable
                href="#"
                class="max-w-56"
            />
            BLADE,
        ],
        [
            'name' => 'Badge color',
            'code' => <<<'BLADE'
            <mds:product-card
                title="One Hundred Years of Solitude"
                image="https://picsum.photos/seed/book/400/400"
                :amount="245000"
                :original="350000"
                badge="Bestseller"
                badge-color="amber"
                href="#"
                class="max-w-56"
            />
            BLADE,
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the price, rating and built-in strings (ناموجود for out of stock) to Persian; <code>:fa="true"</code> does it for a single card.',
            'code' => <<<'BLADE'
            <mds:product-card
                title="گوشی موبایل سامسونگ مدل Galaxy S25 ظرفیت ۲۵۶ گیگابایت"
                image="https://picsum.photos/seed/phone/400/400"
                :amount="42500000"
                :original="48900000"
                :rating="4.6"
                :reviews="342"
                badge="ارسال امروز"
                href="#"
                :fa="true"
                currency="toman"
                class="max-w-56"
            />
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:product-card', 'props' => [
            ['title', 'Product name. Clamped to two lines.'],
            ['image', 'Image URL.'],
            ['href', 'Link target for the whole card.'],
            ['amount', 'Current price.'],
            ['original', 'Pre-discount price; adds the strikethrough and badge.'],
            ['currency', 'Passed through to <code>mds:price</code>.'],
            ['rating', 'Star rating.'],
            ['reviews', 'Review count.'],
            ['badge', 'Corner badge text.'],
            ['badge-color', 'Badge color. Default: <code>lime</code>.'],
            ['unavailable', 'Marks the product out of stock: the price is replaced with "Out of stock", or ناموجود in Persian. Default: <code>false</code>.'],
            ['fa', 'Persian digits and built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
    ],
    'related' => ['price', 'rating', 'quantity'],
];

// --------------------------------------------------------------- mds:stepper

$pages['stepper'] = [
    'group' => 'mds',
    'title' => 'mds:stepper',
    'lede' => 'Checkout steps, with the completed ones ticked.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The step labels are yours, so the component is language-agnostic; the numbers and the nav\'s accessible label follow the digit config.',
            'code' => <<<'BLADE'
            <mds:stepper :steps="['Cart', 'Shipping', 'Payment', 'Review']" :current="2" class="w-full" />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'First and last step',
            'code' => <<<'BLADE'
            <div class="w-full space-y-6">
                <mds:stepper :steps="['Cart', 'Shipping', 'Payment']" :current="1" class="w-full" />
                <mds:stepper :steps="['Cart', 'Shipping', 'Payment']" :current="3" class="w-full" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the step numbers to Persian digits and the nav\'s accessible label to مراحل; <code>:fa="true"</code> does it for a single stepper. On an RTL page the steps flow right-to-left by themselves.',
            'code' => <<<'BLADE'
            <mds:stepper :steps="['سبد خرید', 'آدرس و زمان ارسال', 'پرداخت', 'تأیید نهایی']" :current="2" :fa="true" class="w-full" />
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:stepper', 'props' => [
            ['steps', 'Array of step labels.'],
            ['current', '1-based index of the active step. Default: <code>1</code>.'],
            ['fa', 'Persian digits and built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
    ],
    'related' => ['timeline', 'progress'],
];

// ------------------------------------------------------------- mds:countdown

$pages['countdown'] = [
    'group' => 'mds',
    'title' => 'mds:countdown',
    'lede' => 'A live countdown to a deadline.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Rendered on the server first, then kept ticking by Alpine — so it is never blank before JS boots. These previews are live.',
            'code' => '<mds:countdown :until="now()->addHours(7)->addMinutes(42)" :days="false" />',
        ],
        [
            'name' => 'With days and labels',
            'text' => 'The unit labels follow the config language — days / hours / min / sec here, روز / ساعت / دقیقه / ثانیه in Persian.',
            'code' => '<mds:countdown :until="now()->addDays(2)->addHours(5)" labels size="lg" />',
        ],
        [
            'name' => 'Expired',
            'text' => 'Past the deadline it swaps to <code>expired-text</code> — "Expired" unless you pass your own.',
            'code' => '<mds:countdown :until="now()->subMinute()" expired-text="This deal has ended" />',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the digits, the unit labels (روز / ساعت / دقیقه / ثانیه) and the default expired text (به پایان رسید) to Persian; <code>:fa="true"</code> does it for a single countdown.',
            'code' => '<mds:countdown :until="now()->addDays(2)->addHours(5)" labels size="lg" :fa="true" />',
        ],
    ],
    'reference' => [
        ['name' => 'mds:countdown', 'props' => [
            ['until', 'Deadline: a Carbon instance, a timestamp or a parseable string.'],
            ['days', 'Shows a days segment. Default: <code>true</code>.'],
            ['labels', 'Shows the unit labels under the digits. Default: <code>false</code>.'],
            ['size', 'Options: <code>sm</code>, <code>lg</code>.'],
            ['expired-text', 'Shown once the deadline passes. Default: "Expired", or به پایان رسید in Persian.'],
            ['fa', 'Persian digits and built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
    ],
    'related' => ['jalali-date', 'badge'],
];

// ----------------------------------------------------------- mds:jalali-date

$pages['jalali-date'] = [
    'group' => 'mds',
    'title' => 'mds:jalali-date',
    'lede' => 'Jalali (Shamsi) dates, with no external calendar dependency.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The conversion is implemented in the package itself — no ext-intl, no calendar library. The calendar is always Jalali; the output language follows <code>config(\'mds.persian_digits\')</code>, so on these pages you get Latin digits and transliterated names.',
            'code' => '<mds:jalali-date :date="now()" format="l j F Y" />',
        ],
        [
            'name' => 'Formats',
            'text' => 'Same format characters as PHP\'s <code>date()</code>. Month and weekday names follow the output language: transliterations like Shahrivar and English weekdays here, فروردین تا اسفند and Persian weekdays in Persian.',
            'code' => <<<'BLADE'
            <div class="space-y-1 text-sm">
                <div><mds:jalali-date :date="now()" format="l j F Y" /></div>
                <div><mds:jalali-date :date="now()" format="j F Y" /></div>
                <div><mds:jalali-date :date="now()" format="Y/m/d" /></div>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Relative',
            'text' => '<code>ago</code> gives a short relative phrase — "3 hours ago", "in 2 days", "just now" — with the full date in the <code>title</code>.',
            'code' => <<<'BLADE'
            <div class="space-y-1 text-sm">
                <div><mds:jalali-date :date="now()->subHours(3)" ago /></div>
                <div><mds:jalali-date :date="now()->subDays(4)" ago /></div>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Blade directive',
            'text' => 'The directive reads the config too, so on these pages it comes out transliterated.',
            'code' => '<flux:text>Registered on @jalali(now()).</flux:text>',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the whole output to Persian: digits, month and weekday names, and the relative phrases; <code>:fa="true"</code> does it for a single date.',
            'code' => <<<'BLADE'
            <div class="space-y-1 text-sm">
                <div><mds:jalali-date :date="now()" format="l j F Y" :fa="true" /></div>
                <div><mds:jalali-date :date="now()->subDays(4)" ago :fa="true" /></div>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:jalali-date', 'props' => [
            ['date', 'A Carbon instance, a timestamp or a parseable string.'],
            ['format', 'PHP date format characters. Default: <code>j F Y</code>.'],
            ['ago', 'Renders a relative phrase instead. Default: <code>false</code>.'],
            ['fa', 'Persian output — digits, names and relative phrases; Latin digits with transliterated names when off. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ]],
        ['name' => '@jalali', 'text' => 'Blade directive: <code>@jalali($date, $format = \'j F Y\')</code>. Follows the config for digits and names.'],
        ['name' => 'MajidDs\\Support\\Jalali', 'text' => 'The PHP helper behind the component: <code>Jalali::format()</code>, <code>Jalali::fromGregorian()</code>, <code>Jalali::toGregorian()</code>.'],
    ],
    'related' => ['countdown', 'table', 'chart'],
];

// ----------------------------------------------------------- mds:empty-state

$pages['empty-state'] = [
    'group' => 'mds',
    'title' => 'mds:empty-state',
    'lede' => 'What to show when there is nothing to show.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <mds:empty-state
                icon="shopping-cart"
                title="Your cart is empty"
                description="You haven't added anything to your cart yet."
            >
                <flux:button variant="primary">Browse deals</flux:button>
                <flux:button variant="ghost">Order history</flux:button>
            </mds:empty-state>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Without actions',
            'code' => <<<'BLADE'
            <mds:empty-state
                icon="magnifying-glass"
                title="No results"
                description="Try a different search term or clear your filters."
            />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'All the copy here is yours, so nothing reads <code>config(\'mds.persian_digits\')</code> — a Persian empty state is just Persian props on an RTL page.',
            'code' => <<<'BLADE'
            <mds:empty-state
                icon="shopping-cart"
                title="سبد خرید شما خالی است"
                description="هنوز کالایی به سبد خرید خود اضافه نکرده‌اید."
            >
                <flux:button variant="primary">مشاهده پیشنهادها</flux:button>
                <flux:button variant="ghost">تاریخچه سفارش‌ها</flux:button>
            </mds:empty-state>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:empty-state', 'props' => [
            ['icon', 'Icon shown above the title.'],
            ['title', 'Headline.'],
            ['description', 'Supporting copy.'],
        ], 'slots' => [
            ['default', 'Action buttons under the description.'],
        ]],
    ],
    'related' => ['skeleton', 'callout'],
];

// ---------------------------------------------------------- mds:preview-card

$pages['preview-card'] = [
    'group' => 'mds',
    'title' => 'mds:preview-card',
    'lede' => 'A hover preview of a link\'s destination, shown beside it.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The trigger is a real <code>&lt;a&gt;</code> — it still navigates on click. Hovering or focusing it reveals a card previewing where the link goes: the profile behind an @mention, the product behind a link. Hover the mention below.',
            'code' => <<<'BLADE'
            <p class="max-w-md text-center text-sm text-zinc-600 dark:text-zinc-300">
                This kit is maintained by the
                <mds:preview-card>
                    <mds:preview-card.trigger href="#!">@majid_ds</mds:preview-card.trigger>

                    <mds:preview-card.content>
                        <div class="flex items-center justify-between">
                            <flux:avatar src="https://i.pravatar.cc/48?img=12" />
                            <flux:button size="sm" variant="primary">Follow</flux:button>
                        </div>

                        <div>
                            <div class="font-semibold text-zinc-800 dark:text-white">Majid Design System</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">@majid_ds</div>
                        </div>

                        <p>An RTL-first UI kit for Laravel Livewire, on top of Flux UI.</p>

                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-medium text-zinc-800 dark:text-white">2,481</span> followers
                        </div>
                    </mds:preview-card.content>
                </mds:preview-card>
                team.
            </p>
            BLADE,
            'note' => '<p><strong>Keep the preview supplementary.</strong> The card only appears on hover or keyboard focus — it never opens on touch, and screen readers do not announce it. It previews where the link goes; anything essential belongs on the linked page itself.</p>',
        ],
        [
            'name' => 'Media preview',
            'text' => 'The content is just a styled container, so a card can lead with a cover image: cancel the padding with <code>!p-0</code>, clip with <code>overflow-hidden</code>, pad the text back in.',
            'code' => <<<'BLADE'
            <p class="max-w-md text-center text-sm text-zinc-600 dark:text-zinc-300">
                The next team offsite is at the
                <mds:preview-card>
                    <mds:preview-card.trigger href="#!">Coast House</mds:preview-card.trigger>

                    <mds:preview-card.content :arrow="false" class="overflow-hidden !p-0">
                        <img src="https://picsum.photos/seed/coast/288/140" alt="" class="h-32 w-full object-cover">

                        <div class="flex flex-col gap-1 p-4">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-zinc-800 dark:text-white">Coast House</div>
                                <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    <mds:icon icon="star" class="size-4 text-amber-500" />
                                    4.9
                                </span>
                            </div>
                            <p>A bright, open workspace facing the bay — room for 24.</p>
                        </div>
                    </mds:preview-card.content>
                </mds:preview-card>
                venue.
            </p>
            BLADE,
        ],
        [
            'name' => 'Side and alignment',
            'text' => 'Position with <code>side</code> (<code>top</code> / <code>bottom</code> / <code>start</code> / <code>end</code>) and <code>align</code> (<code>start</code> / <code>center</code> / <code>end</code>). Logical sides on purpose: <code>start</code>/<code>end</code> and the alignment axis mirror on RTL pages by themselves. The card flips and shifts automatically to stay in view.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm">
                <mds:preview-card delay="200">
                    <mds:preview-card.trigger href="#!">top / start</mds:preview-card.trigger>
                    <mds:preview-card.content side="top" align="start" class="w-56">
                        <p>The card flips and shifts automatically to stay in view.</p>
                    </mds:preview-card.content>
                </mds:preview-card>

                <mds:preview-card delay="200">
                    <mds:preview-card.trigger href="#!">end / center</mds:preview-card.trigger>
                    <mds:preview-card.content side="end" class="w-56">
                        <p>The card flips and shifts automatically to stay in view.</p>
                    </mds:preview-card.content>
                </mds:preview-card>

                <mds:preview-card delay="200">
                    <mds:preview-card.trigger href="#!">bottom / end</mds:preview-card.trigger>
                    <mds:preview-card.content side="bottom" align="end" class="w-56">
                        <p>The card flips and shifts automatically to stay in view.</p>
                    </mds:preview-card.content>
                </mds:preview-card>

                <mds:preview-card delay="200">
                    <mds:preview-card.trigger href="#!">start / center</mds:preview-card.trigger>
                    <mds:preview-card.content side="start" class="w-56">
                        <p>The card flips and shifts automatically to stay in view.</p>
                    </mds:preview-card.content>
                </mds:preview-card>
            </div>
            BLADE,
        ],
        [
            'name' => 'Without an arrow',
            'text' => 'Pass <code>:arrow="false"</code> to drop the pointer for a flatter card that sits a little closer to the link (offset 6 instead of 10).',
            'code' => <<<'BLADE'
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                Read more in the
                <mds:preview-card>
                    <mds:preview-card.trigger href="#!">getting started</mds:preview-card.trigger>
                    <mds:preview-card.content :arrow="false" class="w-64">
                        <div class="font-semibold text-zinc-800 dark:text-white">Getting started</div>
                        <p>Install the package, import the styles, and drop your first component in.</p>
                    </mds:preview-card.content>
                </mds:preview-card>
                guide.
            </p>
            BLADE,
        ],
        [
            'name' => 'Delays',
            'text' => 'Hover waits <code>delay</code> before opening and <code>close-delay</code> before closing, so the card neither flickers on a passing cursor nor vanishes on the way to it. Keyboard focus opens immediately — focusing a link is intentional — and <code>Escape</code> always closes.',
            'code' => <<<'BLADE'
            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                An
                <mds:preview-card delay="0" close-delay="100">
                    <mds:preview-card.trigger href="#!">impatient</mds:preview-card.trigger>
                    <mds:preview-card.content :arrow="false" class="w-52">
                        <p>Opened with no delay at all.</p>
                    </mds:preview-card.content>
                </mds:preview-card>
                preview next to a
                <mds:preview-card delay="1500">
                    <mds:preview-card.trigger href="#!">patient</mds:preview-card.trigger>
                    <mds:preview-card.content :arrow="false" class="w-52">
                        <p>Waited a second and a half.</p>
                    </mds:preview-card.content>
                </mds:preview-card>
                one.
            </p>
            BLADE,
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'Everything positional is logical, so on an RTL page <code>start</code>/<code>end</code> and the alignment axis mirror by themselves. The copy is yours; the follower count here uses the <code>@faNum</code> directive, which formats with Persian digits by definition.',
            'code' => <<<'BLADE'
            <p class="max-w-md text-center text-sm text-zinc-600 dark:text-zinc-300">
                این کیت توسط تیم
                <mds:preview-card>
                    <mds:preview-card.trigger href="#!">@majid_ds</mds:preview-card.trigger>

                    <mds:preview-card.content>
                        <div class="flex items-center justify-between">
                            <flux:avatar src="https://i.pravatar.cc/48?img=12" />
                            <flux:button size="sm" variant="primary">دنبال کردن</flux:button>
                        </div>

                        <div>
                            <div class="font-semibold text-zinc-800 dark:text-white">مجید دیزاین سیستم</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">@majid_ds</div>
                        </div>

                        <p>کیت رابط کاربری راست‌چین برای Laravel Livewire، روی Flux UI.</p>

                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                            <span class="font-medium text-zinc-800 dark:text-white">@faNum(2481)</span> دنبال‌کننده
                        </div>
                    </mds:preview-card.content>
                </mds:preview-card>
                نگه‌داری می‌شود.
            </p>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:preview-card', 'text' => 'The root. Holds the open state; renders an inline wrapper.', 'props' => [
            ['delay', 'Hover-open delay in milliseconds. Default: <code>600</code>.'],
            ['close-delay', 'Hover-close delay in milliseconds. Default: <code>300</code>.'],
        ]],
        ['name' => 'mds:preview-card.trigger', 'text' => 'The inline anchor that owns the link — it navigates on click like any <code>&lt;a&gt;</code>.', 'props' => [
            ['href', 'Navigation target. Any other anchor attribute (<code>target</code>, <code>rel</code>, …) passes through.'],
        ]],
        ['name' => 'mds:preview-card.content', 'text' => 'The auto-positioned popup — teleported to <code>&lt;body&gt;</code> (so a block-level card can legally live inside a paragraph) and <code>position: fixed</code>.', 'props' => [
            ['side', 'Preferred side: <code>top</code>, <code>bottom</code>, <code>start</code>, <code>end</code>. Flips when out of room. Default: <code>bottom</code>.'],
            ['align', 'Alignment along that side: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>center</code>.'],
            ['side-offset', 'Gap between the link and the card. Default: <code>10</code>, or <code>6</code> without the arrow.'],
            ['arrow', 'The pointer toward the link. Default: <code>true</code>.'],
        ]],
    ],
    'related' => ['tooltip', 'dropdown'],
];

// --------------------------------------------------------------- mds:command

$pages['command'] = [
    'group' => 'mds',
    'title' => 'mds:command',
    'lede' => 'A ⌘K command palette — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Flux\'s Command component is Pro-only. This is a working replacement with keyboard navigation, a ⌘K shortcut and search that handles Persian text as comfortably as Latin. The preview is live — type in it.',
            'code' => <<<'BLADE'
            <mds:command class="max-w-md">
                <mds:command.input placeholder="Search commands..." clearable />

                <mds:command.items>
                    <mds:command.heading>Navigation</mds:command.heading>
                    <mds:command.item icon="shopping-bag" kbd="⌘O">My orders</mds:command.item>
                    <mds:command.item icon="heart" kbd="⌘F">Favorites</mds:command.item>
                    <mds:command.item icon="map-pin">My addresses</mds:command.item>

                    <mds:command.heading>Actions</mds:command.heading>
                    <mds:command.item icon="truck" kbd="⌘T">Track shipment</mds:command.item>
                    <mds:command.item icon="arrow-right-start-on-rectangle">Sign out</mds:command.item>
                </mds:command.items>
            </mds:command>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inside a modal',
            'text' => 'The usual shape: a bare modal opened by <code>⌘K</code>.',
            'code' => <<<'BLADE'
            <flux:modal.trigger name="global-search" shortcut="cmd.k">
                <flux:input as="button" placeholder="Search..." icon="magnifying-glass" kbd="⌘K" class="max-w-md" />
            </flux:modal.trigger>

            <flux:modal name="global-search" variant="bare" class="w-full max-w-md">
                <mds:command>
                    <mds:command.input placeholder="Search everywhere..." closable autofocus />

                    <mds:command.items>
                        <mds:command.heading>Suggestions</mds:command.heading>
                        <mds:command.item icon="fire">Today's deals</mds:command.item>
                        <mds:command.item icon="device-phone-mobile">Mobile phones</mds:command.item>
                    </mds:command.items>
                </mds:command>
            </flux:modal>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Items as links',
            'code' => <<<'BLADE'
            <mds:command class="max-w-md">
                <mds:command.input placeholder="Search pages..." />

                <mds:command.items>
                    <mds:command.item icon="home" href="#">Dashboard</mds:command.item>
                    <mds:command.item icon="cog-6-tooth" href="#">Settings</mds:command.item>
                </mds:command.items>
            </mds:command>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => 'The built-in microcopy — the empty message and the clear/close button labels — follows <code>config(\'mds.persian_digits\')</code>, which is on by default in a real app and off in these docs. There is no <code>fa</code> prop here, so to force Persian on one palette pass the string props (<code>empty</code>, <code>clear-label</code>, <code>close-label</code>) as this example does.',
            'code' => <<<'BLADE'
            <mds:command class="max-w-md">
                <mds:command.input placeholder="جستجوی فرمان..." clearable clear-label="پاک کردن" />

                <mds:command.items empty="نتیجه‌ای یافت نشد.">
                    <mds:command.heading>ناوبری</mds:command.heading>
                    <mds:command.item icon="shopping-bag" kbd="⌘O">سفارش‌های من</mds:command.item>
                    <mds:command.item icon="heart" kbd="⌘F">علاقه‌مندی‌ها</mds:command.item>
                    <mds:command.item icon="truck" kbd="⌘T">پیگیری مرسوله</mds:command.item>
                </mds:command.items>
            </mds:command>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:command', 'text' => 'The palette shell. Takes no props.'],
        ['name' => 'mds:command.input', 'props' => [
            ['placeholder', 'Placeholder text.'],
            ['icon', 'Leading icon. Default: <code>magnifying-glass</code>.'],
            ['clearable', 'Adds a clear button. Default: <code>false</code>.'],
            ['closable', 'Adds a close button, for use inside a modal. Default: <code>false</code>.'],
            ['autofocus', 'Focuses the field on open.'],
            ['clear-label', 'Accessible label for the clear button. Default: "Clear", or پاک کردن in Persian.'],
            ['close-label', 'Accessible label for the close button. Default: "Close", or بستن in Persian.'],
        ]],
        ['name' => 'mds:command.items', 'props' => [
            ['empty', 'Message shown when the search matches nothing. Default: "No results found.", or نتیجه‌ای یافت نشد. in Persian.'],
        ]],
        ['name' => 'mds:command.heading', 'text' => 'A group label inside the list.'],
        ['name' => 'mds:command.item', 'props' => [
            ['icon', 'Leading icon name.'],
            ['kbd', 'Keyboard shortcut hint on the trailing edge.'],
            ['href', 'Renders the item as a link.'],
        ]],
    ],
    'related' => ['modal', 'input'],
];

// -------------------------------------------------------------- mds:composer

$pages['composer'] = [
    'group' => 'mds',
    'title' => 'mds:composer',
    'lede' => 'A chat / prompt input with an action bar — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A textarea that grows with what you type, an action bar around it, and <code>Ctrl</code>/<code>⌘</code> + <code>Enter</code> to submit the surrounding form. The preview is live — type a few lines into it.',
            'code' => <<<'BLADE'
            <form wire:submit="send">
                <mds:composer wire:model="prompt" label="Message" label:sr-only placeholder="How can I help you today?">
                    <x-slot name="actionsLeading">
                        <flux:button size="sm" variant="subtle" square><mds:icon icon="paper-clip" class="size-4" /></flux:button>
                        <flux:button size="sm" variant="subtle" square><mds:icon icon="adjustments-horizontal" class="size-4" /></flux:button>
                    </x-slot>

                    <x-slot name="actionsTrailing">
                        <flux:button size="sm" variant="filled" square><mds:icon icon="microphone" class="size-4" /></flux:button>
                        <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                    </x-slot>
                </mds:composer>
            </form>
            BLADE,
            'align' => 'stretch',
            'note' => 'The action bar is plain <code>flux:button</code>s, so they follow your accent colour. <code>square</code> plus an <code>mds:icon</code> slot keeps the buttons icon-only while drawing Hugeicons rather than heroicons.',
        ],
        [
            'name' => 'With a header',
            'text' => 'The <code>header</code> slot sits above the input — attachments, a reply-to line, a model picker.',
            'code' => <<<'BLADE'
            <mds:composer placeholder="Write a caption for this image...">
                <x-slot name="header">
                    <mds:file-item heading="galaxy-phone.jpg" image="https://picsum.photos/seed/phone/80/80" :size="162400" class="w-full max-w-64">
                        <x-slot name="actions">
                            <mds:file-item.remove label="Remove image" />
                        </x-slot>
                    </mds:file-item>
                </x-slot>

                <x-slot name="actionsTrailing">
                    <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                </x-slot>
            </mds:composer>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inline',
            'text' => 'One row: the actions sit beside the input instead of under it. Pair it with <code>rows="1"</code>.',
            'code' => <<<'BLADE'
            <mds:composer rows="1" inline placeholder="Write your message...">
                <x-slot name="actionsLeading">
                    <flux:button size="sm" variant="ghost" square><mds:icon icon="plus" class="size-4" /></flux:button>
                </x-slot>

                <x-slot name="actionsTrailing">
                    <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                </x-slot>
            </mds:composer>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Input variant',
            'text' => '<code>variant="input"</code> trades the pill radius for the same corners as the rest of your form controls, so a composer can sit in a field stack without looking like a visitor.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-4">
                <flux:input label="Name" placeholder="Your name" />

                <mds:composer variant="input" label="Message" placeholder="What's on your mind?">
                    <x-slot name="actionsTrailing">
                        <flux:button type="submit" size="sm" variant="primary">Send</flux:button>
                    </x-slot>
                </mds:composer>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Height',
            'text' => '<code>rows</code> is the height it starts at, <code>max-rows</code> the height it stops growing at — past that the input scrolls.',
            'code' => <<<'BLADE'
            <mds:composer rows="4" max-rows="8" placeholder="Four rows to start, eight at most..." class="max-w-md" />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Submit behavior',
            'text' => 'By default <code>Ctrl</code>/<code>⌘</code> + <code>Enter</code> submits and <code>Enter</code> makes a new line. <code>submit="enter"</code> promotes the bare <code>Enter</code> to sending, and <code>Shift</code> + <code>Enter</code> takes over the new line. <code>Ctrl</code>/<code>⌘</code> + <code>Enter</code> sends either way.',
            'code' => <<<'BLADE'
            <form wire:submit="send">
                <mds:composer wire:model="prompt" submit="enter" rows="1" inline placeholder="Enter sends this one...">
                    <x-slot name="actionsTrailing">
                        <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                    </x-slot>
                </mds:composer>
            </form>
            BLADE,
            'align' => 'stretch',
            'note' => 'Either way the key press calls <code>form.requestSubmit()</code>, so <code>wire:submit</code> and native validation behave exactly as they would on a click. An open IME candidate window keeps its <code>Enter</code>.',
        ],
        [
            'name' => 'Character counter',
            'text' => 'Not in Flux: <code>maxlength</code> caps the message and <code>counter</code> shows how much of it is used.',
            'code' => <<<'BLADE'
            <mds:composer :maxlength="280" counter rows="2" placeholder="280 characters at most..." value="Hi! Every character of this message is counted.">
                <x-slot name="actionsTrailing">
                    <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                </x-slot>
            </mds:composer>
            BLADE,
            'align' => 'stretch',
            'note' => 'The count is in characters, not bytes — a four-letter Persian word like «سلام» counts as 4, not 8.',
        ],
        [
            'name' => 'Footer',
            'text' => 'The <code>footer</code> slot runs under the action bar, sharing its row with the counter.',
            'code' => <<<'BLADE'
            <mds:composer :maxlength="280" counter rows="2" placeholder="Message...">
                <x-slot name="footer">
                    <span>Replies may be inaccurate.</span>
                </x-slot>

                <x-slot name="actionsTrailing">
                    <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                </x-slot>
            </mds:composer>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'A different input',
            'text' => 'The <code>input</code> slot replaces the textarea. Flux fills it with its Pro editor; anything that collects text works — here a plain <code>flux:textarea</code> keeps its own resize handle.',
            'code' => <<<'BLADE'
            <mds:composer class="max-w-md">
                <x-slot name="input">
                    <flux:textarea rows="3" resize="none" placeholder="Your own control goes here..." class="border-0! bg-transparent! px-2! py-1.5! shadow-none!" />
                </x-slot>

                <x-slot name="actionsTrailing">
                    <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                </x-slot>
            </mds:composer>
            BLADE,
            'align' => 'stretch',
            'note' => 'Auto-growing, the counter and <code>submit="enter"</code> all belong to the built-in textarea, so a replacement brings its own. <code>Ctrl</code>/<code>⌘</code> + <code>Enter</code> keeps working either way.',
        ],
        [
            'name' => 'Disabled and invalid',
            'text' => 'A disabled composer is <code>inert</code> — the whole box, action buttons included, stops taking input. Validation errors come from the error bag for <code>name</code>, or from an explicit <code>error</code>.',
            'code' => <<<'BLADE'
            <div class="w-full space-y-4">
                <mds:composer disabled rows="1" inline placeholder="Sign in to start writing">
                    <x-slot name="actionsTrailing">
                        <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                    </x-slot>
                </mds:composer>

                <mds:composer name="prompt" label="Message" error="A message is required." rows="1" inline>
                    <x-slot name="actionsTrailing">
                        <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                    </x-slot>
                </mds:composer>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the digits and the built-in strings to Persian; <code>:fa="true"</code> does it for a single composer, visible here in the counter («۰ / ۲۸۰»). The transcript above the composer is your own markup — the shape most Livewire chat pages end up with; the date in the reply is <code>mds:jalali-date</code>.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-lg space-y-4">
                <div class="space-y-3">
                    <div class="flex justify-start">
                        <div class="max-w-[80%] rounded-2xl rounded-ss-sm bg-zinc-100 px-3 py-2 text-sm dark:bg-white/10">
                            سلام! سفارش من کِی می‌رسد؟
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="max-w-[80%] rounded-2xl rounded-se-sm bg-accent px-3 py-2 text-sm text-accent-foreground">
                            مرسوله شما <mds:jalali-date :date="'2026-08-25'" :fa="true" /> به دستتان می‌رسد.
                        </div>
                    </div>
                </div>

                <form wire:submit="send">
                    <mds:composer wire:model="message" label="پیام" label:sr-only rows="1" max-rows="6" :maxlength="280" counter submit="enter" :fa="true" placeholder="پیام خود را بنویسید...">
                        <x-slot name="actionsLeading">
                            <flux:button size="sm" variant="ghost" square><mds:icon icon="paper-clip" class="size-4" /></flux:button>
                        </x-slot>

                        <x-slot name="actionsTrailing">
                            <flux:button type="submit" size="sm" variant="primary" square><mds:icon icon="paper-airplane" class="size-4" /></flux:button>
                        </x-slot>
                    </mds:composer>
                </form>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:composer', 'props' => [
            ['name', 'Field name for a plain form post, and the key the error bag is read with.'],
            ['value', 'Initial text. The default slot works too.'],
            ['placeholder', 'Placeholder for the input.'],
            ['label', 'Label text. Wraps the composer in a <code>flux:field</code>.'],
            ['label:sr-only', 'Keeps the label for screen readers only. <code>label-sr-only</code> also works.'],
            ['description', 'Help text under the composer.'],
            ['description:sr-only', 'Same, for screen readers only.'],
            ['rows', 'Height the input starts at, in lines. Default: <code>2</code>.'],
            ['max-rows', 'Height it stops growing at. Unbounded when absent.'],
            ['maxlength', 'Character cap on the input.'],
            ['counter', 'Shows the character count. Default: <code>false</code>.'],
            ['inline', 'Puts the actions beside the input instead of under it. Default: <code>false</code>.'],
            ['variant', '<code>input</code> matches the corner radius of other form controls.'],
            ['submit', 'Key that submits: <code>cmd+enter</code> (default) or <code>enter</code>.'],
            ['autofocus', 'Focuses the input on load.'],
            ['dir', 'Direction of the input. <code>auto</code> follows what is being typed.'],
            ['disabled', 'Makes the whole composer <code>inert</code>.'],
            ['invalid', 'Applies error styling.'],
            ['error', 'Validation message. Falls back to the bag for <code>name</code>.'],
            ['fa', 'Persian digits in the counter. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['wire:model', 'Binds the input to a Livewire property.'],
        ], 'slots' => [
            ['input', 'Replaces the built-in textarea.'],
            ['header', 'Above the input — attachments, a reply-to line.'],
            ['footer', 'Under the action bar, sharing its row with the counter.'],
            ['actionsLeading', 'Actions at the start of the action bar.'],
            ['actionsTrailing', 'Actions at the end, usually the submit button.'],
        ]],
    ],
    'related' => ['textarea', 'field'],
];

// ---------------------------------------------------------- mds:color-picker

$pages['color-picker'] = [
    'group' => 'mds',
    'title' => 'mds:color-picker',
    'lede' => 'A colour picker — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A saturation/brightness canvas, hue and alpha sliders, a swatch palette, an eyedropper and six output formats. Live — open it.',
            'code' => <<<'BLADE'
            <mds:color-picker
                label="Brand color"
                value="#e11d48"
                name="brand_color"
                clearable
                dropper
                class="max-w-xs"
            />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Output format',
            'text' => 'Options are <code>hex</code>, <code>hexa</code>, <code>rgb</code>, <code>rgba</code>, <code>hsl</code> and <code>hsla</code>. The alpha formats add the transparency slider.',
            'code' => <<<'BLADE'
            <mds:color-picker
                label="Background (rgba)"
                description="Output format: rgba, with an alpha slider"
                value="rgba(59, 130, 246, 0.5)"
                format="rgba"
                class="max-w-xs"
            />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Custom swatches',
            'code' => <<<'BLADE'
            <mds:color-picker
                label="Custom swatches"
                value="#00c16a"
                :swatches="[['#ef4444', 'Red'], ['#f59e0b', 'Amber'], ['#00c16a', 'Green'], ['#3b82f6', 'Blue'], ['#000000', 'Black']]"
                class="max-w-xs"
            />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Button mode',
            'text' => '<code>type="button"</code> shows just a swatch, for a toolbar.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3">
                <mds:color-picker type="button" value="#8b5cf6" />
                <mds:color-picker type="button" />
            </div>
            BLADE,
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'The built-in labels — clear, the eyedropper, the saturation area and the hue/opacity sliders — follow <code>config(\'mds.persian_digits\')</code>, which is on by default in a real app and off in these docs; there is no <code>fa</code> prop here. Your own <code>label</code>, <code>description</code> and swatch names can be any language, and the layout mirrors on an RTL page by itself.',
            'code' => <<<'BLADE'
            <mds:color-picker
                label="رنگ سازمانی"
                description="رنگ اصلی فروشگاه شما"
                value="#e11d48"
                :swatches="[['#ef4444', 'قرمز'], ['#f59e0b', 'کهربایی'], ['#00c16a', 'سبز'], ['#3b82f6', 'آبی']]"
                clearable
                dropper
                class="max-w-xs"
            />
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:color-picker', 'props' => [
            ['value', 'Initial colour, in any supported format.'],
            ['format', 'Output format: <code>hex</code>, <code>hexa</code>, <code>rgb</code>, <code>rgba</code>, <code>hsl</code>, <code>hsla</code>. Default: <code>hex</code>.'],
            ['type', 'Options: <code>input</code>, <code>button</code>. Default: <code>input</code>.'],
            ['label', 'Field label.'],
            ['description', 'Smaller text under the label.'],
            ['placeholder', 'Placeholder for the text field.'],
            ['swatches', 'Array of colours, or of <code>[colour, label]</code> pairs.'],
            ['dropper', 'Adds the eyedropper button where the browser supports it. Default: <code>false</code>.'],
            ['clearable', 'Adds a clear button. Default: <code>false</code>.'],
            ['size', 'Options: <code>sm</code>.'],
            ['disabled', 'Disables the control.'],
            ['invalid', 'Marks the control as invalid.'],
            ['name', 'Field name for a plain form.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['input', 'field'],
];

// ----------------------------------------------------------- mds:file-upload

$pages['file-upload'] = [
    'group' => 'mds',
    'title' => 'mds:file-upload',
    'lede' => 'Drag-and-drop uploads — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <mds:file-upload name="photos" label="Upload images" description="You can pick several files at once." accept="image/*" multiple class="max-w-md">
                <mds:file-upload.dropzone
                    heading="Drop your images here or click to browse"
                    text="JPG, PNG or GIF up to 10 MB"
                />
            </mds:file-upload>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inline',
            'text' => 'A compact single-row dropzone, with an optional upload progress bar.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-4">
                <mds:file-upload name="invoice" label="Compact" accept="application/pdf">
                    <mds:file-upload.dropzone heading="Drop a file, or click to choose" text="PDF only, up to 5 MB" inline />
                </mds:file-upload>

                <mds:file-upload name="attachment" label="With a progress bar">
                    <mds:file-upload.dropzone heading="Add an attachment" text="Any format up to 20 MB" inline with-progress />
                </mds:file-upload>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'File list',
            'text' => '<code>mds:file-item</code> renders the chosen files. Give it a byte count and it formats the size — "159 KB" here, Persian units under a Persian config.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-2">
                <mds:file-item heading="Profile_pic.jpg" image="https://picsum.photos/seed/phone/80/80" :size="162400">
                    <x-slot name="actions">
                        <mds:file-item.remove label="Remove Profile_pic.jpg" />
                    </x-slot>
                </mds:file-item>

                <mds:file-item heading="archive.zip" text="This file is over the 10 MB limit." icon="exclamation-triangle" invalid>
                    <x-slot name="actions">
                        <mds:file-item.remove label="Remove archive.zip" />
                    </x-slot>
                </mds:file-item>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Error and disabled',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-4">
                <mds:file-upload name="broken" label="With a validation error" error="The selected file is over the size limit.">
                    <mds:file-upload.dropzone heading="Product image" text="JPG or PNG up to 2 MB" inline />
                </mds:file-upload>

                <mds:file-upload label="Disabled" disabled>
                    <mds:file-upload.dropzone heading="Uploading is unavailable" text="Place the order first" inline />
                </mds:file-upload>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Custom uploader',
            'text' => 'Any markup inside <code>mds:file-upload</code> gets the full upload behaviour. Style the states with the <code>in-data-dragging:</code> and <code>in-data-loading:</code> variants.',
            'code' => <<<'BLADE'
            <mds:file-upload name="avatar" accept="image/*">
                <div class="relative flex size-20 cursor-pointer items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 transition-colors hover:bg-zinc-200 in-data-dragging:border-accent dark:border-white/10 dark:bg-white/10">
                    <mds:icon icon="user" class="text-zinc-500 dark:text-zinc-400" />

                    <div class="absolute bottom-0 end-0 rounded-full bg-white dark:bg-zinc-800">
                        <mds:icon icon="arrow-up-circle" class="size-5 text-zinc-500 dark:text-zinc-400" />
                    </div>
                </div>
            </mds:file-upload>
            BLADE,
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the digits and the built-in strings (the dropzone\'s default heading, the remove button\'s label, the size units) to Persian. <code>:fa="true"</code> flips the upload progress on <code>mds:file-upload</code> and the size on <code>mds:file-item</code>; the dropzone has no <code>fa</code> prop, so this example passes its heading in Persian directly.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-4">
                <mds:file-upload name="photos" label="بارگذاری تصاویر" accept="image/*" multiple :fa="true">
                    <mds:file-upload.dropzone
                        heading="فایل‌ها را اینجا رها کنید یا کلیک کنید"
                        text="JPG، PNG یا GIF تا ۱۰ مگابایت"
                    />
                </mds:file-upload>

                <mds:file-item heading="گوشی-گلکسی.jpg" image="https://picsum.photos/seed/phone/80/80" :size="162400" :fa="true">
                    <x-slot name="actions">
                        <mds:file-item.remove label="حذف فایل" />
                    </x-slot>
                </mds:file-item>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:file-upload', 'props' => [
            ['name', 'Field name, or the Livewire property to upload into.'],
            ['label', 'Field label.'],
            ['description', 'Smaller text under the label.'],
            ['accept', 'Accepted MIME types or extensions.'],
            ['multiple', 'Allows several files. Default: <code>false</code>.'],
            ['error', 'Validation message shown under the control.'],
            ['invalid', 'Marks the control as invalid.'],
            ['disabled', 'Disables the control.'],
            ['fa', 'Persian digits in the upload progress. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['wire:model', 'Binds to a Livewire property for real uploads.'],
        ]],
        ['name' => 'mds:file-upload.dropzone', 'props' => [
            ['heading', 'Main line inside the dropzone. Default: "Drop a file here or click to browse", or the Persian equivalent — follows the config.'],
            ['text', 'Supporting line, usually the accepted types and size.'],
            ['icon', 'Icon name. Default: <code>cloud-arrow-up</code>.'],
            ['inline', 'Compact single-row layout. Default: <code>false</code>.'],
            ['with-progress', 'Shows the Livewire upload progress bar. Default: <code>false</code>.'],
        ]],
        ['name' => 'mds:file-item', 'props' => [
            ['heading', 'File name.'],
            ['text', 'Secondary line. Derived from <code>size</code> when absent.'],
            ['size', 'Byte count, formatted into a readable size — "159 KB", or ۱۵۹ کیلوبایت in Persian.'],
            ['image', 'Thumbnail URL.'],
            ['icon', 'Icon when there is no thumbnail. Default: <code>document</code>.'],
            ['invalid', 'Marks the file as rejected.'],
            ['fa', 'Persian digits and size units. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ], 'slots' => [['actions', 'Buttons on the trailing edge, e.g. remove.']]],
        ['name' => 'mds:file-item.remove', 'props' => [
            ['label', 'Accessible label. Default: "Remove file", or حذف فایل in Persian.'],
            ['icon', 'Icon name. Default: <code>x-mark</code>.'],
        ]],
    ],
    'related' => ['input', 'field'],
];

// -------------------------------------------------------------- mds:timeline

$pages['timeline'] = [
    'group' => 'mds',
    'title' => 'mds:timeline',
    'lede' => 'An event timeline — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The rail runs down the leading edge, so it sits on the right in RTL and the left in LTR.',
            'code' => <<<'BLADE'
            <mds:timeline>
                <mds:timeline.item>
                    <mds:timeline.indicator>
                        <flux:icon icon="shopping-bag" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Order placed</flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item>
                    <mds:timeline.indicator>
                        <flux:icon icon="banknotes" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Payment confirmed <flux:badge size="sm" color="lime">Online</flux:badge></flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item>
                    <mds:timeline.indicator color="green">
                        <flux:icon icon="check" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Delivered</flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Numbered steps',
            'text' => '<code>status</code> marks each item <code>complete</code>, <code>current</code> or <code>incomplete</code>.',
            'code' => <<<'BLADE'
            <mds:timeline size="lg" align="start">
                <mds:timeline.item status="complete">
                    <mds:timeline.indicator>1</mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Cart</flux:heading>
                        <flux:text>Review the items and finalize quantities.</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="current">
                    <mds:timeline.indicator>2</mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Shipping</flux:heading>
                        <flux:text>Pick the address and a delivery window.</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="incomplete">
                    <mds:timeline.indicator>3</mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Payment</flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Horizontal',
            'code' => <<<'BLADE'
            <mds:timeline horizontal>
                <mds:timeline.item status="complete">
                    <mds:timeline.indicator><flux:icon icon="credit-card" variant="micro" /></mds:timeline.indicator>
                    <mds:timeline.content><flux:heading>Paid</flux:heading></mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="complete">
                    <mds:timeline.indicator><flux:icon icon="archive-box" variant="micro" /></mds:timeline.indicator>
                    <mds:timeline.content><flux:heading>Packed</flux:heading></mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="current">
                    <mds:timeline.indicator><flux:icon icon="truck" variant="micro" /></mds:timeline.indicator>
                    <mds:timeline.content><flux:heading>Shipping</flux:heading></mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item status="incomplete">
                    <mds:timeline.indicator><flux:icon icon="home" variant="micro" /></mds:timeline.indicator>
                    <mds:timeline.content><flux:heading>Delivered</flux:heading></mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Indicator alignment',
            'text' => 'With long content, <code>align</code> decides where the dot sits: <code>start</code>, <code>baseline</code>, <code>center</code> (the default) or <code>end</code>.',
            'code' => <<<'BLADE'
            <mds:timeline align="baseline">
                <mds:timeline.item>
                    <mds:timeline.indicator>1</mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Submit documents</flux:heading>
                        <flux:text>Upload a photo of your ID and your latest certificate so our team can review your file.</flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Full-width blocks',
            'text' => 'A <code>block</code> spans the whole row instead of sitting beside an indicator — for a quoted reply or an embedded card.',
            'code' => <<<'BLADE'
            <mds:timeline>
                <mds:timeline.item>
                    <mds:timeline.indicator><flux:icon icon="chat-bubble-left-right" variant="micro" /></mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>Mahdi <flux:text inline>left a comment</flux:text></flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item>
                    <mds:timeline.block class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-white/5">
                        <mds:timeline.subgrid class="p-3">
                            <flux:avatar size="xs" circle src="https://picsum.photos/seed/user/64/64" />
                            <div class="space-y-1">
                                <flux:heading>Support <flux:text inline>replied</flux:text></flux:heading>
                                <flux:text>Your parcel ships from the Tehran warehouse today.</flux:text>
                            </div>
                        </mds:timeline.subgrid>
                    </mds:timeline.block>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'On this RTL preview the rail sits on the right with no extra props — it follows the leading edge. The timeline has no digits or built-in strings of its own, so nothing reads <code>config(\'mds.persian_digits\')</code>; the date here is <code>mds:jalali-date</code> with <code>:fa="true"</code>.',
            'code' => <<<'BLADE'
            <mds:timeline>
                <mds:timeline.item>
                    <mds:timeline.indicator>
                        <flux:icon icon="shopping-bag" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>سفارش ثبت شد</flux:heading>
                        <flux:text><mds:jalali-date :date="now()->subDays(4)" :fa="true" /></flux:text>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item>
                    <mds:timeline.indicator>
                        <flux:icon icon="banknotes" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>پرداخت تأیید شد <flux:badge size="sm" color="lime">آنلاین</flux:badge></flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>

                <mds:timeline.item>
                    <mds:timeline.indicator color="green">
                        <flux:icon icon="check" variant="micro" />
                    </mds:timeline.indicator>
                    <mds:timeline.content>
                        <flux:heading>تحویل شد</flux:heading>
                    </mds:timeline.content>
                </mds:timeline.item>
            </mds:timeline>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:timeline', 'props' => [
            ['horizontal', 'Lays the timeline out along the inline axis. Default: <code>false</code>.'],
            ['align', 'Indicator alignment: <code>start</code>, <code>baseline</code>, <code>center</code>, <code>end</code>. Default: <code>center</code>.'],
            ['size', 'Options: <code>lg</code>.'],
        ]],
        ['name' => 'mds:timeline.item', 'props' => [
            ['status', 'Options: <code>complete</code>, <code>current</code>, <code>incomplete</code>.'],
            ['align', 'Overrides the timeline\'s alignment for this item.'],
            ['size', 'Overrides the timeline\'s size for this item.'],
        ]],
        ['name' => 'mds:timeline.indicator', 'props' => [
            ['color', 'Any Tailwind color.'],
            ['variant', 'Options: <code>bare</code> for no chrome around the glyph.'],
            ['status', 'Overrides the item\'s status for this indicator.'],
        ]],
        ['name' => 'mds:timeline.content', 'text' => 'The text beside an indicator.'],
        ['name' => 'mds:timeline.block', 'text' => 'A row that spans the full width, with no indicator.'],
        ['name' => 'mds:timeline.subgrid', 'text' => 'Aligns nested content with the timeline\'s own columns.'],
    ],
    'related' => ['stepper', 'jalali-date', 'chart'],
];

// ----------------------------------------------------------------- mds:chart

$pages['chart'] = [
    'group' => 'mds',
    'title' => 'mds:chart',
    'lede' => 'Monochrome dashboard charts, server-rendered as SVG — an open answer to a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A family of dashboard visualizers in one ink: rounded splines, pill bars, arc dials. Everything renders on the server — no chart library, no JSON payload, no script. The design follows Amicro\'s <a href="https://github.com/Subhan-code/Monocharts">Mono Charts</a> (MIT). Flux Pro\'s <code>flux:chart</code> is a client-side line-chart toolkit; <code>mds:chart</code> is a different, batteries-included take for static dashboard cards.',
            'code' => <<<'BLADE'
            <mds:chart
                label="Spline dynamics"
                badge="Line"
                :value="84"
                unit="k nodes"
                footer-start="Rounded caps"
                footer-end="84k peak"
                class="w-full max-w-sm"
            >
                <mds:chart.line :data="[24, 45, 38, 65, 52, 84]" :labels="['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']" />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'The card and the stage',
            'text' => 'Two layers, both optional. <code>mds:chart</code> is the card — label, badge, big stat, footer. The <code>mds:chart.*</code> stages draw the actual chart and work anywhere on their own: inside a <code>flux:card</code>, a table cell, or bare. The ink is <code>currentColor</code>, so one text utility recolors a whole chart.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-sm space-y-4">
                <mds:chart.line :data="[18, 34, 72, 89, 64, 48]" :labels="['02', '06', '10', '14', '18', '22']" area :dots="false" />
                <mds:chart.line :data="[18, 34, 72, 89, 64, 48]" area :dots="false" :axis="false" class="text-accent" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Line',
            'text' => 'The line is a monotone spline — smooth, but it never overshoots the data. <code>baseline</code> adds a dashed comparison series on the same axis, <code>area</code> pours a soft gradient under the curve, and <code>curve="straight"</code> switches to plain segments.',
            'code' => <<<'BLADE'
            <mds:chart
                label="Weekly sessions"
                badge="vs last week"
                :value="84"
                unit="k"
                footer-start="Dashed: last week"
                footer-end="84k peak"
                class="w-full max-w-sm"
            >
                <mds:chart.line
                    :data="[24, 45, 38, 65, 52, 84]"
                    :baseline="[18, 32, 29, 48, 41, 62]"
                    :labels="['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu']"
                    area
                />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'Bars',
            'text' => 'Bars are pills — fully rounded ends, one tone. A <code>secondary</code> series renders as a faint twin next to each bar. When an item is an <em>array</em>, its values stack: solid base, lighter tones upward, only the stack\'s outer ends rounded.',
            'code' => <<<'BLADE'
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <mds:chart label="Pill pillars" badge="Grouped" :value="422" unit="units">
                    <mds:chart.bars
                        :data="[45, 78, 62, 95, 88, 54]"
                        :secondary="[25, 40, 30, 55, 50, 28]"
                        :labels="['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
                    />
                </mds:chart>

                <mds:chart label="Stacked tones" badge="3 layers" :value="160" unit="cumulative">
                    <mds:chart.bars
                        :data="[[30, 25, 20], [45, 35, 25], [60, 40, 30], [75, 50, 35]]"
                        :labels="['Q1', 'Q2', 'Q3', 'Q4']"
                    />
                </mds:chart>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Funnel rows',
            'text' => '<code>horizontal</code> switches to HTML rows — label, pill track, value. Rows follow the page direction (they grow from the right in RTL), which makes them the funnel of choice for Persian checkouts.',
            'code' => <<<'BLADE'
            <mds:chart label="Stage funnel" badge="Pipeline" value="24%" unit="conversion" class="w-full max-w-sm">
                <mds:chart.bars horizontal :data="[100, 68, 42, 24]" :labels="['Visits', 'Signup', 'Active', 'Paid']" />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'Donut',
            'text' => 'Rounded segments separated by real gaps, shaded down a fixed tone ladder in segment order. The keys of <code>data</code> are the labels; the legend inherits each segment\'s tone. <code>value</code> and <code>label</code> fill the center.',
            'code' => <<<'BLADE'
            <mds:chart label="Rounded donut" badge="Soft arc caps" value="100%" unit="allocation" class="w-full max-w-sm">
                <mds:chart.donut
                    :data="['Core engine' => 45, 'UI layer' => 30, 'Assets' => 15, 'Other' => 10]"
                    value="100%"
                    label="Mono arc"
                />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'Gauge and bullet',
            'text' => 'The gauge is a 240° dial with pill ends over a faint track. The bullet rows compare a value against a target — the marker is the one accent-colored element in the family, sitting on <code>--color-accent</code>.',
            'code' => <<<'BLADE'
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <mds:chart label="Speedometer arc" badge="Gauge" value="84%" unit="performance">
                    <mds:chart.gauge :value="84" label="Target met" />
                </mds:chart>

                <mds:chart label="Bullet target" badge="Benchmark" :value="3" unit="targets">
                    <mds:chart.bullet :items="[
                        ['label' => 'Throughput', 'value' => 82, 'target' => 75],
                        ['label' => 'Latency', 'value' => 65, 'target' => 80],
                        ['label' => 'Uptime', 'value' => 95, 'target' => 90],
                    ]" />
                </mds:chart>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Radar',
            'text' => 'A polygon web: four grid rings, one spoke per key, the shape filled at 15% ink.',
            'code' => <<<'BLADE'
            <mds:chart label="Polygon web" badge="Radar" :value="85" unit="score" class="w-full max-w-sm">
                <mds:chart.radar :data="['Speed' => 90, 'Memory' => 75, 'Scale' => 85, 'Latency' => 95, 'IOPS' => 80]" />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'Activity heatmap',
            'text' => 'A contribution grid: values grade into five tones of the ink, columns follow the page direction, and hovering a tile reports its value in the callout row (Alpine, with a <code>title</code> fallback). <code>color="accent"</code> swaps the ladder onto the accent color.',
            'code' => <<<'BLADE'
            <mds:chart label="Activity heatmap" badge="14 weeks" :value="807" unit="contributions" class="w-full max-w-md">
                <mds:chart.heatmap
                    :data="array_map(fn ($i) => ($i * 7) % 13, range(1, 98))"
                    :labels="['Jan', 'Feb', 'Mar']"
                    color="accent"
                />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'KPI card recipe',
            'text' => 'There is no KPI component — it is the card doing what it already does: a stat, a <code>delta</code> (a leading minus turns it red), and a bare <code>mds:chart.sparkline</code>, which also works inline in tables and lists.',
            'code' => <<<'BLADE'
            <mds:chart label="KPI stat card" badge="Metric" value="$48,920" delta="+14.2%" footer-start="Rounded sparkline" footer-end="Monthly revenue" class="w-full max-w-sm">
                <mds:chart.sparkline :data="[30, 45, 35, 60, 50, 85, 75, 95]" area class="h-16" />
            </mds:chart>
            BLADE,
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches every digit the family renders: the card\'s stat and delta, axis ticks, bullet readouts, the heatmap\'s callout and its built-in hover hint. <code>:fa="true"</code> on the card flows into the stages inside it. The SVG plot plane itself never mirrors; the HTML stages (funnel rows, bullet, heatmap) follow the page direction.',
            'code' => <<<'BLADE'
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <mds:chart label="فروش ماهانه" badge="تومان" :value="48920" unit="هزار" delta="+14.2%" footer-start="شش ماه اخیر" footer-end="اوج در مرداد" :fa="true">
                    <mds:chart.line :data="[24, 45, 38, 65, 52, 84]" :labels="['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور']" area />
                </mds:chart>

                <mds:chart label="قیف فروش" badge="۴ مرحله" value="۲۴٪" unit="نرخ تبدیل" :fa="true">
                    <mds:chart.bars horizontal :data="[100, 68, 42, 24]" :labels="['بازدید', 'سبد خرید', 'پرداخت', 'خرید']" />
                </mds:chart>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:chart', 'props' => [
            ['label', 'Uppercase kicker line above the stat.'],
            ['badge', 'Small pill next to the label.'],
            ['value', 'The big stat. Numbers get thousands separators; strings ("84%") keep their shape with localized digits.'],
            ['unit', 'Faint suffix after the stat.'],
            ['delta', 'Change readout after the stat — green, or red when it starts with a minus.'],
            ['footer-start', 'Muted footer text on the leading edge.'],
            ['footer-end', 'Emphasized footer text on the trailing edge.'],
            ['fa', 'Persian digits for the stat and delta — inherited by the stages inside. Default: <code>config(\'mds.persian_digits\')</code>.'],
        ], 'slots' => [
            ['header', 'Extra content on the header\'s trailing edge — filters, a toggle.'],
            ['footer', 'Replaces the two footer props.'],
            ['default', 'The stage(s).'],
        ]],
        ['name' => 'mds:chart.line', 'props' => [
            ['data', 'Array of numbers.'],
            ['labels', 'Category labels under the plot, one per point.'],
            ['baseline', 'Second series, drawn dashed and faint on the same axis.'],
            ['area', 'Gradient fill under the curve. Default: <code>false</code>.'],
            ['dots', 'Point markers with a stage-colored halo. Default: <code>true</code>.'],
            ['curve', 'Options: <code>smooth</code> (monotone spline), <code>straight</code>. Default: <code>smooth</code>.'],
            ['axis', 'Y ticks, grid lines and x labels. Default: <code>true</code>.'],
            ['max', 'Pins the axis ceiling. Default: a "nice" ceiling above the data.'],
            ['width', 'ViewBox width. Default: <code>360</code>.'],
            ['height', 'ViewBox height. Default: <code>170</code>.'],
            ['fa', 'Persian digits on ticks and labels. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.bars', 'props' => [
            ['data', 'Array of numbers — or arrays, which stack into layers (bottom first).'],
            ['labels', 'Category labels.'],
            ['secondary', 'Faint second series next to each bar.'],
            ['horizontal', 'HTML rows that follow the page direction — the funnel layout. Default: <code>false</code>.'],
            ['axis', 'Y ticks, grid lines and x labels. Default: <code>true</code>.'],
            ['max', 'Pins the scale ceiling.'],
            ['width', 'ViewBox width. Default: <code>360</code>.'],
            ['height', 'ViewBox height. Default: <code>170</code>.'],
            ['fa', 'Persian digits. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.donut', 'props' => [
            ['data', 'Assoc array — labels to values.'],
            ['value', 'Center readout. Default: the values\' sum.'],
            ['label', 'Small line under the center readout.'],
            ['legend', 'Tone-matched legend under the ring. Default: <code>true</code>.'],
            ['size', 'ViewBox size. Default: <code>160</code>.'],
            ['thickness', 'Ring thickness. Default: <code>22</code>.'],
            ['fa', 'Persian digits. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.gauge', 'props' => [
            ['value', 'The dialed value.'],
            ['max', 'Full-dial value. Default: <code>100</code>.'],
            ['label', 'Small line under the readout.'],
            ['decimals', 'Decimals in the readout. Default: <code>0</code>.'],
            ['fa', 'Persian digits. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.radar', 'props' => [
            ['data', 'Assoc array — axis labels to values.'],
            ['max', 'The web\'s outer ring value. Default: <code>100</code>.'],
            ['fa', 'Persian digits in labels. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.bullet', 'props' => [
            ['items', 'Rows: <code>[[\'label\' => ..., \'value\' => ..., \'target\' => ...], ...]</code> — <code>target</code> optional.'],
            ['max', 'Full-track value. Default: <code>100</code>.'],
            ['unit', 'Suffix in the readouts. Default: <code>%</code>.'],
            ['fa', 'Persian digits. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.heatmap', 'props' => [
            ['data', 'Array of counts, column-major — every <code>rows</code> values are one column.'],
            ['rows', 'Cells per column. Default: <code>7</code>.'],
            ['labels', 'Labels spread across the top — usually months.'],
            ['color', 'Options: <code>accent</code>. Default: the ink.'],
            ['unit', 'Word in the hover callout. Default: "items" / مورد by language.'],
            ['callout', 'The hover-readout row (needs Alpine; tiles keep a <code>title</code> either way). Default: <code>true</code>.'],
            ['fa', 'Persian digits and built-in strings. Default: the enclosing card\'s, else the config.'],
        ]],
        ['name' => 'mds:chart.sparkline', 'props' => [
            ['data', 'Array of numbers — the sparkline auto-fits its own range.'],
            ['area', 'Gradient fill under the curve. Default: <code>false</code>.'],
            ['curve', 'Options: <code>smooth</code>, <code>straight</code>. Default: <code>smooth</code>.'],
            ['width', 'ViewBox width. Default: <code>120</code>.'],
            ['height', 'ViewBox height. Default: <code>28</code>.'],
        ], 'text' => 'A bare <code>&lt;svg&gt;</code> that stretches to any box (strokes stay 2px crisp) — size it with width/height utilities.'],
    ],
    'related' => ['price', 'jalali-date', 'timeline'],
];

// ----------------------------------------------------------- mds:popover

// --------------------------------------------------------------- mds:popover

$pages['popover'] = [
    'group' => 'mds',
    'title' => 'mds:popover',
    'lede' => 'A panel anchored to the button that opens it — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Flux\'s Popover component is Pro-only. This is a working replacement: a non-modal dialog anchored to its trigger, positioned from measured rectangles, teleported to <code>&lt;body&gt;</code> so no <code>overflow-hidden</code> ancestor can clip it. The preview is live — click the button.',
            'code' => <<<'BLADE'
            <mds:popover>
                <mds:popover.trigger>
                    <flux:button icon="bell">Notifications</flux:button>
                </mds:popover.trigger>

                <mds:popover.content class="w-72">
                    <flux:heading size="sm">Notifications</flux:heading>
                    <flux:text class="mt-2">Your order has shipped — it should arrive on Tuesday.</flux:text>
                </mds:popover.content>
            </mds:popover>
            BLADE,
            'note' => '<p><strong>A popover is not a menu.</strong> It holds arbitrary content — a form, a summary, a mini profile — and the keyboard walks through that content with <code>Tab</code>. For a list of commands or links, use <a href="dropdown.html"><code>flux:dropdown</code></a>, which gives you a real <code>menu</code> role and arrow-key navigation.</p>',
        ],
        [
            'name' => 'Placement',
            'text' => 'Position with <code>position</code> (<code>top</code> / <code>bottom</code> / <code>start</code> / <code>end</code>) and <code>align</code> (<code>start</code> / <code>center</code> / <code>end</code>). The sides are logical: <code>start</code> and <code>end</code> — and the alignment axis — mirror by themselves on an RTL page. Whatever you ask for, the panel flips to the opposite side when the preferred one runs out of room and stays clamped inside the viewport.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center justify-center gap-3">
                <mds:popover position="top" align="center">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">top / center</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-52"><flux:text class="text-sm">Above the trigger, centred on it.</flux:text></mds:popover.content>
                </mds:popover>

                <mds:popover position="bottom" align="end">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">bottom / end</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-52"><flux:text class="text-sm">Under the trigger, trailing edges aligned.</flux:text></mds:popover.content>
                </mds:popover>

                <mds:popover position="start" align="center">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">start / center</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-52"><flux:text class="text-sm">On the leading side — left here, right in RTL.</flux:text></mds:popover.content>
                </mds:popover>

                <mds:popover position="end" align="start">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">end / start</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-52"><flux:text class="text-sm">On the trailing side, top edges aligned.</flux:text></mds:popover.content>
                </mds:popover>
            </div>
            BLADE,
        ],
        [
            'name' => 'Arrow and offset',
            'text' => 'Add <code>arrow</code> for a pointer that tracks the middle of the trigger, wherever the panel was flipped or clamped to. <code>offset</code> is the gap in pixels between trigger and panel — <code>8</code> by default.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap items-center justify-center gap-3">
                <mds:popover arrow>
                    <mds:popover.trigger><flux:button size="sm">With an arrow</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-56"><flux:text class="text-sm">The arrow borrows the panel's own border and follows the side that was actually used.</flux:text></mds:popover.content>
                </mds:popover>

                <mds:popover arrow offset="20">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">Offset 20</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-56"><flux:text class="text-sm">Pushed twenty pixels away from its trigger.</flux:text></mds:popover.content>
                </mds:popover>

                <mds:popover offset="0">
                    <mds:popover.trigger><flux:button size="sm" variant="subtle">Offset 0</flux:button></mds:popover.trigger>
                    <mds:popover.content class="w-56"><flux:text class="text-sm">Flush against the trigger.</flux:text></mds:popover.content>
                </mds:popover>
            </div>
            BLADE,
        ],
        [
            'name' => 'A closable panel',
            'text' => '<code>closable</code> adds a labelled close button in the corner and reserves room for it. It comes last in the DOM on purpose, so opening the popover still lands focus on the first control of your content rather than on the dismiss button.',
            'code' => <<<'BLADE'
            <mds:popover position="bottom" align="start" arrow>
                <mds:popover.trigger>
                    <flux:button icon="funnel" variant="subtle">Filters</flux:button>
                </mds:popover.trigger>

                <mds:popover.content closable class="w-64">
                    <flux:heading size="sm" class="mb-3">Filters</flux:heading>

                    <div class="space-y-2">
                        <flux:checkbox label="In stock only" checked />
                        <flux:checkbox label="Free shipping" />
                        <flux:checkbox label="Discounted" />
                    </div>

                    <flux:button size="sm" variant="primary" class="mt-4 w-full">Apply</flux:button>
                </mds:popover.content>
            </mds:popover>
            BLADE,
        ],
        [
            'name' => 'Opening on hover',
            'text' => 'With <code>hover</code>, the panel also opens on hover (after 100ms, closing 300ms after the cursor leaves) and on keyboard focus. A click still works and <em>pins</em> the panel open, so a reader who committed to it is not chased away by the mouse. Keep hover popovers supplementary — a touch user only gets the click.',
            'code' => <<<'BLADE'
            <mds:popover hover position="top" align="center" arrow>
                <mds:popover.trigger>
                    <flux:button icon="question-mark-circle" variant="ghost">Size guide</flux:button>
                </mds:popover.trigger>

                <mds:popover.content class="w-64">
                    <flux:heading size="sm">How we measure</flux:heading>
                    <flux:text class="mt-2 text-sm">Chest is measured flat, across the garment, 2cm below the armhole. Between two sizes, take the larger.</flux:text>
                </mds:popover.content>
            </mds:popover>
            BLADE,
        ],
        [
            'name' => 'Any content, any width',
            'text' => 'The panel is a plain container: set its width with a class, cancel the padding with <code>!p-0</code> for an image-led or list-led layout, and put whatever you like inside — the keyboard walks through it with <code>Tab</code>, and tabbing off either end closes the popover and continues down the page.',
            'code' => <<<'BLADE'
            <mds:popover position="bottom" align="end" arrow>
                <mds:popover.trigger>
                    <flux:button variant="ghost">
                        <flux:avatar size="xs" src="https://i.pravatar.cc/48?img=12" />
                    </flux:button>
                </mds:popover.trigger>

                <mds:popover.content class="w-64 !p-0 overflow-hidden">
                    <div class="flex items-center gap-3 p-4">
                        <flux:avatar src="https://i.pravatar.cc/48?img=12" />
                        <div class="min-w-0">
                            <div class="truncate font-medium text-zinc-800 dark:text-white">Mahdi Majidzadeh</div>
                            <div class="truncate text-xs text-zinc-500 dark:text-zinc-400">m.majidzadeh@example.com</div>
                        </div>
                    </div>

                    <flux:separator />

                    <div class="flex flex-col p-2">
                        <flux:button size="sm" variant="ghost" icon="shopping-bag" class="justify-start">My orders</flux:button>
                        <flux:button size="sm" variant="ghost" icon="cog-6-tooth" class="justify-start">Settings</flux:button>
                        <flux:button size="sm" variant="ghost" icon="arrow-right-start-on-rectangle" class="justify-start">Sign out</flux:button>
                    </div>
                </mds:popover.content>
            </mds:popover>
            BLADE,
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'Placement is measured, not guessed: <code>start</code> and <code>end</code> read the component\'s own direction at open time, so the same markup mirrors on a Persian page. The one built-in string — the close button\'s label — follows <code>config(\'mds.persian_digits\')</code>, off in these docs; the <code>fa</code> prop forces it either way.',
            'code' => <<<'BLADE'
            <mds:popover position="bottom" align="start" arrow :fa="true">
                <mds:popover.trigger>
                    <flux:button icon="bell">اعلان‌ها</flux:button>
                </mds:popover.trigger>

                <mds:popover.content closable class="w-72">
                    <flux:heading size="sm">اعلان‌ها</flux:heading>
                    <flux:text class="mt-2 text-sm">سفارش شما ارسال شد و سه‌شنبه به دستتان می‌رسد.</flux:text>
                    <flux:separator class="my-3" />
                    <flux:link href="#!">مشاهده همه اعلان‌ها</flux:link>
                </mds:popover.content>
            </mds:popover>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:popover', 'text' => 'The root. Holds the open state and renders an inline wrapper; dispatches bubbling <code>mds-popover-open</code> and <code>mds-popover-close</code> events.', 'props' => [
            ['position', 'Preferred side: <code>top</code>, <code>bottom</code>, <code>start</code>, <code>end</code>. Flips when out of room. Default: <code>bottom</code>.'],
            ['align', 'Alignment along that side: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>start</code>.'],
            ['offset', 'Gap between trigger and panel, in pixels. Default: <code>8</code>.'],
            ['arrow', 'Adds a pointer toward the trigger; inherited by the content. Default: <code>false</code>.'],
            ['hover', 'Also open on hover and keyboard focus, not only on click. Default: <code>false</code>.'],
        ]],
        ['name' => 'mds:popover.trigger', 'text' => 'Wraps exactly one focusable element — a button or a link. It owns the click; <code>aria-haspopup</code>, <code>aria-expanded</code> and <code>aria-controls</code> are written onto that element at runtime. Takes no props.'],
        ['name' => 'mds:popover.content', 'text' => 'The panel: a non-modal <code>role="dialog"</code>, teleported to <code>&lt;body&gt;</code> and positioned <code>fixed</code>. Named by its trigger unless you pass your own <code>aria-label</code> or <code>aria-labelledby</code>. Set the width with a class.', 'props' => [
            ['arrow', 'Overrides the root\'s <code>arrow</code> for this panel.'],
            ['closable', 'Adds a close button in the corner. Default: <code>false</code>.'],
        ]],
    ],
    'related' => ['dropdown', 'tooltip', 'preview-card'],
];

// --------------------------------------------------------- mds:accordion

// ------------------------------------------------------------- mds:accordion

$pages['accordion'] = [
    'group' => 'mds',
    'title' => 'mds:accordion',
    'lede' => 'Collapsible sections built on native details/summary — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Flux\'s Accordion component is Pro-only. This is a working replacement, and it is deliberately thin: every item is a real <code>&lt;details&gt;</code>/<code>&lt;summary&gt;</code> pair, so opening and closing, <code>Enter</code> and <code>Space</code>, focus and browser find-in-page all come from the platform — and keep working with JavaScript off. The preview is live.',
            'code' => <<<'BLADE'
            <mds:accordion class="w-full max-w-lg">
                <mds:accordion.item heading="How long does delivery take?">
                    Orders leave the warehouse within two working days, and arrive within a week almost everywhere.
                </mds:accordion.item>

                <mds:accordion.item heading="Can I return something?">
                    Fourteen days from delivery, unused and in its original packaging. We pay the return postage.
                </mds:accordion.item>

                <mds:accordion.item heading="Is there a warranty?">
                    Every device carries a two-year warranty, handled by us rather than by the manufacturer.
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'One at a time',
            'text' => 'Add <code>exclusive</code> and opening one item closes the others. It is the native <code>&lt;details name&gt;</code> group doing the work, so it holds before any script has run; each accordion gets its own group, and nesting one inside another keeps the two apart. Pass <code>name</code> to choose the group name yourself.',
            'code' => <<<'BLADE'
            <mds:accordion exclusive name="shipping-faq" class="w-full max-w-lg">
                <mds:accordion.item heading="Standard shipping" expanded>
                    Three to five working days, free over 500,000 Toman.
                </mds:accordion.item>

                <mds:accordion.item heading="Express shipping">
                    Next working day in Tehran, ordered before 14:00.
                </mds:accordion.item>

                <mds:accordion.item heading="Pick-up point">
                    Collect from any of 120 lockers, held for 72 hours.
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Animated',
            'text' => '<code>transition</code> animates the height of the body over 200ms instead of snapping it open. The tween is skipped under <code>prefers-reduced-motion</code>, where the item toggles natively — and re-opening halfway through a collapse grows back from wherever the box has got to.',
            'code' => <<<'BLADE'
            <mds:accordion transition exclusive class="w-full max-w-lg">
                <mds:accordion.item heading="What is Majid DS?">
                    An RTL, Persian-first UI kit for Laravel Livewire, layered on top of Flux UI's free tier rather than replacing it.
                </mds:accordion.item>

                <mds:accordion.item heading="Do I still need Flux?">
                    Yes — the kit adds an <code>mds:</code> namespace next to <code>flux:</code>, and the two are meant to be used together.
                </mds:accordion.item>

                <mds:accordion.item heading="Which Pro components does it replace?">
                    Command, composer, colour picker, file upload, timeline, chart — plus this accordion and the popover.
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Open and disabled items',
            'text' => '<code>expanded</code> renders the <code>open</code> attribute server-side, so the first paint is already right — no flash of a closed section. <code>disabled</code> takes an item out of the tab order and refuses to open it; it is the state to use for a section that is not ready yet, not a way to hide content.',
            'code' => <<<'BLADE'
            <mds:accordion class="w-full max-w-lg">
                <mds:accordion.item heading="Payment methods" expanded>
                    Every Shetab card, plus instalments over three or six months.
                </mds:accordion.item>

                <mds:accordion.item heading="Invoices">
                    Download a VAT invoice from the order page as soon as it ships.
                </mds:accordion.item>

                <mds:accordion.item heading="Business accounts" disabled>
                    Coming soon.
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Headings with markup',
            'text' => 'The <code>heading</code> prop is a shortcut, and its text is escaped. When the heading needs more than a string — a badge, an icon, a count — write the two subcomponents out instead.',
            'code' => <<<'BLADE'
            <mds:accordion transition class="w-full max-w-lg">
                <mds:accordion.item expanded>
                    <mds:accordion.heading>
                        <span class="flex items-center gap-2">
                            <mds:icon icon="truck" variant="micro" class="size-4 text-zinc-400" />
                            Order 1481 — shipped
                            <flux:badge size="sm" color="green">2 items</flux:badge>
                        </span>
                    </mds:accordion.heading>

                    <mds:accordion.content>
                        <div class="flex items-center justify-between">
                            <span>Handed to the courier on Sunday.</span>
                            <flux:button size="sm" variant="subtle">Track</flux:button>
                        </div>
                    </mds:accordion.content>
                </mds:accordion.item>

                <mds:accordion.item>
                    <mds:accordion.heading>
                        <span class="flex items-center gap-2">
                            <mds:icon icon="clock" variant="micro" class="size-4 text-zinc-400" />
                            Order 1477 — delivered
                        </span>
                    </mds:accordion.heading>

                    <mds:accordion.content>Delivered last Wednesday and signed for.</mds:accordion.content>
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'In RTL',
            'rtl' => true,
            'text' => 'The accordion has no built-in strings and prints no digits of its own, so nothing here switches language — the layout simply mirrors: the chevron moves to the left edge and the heading text starts on the right. The copy, and any digits in it, are yours.',
            'code' => <<<'BLADE'
            <mds:accordion exclusive transition class="w-full max-w-lg">
                <mds:accordion.item heading="ارسال و تحویل" expanded>
                    سفارش‌ها حداکثر تا دو روز کاری ارسال می‌شوند و در بیشتر شهرها کمتر از یک هفته به دستتان می‌رسد.
                </mds:accordion.item>

                <mds:accordion.item heading="مرجوع کردن کالا">
                    تا هفت روز پس از تحویل، بدون پرسش. هزینه ارسال مرجوعی با ماست.
                </mds:accordion.item>

                <mds:accordion.item heading="گارانتی">
                    همه کالاهای دیجیتال دو سال گارانتی دارند.
                </mds:accordion.item>
            </mds:accordion>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:accordion', 'text' => 'The group. Renders a divided list; adds no behaviour of its own beyond the group name.', 'props' => [
            ['exclusive', 'Only one item open at a time, through the native <code>&lt;details name&gt;</code> group. Default: <code>false</code>.'],
            ['transition', 'Animate the open/close height. Default: <code>false</code>.'],
            ['name', 'The group name to use when <code>exclusive</code>. Generated per accordion when omitted.'],
        ]],
        ['name' => 'mds:accordion.item', 'text' => 'One <code>&lt;details&gt;</code>. With <code>heading</code> it renders the heading and content parts for you; otherwise put them in the slot yourself.', 'props' => [
            ['heading', 'Heading text (escaped). Omit it and compose the two subcomponents instead.'],
            ['expanded', 'Render the item open. Default: <code>false</code>.'],
            ['disabled', 'Not focusable, and refuses to toggle. Default: <code>false</code>.'],
        ]],
        ['name' => 'mds:accordion.heading', 'text' => 'The <code>&lt;summary&gt;</code> — the toggle. Takes the item\'s state from it; adds the rotating chevron. Takes no props of its own.'],
        ['name' => 'mds:accordion.content', 'text' => 'The body. Its padding sits on an inner box, so the height animation can run the outer element down to zero. Takes no props of its own.'],
    ],
    'related' => ['card', 'separator'],
];

// ------------------------------------------------------------ mds:slider

$pages['slider'] = [
    'group' => 'mds',
    'title' => 'mds:slider',
    'lede' => 'A one- or two-thumb range slider — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A native <code>&lt;input type="range"&gt;</code> under the kit\'s styling, so the keyboard, the pointer and the screen-reader announcement are the browser\'s own. <code>show-value</code> adds a live readout beside the label. The preview is live — drag it, or focus it and press the arrow keys.',
            'code' => <<<'BLADE'
            <mds:slider label="Volume" description="Arrow keys move it too." :value="60" :step="5" format="{value}%" show-value class="max-w-md" />
            BLADE,
            'align' => 'stretch',
            'note' => 'Nothing is clamped in JavaScript alone: the value is clamped to <code>min</code>/<code>max</code> and snapped to the <code>step</code> grid on the server as well, so the first paint, a no-JS form post and the thumb never disagree.',
        ],
        [
            'name' => 'Range',
            'text' => 'Add <code>range</code> and pass <code>[low, high]</code>. Two thumbs share one track and may not cross; a press on the bare track moves the nearer one.',
            'code' => <<<'BLADE'
            <mds:slider range label="Price range" :min="0" :max="5000000" :step="250000" :value="[500000, 3000000]" format="{value} Toman" show-value class="max-w-md" />
            BLADE,
            'align' => 'stretch',
            'note' => 'An inverted pair like <code>[80, 20]</code> is put back in order rather than rendering a crossed range.',
        ],
        [
            'name' => 'Bounds and step',
            'text' => 'Any numeric <code>min</code>, <code>max</code> and <code>step</code> — fractions included. A value off the grid is snapped to it.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider label="Rating" :min="1" :max="5" :step="0.5" :value="3.5" show-value />
                <mds:slider label="Temperature" :min="-20" :max="40" :step="1" :value="21" format="{value}°C" show-value />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Ticks',
            'text' => 'Not in Flux: <code>ticks</code> draws one mark per step while the span divides into at most twenty whole steps — and quietly draws none when they would crowd the track. An integer asks for that many, evenly spaced.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider label="Screen brightness" :min="1" :max="5" :value="3" ticks show-value />
                <mds:slider label="Progress" :ticks="5" :value="40" show-value />
            </div>
            BLADE,
            'align' => 'stretch',
            'note' => 'Ticks are decoration — <code>aria-hidden</code> and never a substitute for <code>step</code>, which is what actually constrains the value.',
        ],
        [
            'name' => 'Formatting the readout',
            'text' => '<code>format</code> is a <code>{value}</code> template. It shapes the readout and the <code>aria-valuetext</code> a screen reader announces, and leaves the machine value alone.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider label="Discount" :value="25" format="{value}%" show-value />
                <mds:slider label="Budget" :min="0" :max="500" :step="10" :value="120" format="{value} Toman" show-value />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Size and disabled',
            'text' => '<code>size="sm"</code> thins the track for dense filter panels. <code>disabled</code> dims the slider and disables every thumb.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider size="sm" label="Compact" :value="35" show-value />
                <mds:slider label="Data allowance" :value="10" :max="50" disabled description="Sign in to change this." />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Validation',
            'text' => 'An explicit <code>error</code> wins; otherwise the message comes from the error bag for <code>name</code> — and for a range, from <code>name.*</code> as well, because it posts as an array. Either way the thumbs get <code>aria-invalid</code> and the fill turns red.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider range name="price" label="Price range" :value="[10, 90]" error="Pick a narrower range." show-value />
                <mds:slider label="Weight" :value="80" invalid show-value />
            </div>
            BLADE,
            'align' => 'stretch',
            'note' => 'The message is rendered with the same markup as <code>flux:error</code> — <code>role="alert"</code>, linked from every thumb through <code>aria-describedby</code> — without depending on the session error bag.',
        ],
        [
            'name' => 'Livewire',
            'text' => 'A single slider binds the property as written. A <strong>range</strong> binds the two ends as dotted paths — <code>wire:model.live="price"</code> becomes <code>price.0</code> on the low thumb and <code>price.1</code> on the high one — so the bound property must be an array of two numbers.',
            'code' => <<<'BLADE'
            <mds:slider wire:model.live="price" range name="price" label="Price range" :min="0" :max="5000000" :step="250000" :value="[500000, 3000000]" format="{value} Toman" show-value class="max-w-md" />
            BLADE,
            'align' => 'stretch',
            'note' => '<code>public array $price = [500000, 3000000];</code> — a scalar property would break the binding. Modifiers are kept: <code>wire:model.live.debounce.500ms="price"</code> reaches both thumbs. A server-side change of the property flows back into the readout and the fill.',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the readout to Persian digits and the built-in thumb labels to Persian («مقدار — حداقل» / «مقدار — حداکثر»); <code>:fa="true"</code> does it for a single slider. The machine value stays Latin, whatever is shown.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-md space-y-6">
                <mds:slider :fa="true" range label="بازه قیمت" :min="0" :max="5000000" :step="250000" :value="[500000, 3000000]" format="{value} تومان" show-value description="قیمت‌ها به تومان است." />

                <mds:slider :fa="true" label="میزان صدا" :value="60" :step="5" format="{value}٪" ticks show-value />
            </div>
            BLADE,
            'align' => 'stretch',
            'note' => 'The track is RTL-aware: the fill runs from the start edge, and the arrow keys follow the visual order because the native control already does.',
        ],
    ],
    'reference' => [
        ['name' => 'mds:slider', 'props' => [
            ['value', 'Current value — a number, or <code>[low, high]</code> with <code>range</code>. Default: <code>min</code>.'],
            ['min', 'Lower bound. Default: <code>0</code>.'],
            ['max', 'Upper bound. Default: <code>100</code>.'],
            ['step', 'Granularity. Default: <code>1</code>.'],
            ['range', 'Two thumbs holding a low and a high value. Default: <code>false</code>.'],
            ['label', 'Label text. Also names the thumbs for screen readers.'],
            ['description', 'Help text under the track.'],
            ['name', 'Field name for a plain form post — <code>name[]</code> for a range — and the key the error bag is read with.'],
            ['size', '<code>sm</code> for a thinner track.'],
            ['disabled', 'Disables every thumb. Default: <code>false</code>.'],
            ['show-value', 'Live readout beside the label. Default: <code>false</code>.'],
            ['ticks', '<code>true</code> for one tick per step (up to twenty), or an integer count. Default: <code>false</code>.'],
            ['format', '<code>{value}</code> template for the readout and <code>aria-valuetext</code>.'],
            ['invalid', 'Applies error styling.'],
            ['error', 'Validation message. Falls back to the bag for <code>name</code> (and <code>name.*</code> for a range).'],
            ['fa', 'Persian digits and Persian built-in labels. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['wire:model', 'Binds the thumb. A range binds <code>property.0</code> and <code>property.1</code>.'],
        ]],
    ],
    'related' => ['quantity', 'progress', 'price'],
];

// ------------------------------------------------------- mds:time-picker

$pages['time-picker'] = [
    'group' => 'mds',
    'title' => 'mds:time-picker',
    'lede' => 'A typable time field over a list of times — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A combobox: type a time, or pick one from the list. The field shows the time the way your app reads it; a hidden input carries the machine value — always 24-hour <code>HH:MM</code> with Latin digits, whatever is displayed. The preview is live — click it and try typing <code>1430</code>.',
            'code' => <<<'BLADE'
            <mds:time-picker label="Delivery time" description="Half-hour slots." value="10:30" name="at" class="max-w-xs" />
            BLADE,
            'note' => 'Typing is lenient: <code>1430</code>, <code>14:30</code>, <code>2:30 pm</code>, <code>۱۴:۳۰</code> and <code>۲:۳۰ ب.ظ</code> all land on the same value. Text that is not a time reverts to the last valid one on <code>Enter</code> or blur.',
        ],
        [
            'name' => 'Step and bounds',
            'text' => '<code>step</code> is the number of minutes between the listed times; <code>min</code> and <code>max</code> cut the list down to the hours you actually open.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap gap-6">
                <mds:time-picker label="Appointment" value="09:15" :step="15" min="09:00" max="17:00" class="max-w-xs" />
                <mds:time-picker label="Reminder" value="07:00" :step="60" class="max-w-xs" />
            </div>
            BLADE,
            'note' => 'The list is only a shortcut — a typed time off the grid, like <code>10:07</code>, is kept as typed and simply matches no option. What it never escapes is <code>min</code>/<code>max</code>: a typed time outside them is clamped.',
        ],
        [
            'name' => 'Twelve-hour clock',
            'text' => '<code>hours="12"</code> shows 12-hour times with an AM/PM word — «ق.ظ» / «ب.ظ» when the kit is in Persian. The bound value stays 24-hour either way.',
            'code' => <<<'BLADE'
            <mds:time-picker label="Opens at" value="14:30" hours="12" :step="30" class="max-w-xs" />
            BLADE,
            'note' => 'In 12-hour mode the field does not ask for a numeric keyboard, because typing "pm" needs letters.',
        ],
        [
            'name' => 'Clearable, placeholder and icon',
            'text' => '<code>clearable</code> adds a clear button that appears once there is a value. <code>placeholder</code> replaces the default <code>--:--</code>, and <code>icon</code> swaps the leading clock — or removes it with <code>:icon="false"</code>.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap gap-6">
                <mds:time-picker label="Pick-up" value="18:00" clearable class="max-w-xs" />
                <mds:time-picker label="Closes at" placeholder="Choose a time" icon="moon" class="max-w-xs" />
                <mds:time-picker label="Bare" value="08:00" :icon="false" class="max-w-xs" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Keyboard',
            'text' => 'With the list open the arrows preview an option in the field and <code>Enter</code> commits it; <code>Home</code> and <code>End</code> jump to the ends. With the list closed the arrows nudge the value itself by <code>step</code>. <code>Alt</code> + arrow opens and closes the list, <code>Escape</code> reverts what you typed, and <code>Tab</code> leaves it committed.',
            'code' => <<<'BLADE'
            <mds:time-picker label="Try the arrow keys" value="12:00" :step="15" class="max-w-xs" />
            BLADE,
            'note' => 'Opening an empty picker scrolls the list to the time nearest the clock, so the useful options are the ones under the cursor.',
        ],
        [
            'name' => 'Size and disabled',
            'text' => '<code>size="sm"</code> matches a compact form row. <code>disabled</code> makes the whole control <code>inert</code> — the field, the list and the clear button all stop taking input.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap gap-6">
                <mds:time-picker label="Compact" value="08:30" size="sm" class="max-w-xs" />
                <mds:time-picker label="Locked" value="08:30" clearable disabled class="max-w-xs" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Validation',
            'text' => 'An explicit <code>error</code> wins; otherwise the message comes from the error bag for <code>name</code>. Either way the field gets <code>aria-invalid</code> and a red border.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap gap-6">
                <mds:time-picker name="at" label="Delivery time" error="Pick a time inside opening hours." min="09:00" max="17:00" class="max-w-xs" />
                <mds:time-picker label="Closing time" value="23:00" invalid class="max-w-xs" />
            </div>
            BLADE,
            'note' => 'The message uses the same markup as <code>flux:error</code> — <code>role="alert"</code> — without depending on the session error bag. <code>date_format:H:i</code> is the rule that matches what the field posts.',
        ],
        [
            'name' => 'Livewire',
            'text' => '<code>wire:model</code> reaches the hidden input and nothing else, so the bound property is a plain <code>HH:MM</code> string — <code>\'\'</code> once cleared.',
            'code' => <<<'BLADE'
            <mds:time-picker wire:model.live="at" name="at" label="Delivery time" value="10:30" min="09:00" max="21:00" clearable class="max-w-xs" />
            BLADE,
            'note' => 'A server-side change of the property is pulled back into the field by a <code>MutationObserver</code> on the hidden input, so <code>$this->at = \'09:00\'</code> in an action updates the displayed text too.',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the digits and the built-in words to Persian; <code>:fa="true"</code> does it for a single picker. The field and the list stay <code>dir="ltr"</code>, because a time reads hour-then-minute in both languages, and sit end-aligned in an RTL form. The hidden value is Latin regardless.',
            'code' => <<<'BLADE'
            <div class="flex flex-wrap gap-6">
                <mds:time-picker :fa="true" label="ساعت تحویل" description="بازه‌های نیم‌ساعته، از ۹ تا ۲۱." value="10:30" min="09:00" max="21:00" clearable class="max-w-xs" />

                <mds:time-picker :fa="true" label="یادآوری" value="14:30" hours="12" class="max-w-xs" />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'mds:time-picker', 'props' => [
            ['value', 'Initial time. <code>HH:MM</code>, <code>H:MM</code> or <code>HH:MM:SS</code>, Persian digits accepted; anything else renders empty.'],
            ['name', 'Field name for a plain form post, and the key the error bag is read with. Lands on the hidden input.'],
            ['label', 'Label text. Also names the listbox.'],
            ['description', 'Help text under the field.'],
            ['placeholder', 'Placeholder for the field. Default: <code>--:--</code>.'],
            ['step', 'Minutes between listed times, and the arrow-key increment. Default: <code>30</code>.'],
            ['min', 'Earliest listed time, and the floor a typed time is clamped to. Default: <code>00:00</code>.'],
            ['max', 'Latest listed time, and the ceiling a typed time is clamped to. Default: <code>23:59</code>.'],
            ['hours', '<code>12</code> for a 12-hour display with AM/PM. Default: <code>24</code>.'],
            ['clearable', 'Adds a clear button once there is a value. Default: <code>false</code>.'],
            ['disabled', 'Makes the whole control <code>inert</code>. Default: <code>false</code>.'],
            ['size', '<code>sm</code> for a compact field.'],
            ['icon', 'Leading icon. <code>false</code> removes it. Default: <code>clock</code>.'],
            ['invalid', 'Applies error styling.'],
            ['error', 'Validation message. Falls back to the bag for <code>name</code>.'],
            ['fa', 'Persian digits and Persian AM/PM words. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['wire:model', 'Binds the hidden <code>HH:MM</code> value to a Livewire property.'],
        ]],
    ],
    'related' => ['jalali-date', 'input', 'field'],
];

// -------------------------------------------------------------- mds:tabs

// ---------------------------------------------------------------- mds:tabs

$pages['tabs'] = [
    'group' => 'mds',
    'title' => 'mds:tabs',
    'lede' => 'Tabs and tab panels — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Flux\'s Tabs component is Pro-only. This is a working replacement: a <code>role="tablist"</code> of buttons, one panel per tab, the WAI-ARIA keyboard pattern, and — unlike most JavaScript tab widgets — the right panel already visible in the first paint, because the server resolves the active tab before Alpine runs. The previews are live: click a tab, or focus one and use the arrow keys.',
            'code' => <<<'BLADE'
            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="profile">Profile</mds:tab>
                    <mds:tab name="account">Account</mds:tab>
                    <mds:tab name="billing">Billing</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="profile">Your name, photo and public profile.</mds:tab.panel>
                <mds:tab.panel name="account">Email address, password and sessions.</mds:tab.panel>
                <mds:tab.panel name="billing">Plan, invoices and payment method.</mds:tab.panel>
            </mds:tab.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Variants',
            'text' => 'Three looks, same markup: the default underlined row, <code>segmented</code> (a raised pill on a track) and <code>pills</code>.',
            'code' => <<<'BLADE'
            <div class="space-y-6">
                <mds:tab.group>
                    <mds:tabs variant="segmented">
                        <mds:tab name="week">This week</mds:tab>
                        <mds:tab name="month">This month</mds:tab>
                        <mds:tab name="year">This year</mds:tab>
                    </mds:tabs>

                    <mds:tab.panel name="week">7 days of orders.</mds:tab.panel>
                    <mds:tab.panel name="month">30 days of orders.</mds:tab.panel>
                    <mds:tab.panel name="year">12 months of orders.</mds:tab.panel>
                </mds:tab.group>

                <mds:tab.group>
                    <mds:tabs variant="pills">
                        <mds:tab name="all">All</mds:tab>
                        <mds:tab name="open">Open</mds:tab>
                        <mds:tab name="closed">Closed</mds:tab>
                    </mds:tabs>

                    <mds:tab.panel name="all">Every ticket.</mds:tab.panel>
                    <mds:tab.panel name="open">Tickets waiting on us.</mds:tab.panel>
                    <mds:tab.panel name="closed">Tickets that are done.</mds:tab.panel>
                </mds:tab.group>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Icons, a small size, and a disabled tab',
            'text' => 'Leading and trailing icons go through <code>mds:icon</code>, so any heroicon name works. <code>size="sm"</code> shrinks the whole row. A <code>disabled</code> tab is a real disabled button: it is skipped by the arrow keys and is never chosen as the default.',
            'code' => <<<'BLADE'
            <mds:tab.group>
                <mds:tabs size="sm">
                    <mds:tab name="specs" icon="document-text">Specifications</mds:tab>
                    <mds:tab name="reviews" icon="star" icon-trailing="chat-bubble-left-right">Reviews</mds:tab>
                    <mds:tab name="qna" icon="question-mark-circle" disabled>Questions</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="specs">Weight, dimensions and warranty.</mds:tab.panel>
                <mds:tab.panel name="reviews">What buyers said.</mds:tab.panel>
                <mds:tab.panel name="qna">Ask the seller.</mds:tab.panel>
            </mds:tab.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Choosing the open tab',
            'text' => 'The group\'s <code>value</code> names the tab to open. Point it at a disabled or unknown tab and the first enabled tab wins instead — so a stale value from the database can never render a page with nothing selected.',
            'code' => <<<'BLADE'
            <mds:tab.group value="shipping">
                <mds:tabs variant="segmented">
                    <mds:tab name="cart">Cart</mds:tab>
                    <mds:tab name="shipping">Shipping</mds:tab>
                    <mds:tab name="payment">Payment</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="cart">3 items.</mds:tab.panel>
                <mds:tab.panel name="shipping">Where should it go?</mds:tab.panel>
                <mds:tab.panel name="payment">How would you like to pay?</mds:tab.panel>
            </mds:tab.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Binding to Livewire',
            'text' => 'Put <code>wire:model</code> on <code>mds:tabs</code>, not on the group: it is forwarded to the hidden input the list carries, and the bound property holds the active tab\'s name. Use <code>wire:model.live</code> when the server should load the panel\'s content, and add <code>name</code> when a plain form should post it.',
            'code' => <<<'BLADE'
            <mds:tab.group>
                <mds:tabs wire:model.live="tab" name="tab">
                    <mds:tab name="inbox">Inbox</mds:tab>
                    <mds:tab name="archive">Archive</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="inbox">Unread messages.</mds:tab.panel>
                <mds:tab.panel name="archive">Everything else.</mds:tab.panel>
            </mds:tab.group>
            BLADE,
            'align' => 'stretch',
            'note' => 'The panels stay in the DOM and are toggled with <code>hidden</code>, so a half-filled form in a hidden panel keeps its values. Flux takes the binding on the group instead; writing <code>wire:model</code> on <code>mds:tab.group</code> throws, rather than quietly landing on a div Livewire ignores.',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => 'The tablist\'s accessible name is the only built-in string, and it follows <code>config(\'mds.persian_digits\')</code> — «زبانه‌ها» in a Persian app, "Tabs" in these docs. Pass <code>label</code> to name a specific tablist, or <code>:fa="true"</code> to force the Persian default. Left and right arrow keys read the tablist\'s own direction, so in this preview they move right-to-left.',
            'code' => <<<'BLADE'
            <mds:tab.group :fa="true">
                <mds:tabs variant="pills" label="بخش‌های محصول">
                    <mds:tab name="مشخصات" icon="document-text">مشخصات</mds:tab>
                    <mds:tab name="نظرات" icon="star">نظرات</mds:tab>
                    <mds:tab name="پرسش" icon="chat-bubble-left-right">پرسش و پاسخ</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="مشخصات">وزن، ابعاد و مدت گارانتی.</mds:tab.panel>
                <mds:tab.panel name="نظرات">آنچه خریداران نوشته‌اند.</mds:tab.panel>
                <mds:tab.panel name="پرسش">پرسش خود را از فروشنده بپرسید.</mds:tab.panel>
            </mds:tab.group>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:tab.group', 'text' => 'Wraps the list and its panels, and holds the state.', 'props' => [
            ['value', 'Name of the tab to open. Falls back to the first enabled tab when it names a disabled or unknown one.'],
            ['fa', 'Persian built-in strings. Default: <code>config(\'mds.persian_digits\')</code>. Inherited by the list, the tabs and the panels.'],
        ]],
        ['name' => 'mds:tabs', 'text' => 'The <code>role="tablist"</code> row. Takes <code>wire:model</code>.', 'props' => [
            ['variant', 'One of <code>default</code>, <code>segmented</code>, <code>pills</code>. Default: <code>default</code>.'],
            ['size', '<code>sm</code> for a smaller row. Default: none.'],
            ['label', 'Accessible name for the tablist. Default: "Tabs", or زبانه‌ها in Persian.'],
            ['name', 'Form field name for the hidden input that carries the active tab.'],
            ['fa', 'Persian built-in strings. Inherited from the group.'],
        ]],
        ['name' => 'mds:tab', 'text' => 'One tab. The name is required.', 'props' => [
            ['name', 'Required. Pairs the tab with the panel of the same name.'],
            ['icon', 'Leading icon name.'],
            ['icon-trailing', 'Trailing icon name. Flux\'s <code>icon:trailing</code> spelling works too.'],
            ['disabled', 'Renders a disabled button, skipped by the keyboard. Default: <code>false</code>.'],
            ['fa', 'Persian built-in strings. Inherited from the group.'],
        ]],
        ['name' => 'mds:tab.panel', 'text' => 'The panel for one tab. The name is required.', 'props' => [
            ['name', 'Required. The name of the tab this panel belongs to.'],
            ['fa', 'Persian built-in strings. Inherited from the group.'],
        ]],
    ],
    'related' => ['navbar', 'card', 'command'],
];

// ------------------------------------------------------ mds:autocomplete

// Paste into bin/docs/mds.php (keep the file's slug order in sync with bin/docs/nav.php).
// Nav entry:  'autocomplete' => 'mds:autocomplete',

// --------------------------------------------------------- mds:autocomplete

$pages['autocomplete'] = [
    'group' => 'mds',
    'title' => 'mds:autocomplete',
    'lede' => 'A text field with suggestions — an open version of a Flux Pro component.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Flux\'s Autocomplete is Pro-only. This is a working replacement: a real <code>flux:input</code> that keeps whatever the user types, with a filtered list of suggestions underneath. Unlike a select it never forces a choice — the options are a shortcut, not a constraint. The preview is live; type a letter or two into it.',
            'code' => <<<'BLADE'
            <mds:autocomplete label="City" placeholder="Start typing a city..." class="max-w-sm">
                <mds:autocomplete.item>Tehran</mds:autocomplete.item>
                <mds:autocomplete.item>Mashhad</mds:autocomplete.item>
                <mds:autocomplete.item>Isfahan</mds:autocomplete.item>
                <mds:autocomplete.item>Karaj</mds:autocomplete.item>
                <mds:autocomplete.item>Shiraz</mds:autocomplete.item>
                <mds:autocomplete.item>Tabriz</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
            'note' => 'Matching folds Persian and Arabic spellings together (ي/ى → ی, ك → ک) and both digit scripts to Latin, so a shopper typing <span dir="rtl">كتاب</span> on an Arabic keyboard still finds <span dir="rtl">کتاب</span>.',
        ],
        [
            'name' => 'A value distinct from the label',
            'text' => 'By default picking an option writes its own text into the field. Give the item a <code>value</code> to write something else — a code, a canonical spelling, an id.',
            'code' => <<<'BLADE'
            <mds:autocomplete label="Province" placeholder="Province code" class="max-w-sm">
                <mds:autocomplete.item value="THR">Tehran</mds:autocomplete.item>
                <mds:autocomplete.item value="RKH">Razavi Khorasan</mds:autocomplete.item>
                <mds:autocomplete.item value="ESF">Isfahan</mds:autocomplete.item>
                <mds:autocomplete.item value="FRS">Fars</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Empty state',
            'text' => 'Without <code>empty</code> the list simply disappears when nothing matches. A bare <code>empty</code> attribute shows the built-in line, and a string replaces it. Type something that matches nothing to see it.',
            'code' => <<<'BLADE'
            <div class="grid w-full gap-6 sm:grid-cols-2">
                <mds:autocomplete label="Built-in message" placeholder="Try 'zzz'" empty>
                    <mds:autocomplete.item>Laptop</mds:autocomplete.item>
                    <mds:autocomplete.item>Headphones</mds:autocomplete.item>
                </mds:autocomplete>

                <mds:autocomplete label="Your own message" placeholder="Try 'zzz'" empty="No such product — try another spelling.">
                    <mds:autocomplete.item>Laptop</mds:autocomplete.item>
                    <mds:autocomplete.item>Headphones</mds:autocomplete.item>
                </mds:autocomplete>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Waiting for a few characters',
            'text' => '<code>min-chars</code> keeps the list shut until the query is long enough — the shape you want when every keystroke is a database query.',
            'code' => <<<'BLADE'
            <mds:autocomplete label="Search products" placeholder="At least two characters..." :min-chars="2" empty class="max-w-sm">
                <mds:autocomplete.item>Galaxy S24</mds:autocomplete.item>
                <mds:autocomplete.item>Galaxy A55</mds:autocomplete.item>
                <mds:autocomplete.item>Galaxy Watch 7</mds:autocomplete.item>
                <mds:autocomplete.item>Galaxy Buds 3</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Strict values',
            'text' => 'Free text is the default. <code>strict</code> makes the field settle on an option when it loses focus: text that matches one is snapped to that option\'s own spelling, and text that matches none is cleared. Type <code>tehran</code> and click away.',
            'code' => <<<'BLADE'
            <mds:autocomplete label="City" placeholder="Only a listed city is kept" strict empty class="max-w-sm">
                <mds:autocomplete.item>Tehran</mds:autocomplete.item>
                <mds:autocomplete.item>Mashhad</mds:autocomplete.item>
                <mds:autocomplete.item>Isfahan</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Icon, size, clearable and disabled options',
            'text' => 'The field is a <code>flux:input</code>, so <code>icon</code> and <code>size</code> go straight through. <code>clearable</code> adds a trailing button that empties the field, and an option can be <code>disabled</code> — still listed, but not selectable.',
            'code' => <<<'BLADE'
            <div class="w-full max-w-sm space-y-4">
                <mds:autocomplete label="Branch" icon="map-pin" clearable value="Tehran — Saadat Abad" empty>
                    <mds:autocomplete.item>Tehran — Saadat Abad</mds:autocomplete.item>
                    <mds:autocomplete.item>Tehran — Tajrish</mds:autocomplete.item>
                    <mds:autocomplete.item disabled>Karaj — Gohardasht (closed)</mds:autocomplete.item>
                </mds:autocomplete>

                <mds:autocomplete size="sm" icon="magnifying-glass" placeholder="Small" description="A compact field for a toolbar.">
                    <mds:autocomplete.item>Orders</mds:autocomplete.item>
                    <mds:autocomplete.item>Invoices</mds:autocomplete.item>
                </mds:autocomplete>

                <mds:autocomplete label="Locked" value="Tehran" disabled>
                    <mds:autocomplete.item>Tehran</mds:autocomplete.item>
                </mds:autocomplete>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Validation',
            'text' => 'Pass <code>error</code> for an explicit message. With a <code>name</code> (or a <code>wire:model</code> to take the name from) and no <code>error</code>, the component reads the message out of the validation bag itself.',
            'code' => <<<'BLADE'
            <mds:autocomplete label="City" name="city" error="Choose a city we deliver to." value="Bandar Abbas" class="max-w-sm">
                <mds:autocomplete.item>Tehran</mds:autocomplete.item>
                <mds:autocomplete.item>Mashhad</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With Livewire',
            'text' => 'The bound property holds the text, so the options can be queried on the server as it changes. Nothing else is needed — the component re-scans its own list when Livewire morphs new items in.',
            'code' => <<<'BLADE'
            <mds:autocomplete wire:model.live.debounce.300ms="city" name="city" label="City" icon="map-pin"
                              placeholder="Start typing..." :min-chars="2" clearable empty class="max-w-sm">
                <mds:autocomplete.item>Tehran</mds:autocomplete.item>
                <mds:autocomplete.item>Tabriz</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
            'note' => 'In the component: <code>public string $city = \'\';</code> and a computed property that queries with <code>$this->city</code>. Because the value is free text, validate it server-side even when <code>strict</code> is on — the browser is not the authority.',
        ],
        [
            'name' => 'Persian output',
            'rtl' => true,
            'text' => '<code>config(\'mds.persian_digits\')</code> — on by default in a real app, off in these docs — switches the built-in strings to Persian; <code>:fa="true"</code> does it for a single field. The popup, the highlight and the clear button all follow the page direction with no extra work.',
            'code' => <<<'BLADE'
            <mds:autocomplete :fa="true" label="شهر" placeholder="نام شهر را بنویسید..." icon="map-pin"
                              clearable empty strict class="max-w-sm">
                <mds:autocomplete.item>تهران</mds:autocomplete.item>
                <mds:autocomplete.item>مشهد</mds:autocomplete.item>
                <mds:autocomplete.item>اصفهان</mds:autocomplete.item>
                <mds:autocomplete.item>کرج</mds:autocomplete.item>
                <mds:autocomplete.item>شیراز</mds:autocomplete.item>
                <mds:autocomplete.item disabled>کیش (خارج از محدوده ارسال)</mds:autocomplete.item>
            </mds:autocomplete>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'mds:autocomplete', 'props' => [
            ['value', 'Initial text in the field.'],
            ['name', 'Field name for a plain form post, and the key the error bag is read with. Defaults to the <code>wire:model</code> expression.'],
            ['label', 'Label text. Also names the suggestion listbox.'],
            ['description', 'Help text under the field.'],
            ['placeholder', 'Placeholder for the field.'],
            ['icon', 'Leading icon name.'],
            ['clearable', 'Adds a button that empties the field. Default: <code>false</code>.'],
            ['min-chars', 'Characters needed before the list opens. Default: <code>0</code>.'],
            ['empty', 'Message shown when nothing matches. Absent, the list just hides; a bare attribute uses "No matches.", or موردی یافت نشد. in Persian.'],
            ['strict', 'On blur, snap the text to a matching option or clear it. Default: <code>false</code>.'],
            ['size', 'Field size: <code>sm</code> or <code>xs</code>.'],
            ['disabled', 'Disables the field and hides the popup.'],
            ['invalid', 'Applies error styling.'],
            ['error', 'Validation message. Falls back to the bag for <code>name</code>.'],
            ['fa', 'Persian built-in strings. Default: <code>config(\'mds.persian_digits\')</code>.'],
            ['wire:model', 'Binds the text to a Livewire property.'],
        ]],
        ['name' => 'mds:autocomplete.item', 'props' => [
            ['value', 'Text written into the field when the option is picked. Defaults to the option\'s own text.'],
            ['disabled', 'Lists the option but does not let it be picked. Default: <code>false</code>.'],
            ['fa', 'Inherited from the autocomplete.'],
        ]],
    ],
    'related' => ['input', 'select', 'command'],
];

return $pages;
