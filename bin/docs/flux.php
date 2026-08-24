<?php

/*
| The free Flux UI components that ship with this package.
|
| Only components whose Blade views are actually bundled appear here — Flux's
| Pro-only components (accordion, autocomplete, calendar, chart, editor, kanban
| and the rest) ship no code, so there is nothing to document or preview. Five
| of them have open mds equivalents; those live in the mds section.
|
| Every example's preview is the snippet below it, rendered.
*/

$pages = [];

// ------------------------------------------------------------------- avatar

$pages['avatar'] = [
    'group' => 'components',
    'title' => 'Avatar',
    'lede' => 'Show a user or entity with an image, initials, or an icon.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:avatar src="https://picsum.photos/seed/user/64/64" />',
        ],
        [
            'name' => 'Initials',
            'text' => 'Pass <code>initials</code> directly, or pass a <code>name</code> and Flux derives the initials from it. Persian names work as-is — the letters are rendered by the same Vazirmatn face as the rest of the page.',
            'code' => <<<'BLADE'
            <flux:avatar initials="MM" />
            <flux:avatar name="Sara Rezaei" />
            <flux:avatar initials="مم" />
            BLADE,
        ],
        [
            'name' => 'Colors',
            'text' => 'A fixed color, or <code>color="auto"</code> to derive one from the name so the same person always gets the same color.',
            'code' => <<<'BLADE'
            <flux:avatar name="Sara Rezaei" color="auto" />
            <flux:avatar initials="MM" color="blue" />
            <flux:avatar initials="AB" color="rose" />
            <flux:avatar initials="CD" color="green" />
            BLADE,
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <flux:avatar size="xs" initials="XS" />
            <flux:avatar size="sm" initials="SM" />
            <flux:avatar initials="MD" />
            <flux:avatar size="lg" initials="LG" />
            <flux:avatar size="xl" initials="XL" />
            BLADE,
        ],
        [
            'name' => 'Circle',
            'code' => <<<'BLADE'
            <flux:avatar circle src="https://picsum.photos/seed/user/64/64" />
            <flux:avatar circle initials="MM" color="indigo" />
            <flux:avatar circle icon="user" />
            BLADE,
        ],
        [
            'name' => 'Badge',
            'text' => 'A presence dot, a count, or any content in the corner.',
            'code' => <<<'BLADE'
            <flux:avatar circle badge badge:color="green" badge:circle initials="MM" />
            <flux:avatar circle badge="7" badge:color="red" initials="AB" />
            BLADE,
        ],
        [
            'name' => 'Group',
            'text' => 'Overlap a set of avatars with <code>flux:avatar.group</code>.',
            'code' => <<<'BLADE'
            <flux:avatar.group>
                <flux:avatar circle src="https://picsum.photos/seed/a1/48/48" />
                <flux:avatar circle src="https://picsum.photos/seed/a2/48/48" />
                <flux:avatar circle src="https://picsum.photos/seed/a3/48/48" />
                <flux:avatar circle initials="3+" />
            </flux:avatar.group>
            BLADE,
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:avatar',
            'props' => [
                ['src', 'Image URL.'],
                ['initials', 'Letters shown when there is no image.'],
                ['name', 'Full name; initials are derived from it when <code>initials</code> is absent.'],
                ['icon', 'Icon shown instead of initials (e.g. <code>user</code>).'],
                ['icon:variant', 'Icon variant. Default: <code>solid</code>.'],
                ['size', 'Options: <code>xs</code>, <code>sm</code>, <code>md</code>, <code>lg</code>, <code>xl</code>. Default: <code>md</code>.'],
                ['color', 'A Tailwind color, or <code>auto</code> to derive one from the name.'],
                ['circle', 'If <code>true</code>, fully rounded instead of a squircle.'],
                ['badge', 'Corner badge. <code>true</code> for a plain dot, or any content.'],
                ['tooltip', 'Tooltip content shown on hover.'],
                ['href', 'Renders the avatar as a link.'],
                ['as', 'Underlying element. Default: <code>div</code>.'],
                ['alt', 'Alt text for the image.'],
            ],
        ],
        ['name' => 'flux:avatar.group', 'text' => 'Overlaps its child avatars. Takes no props.'],
    ],
    'related' => ['profile', 'badge'],
];

// -------------------------------------------------------------------- badge

$pages['badge'] = [
    'group' => 'components',
    'title' => 'Badge',
    'lede' => 'Label a status, a count, or a category.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:badge>Pending</flux:badge>',
        ],
        [
            'name' => 'Colors',
            'text' => 'Any Tailwind color name. Without one, the badge is zinc.',
            'code' => <<<'BLADE'
            <flux:badge>Default</flux:badge>
            <flux:badge color="red">Cancelled</flux:badge>
            <flux:badge color="amber">Pending</flux:badge>
            <flux:badge color="lime">Ships today</flux:badge>
            <flux:badge color="green">Delivered</flux:badge>
            <flux:badge color="blue">Shipping</flux:badge>
            <flux:badge color="indigo">Featured</flux:badge>
            <flux:badge color="purple">Subscription</flux:badge>
            <flux:badge color="pink">Offer</flux:badge>
            BLADE,
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <flux:badge size="sm" color="lime">Small</flux:badge>
            <flux:badge color="lime">Base</flux:badge>
            <flux:badge size="lg" color="lime">Large</flux:badge>
            BLADE,
        ],
        [
            'name' => 'Solid',
            'text' => 'A filled badge for when the label needs to carry more weight.',
            'code' => <<<'BLADE'
            <flux:badge variant="solid" color="red">Sold out</flux:badge>
            <flux:badge variant="solid" color="amber" icon="bolt">Flash sale</flux:badge>
            <flux:badge variant="solid">Draft</flux:badge>
            BLADE,
        ],
        [
            'name' => 'Rounded',
            'code' => <<<'BLADE'
            <flux:badge rounded color="green" icon="check-circle">Verified</flux:badge>
            <flux:badge rounded variant="solid" color="blue">New</flux:badge>
            BLADE,
        ],
        [
            'name' => 'Icons',
            'text' => 'Leading and trailing icons. In RTL, "leading" is the right-hand side — Flux uses logical properties, so no direction-specific classes are needed.',
            'code' => <<<'BLADE'
            <flux:badge icon="truck" color="blue">Shipping</flux:badge>
            <flux:badge icon:trailing="arrow-up-right" color="zinc">Details</flux:badge>
            BLADE,
        ],
        [
            'name' => 'Dismissible',
            'text' => 'Add <code>flux:badge.close</code> for a removable filter chip.',
            'code' => <<<'BLADE'
            <flux:badge color="zinc">
                Under 500,000 Toman
                <flux:badge.close />
            </flux:badge>
            BLADE,
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:badge',
            'props' => [
                ['color', 'Any Tailwind color (e.g. <code>red</code>, <code>lime</code>). Default: zinc.'],
                ['variant', 'Options: <code>solid</code>. Default: soft/tinted.'],
                ['size', 'Options: <code>sm</code>, <code>lg</code>. Default: base.'],
                ['rounded', 'If <code>true</code>, a pill instead of a rounded rectangle.'],
                ['icon', 'Leading icon name.'],
                ['icon:trailing', 'Trailing icon name.'],
                ['icon:variant', 'Icon variant. Default: <code>micro</code>.'],
                ['label', 'Badge text, as an alternative to the slot.'],
                ['inset', 'Negative margins so the badge does not add height: <code>top</code>, <code>right</code>, <code>bottom</code>, <code>left</code>.'],
            ],
        ],
        ['name' => 'flux:badge.close', 'text' => 'A close button for a dismissible badge.', 'props' => [
            ['icon', 'Icon name. Default: <code>x-mark</code>.'],
        ]],
    ],
    'related' => ['callout', 'avatar'],
];

// -------------------------------------------------------------------- brand

$pages['brand'] = [
    'group' => 'components',
    'title' => 'Brand',
    'lede' => 'Your logo and product name, as a link home.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:brand href="/" name="Majid DS" logo="https://picsum.photos/seed/logo/48/48" />
            BLADE,
        ],
        [
            'name' => 'Logo slot',
            'text' => 'Pass any markup as the logo — an inline SVG, or a lettermark like the one this kit uses.',
            'code' => <<<'BLADE'
            <flux:brand href="/" name="Majid DS">
                <x-slot name="logo">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">M</div>
                </x-slot>
            </flux:brand>
            BLADE,
        ],
        [
            'name' => 'Logo only',
            'text' => 'Leave <code>name</code> off for a mark with no wordmark — useful in a collapsed sidebar.',
            'code' => '<flux:brand href="/" logo="https://picsum.photos/seed/logo/48/48" alt="Majid DS" />',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:brand',
            'props' => [
                ['name', 'Product name shown beside the logo. Omit for a logo-only mark.'],
                ['logo', 'Logo image URL, or use the <code>logo</code> slot for markup.'],
                ['logo:dark', 'Alternative logo used in dark mode.'],
                ['href', 'Link target. Default: <code>/</code>.'],
                ['alt', 'Alt text for the logo image.'],
            ],
            'slots' => [['logo', 'Custom logo markup, in place of the <code>logo</code> prop.']],
        ],
    ],
    'related' => ['sidebar', 'header'],
];

// -------------------------------------------------------------- breadcrumbs

$pages['breadcrumbs'] = [
    'group' => 'components',
    'title' => 'Breadcrumbs',
    'lede' => 'Show where the current page sits in the hierarchy.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#">Electronics</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#">Mobile phones</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>Galaxy S25</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            BLADE,
            'note' => 'The chevron between items points the way the page reads, so the same markup gives you <code>›</code> in LTR and <code>‹</code> in RTL with no extra work.',
        ],
        [
            'name' => 'Home icon',
            'text' => 'An item with only an <code>icon</code> and no text makes a compact root link.',
            'code' => <<<'BLADE'
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" icon="home" />
                <flux:breadcrumbs.item href="#">Orders</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>MDS-140529</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            BLADE,
        ],
        [
            'name' => 'Slash separator',
            'code' => <<<'BLADE'
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="#" separator="slash">Home</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="#" separator="slash">Orders</flux:breadcrumbs.item>
                <flux:breadcrumbs.item separator="slash">MDS-140529</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:breadcrumbs', 'text' => 'Wraps the items. Takes no props.'],
        [
            'name' => 'flux:breadcrumbs.item',
            'props' => [
                ['href', 'Link target. Without it the item is plain text — use that for the current page.'],
                ['icon', 'Icon name, shown before the label (or alone, if there is no label).'],
                ['icon:variant', 'Icon variant. Default: <code>mini</code>.'],
                ['separator', 'Separator style, e.g. <code>slash</code>. Default: a chevron.'],
            ],
        ],
    ],
    'related' => ['navbar', 'header'],
];

// ------------------------------------------------------------------- button

$pages['button'] = [
    'group' => 'components',
    'title' => 'Button',
    'lede' => 'Trigger an action or navigate somewhere.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:button>Add to cart</flux:button>',
        ],
        [
            'name' => 'Variants',
            'text' => 'The default is <code>outline</code>. <code>primary</code> follows your <code>--color-accent</code> token, so it changes with the theme.',
            'code' => <<<'BLADE'
            <flux:button variant="primary">Pay and place order</flux:button>
            <flux:button variant="filled">Add to cart</flux:button>
            <flux:button>Default</flux:button>
            <flux:button variant="ghost">Ghost</flux:button>
            <flux:button variant="subtle">Subtle</flux:button>
            <flux:button variant="danger">Delete</flux:button>
            BLADE,
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <flux:button size="xs">Extra small</flux:button>
            <flux:button size="sm">Small</flux:button>
            <flux:button>Base</flux:button>
            BLADE,
        ],
        [
            'name' => 'Icons',
            'text' => 'A leading icon, a trailing one, or both. Directional icons should follow the reading direction — see the note below.',
            'code' => <<<'BLADE'
            <flux:button icon="shopping-cart">Add to cart</flux:button>
            <flux:button icon:trailing="arrow-right">Continue to checkout</flux:button>
            <flux:button icon="truck" icon:trailing="chevron-down">Track</flux:button>
            BLADE,
            'note' => '<p><strong>In RTL, flip directional icons.</strong> Flux mirrors layout automatically, but an <code>arrow-right</code> is a picture of an arrow, not a logical direction — it stays pointing right. For a "next" affordance, choose the icon from the locale: <code>arrow-left</code> in Persian, <code>arrow-right</code> in English.</p>',
        ],
        [
            'name' => 'Square',
            'text' => 'An icon-only button. Always give it an <code>aria-label</code>.',
            'code' => <<<'BLADE'
            <flux:button icon="heart" square variant="ghost" aria-label="Favorite" />
            <flux:button icon="magnifying-glass" square aria-label="Search" />
            <flux:button icon="trash" square variant="danger" aria-label="Delete" />
            BLADE,
        ],
        [
            'name' => 'Keyboard shortcut',
            'text' => 'The <code>kbd</code> prop shows the shortcut inside the button.',
            'code' => '<flux:button kbd="⌘S" variant="filled">Save</flux:button>',
        ],
        [
            'name' => 'Loading',
            'text' => 'Buttons that trigger a Livewire action show a spinner while it runs. Set <code>:loading="false"</code> to opt out.',
            'code' => '<flux:button variant="primary" icon="loading">Placing order</flux:button>',
        ],
        [
            'name' => 'Full width',
            'code' => '<flux:button variant="primary" class="w-full">Confirm and pay</flux:button>',
            'align' => 'stretch',
        ],
        [
            'name' => 'Group',
            'text' => 'Join related buttons into one control with <code>flux:button.group</code>.',
            'code' => <<<'BLADE'
            <flux:button.group>
                <flux:button>List</flux:button>
                <flux:button>Grid</flux:button>
                <flux:button>Table</flux:button>
            </flux:button.group>
            BLADE,
        ],
        [
            'name' => 'As a link',
            'text' => 'Any <code>href</code> renders an anchor with the same styling.',
            'code' => '<flux:button href="#" icon:trailing="arrow-up-right">View invoice</flux:button>',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:button',
            'props' => [
                ['variant', 'Options: <code>primary</code>, <code>filled</code>, <code>outline</code>, <code>ghost</code>, <code>subtle</code>, <code>danger</code>. Default: <code>outline</code>.'],
                ['size', 'Options: <code>xs</code>, <code>sm</code>, <code>base</code>. Default: <code>base</code>.'],
                ['icon', 'Leading icon name.'],
                ['icon:trailing', 'Trailing icon name.'],
                ['icon:variant', 'Icon variant, e.g. <code>outline</code>, <code>solid</code>, <code>mini</code>, <code>micro</code>.'],
                ['square', 'If <code>true</code>, an icon-only square button.'],
                ['color', 'Custom Tailwind color for the button surface.'],
                ['kbd', 'Keyboard shortcut hint shown inside the button.'],
                ['loading', 'If <code>false</code>, no spinner while a Livewire action runs. Default: <code>true</code>.'],
                ['type', 'Options: <code>button</code>, <code>submit</code>, <code>reset</code>. Default: <code>button</code>.'],
                ['align', 'Content alignment. Default: <code>center</code>.'],
                ['inset', 'Negative margins so the button does not add height.'],
                ['href', 'Renders an anchor instead of a button.'],
            ],
        ],
        ['name' => 'flux:button.group', 'text' => 'Joins its child buttons into a single segmented control.'],
    ],
    'related' => ['dropdown', 'modal'],
];

// ------------------------------------------------------------------ callout

$pages['callout'] = [
    'group' => 'components',
    'title' => 'Callout',
    'lede' => 'Highlight important information or guide users toward key actions.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:callout icon="clock">
                <flux:callout.heading>Payment is awaiting confirmation</flux:callout.heading>

                <flux:callout.text>
                    The bank will report the result in a moment.
                    <flux:callout.link href="#">Learn more</flux:callout.link>
                </flux:callout.text>
            </flux:callout>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Shorthand',
            'text' => 'For a plain callout, <code>heading</code> and <code>text</code> save you the child components.',
            'code' => '<flux:callout icon="check-circle" variant="success" heading="Order placed successfully" text="Tracking code: MDS-140529" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Icon inside heading',
            'text' => 'For a more compact layout, move the icon onto the heading instead of the callout root.',
            'code' => <<<'BLADE'
            <flux:callout>
                <flux:callout.heading icon="exclamation-triangle">Only 2 left in stock</flux:callout.heading>
            </flux:callout>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Variants',
            'text' => 'Four semantic variants, each mapping to a color.',
            'code' => <<<'BLADE'
            <div class="space-y-3">
                <flux:callout variant="secondary" icon="information-circle" heading="Heads up" text="Your address has not been verified yet." />
                <flux:callout variant="success" icon="check-circle" heading="Payment received" text="Your order is being packed." />
                <flux:callout variant="warning" icon="exclamation-triangle" heading="Low stock" text="Only 2 items left at this price." />
                <flux:callout variant="danger" icon="x-circle" heading="Payment failed" text="Any amount charged is returned within 72 hours." />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Colors',
            'text' => 'Any Tailwind color, when the semantic variants do not fit.',
            'code' => <<<'BLADE'
            <div class="space-y-3">
                <flux:callout color="blue" icon="truck" heading="Free shipping over 1,000,000 Toman" />
                <flux:callout color="purple" icon="sparkles" heading="You have 3 gift cards to spend" />
                <flux:callout color="teal" icon="shield-check" heading="7-day returns on every order" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With actions',
            'text' => 'The <code>actions</code> slot puts buttons under the text.',
            'code' => <<<'BLADE'
            <flux:callout variant="secondary" icon="user-group">
                <flux:callout.heading>Team collaboration</flux:callout.heading>

                <flux:callout.text>Share projects, manage permissions, and collaborate in real time.</flux:callout.text>

                <x-slot name="actions">
                    <flux:button>Invite member</flux:button>
                    <flux:button variant="ghost">Manage team</flux:button>
                </x-slot>
            </flux:callout>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inline actions',
            'text' => 'With <code>inline</code>, the actions sit beside the text instead of below it.',
            'code' => <<<'BLADE'
            <flux:callout inline icon="arrow-up-circle" heading="A new version is available">
                <x-slot name="actions">
                    <flux:button size="sm">Update</flux:button>
                </x-slot>
            </flux:callout>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Dismissible',
            'text' => 'The <code>controls</code> slot holds anything that belongs in the top corner — usually a close button.',
            'code' => <<<'BLADE'
            <flux:callout variant="secondary" icon="megaphone" x-data="{ shown: true }" x-show="shown">
                <flux:callout.heading>Nowruz sale starts tomorrow</flux:callout.heading>

                <x-slot name="controls">
                    <flux:button icon="x-mark" variant="ghost" size="sm" square aria-label="Dismiss" x-on:click="shown = false" />
                </x-slot>
            </flux:callout>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:callout',
            'props' => [
                ['icon', 'Name of the icon displayed next to the heading (e.g. <code>clock</code>).'],
                ['icon:variant', 'Variant of that icon. Default: <code>mini</code>.'],
                ['variant', 'Options: <code>secondary</code>, <code>success</code>, <code>warning</code>, <code>danger</code>.'],
                ['color', 'Any Tailwind color (e.g. <code>blue</code>, <code>purple</code>). Default: <code>white</code>.'],
                ['inline', 'If <code>true</code>, actions appear inline. Default: <code>false</code>.'],
                ['heading', 'Shorthand for <code>flux:callout.heading</code>.'],
                ['text', 'Shorthand for <code>flux:callout.text</code>.'],
            ],
            'slots' => [
                ['icon', 'Custom icon markup next to the heading.'],
                ['actions', 'Buttons or links inside the callout.'],
                ['controls', 'Extra UI in the top corner (e.g. a close button).'],
            ],
        ],
        [
            'name' => 'flux:callout.heading',
            'props' => [
                ['icon', 'Moves the icon inside the heading instead of the callout root.'],
                ['icon:variant', 'Variant of that icon. Default: <code>mini</code>.'],
            ],
        ],
        ['name' => 'flux:callout.text', 'text' => 'The body copy. Takes no props.'],
        ['name' => 'flux:callout.link', 'props' => [
            ['external', 'If <code>true</code>, opens in a new tab (with <code>rel="noopener"</code>).'],
        ]],
    ],
    'related' => ['toast', 'card'],
];

// --------------------------------------------------------------------- card

$pages['card'] = [
    'group' => 'components',
    'title' => 'Card',
    'lede' => 'A bordered surface for grouping related content.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:card class="space-y-2">
                <flux:heading size="lg">Order summary</flux:heading>
                <flux:text>3 items, shipping to Tehran.</flux:text>
            </flux:card>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Sizes',
            'text' => '<code>size="sm"</code> tightens the padding — good for dense dashboards.',
            'code' => <<<'BLADE'
            <div class="space-y-3">
                <flux:card size="sm">Small padding</flux:card>
                <flux:card>Default padding</flux:card>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With actions',
            'code' => <<<'BLADE'
            <flux:card class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading>Delete account</flux:heading>
                        <flux:text class="mt-1">This cannot be undone.</flux:text>
                    </div>

                    <flux:badge color="red" size="sm">Danger</flux:badge>
                </div>

                <flux:button variant="danger" size="sm">Delete account</flux:button>
            </flux:card>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:card',
            'props' => [
                ['variant', 'Options: <code>outline</code>. Default: <code>outline</code>.'],
                ['size', 'Options: <code>sm</code>. Default: base padding.'],
            ],
        ],
    ],
    'related' => ['callout', 'table'],
];

// ----------------------------------------------------------------- checkbox

$pages['checkbox'] = [
    'group' => 'components',
    'title' => 'Checkbox',
    'lede' => 'Select one or more options from a set.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:checkbox label="In stock only" />',
        ],
        [
            'name' => 'With a description',
            'code' => '<flux:checkbox label="Order status" description="An SMS at every shipping step" checked />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Group',
            'text' => '<code>flux:checkbox.group</code> adds the shared label and spacing.',
            'code' => <<<'BLADE'
            <flux:checkbox.group label="Notifications">
                <flux:checkbox label="Order status" checked />
                <flux:checkbox label="Discounts and offers" />
                <flux:checkbox label="Weekly newsletter" />
            </flux:checkbox.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Check all',
            'text' => '<code>flux:checkbox.all</code> toggles every checkbox in the group and shows an indeterminate state when only some are checked.',
            'code' => <<<'BLADE'
            <flux:checkbox.group>
                <flux:checkbox.all label="Select all" />

                <flux:separator variant="subtle" class="my-2" />

                <flux:checkbox label="Galaxy S25" checked />
                <flux:checkbox label="AirSound Pro" />
                <flux:checkbox label="Fit Band 8" />
            </flux:checkbox.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Variants',
            'text' => 'Cards, buttons and pills turn the group into a selectable set instead of a list.',
            'code' => <<<'BLADE'
            <div class="space-y-6">
                <flux:checkbox.group label="Cards" variant="cards">
                    <flux:checkbox label="Courier" description="Same-day delivery" checked />
                    <flux:checkbox label="Priority post" description="2 to 4 business days" />
                </flux:checkbox.group>

                <flux:checkbox.group label="Pills" variant="pills">
                    <flux:checkbox label="Phones" checked />
                    <flux:checkbox label="Laptops" />
                    <flux:checkbox label="Cameras" />
                </flux:checkbox.group>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Disabled',
            'code' => <<<'BLADE'
            <flux:checkbox label="Gift wrapping" description="Unavailable for this seller" disabled />
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:checkbox',
            'props' => [
                ['label', 'Label beside the box.'],
                ['description', 'Smaller text under the label.'],
                ['variant', 'Options: <code>default</code>, <code>cards</code>, <code>buttons</code>, <code>pills</code>.'],
                ['checked', 'Checked state for a plain form.'],
                ['disabled', 'Disables the input.'],
                ['value', 'Submitted value.'],
                ['wire:model', 'Binds to a Livewire property.'],
            ],
        ],
        [
            'name' => 'flux:checkbox.group',
            'props' => [
                ['label', 'Group label.'],
                ['description', 'Group description.'],
                ['variant', 'Applied to every checkbox in the group.'],
            ],
        ],
        ['name' => 'flux:checkbox.all', 'text' => 'Toggles every checkbox in the group; renders indeterminate when the selection is partial.'],
    ],
    'related' => ['radio', 'switch'],
];

// ----------------------------------------------------------------- dropdown

$pages['dropdown'] = [
    'group' => 'components',
    'title' => 'Dropdown',
    'lede' => 'A menu of actions or options, anchored to a trigger.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'The previews on this page are live — open them.',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down">Order actions</flux:button>

                <flux:menu>
                    <flux:menu.item icon="eye">View details</flux:menu.item>
                    <flux:menu.item icon="printer">Print invoice</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="trash" variant="danger">Cancel order</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Position and align',
            'text' => '<code>align</code> is logical — <code>start</code> and <code>end</code> follow the reading direction. <code>position</code> takes physical sides (<code>top</code>, <code>right</code>, <code>bottom</code>, <code>left</code>).',
            'code' => <<<'BLADE'
            <flux:dropdown position="top" align="end">
                <flux:button icon:trailing="chevron-up">Opens upward</flux:button>

                <flux:menu>
                    <flux:menu.item>Newest</flux:menu.item>
                    <flux:menu.item>Cheapest</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Submenus',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down">Sort</flux:button>

                <flux:menu>
                    <flux:menu.item icon="eye">View details</flux:menu.item>

                    <flux:menu.submenu heading="Sort by" icon="bars-arrow-down">
                        <flux:menu.radio checked>Newest</flux:menu.radio>
                        <flux:menu.radio>Cheapest</flux:menu.radio>
                        <flux:menu.radio>Best selling</flux:menu.radio>
                    </flux:menu.submenu>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Checkboxes and radios',
            'text' => 'A menu can hold state instead of only actions.',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon="funnel" icon:trailing="chevron-down">Filters</flux:button>

                <flux:menu>
                    <flux:menu.checkbox checked>In stock</flux:menu.checkbox>
                    <flux:menu.checkbox>Free shipping</flux:menu.checkbox>
                    <flux:menu.checkbox>Discounted</flux:menu.checkbox>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Groups and headings',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon:trailing="chevron-down">Account</flux:button>

                <flux:menu>
                    <flux:menu.group heading="Account">
                        <flux:menu.item icon="user">My profile</flux:menu.item>
                        <flux:menu.item icon="shopping-bag" kbd="⌘O">My orders</flux:menu.item>
                    </flux:menu.group>

                    <flux:menu.separator />

                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">Sign out</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Navmenu',
            'text' => 'Use <code>flux:navmenu</code> instead of <code>flux:menu</code> when the items are links rather than actions.',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon="bars-2" square aria-label="Menu" />

                <flux:navmenu>
                    <flux:navmenu.item href="#" icon="home">Home</flux:navmenu.item>
                    <flux:navmenu.item href="#" icon="fire">Flash deals</flux:navmenu.item>
                    <flux:navmenu.separator />
                    <flux:navmenu.item href="#" icon="chat-bubble-left-right">Support</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
            BLADE,
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:dropdown',
            'props' => [
                ['position', 'Options: <code>top</code>, <code>right</code>, <code>bottom</code>, <code>left</code>. Default: <code>bottom</code>.'],
                ['align', 'Options: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>start</code>.'],
                ['gap', 'Pixel gap between trigger and panel. Default: <code>5</code>.'],
                ['offset', 'Pixel shift along the aligned edge. Default: <code>0</code>.'],
                ['hover', 'Opens on hover instead of click.'],
            ],
        ],
        ['name' => 'flux:menu', 'text' => 'The panel for action items.'],
        [
            'name' => 'flux:menu.item',
            'props' => [
                ['icon', 'Leading icon name.'],
                ['icon:trailing', 'Trailing icon name.'],
                ['icon:variant', 'Icon variant. Default: <code>mini</code>.'],
                ['variant', 'Options: <code>danger</code>. Default: <code>default</code>.'],
                ['kbd', 'Keyboard shortcut hint on the trailing edge.'],
                ['suffix', 'Extra content on the trailing edge.'],
                ['value', 'Value for checkbox and radio items.'],
                ['href', 'Renders the item as a link.'],
            ],
        ],
        ['name' => 'flux:menu.submenu', 'props' => [
            ['heading', 'Submenu label.'],
            ['icon', 'Leading icon name.'],
            ['keep-open', 'Keeps the parent menu open on select. Default: <code>false</code>.'],
        ]],
        ['name' => 'flux:menu.separator', 'text' => 'A dividing line between items.'],
        ['name' => 'flux:menu.group', 'props' => [['heading', 'Group label above the items.']]],
        ['name' => 'flux:menu.checkbox', 'text' => 'A menu item with a checkbox. Takes <code>checked</code>, <code>value</code>, <code>wire:model</code>.'],
        ['name' => 'flux:menu.radio', 'text' => 'A menu item with a radio. Takes <code>checked</code>, <code>value</code>, <code>wire:model</code>.'],
    ],
    'related' => ['navmenu', 'button', 'modal'],
];

// ---------------------------------------------------------------- field set

$pages['field'] = [
    'group' => 'components',
    'title' => 'Field',
    'lede' => 'Wire a label, description and validation error to an input.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Most inputs take <code>label</code> and <code>description</code> props directly. Reach for <code>flux:field</code> when you need to compose the parts yourself.',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label>Mobile number</flux:label>
                <flux:description>A verification code will be sent to this number.</flux:description>

                <flux:input type="tel" placeholder="0912 345 6789" />

                <flux:error name="phone" />
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inline',
            'text' => '<code>variant="inline"</code> puts the label beside the control — the usual shape for a switch or a single checkbox.',
            'code' => <<<'BLADE'
            <div class="space-y-3">
                <flux:field variant="inline">
                    <flux:label>Free shipping</flux:label>
                    <flux:switch checked />
                </flux:field>

                <flux:field variant="inline">
                    <flux:label>In stock only</flux:label>
                    <flux:switch />
                </flux:field>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Required and badges',
            'text' => 'A label can carry a badge, or aside content on the trailing edge.',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label badge="Required">Full name</flux:label>
                <flux:input />
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Fieldset',
            'text' => 'Group several fields under one legend.',
            'code' => <<<'BLADE'
            <flux:fieldset>
                <flux:legend>Shipping address</flux:legend>

                <div class="space-y-4">
                    <flux:input label="Street" />
                    <flux:input label="City" value="Tehran" />
                </div>
            </flux:fieldset>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:field', 'props' => [
            ['variant', 'Options: <code>block</code>, <code>inline</code>. Default: <code>block</code>.'],
        ]],
        ['name' => 'flux:label', 'props' => [
            ['badge', 'Badge text after the label, e.g. <code>Required</code>.'],
            ['aside', 'Content pinned to the trailing edge of the label row.'],
            ['trailing', 'Content directly after the label text.'],
            ['sr-only', 'Hides the label visually but keeps it for screen readers.'],
        ]],
        ['name' => 'flux:description', 'props' => [
            ['sr-only', 'Hides the description visually but keeps it for screen readers.'],
        ]],
        ['name' => 'flux:error', 'props' => [
            ['name', 'The validation key to show errors for.'],
            ['message', 'An explicit message instead of one from the error bag.'],
            ['bag', 'Error bag name. Default: <code>default</code>.'],
            ['icon', 'Icon name. Default: <code>exclamation-triangle</code>.'],
        ]],
        ['name' => 'flux:fieldset', 'props' => [
            ['legend', 'Shorthand for <code>flux:legend</code>.'],
            ['description', 'Description under the legend.'],
        ]],
        ['name' => 'flux:legend', 'text' => 'The fieldset caption. Takes no props.'],
    ],
    'related' => ['input', 'select', 'switch'],
];

// ------------------------------------------------------------------ heading

$pages['heading'] = [
    'group' => 'components',
    'title' => 'Heading',
    'lede' => 'Section titles, with a matching subheading.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <div>
                <flux:heading>Order summary</flux:heading>
                <flux:subheading>3 items, shipping to Tehran.</flux:subheading>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="space-y-2">
                <flux:heading size="xl">Extra large</flux:heading>
                <flux:heading size="lg">Large</flux:heading>
                <flux:heading>Base</flux:heading>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Heading level',
            'text' => 'Size is visual; <code>level</code> sets the actual <code>h1</code>–<code>h6</code> element, so the document outline stays correct regardless of how big the text looks.',
            'code' => '<flux:heading size="xl" level="1">Page title</flux:heading>',
            'align' => 'stretch',
        ],
        [
            'name' => 'Accent',
            'text' => 'Paints the heading in your accent color.',
            'code' => '<flux:heading size="lg" accent>Flash deals</flux:heading>',
            'align' => 'stretch',
        ],
        [
            'name' => 'With Persian text',
            'text' => 'Headings pick up Vazirmatn from the <code>--font-sans</code> token the kit sets, so Persian and Latin headings share one type scale.',
            'code' => <<<'BLADE'
            <div dir="rtl" class="space-y-1">
                <flux:heading size="xl">سفارش‌های من</flux:heading>
                <flux:subheading>۳ سفارش در جریان است.</flux:subheading>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:heading', 'props' => [
            ['size', 'Options: <code>base</code>, <code>lg</code>, <code>xl</code>. Default: <code>base</code>.'],
            ['level', 'Renders <code>h1</code>–<code>h6</code> instead of a <code>div</code>.'],
            ['accent', 'If <code>true</code>, uses the accent color.'],
        ]],
        ['name' => 'flux:subheading', 'props' => [
            ['size', 'Options: <code>sm</code>, <code>base</code>, <code>lg</code>, <code>xl</code>. Default: <code>base</code>.'],
        ]],
    ],
    'related' => ['text', 'separator'],
];

// --------------------------------------------------------------------- icon

$pages['icon'] = [
    'group' => 'components',
    'title' => 'Icon',
    'lede' => 'Flux ships the full heroicons set; this kit adds Hugeicons on top.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4 text-zinc-600 dark:text-zinc-300">
                <flux:icon icon="shopping-cart" />
                <flux:icon icon="truck" />
                <flux:icon icon="credit-card" />
                <flux:icon icon="gift" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Variants',
            'text' => 'Four sizes of the same glyph set. <code>outline</code> is 24px, <code>solid</code> is filled 24px, <code>mini</code> is 20px and <code>micro</code> is 16px.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4 text-zinc-600 dark:text-zinc-300">
                <flux:icon icon="star" variant="outline" />
                <flux:icon icon="star" variant="solid" class="text-amber-400" />
                <flux:icon icon="star" variant="mini" />
                <flux:icon icon="star" variant="micro" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Sizing',
            'text' => 'Size with Tailwind classes; the icon inherits <code>currentColor</code>.',
            'code' => <<<'BLADE'
            <div class="flex items-end gap-3 text-zinc-600 dark:text-zinc-300">
                <flux:icon icon="bell" class="size-4" />
                <flux:icon icon="bell" class="size-6" />
                <flux:icon icon="bell" class="size-8" />
                <flux:icon icon="bell" class="size-10 text-accent" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Shorthand',
            'text' => 'Every icon is also a component of its own.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4 text-zinc-600 dark:text-zinc-300">
                <flux:icon.heart variant="solid" class="text-red-500" />
                <flux:icon.map-pin />
                <flux:icon.loading />
            </div>
            BLADE,
        ],
        [
            'name' => 'Hugeicons',
            'text' => 'This kit replaces heroicons with <a href="https://hugeicons.com">Hugeicons</a> across every <code>mds:*</code> component, and gives you <code>mds:icon</code> to use them directly. heroicon names still work through an alias map.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4 text-zinc-600 dark:text-zinc-300">
                <mds:icon icon="shopping-cart-01" />
                <mds:icon icon="truck-delivery" />
                <mds:icon icon="discount-tag-01" />
                <mds:icon icon="wallet-01" />
            </div>
            BLADE,
            'note' => 'See <a href="../mds/mds-icon.html">mds:icon</a> for stroke widths, the alias map and registering Pro styles.',
        ],
    ],
    'reference' => [
        ['name' => 'flux:icon', 'props' => [
            ['icon', 'Icon name, e.g. <code>shopping-cart</code>.'],
            ['variant', 'Options: <code>outline</code>, <code>solid</code>, <code>mini</code>, <code>micro</code>. Default: <code>outline</code>.'],
        ]],
        ['name' => 'flux:icon.{name}', 'text' => 'Shorthand for a specific icon, e.g. <code>flux:icon.heart</code>.'],
    ],
    'related' => ['button', 'badge'],
];

// -------------------------------------------------------------------- input

$pages['input'] = [
    'group' => 'components',
    'title' => 'Input',
    'lede' => 'A single-line text field, with the affordances forms usually need.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:input label="Full name" placeholder="Sara Rezaei" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Description',
            'code' => '<flux:input label="Mobile number" type="tel" placeholder="0912 345 6789" description="A verification code will be sent to this number." />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Icons',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:input icon="magnifying-glass" placeholder="Search products..." />
                <flux:input icon:trailing="credit-card" placeholder="Card number" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Clearable, copyable, viewable',
            'text' => 'Three one-word props that add the button most text fields eventually want.',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:input label="Search" value="Galaxy" icon="magnifying-glass" clearable />
                <flux:input label="Referral code" value="MAJID-1405" copyable />
                <flux:input label="Password" type="password" value="secret123" viewable />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Keyboard shortcut',
            'code' => '<flux:input placeholder="Search everything..." icon="magnifying-glass" kbd="⌘K" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:input size="sm" placeholder="Small" />
                <flux:input placeholder="Base" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Invalid',
            'text' => 'With Livewire, validation errors mark the field automatically. <code>invalid</code> is the manual escape hatch.',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input value="not-an-email" invalid />
                <flux:error name="email" message="Enter a valid email address." />
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Disabled and readonly',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:input label="Seller" value="Digikala" disabled />
                <flux:input label="Order number" value="MDS-140529" readonly />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'File',
            'code' => '<flux:input type="file" label="Product image" />',
            'align' => 'stretch',
            'note' => 'For drag-and-drop, multiple files and an upload progress bar, use <a href="../mds/file-upload.html">mds:file-upload</a> — the open alternative to Flux Pro\'s File Upload.',
        ],
        [
            'name' => 'Group with prefix and suffix',
            'text' => 'Attach text or a control to either edge. Both edges are logical, so they swap in RTL.',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:input.group>
                    <flux:input.group.prefix>https://</flux:input.group.prefix>
                    <flux:input placeholder="your-shop.ir" />
                </flux:input.group>

                <flux:input.group>
                    <flux:input placeholder="0" />
                    <flux:input.group.suffix>Toman</flux:input.group.suffix>
                </flux:input.group>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'As a button',
            'text' => '<code>as="button"</code> renders something that looks like an input but behaves like a trigger — the usual shape for opening a command palette.',
            'code' => '<flux:input as="button" placeholder="Search..." icon="magnifying-glass" kbd="⌘K" />',
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:input',
            'props' => [
                ['label', 'Label above the field.'],
                ['description', 'Smaller text under the label.'],
                ['placeholder', 'Placeholder text.'],
                ['type', 'Any HTML input type: <code>text</code>, <code>email</code>, <code>password</code>, <code>tel</code>, <code>file</code>, <code>date</code>… Default: <code>text</code>.'],
                ['size', 'Options: <code>sm</code>, <code>xs</code>. Default: base.'],
                ['variant', 'Options: <code>outline</code>, <code>filled</code>. Default: <code>outline</code>.'],
                ['icon', 'Leading icon name.'],
                ['icon:trailing', 'Trailing icon name.'],
                ['clearable', 'Adds a clear button once there is a value.'],
                ['copyable', 'Adds a copy-to-clipboard button.'],
                ['viewable', 'For passwords: adds a reveal toggle.'],
                ['expandable', 'Adds a trailing chevron button (for inputs that expand into a dropdown).'],
                ['kbd', 'Keyboard shortcut hint on the trailing edge.'],
                ['invalid', 'Marks the field as invalid.'],
                ['loading', 'Shows a spinner while a Livewire action runs.'],
                ['mask', 'Input mask, e.g. <code>99/99</code>.'],
                ['as', 'Render as another element, e.g. <code>button</code>.'],
                ['wire:model', 'Binds to a Livewire property.'],
            ],
        ],
        ['name' => 'flux:input.group', 'text' => 'Joins an input with a prefix and/or suffix.'],
        ['name' => 'flux:input.group.prefix', 'text' => 'Content on the leading edge of the group.'],
        ['name' => 'flux:input.group.suffix', 'text' => 'Content on the trailing edge of the group.'],
    ],
    'related' => ['textarea', 'select', 'field'],
];

// -------------------------------------------------------------------- modal

$pages['modal'] = [
    'group' => 'components',
    'title' => 'Modal',
    'lede' => 'A dialog or flyout panel, opened from a trigger.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'A trigger and a modal share a <code>name</code>. The previews here are live — click them.',
            'code' => <<<'BLADE'
            <flux:modal.trigger name="confirm-delete">
                <flux:button variant="danger" icon="trash">Delete order</flux:button>
            </flux:modal.trigger>

            <flux:modal name="confirm-delete" class="md:w-96">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete this order?</flux:heading>
                        <flux:text class="mt-2">This cannot be undone and the order is cancelled entirely.</flux:text>
                    </div>

                    <div class="flex gap-2">
                        <flux:spacer />

                        <flux:modal.close>
                            <flux:button variant="ghost">Cancel</flux:button>
                        </flux:modal.close>

                        <flux:button variant="danger">Delete order</flux:button>
                    </div>
                </div>
            </flux:modal>
            BLADE,
        ],
        [
            'name' => 'Flyout',
            'text' => '<code>variant="flyout"</code> slides in from the trailing edge in both directions — the right in LTR, the left in RTL — with no extra classes.',
            'code' => <<<'BLADE'
            <flux:modal.trigger name="cart">
                <flux:button icon="shopping-cart">Cart</flux:button>
            </flux:modal.trigger>

            <flux:modal name="cart" variant="flyout" class="md:w-96">
                <div class="space-y-6">
                    <flux:heading size="lg">Cart</flux:heading>

                    <div class="flex items-center justify-between gap-3">
                        <flux:text>AirSound Pro</flux:text>
                        <mds:quantity :value="1" :min="1" :max="3" size="sm" />
                    </div>

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text>Total</flux:text>
                        <mds:price :amount="1890000" />
                    </div>

                    <flux:button variant="primary" class="w-full">Checkout</flux:button>
                </div>
            </flux:modal>
            BLADE,
        ],
        [
            'name' => 'Keyboard shortcut',
            'text' => 'A trigger can open on a shortcut instead of a click.',
            'code' => <<<'BLADE'
            <flux:modal.trigger name="quick-search" shortcut="cmd.k">
                <flux:input as="button" placeholder="Press ⌘K" icon="magnifying-glass" kbd="⌘K" />
            </flux:modal.trigger>

            <flux:modal name="quick-search" variant="bare" class="w-full max-w-md">
                <mds:command>
                    <mds:command.input placeholder="Search everything..." closable autofocus />

                    <mds:command.items empty="No results found.">
                        <mds:command.heading>Account</mds:command.heading>
                        <mds:command.item icon="shopping-bag" kbd="⌘O">My orders</mds:command.item>
                        <mds:command.item icon="wallet" kbd="⌘W">Wallet</mds:command.item>
                    </mds:command.items>
                </mds:command>
            </flux:modal>
            BLADE,
        ],
        [
            'name' => 'Not dismissible',
            'text' => 'Force a decision by disabling the backdrop click and the escape key.',
            'code' => <<<'BLADE'
            <flux:modal.trigger name="terms">
                <flux:button>Accept terms</flux:button>
            </flux:modal.trigger>

            <flux:modal name="terms" :dismissible="false" :escapable="false" class="md:w-96">
                <div class="space-y-6">
                    <flux:heading size="lg">Accept the terms</flux:heading>
                    <flux:text>You need to accept the terms before checking out.</flux:text>

                    <flux:modal.close>
                        <flux:button variant="primary" class="w-full">I accept</flux:button>
                    </flux:modal.close>
                </div>
            </flux:modal>
            BLADE,
        ],
    ],
    'reference' => [
        [
            'name' => 'flux:modal',
            'props' => [
                ['name', 'Identifier shared with its trigger.'],
                ['variant', 'Options: <code>flyout</code>, <code>bare</code>. Default: a centred dialog.'],
                ['position', 'For flyouts: which edge it slides from.'],
                ['dismissible', 'If <code>false</code>, clicking the backdrop does not close it.'],
                ['escapable', 'If <code>false</code>, Escape does not close it.'],
                ['closable', 'Shows or hides the built-in close button.'],
                ['scroll', 'Which part scrolls when the content is tall.'],
            ],
        ],
        ['name' => 'flux:modal.trigger', 'props' => [
            ['name', 'The modal to open.'],
            ['shortcut', 'Keyboard shortcut that opens it, e.g. <code>cmd.k</code>.'],
        ]],
        ['name' => 'flux:modal.close', 'text' => 'Wraps any element so that clicking it closes the modal.'],
    ],
    'related' => ['dropdown', 'toast', 'command'],
];

// ------------------------------------------------------------------- navbar

$pages['navbar'] = [
    'group' => 'components',
    'title' => 'Navbar',
    'lede' => 'Horizontal navigation, usually inside a header.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:navbar>
                <flux:navbar.item href="#" current>Home</flux:navbar.item>
                <flux:navbar.item href="#">Flash deals</flux:navbar.item>
                <flux:navbar.item href="#">Categories</flux:navbar.item>
                <flux:navbar.item href="#">Support</flux:navbar.item>
            </flux:navbar>
            BLADE,
        ],
        [
            'name' => 'Icons and badges',
            'code' => <<<'BLADE'
            <flux:navbar>
                <flux:navbar.item href="#" icon="home" current>Home</flux:navbar.item>
                <flux:navbar.item href="#" icon="fire">Flash deals</flux:navbar.item>
                <flux:navbar.item href="#" icon="shopping-bag" badge="3">Orders</flux:navbar.item>
                <flux:navbar.item href="#" icon="chat-bubble-left-right" badge="New" badge-color="lime">Support</flux:navbar.item>
            </flux:navbar>
            BLADE,
        ],
        [
            'name' => 'Scrollable',
            'text' => 'On narrow screens a long navbar scrolls instead of wrapping.',
            'code' => <<<'BLADE'
            <flux:navbar scrollable class="max-w-sm">
                <flux:navbar.item href="#" current>Phones</flux:navbar.item>
                <flux:navbar.item href="#">Laptops</flux:navbar.item>
                <flux:navbar.item href="#">Cameras</flux:navbar.item>
                <flux:navbar.item href="#">Appliances</flux:navbar.item>
                <flux:navbar.item href="#">Books</flux:navbar.item>
            </flux:navbar>
            BLADE,
        ],
        [
            'name' => 'In RTL',
            'text' => 'The navbar is laid out with logical properties, so the same markup reads right-to-left with no direction-specific classes.',
            'code' => <<<'BLADE'
            <div dir="rtl">
                <flux:navbar>
                    <flux:navbar.item href="#" icon="home" current>خانه</flux:navbar.item>
                    <flux:navbar.item href="#" icon="fire">پیشنهاد شگفت‌انگیز</flux:navbar.item>
                    <flux:navbar.item href="#" icon="shopping-bag" badge="۳">سفارش‌ها</flux:navbar.item>
                </flux:navbar>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:navbar', 'props' => [
            ['scrollable', 'If <code>true</code>, overflows horizontally instead of wrapping. Default: <code>false</code>.'],
            ['variant', 'Visual variant of the bar.'],
        ]],
        ['name' => 'flux:navbar.item', 'props' => [
            ['href', 'Link target.'],
            ['current', 'Marks the item as the active page.'],
            ['icon', 'Leading icon name.'],
            ['icon:trailing', 'Trailing icon name.'],
            ['badge', 'Badge content on the trailing edge.'],
            ['badge-color', 'Badge color.'],
            ['icon:dot', 'Shows a dot instead of a badge.'],
            ['accent', 'If <code>false</code>, the active item is not accent-coloured. Default: <code>true</code>.'],
            ['square', 'Icon-only item.'],
        ]],
    ],
    'related' => ['navlist', 'navmenu', 'header'],
];

// ------------------------------------------------------------------ navlist

$pages['navlist'] = [
    'group' => 'components',
    'title' => 'Navlist',
    'lede' => 'Vertical navigation for a sidebar or a page rail.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:navlist class="w-64">
                <flux:navlist.item href="#" icon="home" current>Dashboard</flux:navlist.item>
                <flux:navlist.item href="#" icon="shopping-bag" badge="3">My orders</flux:navlist.item>
                <flux:navlist.item href="#" icon="heart">Favorites</flux:navlist.item>
                <flux:navlist.item href="#" icon="cog-6-tooth">Settings</flux:navlist.item>
            </flux:navlist>
            BLADE,
        ],
        [
            'name' => 'Groups',
            'text' => 'A group can be a plain heading, or expandable.',
            'code' => <<<'BLADE'
            <flux:navlist class="w-64">
                <flux:navlist.group heading="Shop">
                    <flux:navlist.item href="#" current>Phones &amp; tablets</flux:navlist.item>
                    <flux:navlist.item href="#">Home appliances</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group heading="Wallet" expandable>
                    <flux:navlist.item href="#">Balance</flux:navlist.item>
                    <flux:navlist.item href="#">Gift cards</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>
            BLADE,
        ],
        [
            'name' => 'On this page',
            'text' => 'A navlist also works as a table of contents in an aside column.',
            'code' => <<<'BLADE'
            <flux:navlist class="w-56">
                <flux:navlist.item href="#" icon="chart-bar">Today\'s stats</flux:navlist.item>
                <flux:navlist.item href="#" icon="table-cells">Latest orders</flux:navlist.item>
                <flux:navlist.item href="#" icon="fire">Flash deals</flux:navlist.item>
            </flux:navlist>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:navlist', 'props' => [['variant', 'Visual variant of the list.']]],
        ['name' => 'flux:navlist.item', 'props' => [
            ['href', 'Link target.'],
            ['current', 'Marks the item as the active page.'],
            ['icon', 'Leading icon name.'],
            ['badge', 'Badge content on the trailing edge.'],
            ['badge-color', 'Badge color.'],
            ['icon:dot', 'Shows a dot instead of a badge.'],
        ]],
        ['name' => 'flux:navlist.group', 'props' => [
            ['heading', 'Group label.'],
            ['expandable', 'Makes the group collapsible.'],
            ['expanded', 'Initial state of an expandable group.'],
        ]],
    ],
    'related' => ['sidebar', 'navbar'],
];

// ------------------------------------------------------------------ navmenu

$pages['navmenu'] = [
    'group' => 'components',
    'title' => 'Navmenu',
    'lede' => 'A dropdown panel whose items are links, not actions.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Same shape as <code>flux:menu</code>, but the items render as anchors — right for a mobile nav or a page switcher.',
            'code' => <<<'BLADE'
            <flux:dropdown>
                <flux:button icon="bars-2" square aria-label="Menu" />

                <flux:navmenu>
                    <flux:navmenu.item href="#" icon="home">Home</flux:navmenu.item>
                    <flux:navmenu.item href="#" icon="fire">Flash deals</flux:navmenu.item>
                    <flux:navmenu.item href="#" icon="squares-2x2">Categories</flux:navmenu.item>
                    <flux:navmenu.separator />
                    <flux:navmenu.item href="#" icon="chat-bubble-left-right">Support</flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:navmenu', 'text' => 'The panel. Takes no props.'],
        ['name' => 'flux:navmenu.item', 'props' => [
            ['href', 'Link target.'],
            ['icon', 'Leading icon name.'],
        ]],
        ['name' => 'flux:navmenu.separator', 'text' => 'A dividing line between items.'],
    ],
    'related' => ['dropdown', 'navbar'],
];

// ---------------------------------------------------------------- otp input

$pages['otp-input'] = [
    'group' => 'components',
    'title' => 'OTP input',
    'lede' => 'A one-time-code field, one box per digit.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Codes read left-to-right even on an RTL page, so wrap the input in <code>dir="ltr"</code>.',
            'code' => <<<'BLADE'
            <div dir="ltr">
                <flux:otp length="5" />
            </div>
            BLADE,
        ],
        [
            'name' => 'With a field',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label>Verification code</flux:label>
                <flux:description>Sent to 0912 345 6789.</flux:description>

                <div dir="ltr">
                    <flux:otp length="6" />
                </div>
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Private',
            'text' => 'Masks the digits as they are typed.',
            'code' => <<<'BLADE'
            <div dir="ltr">
                <flux:otp length="4" private />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:otp', 'props' => [
            ['length', 'Number of digits.'],
            ['private', 'Masks the digits. Default: <code>false</code>.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
        ['name' => 'flux:otp.group', 'text' => 'Groups a run of inputs, for codes split by a separator.'],
        ['name' => 'flux:otp.separator', 'text' => 'An em dash between groups.'],
    ],
    'related' => ['input', 'field'],
];

// --------------------------------------------------------------- pagination

$pages['pagination'] = [
    'group' => 'components',
    'title' => 'Pagination',
    'lede' => 'Page links for a Laravel paginator.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Pass any paginator instance. The previous/next arrows follow the reading direction.',
            'code' => '<flux:pagination :paginator="$orders" />',
            'render' => <<<'BLADE'
            @php
            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                items: collect(range(1, 4)), total: 48, perPage: 4, currentPage: 3, options: ['path' => '#'],
            );
            @endphp

            <flux:pagination :paginator="$orders" />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'In RTL',
            'text' => 'On a Persian page the arrows and the page order mirror, and the numbers can be Persian — see <a href="../guides/directives.html">the digit helpers</a>.',
            'code' => <<<'BLADE'
            <div dir="rtl">
                <flux:pagination :paginator="$orders" />
            </div>
            BLADE,
            'render' => <<<'BLADE'
            @php
            $orders = new \Illuminate\Pagination\LengthAwarePaginator(
                items: collect(range(1, 4)), total: 48, perPage: 4, currentPage: 3, options: ['path' => '#'],
            );
            @endphp

            <div dir="rtl">
                <flux:pagination :paginator="$orders" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:pagination', 'props' => [
            ['paginator', 'A <code>LengthAwarePaginator</code> or <code>Paginator</code> instance.'],
            ['scroll-to', 'Selector to scroll to after a page change (<code>true</code> scrolls to <code>body</code>). Off by default.'],
        ]],
    ],
    'related' => ['table'],
];

// ------------------------------------------------------------------ profile

$pages['profile'] = [
    'group' => 'components',
    'title' => 'Profile',
    'lede' => 'The signed-in user, as a button that opens a menu.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:profile avatar="https://picsum.photos/seed/user/64/64" name="Sara Rezaei" />',
        ],
        [
            'name' => 'With a dropdown',
            'code' => <<<'BLADE'
            <flux:dropdown position="bottom" align="end">
                <flux:profile avatar="https://picsum.photos/seed/user/64/64" name="Sara Rezaei" />

                <flux:menu>
                    <flux:menu.item icon="user">My profile</flux:menu.item>
                    <flux:menu.item icon="shopping-bag">My orders</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger">Sign out</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            BLADE,
        ],
        [
            'name' => 'Initials and no chevron',
            'code' => <<<'BLADE'
            <flux:profile initials="SR" circle :chevron="false" name="Sara Rezaei" />
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:profile', 'props' => [
            ['avatar', 'Avatar image URL.'],
            ['initials', 'Letters shown when there is no avatar.'],
            ['name', 'Name beside the avatar. Omit for avatar-only.'],
            ['circle', 'Fully rounded avatar.'],
            ['chevron', 'Shows the dropdown chevron. Default: <code>true</code>.'],
            ['icon:trailing', 'Trailing icon in place of the chevron.'],
        ]],
    ],
    'related' => ['avatar', 'dropdown', 'sidebar'],
];

// ----------------------------------------------------------------- progress

$pages['progress'] = [
    'group' => 'components',
    'title' => 'Progress',
    'lede' => 'How far along something is.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:progress value="35" max="100" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Colors',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:progress value="35" max="100" />
                <flux:progress value="70" max="100" color="amber" />
                <flux:progress value="92" max="100" color="green" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With a label',
            'code' => <<<'BLADE'
            <div class="space-y-1">
                <flux:text class="text-sm">Profile completion — 35%</flux:text>
                <flux:progress value="35" max="100" />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:progress', 'props' => [
            ['value', 'Current value.'],
            ['max', 'Maximum value.'],
            ['color', 'Any Tailwind color. Default: the accent color.'],
        ]],
    ],
    'related' => ['skeleton', 'stepper'],
];

// -------------------------------------------------------------------- radio

$pages['radio'] = [
    'group' => 'components',
    'title' => 'Radio',
    'lede' => 'Pick exactly one option from a set.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:radio.group label="Shipping method">
                <flux:radio value="express" label="Courier" description="Same-day delivery" checked />
                <flux:radio value="post" label="Priority post" description="2 to 4 business days" />
                <flux:radio value="pickup" label="In-store pickup" />
            </flux:radio.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Segmented',
            'text' => 'For a small set of mutually exclusive views.',
            'code' => <<<'BLADE'
            <flux:radio.group label="View" variant="segmented">
                <flux:radio label="List" checked />
                <flux:radio label="Grid" />
                <flux:radio label="Table" />
            </flux:radio.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Cards',
            'code' => <<<'BLADE'
            <flux:radio.group label="Payment" variant="cards">
                <flux:radio value="online" label="Pay online" description="Card or wallet" checked />
                <flux:radio value="cod" label="Cash on delivery" description="Tehran only" />
            </flux:radio.group>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Pills and buttons',
            'code' => <<<'BLADE'
            <div class="space-y-6">
                <flux:radio.group label="Pills" variant="pills">
                    <flux:radio label="Any price" checked />
                    <flux:radio label="Under 1M" />
                    <flux:radio label="Over 1M" />
                </flux:radio.group>

                <flux:radio.group label="Buttons" variant="buttons">
                    <flux:radio label="Newest" checked />
                    <flux:radio label="Cheapest" />
                </flux:radio.group>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:radio.group', 'props' => [
            ['label', 'Group label.'],
            ['description', 'Group description.'],
            ['variant', 'Options: <code>default</code>, <code>segmented</code>, <code>cards</code>, <code>buttons</code>, <code>pills</code>.'],
            ['wire:model', 'Binds the group to a Livewire property.'],
        ]],
        ['name' => 'flux:radio', 'props' => [
            ['label', 'Label beside the control.'],
            ['description', 'Smaller text under the label.'],
            ['value', 'Submitted value.'],
            ['checked', 'Checked state for a plain form.'],
            ['disabled', 'Disables the input.'],
        ]],
    ],
    'related' => ['checkbox', 'select', 'switch'],
];

// ------------------------------------------------------------------- select

$pages['select'] = [
    'group' => 'components',
    'title' => 'Select',
    'lede' => 'Choose from a list of options.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:select label="Province" placeholder="Choose...">
                <flux:select.option>Tehran</flux:select.option>
                <flux:select.option>Isfahan</flux:select.option>
                <flux:select.option>Fars</flux:select.option>
                <flux:select.option>Razavi Khorasan</flux:select.option>
            </flux:select>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Groups',
            'code' => <<<'BLADE'
            <flux:select label="Category" placeholder="Choose...">
                <flux:select.group label="Electronics">
                    <flux:select.option>Phones</flux:select.option>
                    <flux:select.option>Laptops</flux:select.option>
                </flux:select.group>

                <flux:select.group label="Home">
                    <flux:select.option>Appliances</flux:select.option>
                    <flux:select.option>Furniture</flux:select.option>
                </flux:select.group>
            </flux:select>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Sizes and state',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:select size="sm" placeholder="Small">
                    <flux:select.option>Tehran</flux:select.option>
                </flux:select>

                <flux:select placeholder="Invalid" invalid>
                    <flux:select.option>Tehran</flux:select.option>
                </flux:select>

                <flux:select placeholder="Disabled" disabled>
                    <flux:select.option>Tehran</flux:select.option>
                </flux:select>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:select', 'props' => [
            ['label', 'Label above the control.'],
            ['description', 'Smaller text under the label.'],
            ['placeholder', 'Shown while nothing is selected.'],
            ['size', 'Options: <code>sm</code>, <code>xs</code>. Default: base.'],
            ['variant', 'Options: <code>default</code>.'],
            ['invalid', 'Marks the control as invalid.'],
            ['multiple', 'Allows selecting more than one option.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
        ['name' => 'flux:select.option', 'props' => [['value', 'Submitted value. Defaults to the label text.']]],
        ['name' => 'flux:select.group', 'props' => [['label', 'Group label inside the list.']]],
    ],
    'related' => ['input', 'radio', 'field'],
];

// ---------------------------------------------------------------- separator

$pages['separator'] = [
    'group' => 'components',
    'title' => 'Separator',
    'lede' => 'A dividing line, optionally with a label.',
    'sections' => [
        ['name' => 'Introduction', 'lead' => true, 'code' => '<flux:separator />', 'align' => 'stretch'],
        [
            'name' => 'With text',
            'code' => '<flux:separator text="or" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Subtle and faint',
            'code' => <<<'BLADE'
            <div class="space-y-6">
                <flux:separator />
                <flux:separator variant="subtle" />
                <flux:separator faint />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Vertical',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-4">
                <flux:text>Sign in</flux:text>
                <flux:separator vertical class="h-4" />
                <flux:text>Sign up</flux:text>
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:separator', 'props' => [
            ['text', 'Label centred on the line.'],
            ['vertical', 'Renders a vertical rule. Give it a height.'],
            ['orientation', 'Options: <code>horizontal</code>, <code>vertical</code>.'],
            ['variant', 'Options: <code>subtle</code>.'],
            ['faint', 'A lighter line.'],
        ]],
    ],
    'related' => ['heading', 'card'],
];

// ----------------------------------------------------------------- skeleton

$pages['skeleton'] = [
    'group' => 'components',
    'title' => 'Skeleton',
    'lede' => 'Placeholder shapes while content loads.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <div class="w-full max-w-sm space-y-3">
                <flux:skeleton class="aspect-video w-full rounded-lg" />
                <flux:skeleton class="h-4 w-3/4" />
                <flux:skeleton class="h-4 w-1/2" />
            </div>
            BLADE,
        ],
        [
            'name' => 'Group with shimmer',
            'text' => 'A group animates its children together instead of each on its own clock.',
            'code' => <<<'BLADE'
            <flux:skeleton.group animate="shimmer" class="w-full max-w-sm space-y-3">
                <flux:skeleton class="aspect-video w-full rounded-lg" />
                <flux:skeleton class="h-4 w-3/4" />

                <div class="flex items-center gap-2">
                    <flux:skeleton class="size-9 rounded-full" />
                    <flux:skeleton class="h-4 w-24" />
                </div>
            </flux:skeleton.group>
            BLADE,
        ],
        [
            'name' => 'Lines',
            'code' => <<<'BLADE'
            <flux:skeleton.group class="w-full max-w-sm space-y-2">
                <flux:skeleton.line />
                <flux:skeleton.line />
                <flux:skeleton.line class="w-2/3" />
            </flux:skeleton.group>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:skeleton', 'props' => [
            ['animate', 'Options: <code>shimmer</code>, <code>pulse</code>, <code>false</code>.'],
        ]],
        ['name' => 'flux:skeleton.group', 'text' => 'Animates its children in sync. Takes the same <code>animate</code> prop.'],
        ['name' => 'flux:skeleton.line', 'text' => 'A single line of placeholder text.'],
    ],
    'related' => ['progress', 'empty-state'],
];

// ------------------------------------------------------------------- switch

$pages['switch'] = [
    'group' => 'components',
    'title' => 'Switch',
    'lede' => 'Turn a single setting on or off.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:field variant="inline">
                <flux:label>Free shipping</flux:label>
                <flux:switch checked />
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With a description',
            'code' => <<<'BLADE'
            <flux:switch label="Order notifications" description="An SMS at every shipping step" checked />
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Label alignment',
            'text' => '<code>align="left"</code> (or <code>start</code>) puts the control on the leading edge instead of the trailing one.',
            'code' => <<<'BLADE'
            <div class="space-y-4">
                <flux:switch label="Right (default)" checked />
                <flux:switch label="Left" align="left" checked />
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Disabled',
            'code' => '<flux:switch label="Gift wrapping" description="Unavailable for this seller" disabled />',
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:switch', 'props' => [
            ['label', 'Label beside the switch.'],
            ['description', 'Smaller text under the label.'],
            ['checked', 'On state for a plain form.'],
            ['align', 'Options: <code>right</code>, <code>left</code>. Default: <code>right</code>.'],
            ['disabled', 'Disables the control.'],
            ['name', 'Submitted field name.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['checkbox', 'toggle', 'field'],
];

// -------------------------------------------------------------------- table

$pages['table'] = [
    'group' => 'components',
    'title' => 'Table',
    'lede' => 'Tabular data, with sortable and aligned columns.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => <<<'BLADE'
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Product</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Amount</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell>Galaxy S25</flux:table.cell>
                        <flux:table.cell><flux:badge size="sm" color="green">Delivered</flux:badge></flux:table.cell>
                        <flux:table.cell align="end"><mds:price :amount="42500000" size="sm" /></flux:table.cell>
                    </flux:table.row>

                    <flux:table.row>
                        <flux:table.cell>AirSound Pro</flux:table.cell>
                        <flux:table.cell><flux:badge size="sm" color="blue">Shipping</flux:badge></flux:table.cell>
                        <flux:table.cell align="end"><mds:price :amount="1890000" size="sm" /></flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
            BLADE,
            'align' => 'stretch',
            'note' => '<code>align="end"</code> is logical, so amounts hug the correct edge in both directions. Prices come from <a href="../mds/price.html">mds:price</a>.',
        ],
        [
            'name' => 'Sortable columns',
            'text' => '<code>sortable</code> adds the affordance; <code>sorted</code> and <code>direction</code> show the current state.',
            'code' => <<<'BLADE'
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Product</flux:table.column>
                    <flux:table.column sortable sorted direction="desc">Placed</flux:table.column>
                    <flux:table.column sortable align="end">Amount</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell>Galaxy S25</flux:table.cell>
                        <flux:table.cell>2 days ago</flux:table.cell>
                        <flux:table.cell align="end">42,500,000</flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'With components in cells',
            'code' => <<<'BLADE'
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Product</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>
                    <flux:table.column align="end">Quantity</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    <flux:table.row>
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="sm" src="https://picsum.photos/seed/phone/48/48" />
                            Galaxy S25
                        </flux:table.cell>
                        <flux:table.cell><mds:rating :value="4.6" size="sm" :fa="false" /></flux:table.cell>
                        <flux:table.cell align="end"><mds:quantity :value="2" :min="1" size="sm" :fa="false" /></flux:table.cell>
                    </flux:table.row>
                </flux:table.rows>
            </flux:table>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:table', 'props' => [['paginate', 'A paginator to render pagination links under the table.']]],
        ['name' => 'flux:table.columns', 'text' => 'The header row.'],
        ['name' => 'flux:table.column', 'props' => [
            ['sortable', 'Makes the column sortable. Default: <code>false</code>.'],
            ['sorted', 'Marks this column as the current sort.'],
            ['direction', 'Options: <code>asc</code>, <code>desc</code>.'],
            ['align', 'Options: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>start</code>.'],
            ['sticky', 'Pins the column while the table scrolls sideways.'],
        ]],
        ['name' => 'flux:table.rows', 'text' => 'The table body.'],
        ['name' => 'flux:table.row', 'text' => 'One row.'],
        ['name' => 'flux:table.cell', 'props' => [
            ['align', 'Options: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>start</code>.'],
            ['variant', 'Visual variant, e.g. a strong first cell.'],
            ['sticky', 'Pins the cell while the table scrolls sideways.'],
        ]],
    ],
    'related' => ['pagination', 'card'],
];

// --------------------------------------------------------------------- text

$pages['text'] = [
    'group' => 'components',
    'title' => 'Text',
    'lede' => 'Body copy and inline links.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:text>Your parcel ships from the Tehran warehouse today.</flux:text>',
            'align' => 'stretch',
        ],
        [
            'name' => 'Sizes and colors',
            'code' => <<<'BLADE'
            <div class="space-y-2">
                <flux:text size="sm">Small</flux:text>
                <flux:text>Base</flux:text>
                <flux:text size="lg">Large</flux:text>
                <flux:text variant="strong">Strong</flux:text>
                <flux:text variant="subtle">Subtle</flux:text>
                <flux:text color="red">Red</flux:text>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Inline',
            'text' => '<code>inline</code> renders a <code>span</code> so text can sit inside a heading or another sentence.',
            'code' => <<<'BLADE'
            <flux:heading>Order placed <flux:text inline>· 2 days ago</flux:text></flux:heading>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Links',
            'code' => <<<'BLADE'
            <flux:text>
                Read the <flux:link href="#">shipping policy</flux:link>, or
                <flux:link href="https://fluxui.dev" external>Flux's own docs</flux:link>.
            </flux:text>
            BLADE,
            'align' => 'stretch',
        ],
        [
            'name' => 'Persian body copy',
            'text' => 'The kit points Tailwind\'s <code>--font-sans</code> at Vazirmatn, so Persian text and Latin text share one scale and one weight ramp.',
            'code' => <<<'BLADE'
            <div dir="rtl">
                <flux:text>مرسوله شما امروز از انبار تهران ارسال می‌شود.</flux:text>
            </div>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:text', 'props' => [
            ['size', 'Options: <code>sm</code>, <code>base</code>, <code>lg</code>, <code>xl</code>.'],
            ['variant', 'Options: <code>strong</code>, <code>subtle</code>.'],
            ['color', 'Any Tailwind hue (<code>red</code> … <code>rose</code>) — the neutral scales (zinc, gray…) are not accepted.'],
            ['inline', 'Renders a <code>span</code> instead of a <code>p</code>. Default: <code>false</code>.'],
        ]],
        ['name' => 'flux:link', 'props' => [
            ['href', 'Link target.'],
            ['external', 'Opens in a new tab (with <code>rel="noopener"</code>).'],
            ['variant', 'Visual variant of the link.'],
            ['accent', 'If <code>false</code>, inherits the surrounding color. Default: <code>true</code>.'],
            ['strong', 'Bolder link text. Default: <code>false</code>.'],
        ]],
    ],
    'related' => ['heading', 'callout'],
];

// ----------------------------------------------------------------- textarea

$pages['textarea'] = [
    'group' => 'components',
    'title' => 'Textarea',
    'lede' => 'A multi-line text field.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'code' => '<flux:textarea label="Review" placeholder="Write your thoughts about this product..." />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Rows',
            'code' => '<flux:textarea label="Notes" rows="2" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Resize',
            'text' => 'Options are <code>vertical</code> (the default), <code>horizontal</code>, <code>both</code> and <code>none</code>.',
            'code' => '<flux:textarea label="Fixed size" resize="none" rows="3" />',
            'align' => 'stretch',
        ],
        [
            'name' => 'Invalid',
            'code' => <<<'BLADE'
            <flux:field>
                <flux:label>Review</flux:label>
                <flux:textarea rows="3" invalid />
                <flux:error name="review" message="A review needs at least 10 characters." />
            </flux:field>
            BLADE,
            'align' => 'stretch',
        ],
    ],
    'reference' => [
        ['name' => 'flux:textarea', 'props' => [
            ['label', 'Label above the field.'],
            ['description', 'Smaller text under the label.'],
            ['placeholder', 'Placeholder text.'],
            ['rows', 'Visible rows. Default: <code>4</code>.'],
            ['resize', 'Options: <code>vertical</code>, <code>horizontal</code>, <code>both</code>, <code>none</code>. Default: <code>vertical</code>.'],
            ['invalid', 'Marks the field as invalid.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['input', 'field', 'composer'],
];

// -------------------------------------------------------------------- toast

$pages['toast'] = [
    'group' => 'components',
    'title' => 'Toast',
    'lede' => 'A short, temporary notification.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Put <code>flux:toast</code> once in your layout, then raise toasts from Alpine or Livewire. The buttons below are live.',
            'code' => <<<'BLADE'
            <flux:toast />

            <flux:button
                variant="filled"
                icon="check-circle"
                x-data
                x-on:click="$flux.toast({ heading: 'Done', text: 'Item added to your cart.', variant: 'success' })"
            >Success toast</flux:button>

            <flux:button
                variant="filled"
                icon="x-circle"
                x-data
                x-on:click="$flux.toast({ text: 'Could not reach the payment gateway.', variant: 'danger' })"
            >Error toast</flux:button>
            BLADE,
        ],
        [
            'name' => 'From Livewire',
            'text' => 'Call <code>Flux::toast()</code> from a component and it appears after the round trip.',
            'code' => <<<'BLADE'
            {{-- In your Livewire component --}}
            Flux::toast(
                heading: 'Order placed',
                text: 'Tracking code MDS-140529.',
                variant: 'success',
            );
            BLADE,
        ],
        [
            'name' => 'Position',
            'text' => 'The default is <code>bottom end</code> — the trailing edge, so it lands bottom-left in RTL and bottom-right in LTR.',
            'code' => '<flux:toast position="top end" />',
        ],
    ],
    'reference' => [
        ['name' => 'flux:toast', 'props' => [
            ['position', 'Two words, vertical then horizontal, e.g. <code>bottom end</code>, <code>top start</code>. Default: <code>bottom end</code>.'],
        ]],
        ['name' => '$flux.toast(…)', 'text' => 'Alpine magic. Takes <code>heading</code>, <code>text</code>, <code>variant</code> (<code>success</code>, <code>warning</code>, <code>danger</code>, <code>info</code>) and <code>duration</code>.'],
    ],
    'related' => ['callout', 'modal'],
];

// ------------------------------------------------------------------- toggle

$pages['toggle'] = [
    'group' => 'components',
    'title' => 'Toggle',
    'lede' => 'A button that holds an on/off state.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Where a switch is a form control, a toggle is a button that stays pressed — a bold button in an editor, a favorite star, a filter pill.',
            'code' => '<flux:toggle label="Bold" icon="bold" />',
        ],
        [
            'name' => 'Checked and icons',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-2">
                <flux:toggle icon="bold" label="Bold" checked />
                <flux:toggle icon="italic" label="Italic" />
                <flux:toggle icon="underline" label="Underline" />
            </div>
            BLADE,
        ],
        [
            'name' => 'On and off states',
            'text' => 'Give each state its own label or icon.',
            'code' => <<<'BLADE'
            <flux:toggle on-icon="heart" off-icon="heart" on-label="Saved" off-label="Save" color="rose" />
            BLADE,
        ],
        [
            'name' => 'Sizes',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-2">
                <flux:toggle size="sm" label="Small" icon="star" />
                <flux:toggle label="Base" icon="star" />
            </div>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:toggle', 'props' => [
            ['label', 'Button text.'],
            ['icon', 'Icon name.'],
            ['checked', 'Pressed state.'],
            ['on-label', 'Label used while on.'],
            ['off-label', 'Label used while off.'],
            ['on-icon', 'Icon used while on.'],
            ['off-icon', 'Icon used while off.'],
            ['variant', 'Options: <code>outline</code>, <code>ghost</code>, <code>subtle</code>, <code>filled</code>. Default: <code>outline</code>.'],
            ['size', 'Options: <code>sm</code>, <code>base</code>. Default: <code>base</code>.'],
            ['color', 'Any Tailwind color for the pressed state.'],
            ['name', 'Submitted field name.'],
            ['wire:model', 'Binds to a Livewire property.'],
        ]],
    ],
    'related' => ['switch', 'button'],
];

// ------------------------------------------------------------------ tooltip

$pages['tooltip'] = [
    'group' => 'components',
    'title' => 'Tooltip',
    'lede' => 'A short hint on hover or focus.',
    'sections' => [
        [
            'name' => 'Introduction',
            'lead' => true,
            'text' => 'Hover the button — the previews here are live.',
            'code' => <<<'BLADE'
            <flux:tooltip content="Add to favorites">
                <flux:button icon="heart" square variant="ghost" aria-label="Favorite" />
            </flux:tooltip>
            BLADE,
        ],
        [
            'name' => 'Position',
            'text' => '<code>position</code> takes physical sides — <code>top</code>, <code>right</code>, <code>bottom</code>, <code>left</code>; it is <code>align</code> that is logical.',
            'code' => <<<'BLADE'
            <div class="flex items-center gap-3">
                <flux:tooltip content="Above" position="top">
                    <flux:button size="sm">Top</flux:button>
                </flux:tooltip>

                <flux:tooltip content="Below" position="bottom">
                    <flux:button size="sm">Bottom</flux:button>
                </flux:tooltip>

                <flux:tooltip content="To the right" position="right">
                    <flux:button size="sm">Right</flux:button>
                </flux:tooltip>
            </div>
            BLADE,
        ],
        [
            'name' => 'Keyboard shortcut',
            'code' => <<<'BLADE'
            <flux:tooltip content="Quick search" kbd="⌘K">
                <flux:button icon="magnifying-glass" square aria-label="Search" />
            </flux:tooltip>
            BLADE,
        ],
        [
            'name' => 'Rich content',
            'text' => 'Use <code>flux:tooltip.content</code> when the hint needs markup, and <code>interactive</code> when it holds a link.',
            'code' => <<<'BLADE'
            <flux:tooltip interactive>
                <flux:button size="sm" icon="information-circle">Shipping</flux:button>

                <flux:tooltip.content class="max-w-64">
                    <p>Free over 1,000,000 Toman.</p>
                    <p class="mt-1">Tehran only for same-day delivery.</p>
                </flux:tooltip.content>
            </flux:tooltip>
            BLADE,
        ],
        [
            'name' => 'On a disabled control',
            'text' => 'A disabled button does not fire pointer events, so wrap it and explain why it is disabled.',
            'code' => <<<'BLADE'
            <flux:tooltip content="Add an address first">
                <flux:button variant="primary" disabled>Checkout</flux:button>
            </flux:tooltip>
            BLADE,
        ],
    ],
    'reference' => [
        ['name' => 'flux:tooltip', 'props' => [
            ['content', 'Tooltip text. Use the <code>flux:tooltip.content</code> child for markup.'],
            ['position', 'Options: <code>top</code>, <code>right</code>, <code>bottom</code>, <code>left</code>. Default: <code>top</code>.'],
            ['align', 'Options: <code>start</code>, <code>center</code>, <code>end</code>. Default: <code>center</code>.'],
            ['kbd', 'Keyboard shortcut shown inside the tooltip.'],
            ['interactive', 'Keeps the tooltip open while the pointer is inside it.'],
            ['toggleable', 'Opens on click instead of hover.'],
        ]],
        ['name' => 'flux:tooltip.content', 'text' => 'Rich tooltip body, in place of the <code>content</code> prop.'],
    ],
    'related' => ['button', 'dropdown', 'preview-card'],
];

return $pages;
