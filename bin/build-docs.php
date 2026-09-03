<?php

/*
|--------------------------------------------------------------------------
| Docs site builder
|--------------------------------------------------------------------------
|
| Writes docs/ as a set of plain static HTML pages, one per component, laid
| out like fluxui.dev's component reference: grouped nav on the left, the page
| in the middle, an "on this page" rail on the right.
|
| The pages are the deliverable — nothing at read time needs PHP, Blade or a
| server. This script is the authoring tool that produces them. Each example's
| preview markup comes from rendering its snippet through Blade once, because
| Flux computes its class strings at render time (a `match` over colour, size
| and variant); transcribing them by hand would give previews that only
| resemble the components. So the snippet in the page and the preview above it
| are guaranteed to be the same thing.
|
|   php bin/build-docs.php [--only=callout,button] [--skip-css]
|
*/

$root = dirname(__DIR__);
chdir($root);

require $root.'/vendor/autoload.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Orchestra\Testbench\Foundation\Application as Testbench;
use Orchestra\Testbench\Foundation\Config;

$options = getopt('', ['only::', 'skip-css', 'help']) ?: [];

if (isset($options['help'])) {
    fwrite(STDOUT, "Usage: php bin/build-docs.php [--only=slug,slug] [--skip-css]\n");
    exit(0);
}

$only = isset($options['only']) ? array_filter(explode(',', $options['only'])) : null;

function info(string $message): void
{
    fwrite(STDOUT, $message."\n");
}

function fail(string $message): never
{
    fwrite(STDERR, 'error: '.$message."\n");
    exit(1);
}

// ------------------------------------------------------------------ 1. render

$app = Testbench::createFromConfig(
    Config::loadFromYaml($root),
    options: ['enables_package_discoveries' => true, 'load_environment_variables' => false],
);

$app->make(Kernel::class)->bootstrap();

/*
| Freeze the clock so rebuilds are byte-identical. Several previews call now()
| (relative Jalali dates, countdown deadlines); without this, every rebuild
| dirties those committed pages even when nothing changed. The pinned instant
| is arbitrary but fixed — change it only if a preview needs a different era.
*/
Date::setTestNow('2026-08-24 10:00:00');

/*
| A real request gets $errors from ShareErrorsFromSession. There is no request
| here, so share an empty bag — flux:error reads it unconditionally.
*/
View::share('errors', new ViewErrorBag);

/*
| The Demo pages render the workbench's demo cards partial in place, so the
| builder needs the workbench views resolvable and its Persian-keyed English
| translations registered — exactly what workbench/routes/web.php does for the
| live demo. The en.json keys are Persian strings, so registering them can
| never change what an English snippet on any other page renders.
*/
View::addLocation($root.'/workbench/resources/views');
$app->make('translation.loader')->addJsonPath($root.'/workbench/lang');

/** Render one Blade snippet to the markup a real page would contain. */
function render(string $blade, array $data = []): string
{
    return trim(Blade::render($blade, $data));
}

// ------------------------------------------------------------- 2. the content

require $root.'/bin/docs/highlight.php';
require $root.'/bin/docs/shell.php';

$nav = require $root.'/bin/docs/nav.php';

$pages = array_merge(
    require $root.'/bin/docs/guides.php',
    require $root.'/bin/docs/layouts.php',
    require $root.'/bin/docs/flux.php',
    require $root.'/bin/docs/mds.php',
);

// Every nav entry must resolve to a page, or the sidebar links into the void.
$missing = [];

foreach ($nav as $group) {
    foreach ($group['items'] as $slug => $label) {
        if (! isset($pages[$slug])) {
            $missing[] = $slug;
        }
    }
}

if ($missing !== []) {
    fail('nav references pages that do not exist: '.implode(', ', $missing));
}

$orphans = array_diff(array_keys($pages), array_merge(...array_map(
    fn ($group) => array_keys($group['items']),
    $nav,
)));

if ($orphans !== []) {
    info('  warning: pages missing from the nav: '.implode(', ', $orphans));
}

/*
| The nav ships as data, not markup. Pages render the sidebar client-side from
| docs/assets/nav.js, so adding a page changes this one asset instead of every
| generated file. The .js wrapper (window.__mdsNav = ...) exists because
| browsers block fetch() of JSON on file:// — and the pages must keep working
| straight off disk. nav.json is the same data for anything else that wants it.
*/
$navData = array_map(fn ($group) => [
    'title' => $group['title'],
    'items' => array_map(fn ($slug, $label) => [
        'slug' => $slug,
        'label' => $label,
        'path' => docsPath($slug, $pages),
        'tag' => $pages[$slug]['tag'] ?? null,
    ], array_keys($group['items']), $group['items']),
], $nav);

$navJson = json_encode($navData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

file_put_contents($root.'/docs/assets/nav.js', 'window.__mdsNav = '.$navJson.";\n");
file_put_contents($root.'/docs/assets/nav.json', $navJson."\n");
info('  wrote assets/nav.js ('.count($pages).' pages in '.count($nav).' groups)');

// -------------------------------------------------------------- 3. write them

/*
| The docs are English-first: unless a page says otherwise ('env' on the page),
| previews render with Latin digits, English microcopy and "Toman" — what the
| kit produces in an app configured for English. Pages opt back into Persian
| where Persian is the point: the RTL demo, and Directives & helpers (the
| directives are Persian by definition). Reapplying the defaults for env-less
| pages keeps every page independent of build order.
*/
$defaults = ['locale' => 'en', 'digits' => false, 'currency' => 'toman'];

$written = 0;

foreach ($pages as $slug => $page) {
    if ($only !== null && ! in_array($slug, $only, true)) {
        continue;
    }

    $env = ($page['env'] ?? []) + $defaults;
    $app->setLocale($env['locale']);
    config(['mds.persian_digits' => $env['digits'], 'mds.currency' => $env['currency']]);

    $path = $root.'/docs/'.docsPath($slug, $pages);
    $dir = dirname($path);

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $html = renderPage($slug, $page, $pages);

    /*
    | An English page is only as translated as workbench/lang/en.json — count
    | what is left. A handful is expected (the demo's callout names them), but
    | a jump means a string was added to a view without a translation.
    */
    if (($page['env']['locale'] ?? null) === 'en' && preg_match_all('/[\x{0600}-\x{06FF}]+/u', strip_tags($html), $found)) {
        $words = array_unique($found[0]);
        info(sprintf('  %d Persian strings left in %s: %s', count($words), $slug, implode(' ', array_slice($words, 0, 12))));
    }

    file_put_contents($path, $html);
    $written++;
    info('  wrote '.substr($path, strlen($root) + 6).' ('.round(filesize($path) / 1024).' KB)');
}

info(sprintf('Wrote %d pages.', $written));

// ------------------------------------------------- 4. the README's index

/*
| The kit's API used to be written out three times — llms.txt, these docs
| pages, and a 660-line hand-kept section of README.md — and only llms.txt had
| a test behind it, so the README drifted. It is now generated from the same
| page data the docs are built from: one row per component, its own one-line
| lede, and links to the page and to llms.txt. Editing it by hand is pointless;
| the marker says so and ReadmeIndexTest fails the build when it falls behind.
*/

$rows = '';

foreach ($nav as $group) {
    if ($group['title'] !== 'mds components') {
        continue;
    }

    foreach ($group['items'] as $slug => $label) {
        $page = $pages[$slug];
        $rows .= sprintf(
            "| [`<%s>`](docs/%s.html) | %s |\n",
            $label,
            ($page['group'] ?? 'mds') === 'mds' ? 'mds/'.$slug : $slug,
            rtrim($page['lede'], '.'),
        );
    }
}

$index = "<!-- mds:components (generated by `npm run docs` — edit bin/docs/mds.php, not this) -->\n\n"
    ."| Component | What it is |\n|---|---|\n"
    .$rows
    ."\n<!-- /mds:components -->";

$readmePath = $root.'/README.md';
$readme = (string) file_get_contents($readmePath);

if (! preg_match('/<!-- mds:components .*?<!-- \/mds:components -->/s', $readme)) {
    fail('README.md has no <!-- mds:components --> … <!-- /mds:components --> block to fill.');
}

$updated = preg_replace('/<!-- mds:components .*?<!-- \/mds:components -->/s', str_replace('$', '\$', $index), $readme, 1);

if ($updated !== $readme) {
    file_put_contents($readmePath, $updated);
    info('  wrote README.md component index');
} else {
    info('  README.md component index already current');
}

// ----------------------------------------------------------------- 4. the css

if (! isset($options['skip-css'])) {
    info('Building site.css…');
    exec('npm run --silent css 2>&1', $out, $status);

    if ($status !== 0) {
        fail("`npm run css` failed:\n".implode("\n", $out));
    }

    info('  wrote assets/site.css ('.round(filesize($root.'/docs/assets/site.css') / 1024).' KB)');
}

info('Done.');
