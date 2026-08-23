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

use Illuminate\Support\Facades\Blade;
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

$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

/*
| A real request gets $errors from ShareErrorsFromSession. There is no request
| here, so share an empty bag — flux:error reads it unconditionally.
*/
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);

/** Render one Blade snippet to the markup a real page would contain. */
function render(string $blade): string
{
    return trim(Blade::render($blade));
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

$written = 0;

foreach ($pages as $slug => $page) {
    if ($only !== null && ! in_array($slug, $only, true)) {
        continue;
    }

    $path = $root.'/docs/'.docsPath($slug, $pages);
    $dir = dirname($path);

    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($path, renderPage($slug, $page, $pages));
    $written++;
    info('  wrote '.substr($path, strlen($root) + 6).' ('.round(filesize($path) / 1024).' KB)');
}

info(sprintf('Wrote %d pages.', $written));

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
