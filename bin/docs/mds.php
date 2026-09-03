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

return $pages;
