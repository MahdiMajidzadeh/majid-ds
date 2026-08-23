<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use MajidDs\Support\Persian;

use function Orchestra\Testbench\workbench_path;

/*
| bin/build-pages.php freezes the clock so the crawled static pages are
| byte-identical across rebuilds. Live serving (npm run demo:serve) never sets
| this, so the demo keeps a real clock there.
*/
if (($testNow = env('MDS_TEST_NOW')) !== null) {
    Illuminate\Support\Facades\Date::setTestNow($testNow);
}

/*
|--------------------------------------------------------------------------
| Layout gallery
|--------------------------------------------------------------------------
|
| Every layout arrangement Flux's grid supports, rendered both right-to-left in
| Persian and left-to-right in English. The grid itself comes from flux.css: the element that
| directly wraps <flux:main> becomes a 3x3 grid whose areas are named
| header / sidebar / main / aside / footer. The ORDER of the children is what
| picks the arrangement — a sidebar placed before the header claims the full
| height, a header placed first spans the full width.
|
*/

$layouts = [
    'header' => [
        'title' => 'هدر افقی',
        'icon' => 'bars-3',
        'tagline' => 'ناوبری در بالای صفحه و بدون سایدبار؛ مناسب صفحه‌های عمومی فروشگاه.',
        'note' => 'هدر، محتوا و فوتر هر سه با prop «container» به عرض حداکثر ۷xl محدود شده‌اند.',
        'code' => <<<'CODE'
<body>
    <flux:header sticky container>…</flux:header>

    <flux:main container>…</flux:main>

    <flux:footer container>…</flux:footer>
</body>
CODE,
    ],

    'sidebar' => [
        'title' => 'سایدبار تمام‌قد',
        'icon' => 'view-columns',
        'tagline' => 'سایدبار پیش از هدر می‌آید، پس تمام ارتفاع صفحه را می‌گیرد و هدر کنارش می‌نشیند.',
        'note' => 'در حالت راست‌چین سایدبار به‌طور خودکار سمت راست قرار می‌گیرد؛ هیچ کلاس اضافه‌ای لازم نیست.',
        'code' => <<<'CODE'
<body>
    <flux:sidebar sticky>…</flux:sidebar>

    <flux:header sticky>…</flux:header>

    <flux:main>…</flux:main>
</body>
CODE,
    ],

    'sidebar-header' => [
        'title' => 'هدر سراسری + سایدبار',
        'icon' => 'window',
        'tagline' => 'همان اجزا با ترتیب برعکس: هدر اول می‌آید و کل عرض صفحه را می‌گیرد.',
        'note' => 'تنها تفاوت با چیدمان قبلی، جابه‌جایی دو خط کد است — CSS شبکه بقیه را انجام می‌دهد.',
        'code' => <<<'CODE'
<body>
    <flux:header sticky container>…</flux:header>

    <flux:sidebar sticky>…</flux:sidebar>

    <flux:main>…</flux:main>
</body>
CODE,
    ],

    'collapsible' => [
        'title' => 'سایدبار جمع‌شو',
        'icon' => 'arrows-pointing-in',
        'tagline' => 'سایدبار روی دسکتاپ تا عرض یک ریل آیکونی جمع می‌شود و با هاور باز می‌ماند.',
        'note' => 'دکمه <flux:sidebar.collapse /> وضعیت را نگه می‌دارد؛ آیتم‌ها در حالت جمع‌شده تولتیپ می‌گیرند.',
        'code' => <<<'CODE'
<body>
    <flux:sidebar sticky collapsible>
        <flux:sidebar.header>
            <flux:sidebar.brand … />
            <flux:sidebar.collapse />
        </flux:sidebar.header>
        …
    </flux:sidebar>

    <flux:header sticky>…</flux:header>

    <flux:main>…</flux:main>
</body>
CODE,
    ],

    'mobile' => [
        'title' => 'سایدبار موبایلی',
        'icon' => 'device-phone-mobile',
        'tagline' => 'روی دسکتاپ ثابت، روی موبایل کشویی با پس‌زمینه تار — چیدمان استاندارد پنل کاربری.',
        'note' => 'پنجره را باریک کنید: سایدبار پنهان می‌شود و با دکمه‌ی همبرگری از سمت راست می‌آید.',
        'code' => <<<'CODE'
<body>
    <flux:sidebar sticky collapsible="mobile">…</flux:sidebar>

    <flux:header sticky>
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />
        …
    </flux:header>

    <flux:main>…</flux:main>
</body>
CODE,
    ],

    'aside' => [
        'title' => 'ستون کناری (aside)',
        'icon' => 'rectangle-group',
        'tagline' => 'سه ستون: سایدبار ناوبری، محتوای اصلی و یک ستون کناری چسبان برای خلاصه سفارش.',
        'note' => 'ستون aside ردیف main و footer را می‌گیرد و زیر هدر شروع می‌شود.',
        'code' => <<<'CODE'
<body>
    <flux:sidebar sticky collapsible="mobile">…</flux:sidebar>

    <flux:header sticky>…</flux:header>

    <flux:main>…</flux:main>

    <flux:aside sticky class="w-80">…</flux:aside>
</body>
CODE,
    ],

    'sticky' => [
        'title' => 'چسبان و اسکرول',
        'icon' => 'bookmark',
        'tagline' => 'هدر، سایدبار و ستون کناری همه چسبانند و فقط محتوای اصلی اسکرول می‌شود.',
        'note' => 'صفحه را اسکرول کنید: prop «sticky» ارتفاع هر ناحیه را به ارتفاع دید محدود می‌کند.',
        'code' => <<<'CODE'
<body>
    <flux:sidebar sticky collapsible="mobile">…</flux:sidebar>

    <flux:header sticky>…</flux:header>

    <flux:main>…</flux:main>

    <flux:aside sticky>…</flux:aside>

    <flux:footer>…</flux:footer>
</body>
CODE,
    ],

    'container' => [
        'title' => 'عرض محدود',
        'icon' => 'square-3-stack-3d',
        'tagline' => 'بخش‌های تمام‌عرض با <flux:container> و prop «container» کنار هم مقایسه می‌شوند.',
        'note' => 'نوارِ رنگی تمام‌عرض است ولی محتوایش با flux:container در وسط نگه داشته شده.',
        'code' => <<<'CODE'
<body>
    <flux:header container>…</flux:header>

    <flux:main container>
        <flux:container>…</flux:container>
    </flux:main>

    <flux:footer container>…</flux:footer>
</body>
CODE,
    ],
];

/*
| A miniature of each arrangement for the gallery page: the same CSS grid the
| real layout uses, so the preview can never drift from the markup.
*/
$grids = [
    'header'         => ['cols' => '1fr',         'areas' => ['header', 'main', 'footer'], 'inset' => true],
    'sidebar'        => ['cols' => '22% 1fr',     'areas' => ['sidebar header', 'sidebar main', 'sidebar footer']],
    'sidebar-header' => ['cols' => '22% 1fr',     'areas' => ['header header', 'sidebar main', 'sidebar footer']],
    'collapsible'    => ['cols' => '10% 1fr',     'areas' => ['sidebar header', 'sidebar main', 'sidebar footer']],
    'mobile'         => ['cols' => '22% 1fr',     'areas' => ['sidebar header', 'sidebar main', 'sidebar footer'], 'dashed' => ['sidebar']],
    'aside'          => ['cols' => '22% 1fr 22%', 'areas' => ['sidebar header header', 'sidebar main aside', 'sidebar footer aside']],
    'sticky'         => ['cols' => '22% 1fr 22%', 'areas' => ['sidebar header header', 'sidebar main aside', 'sidebar footer aside'], 'pins' => ['header', 'sidebar', 'aside']],
    'container'      => ['cols' => '1fr',         'areas' => ['header', 'main', 'footer'], 'inset' => true],
];

foreach ($layouts as $slug => $meta) {
    $layouts[$slug]['slug'] = $slug;
    // Unprefixed: $mdsUrl() adds the locale prefix at render time.
    $layouts[$slug]['path'] = '/layouts/'.$slug;
    $layouts[$slug]['grid'] = $grids[$slug];
}

View::share('mdsLayouts', $layouts);

/*
|--------------------------------------------------------------------------
| Locales
|--------------------------------------------------------------------------
|
| Both demos render twice: Persian RTL at /demo and /layouts, English LTR at
| /en/demo and /en/layouts. The Persian copy in the views IS the translation
| key — workbench/lang/en.json maps each Persian string to English, and an
| unmapped key falls through to itself, so `fa` needs no translation file and
| the Blade stays readable in the language the kit is designed for.
|
| Locale also drives the kit's own formatting. Every mds:* component reads
| mds.persian_digits and mds.currency at render time, so setting them per
| locale switches digits (۲٬۵۰۰٬۰۰۰ → 2,500,000), separators and the currency
| label across the whole page without touching a single component call.
|
*/

$locales = [
    'fa' => ['prefix' => '', 'dir' => 'rtl', 'digits' => true, 'currency' => 'toman', 'other' => 'English'],
    'en' => ['prefix' => 'en', 'dir' => 'ltr', 'digits' => false, 'currency' => 'Toman', 'other' => 'فارسی'],
];

// Persian-keyed JSON translations. addJsonPath is how a non-standard lang
// directory joins the ones __() already searches.
app('translation.loader')->addJsonPath(workbench_path('lang'));

View::composer(['demo', 'layouts.*'], function ($view) use ($locales) {
    $locale = app()->getLocale();
    $prefix = $locales[$locale]['prefix'];

    $view->with([
        'mdsLocale' => $locale,
        'mdsDir' => $locales[$locale]['dir'],
        'mdsFa' => $locales[$locale]['digits'],

        // A page in the current locale, and the same page in the other one.
        'mdsUrl' => fn (string $path) => $prefix === '' ? $path : '/'.$prefix.$path,
        'mdsAlt' => fn (string $path) => $locale === 'fa' ? '/en'.$path : $path,
        'mdsAltLabel' => $locales[$locale]['other'],

        // "Forward" follows the reading direction, so the arrow flips with it.
        'mdsForward' => $locale === 'fa' ? 'arrow-left' : 'arrow-right',

        // Plain numbers that are page chrome rather than a component's output:
        // Persian digits and separators in fa, Latin in en.
        'mdsNum' => fn (mixed $value, int $decimals = 0) => $locales[$locale]['digits']
            ? Persian::number($value, $decimals)
            : number_format((float) $value, $decimals),

        // mds:file-item formats a byte count in Persian on its own; in English
        // it needs the text handed to it, since the units are not translatable.
        'mdsBytes' => fn (int $bytes) => $locales[$locale]['digits'] ? null : Number::fileSize($bytes, 1),

        /*
        | Where the "docs" link points. The docs page is a static file with no
        | route here, so it defaults to the published copy; bin/build-pages.php
        | sets this to the sibling file it writes next to the demo pages.
        */
        'mdsDocsHref' => env('MDS_DOCS_HREF', 'https://mahdimajidzadeh.github.io/majid-ds/'),
    ]);
});

/*
| Locale is set in the route action rather than by middleware so that a plain
| `php bin/build-pages.php` crawl needs nothing but the route table.
*/
$enter = function (string $locale) use ($locales) {
    app()->setLocale($locale);

    config([
        'mds.persian_digits' => $locales[$locale]['digits'],
        'mds.currency' => $locales[$locale]['currency'],
    ]);
};

foreach ($locales as $locale => $config) {
    Route::prefix($config['prefix'])->group(function () use ($layouts, $locale, $enter) {
        Route::get('/demo', function () use ($locale, $enter) {
            $enter($locale);

            return view('demo');
        });

        Route::get('/layouts', function () use ($locale, $enter) {
            $enter($locale);

            return view('layouts.index');
        });

        foreach ($layouts as $slug => $layout) {
            Route::get('/layouts/'.$slug, function () use ($slug, $layout, $locale, $enter) {
                $enter($locale);

                return view('layouts.'.$slug, ['layout' => $layout]);
            });
        }
    });
}
