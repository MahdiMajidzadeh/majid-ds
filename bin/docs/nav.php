<?php

/*
| The left-hand nav: groups in order, and the pages inside each group in the
| order they should appear. Every slug here must resolve to a page — the
| builder fails the run otherwise, so the sidebar can never link into the void.
|
| This is data, not markup: build-docs.php exports it to docs/assets/nav.js
| (+ nav.json) and every page renders its sidebar from that at load time. So
| adding a page here changes ONE asset — the other generated pages stay
| byte-identical and never need recommitting.
|
| The Components group lists only the Flux components whose views actually ship
| in the free tier. Flux's Pro-only components are absent on purpose; the
| overview page says so, and names the six that have mds replacements.
*/

return [
    [
        'title' => 'Guides',
        'items' => [
            'index' => 'Overview',
            'installation' => 'Installation',
            'theming' => 'Theming',
            'directives' => 'Directives & helpers',
            'ai-agents' => 'AI agents',
            'demo' => 'Demo',
            'rtl-demo' => 'RTL demo',
        ],
    ],
    [
        'title' => 'Layouts',
        'items' => [
            'grid' => 'The layout grid',
            'header' => 'Header',
            'sidebar' => 'Sidebar',
            'aside' => 'Aside',
        ],
    ],
    [
        'title' => 'Components',
        'items' => [
            'avatar' => 'Avatar',
            'badge' => 'Badge',
            'brand' => 'Brand',
            'breadcrumbs' => 'Breadcrumbs',
            'button' => 'Button',
            'callout' => 'Callout',
            'card' => 'Card',
            'checkbox' => 'Checkbox',
            'dropdown' => 'Dropdown',
            'field' => 'Field',
            'heading' => 'Heading',
            'icon' => 'Icon',
            'input' => 'Input',
            'modal' => 'Modal',
            'navbar' => 'Navbar',
            'navlist' => 'Navlist',
            'navmenu' => 'Navmenu',
            'otp-input' => 'OTP input',
            'pagination' => 'Pagination',
            'profile' => 'Profile',
            'progress' => 'Progress',
            'radio' => 'Radio',
            'select' => 'Select',
            'separator' => 'Separator',
            'skeleton' => 'Skeleton',
            'switch' => 'Switch',
            'table' => 'Table',
            'text' => 'Text',
            'textarea' => 'Textarea',
            'toast' => 'Toast',
            'toggle' => 'Toggle',
            'tooltip' => 'Tooltip',
        ],
    ],
    [
        'title' => 'mds components',
        'items' => [
            'mds-icon' => 'mds:icon',
            'mds-input' => 'mds:input',
            'price' => 'mds:price',
            'discount-badge' => 'mds:discount-badge',
            'quantity' => 'mds:quantity',
            'rating' => 'mds:rating',
            'product-card' => 'mds:product-card',
            'stepper' => 'mds:stepper',
            'countdown' => 'mds:countdown',
            'jalali-date' => 'mds:jalali-date',
            'empty-state' => 'mds:empty-state',
            'preview-card' => 'mds:preview-card',
            'command' => 'mds:command',
            'composer' => 'mds:composer',
            'color-picker' => 'mds:color-picker',
            'file-upload' => 'mds:file-upload',
            'timeline' => 'mds:timeline',
            'chart' => 'mds:chart',
            'popover' => 'mds:popover',
            'accordion' => 'mds:accordion',
            'slider' => 'mds:slider',
            'time-picker' => 'mds:time-picker',
            'tabs' => 'mds:tabs',
        ],
    ],
];
