<?php

/*
| Page layouts.
|
| These previews are miniatures rather than live frames: a header or a sidebar
| only means anything at full page size, and an iframe per example would drag a
| second copy of the stylesheet into every page. The working versions are the
| layout gallery in the demo, which is linked from each page here.
*/

return [
    // ------------------------------------------------------------------ grid
    'grid' => [
        'group' => 'layouts',
        'title' => 'The layout grid',
        'lede' => 'How Flux turns the order of your markup into a page layout.',
        'sections' => [
            [
                'name' => 'Introduction',
                'lead' => true,
                'text' => 'The element that directly wraps <code>flux:main</code> becomes a 3×3 CSS grid whose areas are named <code>header</code>, <code>sidebar</code>, <code>main</code>, <code>aside</code> and <code>footer</code>. You never write the grid — you pick an arrangement by the order you place the children in.',
                'code' => <<<'BLADE'
                <body>
                    <flux:sidebar sticky>…</flux:sidebar>

                    <flux:header sticky>…</flux:header>

                    <flux:main>…</flux:main>

                    <flux:aside sticky>…</flux:aside>

                    <flux:footer>…</flux:footer>
                </body>
                BLADE,
                'render' => <<<'BLADE'
                <div class="w-full max-w-md">
                    <div class="grid h-40 gap-1 rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                        style="grid-template-columns: 22% 1fr 22%; grid-template-rows: 1.5rem 1fr 1.1rem; grid-template-areas: 'sidebar header header' 'sidebar main aside' 'sidebar footer aside';">
                        <div class="flex items-center justify-center rounded-sm bg-accent/15 text-[10px] font-medium text-accent-content" style="grid-area: sidebar;">sidebar</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-200 text-[10px] font-medium text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200" style="grid-area: header;">header</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-50 text-[10px] font-medium text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300" style="grid-area: main;">main</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-100 text-[10px] font-medium text-zinc-500 dark:bg-zinc-600/60 dark:text-zinc-300" style="grid-area: aside;">aside</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-200/70 text-[10px] font-medium text-zinc-500 dark:bg-zinc-600/60 dark:text-zinc-300" style="grid-area: footer;">footer</div>
                    </div>
                </div>
                BLADE,
            ],
            [
                'name' => 'Order decides the arrangement',
                'text' => 'Put <code>flux:sidebar</code> before <code>flux:header</code> and the sidebar claims the full page height with the header beside it. Put the header first and it spans the full width with the sidebar underneath. That is the whole API — two lines swapped.',
                'code' => <<<'BLADE'
                {{-- Sidebar first: it owns the full height --}}
                <flux:sidebar sticky>…</flux:sidebar>
                <flux:header sticky>…</flux:header>

                {{-- Header first: it owns the full width --}}
                <flux:header sticky>…</flux:header>
                <flux:sidebar sticky>…</flux:sidebar>
                BLADE,
                'render' => <<<'BLADE'
                <div class="grid w-full max-w-md grid-cols-2 gap-4">
                    <div class="grid h-32 gap-1 rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                        style="grid-template-columns: 26% 1fr; grid-template-rows: 1.4rem 1fr 1rem; grid-template-areas: 'sidebar header' 'sidebar main' 'sidebar footer';">
                        <div class="flex items-center justify-center rounded-sm bg-accent/15 text-[9px] text-accent-content" style="grid-area: sidebar;">sidebar</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-200 text-[9px] text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200" style="grid-area: header;">header</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-50 text-[9px] text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300" style="grid-area: main;">main</div>
                        <div class="rounded-sm bg-zinc-200/70 dark:bg-zinc-600/60" style="grid-area: footer;"></div>
                    </div>

                    <div class="grid h-32 gap-1 rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                        style="grid-template-columns: 26% 1fr; grid-template-rows: 1.4rem 1fr 1rem; grid-template-areas: 'header header' 'sidebar main' 'sidebar footer';">
                        <div class="flex items-center justify-center rounded-sm bg-zinc-200 text-[9px] text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200" style="grid-area: header;">header</div>
                        <div class="flex items-center justify-center rounded-sm bg-accent/15 text-[9px] text-accent-content" style="grid-area: sidebar;">sidebar</div>
                        <div class="flex items-center justify-center rounded-sm bg-zinc-50 text-[9px] text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300" style="grid-area: main;">main</div>
                        <div class="rounded-sm bg-zinc-200/70 dark:bg-zinc-600/60" style="grid-area: footer;"></div>
                    </div>
                </div>
                BLADE,
            ],
            [
                'name' => 'RTL comes free',
                'text' => 'CSS grid columns follow the page\'s writing direction, so on a page with <code>dir="rtl"</code> the same grid puts the sidebar on the right and the aside on the left. There is no RTL variant of any of these components, and no direction-specific classes to write.',
                'code' => '<html lang="fa" dir="rtl">',
                'render' => <<<'BLADE'
                <div class="grid w-full max-w-md grid-cols-2 gap-4">
                    <div dir="ltr" class="space-y-1">
                        <p class="text-center text-[10px] font-medium text-zinc-500">dir="ltr"</p>
                        <div class="grid h-28 gap-1 rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                            style="grid-template-columns: 26% 1fr; grid-template-areas: 'sidebar main';">
                            <div class="flex items-center justify-center rounded-sm bg-accent/15 text-[9px] text-accent-content" style="grid-area: sidebar;">sidebar</div>
                            <div class="flex items-center justify-center rounded-sm bg-zinc-50 text-[9px] text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300" style="grid-area: main;">main</div>
                        </div>
                    </div>

                    <div dir="rtl" class="space-y-1">
                        <p class="text-center text-[10px] font-medium text-zinc-500">dir="rtl"</p>
                        <div class="grid h-28 gap-1 rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                            style="grid-template-columns: 26% 1fr; grid-template-areas: 'sidebar main';">
                            <div class="flex items-center justify-center rounded-sm bg-accent/15 text-[9px] text-accent-content" style="grid-area: sidebar;">sidebar</div>
                            <div class="flex items-center justify-center rounded-sm bg-zinc-50 text-[9px] text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300" style="grid-area: main;">main</div>
                        </div>
                    </div>
                </div>
                BLADE,
                'note' => 'Every arrangement below is live in the demo\'s <a href="../demo/layouts-en.html">layout gallery</a> — eight full pages you can resize and scroll.',
            ],
        ],
        'reference' => [
            ['name' => 'flux:main', 'text' => 'The main content area. Its parent becomes the layout grid.', 'props' => [
                ['container', 'Caps the content at <code>max-w-7xl</code> and centres it.'],
            ]],
            ['name' => 'flux:footer', 'text' => 'The footer row.', 'props' => [['container', 'Caps and centres the contents.']]],
            ['name' => 'flux:container', 'text' => 'Caps and centres any block by hand — for a full-bleed band whose contents must stay aligned with the rest of the page.'],
            ['name' => 'flux:spacer', 'text' => 'A flexible gap that pushes what follows to the far edge of a flex row.'],
        ],
        'related' => ['header', 'sidebar', 'aside'],
    ],

    // ---------------------------------------------------------------- header
    'header' => [
        'group' => 'layouts',
        'title' => 'Header',
        'lede' => 'A top bar with navigation and actions.',
        'sections' => [
            [
                'name' => 'Introduction',
                'lead' => true,
                'text' => 'A header holds a brand, a navbar, a <code>flux:spacer</code> and then the actions. <code>sticky</code> pins it; <code>container</code> caps its contents at the same width as the main column.',
                'code' => <<<'BLADE'
                <flux:header sticky container class="border-b border-zinc-200 dark:border-zinc-700">
                    <flux:brand href="/" name="Majid DS" />

                    <flux:navbar class="max-lg:hidden">
                        <flux:navbar.item href="#" current>Home</flux:navbar.item>
                        <flux:navbar.item href="#">Flash deals</flux:navbar.item>
                    </flux:navbar>

                    <flux:spacer />

                    <flux:button icon="shopping-cart" square variant="subtle" aria-label="Cart" />
                    <flux:profile avatar="/me.jpg" name="Sara Rezaei" />
                </flux:header>
                BLADE,
                'render' => <<<'BLADE'
                <div class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-4 border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                        <flux:brand href="#" name="Majid DS">
                            <x-slot name="logo">
                                <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-accent text-xs font-bold text-accent-foreground">M</div>
                            </x-slot>
                        </flux:brand>

                        <flux:navbar class="max-sm:hidden">
                            <flux:navbar.item href="#" current>Home</flux:navbar.item>
                            <flux:navbar.item href="#">Deals</flux:navbar.item>
                        </flux:navbar>

                        <flux:spacer />

                        <flux:button icon="shopping-cart" square variant="subtle" size="sm" aria-label="Cart" />
                        <flux:profile avatar="https://picsum.photos/seed/user/64/64" name="Sara" />
                    </div>

                    <div class="grid h-20 place-items-center text-xs text-zinc-400">main</div>
                </div>
                BLADE,
                'align' => 'stretch',
            ],
            [
                'name' => 'Mobile menu',
                'text' => 'Below <code>lg</code>, swap the navbar for a dropdown holding a <code>flux:navmenu</code>.',
                'code' => <<<'BLADE'
                <flux:dropdown position="bottom" align="end" class="lg:hidden">
                    <flux:button variant="subtle" icon="bars-2" square aria-label="Main menu" />

                    <flux:navmenu>
                        <flux:navmenu.item href="#" icon="home">Home</flux:navmenu.item>
                        <flux:navmenu.item href="#" icon="fire">Flash deals</flux:navmenu.item>
                    </flux:navmenu>
                </flux:dropdown>
                BLADE,
            ],
        ],
        'reference' => [
            ['name' => 'flux:header', 'props' => [
                ['sticky', 'Pins the header to the top of the viewport.'],
                ['container', 'Caps the contents at <code>max-w-7xl</code> and centres them.'],
            ]],
        ],
        'related' => ['navbar', 'brand', 'grid'],
    ],

    // --------------------------------------------------------------- sidebar
    'sidebar' => [
        'group' => 'layouts',
        'title' => 'Sidebar',
        'lede' => 'A navigation column, collapsible on desktop and off-canvas on mobile.',
        'sections' => [
            [
                'name' => 'Introduction',
                'lead' => true,
                'text' => 'A sidebar placed before the header owns the full page height. In RTL it sits on the right automatically.',
                'code' => <<<'BLADE'
                <flux:sidebar sticky class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:sidebar.header>
                        <flux:sidebar.brand href="/" name="Majid DS" />
                    </flux:sidebar.header>

                    <flux:sidebar.search placeholder="Search products..." kbd="⌘K" />

                    <flux:sidebar.nav>
                        <flux:sidebar.item href="#" icon="home" current>Dashboard</flux:sidebar.item>
                        <flux:sidebar.item href="#" icon="shopping-bag" badge="3">My orders</flux:sidebar.item>
                        <flux:sidebar.item href="#" icon="heart">Favorites</flux:sidebar.item>

                        <flux:sidebar.group heading="Categories" icon="squares-2x2" expandable :expanded="true">
                            <flux:sidebar.item href="#">Phones &amp; tablets</flux:sidebar.item>
                            <flux:sidebar.item href="#">Home appliances</flux:sidebar.item>
                        </flux:sidebar.group>
                    </flux:sidebar.nav>

                    <flux:sidebar.spacer />

                    <flux:sidebar.nav>
                        <flux:sidebar.item href="#" icon="cog-6-tooth">Settings</flux:sidebar.item>
                    </flux:sidebar.nav>

                    <flux:sidebar.profile avatar="/me.jpg" name="Sara Rezaei" />
                </flux:sidebar>
                BLADE,
                'render' => <<<'BLADE'
                <div class="w-full max-w-xs space-y-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:brand href="#" name="Majid DS">
                        <x-slot name="logo">
                            <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-accent text-xs font-bold text-accent-foreground">M</div>
                        </x-slot>
                    </flux:brand>

                    <flux:navlist>
                        <flux:navlist.item href="#" icon="home" current>Dashboard</flux:navlist.item>
                        <flux:navlist.item href="#" icon="shopping-bag" badge="3">My orders</flux:navlist.item>
                        <flux:navlist.item href="#" icon="heart">Favorites</flux:navlist.item>

                        <flux:navlist.group heading="Categories" expandable>
                            <flux:navlist.item href="#">Phones &amp; tablets</flux:navlist.item>
                        </flux:navlist.group>
                    </flux:navlist>

                    <flux:separator variant="subtle" />

                    <flux:profile avatar="https://picsum.photos/seed/user/64/64" name="Sara Rezaei" />
                </div>
                BLADE,
                'note' => 'This preview is a still of the sidebar\'s contents. For the real thing — collapsing to an icon rail, sliding in over a backdrop on mobile — open the <a href="../demo/layouts-en.html">layout gallery</a>.',
            ],
            [
                'name' => 'Collapsible',
                'text' => '<code>collapsible</code> collapses the sidebar to an icon rail on desktop; <code>collapsible="mobile"</code> keeps it in the grid on desktop and turns it into an off-canvas panel below <code>lg</code>. Items fall back to their tooltips once the labels are hidden.',
                'code' => <<<'BLADE'
                <flux:sidebar sticky collapsible>
                    <flux:sidebar.header>
                        <flux:sidebar.brand href="/" name="Majid DS" />
                        <flux:sidebar.collapse tooltip="Collapse sidebar" />
                    </flux:sidebar.header>

                    <flux:sidebar.nav>
                        <flux:sidebar.item href="#" icon="home" tooltip="Dashboard" current>Dashboard</flux:sidebar.item>
                    </flux:sidebar.nav>
                </flux:sidebar>

                {{-- In the header, for mobile --}}
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" aria-label="Open menu" />
                BLADE,
            ],
        ],
        'reference' => [
            ['name' => 'flux:sidebar', 'props' => [
                ['sticky', 'Pins the sidebar and caps its height to the viewport.'],
                ['collapsible', '<code>true</code> for an icon rail on desktop, or <code>"mobile"</code> for off-canvas below <code>lg</code>.'],
            ]],
            ['name' => 'flux:sidebar.header', 'text' => 'Top block, usually the brand and the collapse button.'],
            ['name' => 'flux:sidebar.brand', 'text' => 'Brand that collapses to the logo mark. Same props as <code>flux:brand</code>.'],
            ['name' => 'flux:sidebar.collapse', 'props' => [['tooltip', 'Tooltip on the collapse button.']]],
            ['name' => 'flux:sidebar.toggle', 'text' => 'Opens the off-canvas sidebar. Put it in the header.'],
            ['name' => 'flux:sidebar.search', 'props' => [
                ['placeholder', 'Placeholder text.'],
                ['kbd', 'Keyboard shortcut hint.'],
            ]],
            ['name' => 'flux:sidebar.nav', 'text' => 'Wraps a run of sidebar items.'],
            ['name' => 'flux:sidebar.item', 'props' => [
                ['href', 'Link target.'],
                ['current', 'Marks the active page.'],
                ['icon', 'Leading icon name.'],
                ['badge', 'Badge content on the trailing edge.'],
                ['badge-color', 'Badge color.'],
                ['tooltip', 'Tooltip shown while the sidebar is collapsed.'],
                ['tooltip:position', 'Tooltip side. Default: <code>right</code>.'],
            ]],
            ['name' => 'flux:sidebar.group', 'props' => [
                ['heading', 'Group label.'],
                ['icon', 'Leading icon name.'],
                ['expandable', 'Makes the group collapsible.'],
                ['expanded', 'Initial state.'],
            ]],
            ['name' => 'flux:sidebar.spacer', 'text' => 'Pushes everything after it to the bottom.'],
            ['name' => 'flux:sidebar.profile', 'text' => 'The user block at the bottom. Same props as <code>flux:profile</code>.'],
        ],
        'related' => ['navlist', 'grid', 'header'],
    ],

    // ----------------------------------------------------------------- aside
    'aside' => [
        'group' => 'layouts',
        'title' => 'Aside',
        'lede' => 'A third column beside the main content.',
        'sections' => [
            [
                'name' => 'Introduction',
                'lead' => true,
                'text' => 'An aside takes the <code>main</code> and <code>footer</code> rows of the grid and starts below the header. The classic use is an order summary that stays put while the list scrolls.',
                'code' => <<<'BLADE'
                <flux:aside sticky class="w-80 border-s border-zinc-200 max-xl:hidden dark:border-zinc-700">
                    <div class="space-y-6 p-6">
                        <flux:heading>Cart summary</flux:heading>

                        <mds:stepper :steps="['Cart', 'Shipping', 'Payment']" :current="2" class="w-full" />

                        <flux:separator />

                        <div class="flex items-center justify-between">
                            <flux:text class="font-medium">Total due</flux:text>
                            <mds:price :amount="38600000" />
                        </div>

                        <flux:button variant="primary" class="w-full">Confirm and pay</flux:button>
                    </div>
                </flux:aside>
                BLADE,
                'render' => <<<'BLADE'
                <div class="w-full max-w-xs space-y-5 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading>Cart summary</flux:heading>

                    <mds:stepper :steps="['Cart', 'Shipping', 'Payment']" :current="2" class="w-full" :fa="false" />

                    <flux:separator />

                    <div class="flex items-center justify-between">
                        <flux:text class="font-medium">Total due</flux:text>
                        <mds:price :amount="38600000" :fa="false" currency="Toman" />
                    </div>

                    <flux:button variant="primary" class="w-full">Confirm and pay</flux:button>
                </div>
                BLADE,
            ],
            [
                'name' => 'Falling back to a flyout',
                'text' => 'The aside is usually hidden on narrow screens. Put the same partial in a flyout modal so the content is still reachable.',
                'code' => <<<'BLADE'
                <flux:modal.trigger name="cart">
                    <flux:button icon="shopping-cart" class="xl:hidden">Cart</flux:button>
                </flux:modal.trigger>

                <flux:modal name="cart" variant="flyout" class="w-80">
                    @include('layouts.partials.aside-content')
                </flux:modal>
                BLADE,
                // The snippet @includes a partial that only exists in an app, so
                // render an inline version of the same thing.
                'render' => <<<'BLADE'
                <flux:modal.trigger name="cart-aside">
                    <flux:button icon="shopping-cart">Cart</flux:button>
                </flux:modal.trigger>

                <flux:modal name="cart-aside" variant="flyout" class="w-80">
                    <div class="space-y-5">
                        <flux:heading>Cart summary</flux:heading>
                        <mds:stepper :steps="['Cart', 'Shipping', 'Payment']" :current="2" class="w-full" :fa="false" />
                        <flux:separator />
                        <flux:button variant="primary" class="w-full">Confirm and pay</flux:button>
                    </div>
                </flux:modal>
                BLADE,
            ],
        ],
        'reference' => [
            ['name' => 'flux:aside', 'props' => [
                ['sticky', 'Pins the column and caps its height to the viewport.'],
            ]],
        ],
        'related' => ['grid', 'sidebar', 'modal'],
    ],
];
