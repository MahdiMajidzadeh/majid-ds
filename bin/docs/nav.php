<?php

/*
| The left-hand nav: groups in order, and the pages inside each group in the
| order they should appear. Every slug here must resolve to a page — the
| builder fails the run otherwise, so the sidebar can never link into the void.
|
| The Components group lists only the Flux components whose views actually ship
| in the free tier. Flux's Pro-only components are absent on purpose; the
| overview page says so, and names the four that have mds replacements.
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
            'price' => 'mds:price',
            'discount-badge' => 'mds:discount-badge',
            'quantity' => 'mds:quantity',
            'rating' => 'mds:rating',
            'product-card' => 'mds:product-card',
            'stepper' => 'mds:stepper',
            'countdown' => 'mds:countdown',
            'jalali-date' => 'mds:jalali-date',
            'empty-state' => 'mds:empty-state',
            'command' => 'mds:command',
            'color-picker' => 'mds:color-picker',
            'file-upload' => 'mds:file-upload',
            'timeline' => 'mds:timeline',
        ],
    ],
];
