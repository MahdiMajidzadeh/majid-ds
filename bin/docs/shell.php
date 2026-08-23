<?php

/*
| The page shell: <head>, the top bar, the grouped left nav, the content
| column, and the "on this page" rail. Every docs page is this shell wrapped
| around a list of sections and reference tables.
|
| Pages are flat inside their group directory (docs/components/button.html), so
| every page sits at the same depth and one relative prefix — "../" — is correct
| everywhere. That keeps the site working under a project subpath like
| https://mahdimajidzadeh.github.io/majid-ds/ and straight off the file system.
*/

const DOCS_REPO = 'https://github.com/MahdiMajidzadeh/majid-ds';

function docsPath(string $slug, array $pages): string
{
    $group = $pages[$slug]['group'] ?? 'components';

    return $slug === 'index' ? 'index.html' : $group.'/'.$slug.'.html';
}

/** A link from one page to another, relative to the linking page. */
function docsLink(string $from, string $to, array $pages): string
{
    $fromDepth = substr_count(docsPath($from, $pages), '/');
    $prefix = str_repeat('../', $fromDepth);

    return $prefix.docsPath($to, $pages);
}

function slugify(string $text): string
{
    $text = strtolower(strip_tags($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);

    return trim($text, '-');
}

/**
 * One example: the rendered component in a card, with its source attached
 * underneath. Matches the shape Flux's own docs use — a single bordered block
 * whose code half is separated by a border, not a gap.
 */
function example(array $section): string
{
    // A few components need a fixture the reader should not have to look at
    // ($paginator, a product array). Those sections give 'render' the version
    // with the fixture and 'code' the version worth reading.
    $preview = render($section['render'] ?? $section['code']);
    $code = highlight(trim($section['code']));

    $align = $section['align'] ?? 'center';
    $previewClasses = 'docs-preview'.($align === 'stretch' ? ' docs-preview-stretch' : '');

    // Persian-first components render right-to-left even though the page is
    // English — that is what the component actually looks like in an app.
    $dir = ($section['rtl'] ?? false) ? ' dir="rtl"' : '';

    return <<<HTML
    <div class="docs-example">
        <div class="{$previewClasses}">
            <div class="docs-preview-inner"{$dir}>{$preview}</div>
        </div>

        <div class="docs-code">
            <button type="button" class="docs-copy" aria-label="Copy code">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true">
                    <rect x="5.5" y="5.5" width="8" height="8" rx="1.75"/>
                    <path d="M10.5 3.5A1.5 1.5 0 0 0 9 2H4a1.5 1.5 0 0 0-1.5 1.5v5A1.5 1.5 0 0 0 4 10"/>
                </svg>
            </button>
            <pre><code>{$code}</code></pre>
        </div>
    </div>
    HTML;
}

/** A Prop/Slot table in the Reference section. */
function referenceTable(string $heading, array $rows): string
{
    if ($rows === []) {
        return '';
    }

    $body = '';

    foreach ($rows as [$name, $description]) {
        $body .= '<tr><td><span class="docs-prop">'.htmlspecialchars($name, ENT_QUOTES).'</span></td><td>'.$description.'</td></tr>';
    }

    return <<<HTML
    <table class="docs-table">
        <thead><tr><th>{$heading}</th><th>Description</th></tr></thead>
        <tbody>{$body}</tbody>
    </table>
    HTML;
}

function renderPage(string $slug, array $page, array $pages): string
{
    $group = $page['group'] ?? 'components';
    $prefix = str_repeat('../', substr_count(docsPath($slug, $pages), '/'));

    /*
    | The sidebar is NOT baked into the page. It renders client-side from
    | assets/nav.js (written by build-docs.php), so a nav change — a new
    | component, say — leaves every existing page byte-identical. The page
    | only carries its own slug and depth prefix, both stable for its lifetime.
    */

    // ------------------------------------------------------ sections + toc
    $body = '';
    $toc = [];
    $sections = $page['sections'] ?? [];

    foreach ($sections as $section) {
        $id = $section['id'] ?? slugify($section['name']);
        $toc[] = ['id' => $id, 'name' => $section['name'], 'sub' => false];

        $body .= '<section id="'.$id.'" class="docs-section">';

        // The first section carries the page title, so it needs no heading of
        // its own — same as the Flux docs, where "Introduction" is the h1 area.
        if (! ($section['lead'] ?? false)) {
            $body .= '<h2>'.htmlspecialchars($section['name'], ENT_QUOTES).'</h2>';
        }

        if (isset($section['text'])) {
            $body .= '<p class="docs-text">'.$section['text'].'</p>';
        }

        if (isset($section['code'])) {
            $body .= example($section);
        }

        if (isset($section['note'])) {
            $body .= '<div class="docs-note">'.$section['note'].'</div>';
        }

        $body .= '</section>';
    }

    // ---------------------------------------------------------- reference
    if ($page['reference'] ?? null) {
        $toc[] = ['id' => 'reference', 'name' => 'Reference', 'sub' => false];
        $body .= '<section id="reference" class="docs-section docs-reference"><h2>Reference</h2>';

        foreach ($page['reference'] as $entry) {
            $id = slugify($entry['name']);
            $toc[] = ['id' => $id, 'name' => $entry['name'], 'sub' => true];

            $body .= '<div id="'.$id.'" class="docs-ref-block">';
            $body .= '<h3>'.htmlspecialchars($entry['name'], ENT_QUOTES).'</h3>';

            if (isset($entry['text'])) {
                $body .= '<p class="docs-text">'.$entry['text'].'</p>';
            }

            $body .= referenceTable('Prop', $entry['props'] ?? []);
            $body .= referenceTable('Slot', $entry['slots'] ?? []);
            $body .= '</div>';
        }

        $body .= '</section>';
    }

    // ------------------------------------------------------------ related
    if ($page['related'] ?? null) {
        $cards = '';

        foreach ($page['related'] as $relatedSlug) {
            if (! isset($pages[$relatedSlug])) {
                continue;
            }

            $cards .= '<a class="docs-related-card" href="'.docsLink($slug, $relatedSlug, $pages).'">'
                .'<span class="docs-related-name">'.htmlspecialchars($pages[$relatedSlug]['title'], ENT_QUOTES).'</span>'
                .'<span class="docs-related-lede">'.htmlspecialchars($pages[$relatedSlug]['lede'], ENT_QUOTES).'</span>'
                .'</a>';
        }

        if ($cards !== '') {
            $body .= '<section class="docs-section"><h2>Related</h2><div class="docs-related">'.$cards.'</div></section>';
        }
    }

    // ---------------------------------------------------------------- toc
    $tocHtml = '';

    foreach ($toc as $item) {
        $tocHtml .= '<a href="#'.$item['id'].'"'.($item['sub'] ? ' class="docs-toc-sub"' : '').'>'
            .htmlspecialchars($item['name'], ENT_QUOTES).'</a>';
    }

    $title = htmlspecialchars($page['title'], ENT_QUOTES);
    $lede = $page['lede'];
    $tagline = isset($page['tag'])
        ? ' <span class="docs-title-tag">'.htmlspecialchars($page['tag'], ENT_QUOTES).'</span>'
        : '';

    $repo = DOCS_REPO;

    $groupLabel = match ($group) {
        'components' => 'Flux components',
        'mds' => 'mds components',
        'layouts' => 'Layouts',
        'guides' => 'Guides',
        default => ucfirst($group),
    };

    return <<<HTML
    <!doctype html>
    <html lang="en" class="docs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$title} — Majid DS</title>
    <meta name="description" content="{$title} — Majid DS, an RTL/Persian-first UI kit for Laravel Livewire built on Flux UI.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&family=Vazirmatn:wght@400..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{$prefix}assets/site.css">
    </head>
    <body>

    <header class="docs-topbar">
        <a class="docs-brand" href="{$prefix}index.html">
            <span class="docs-brand-mark">M</span>
            <span class="docs-brand-name">Majid DS</span>
            <span class="docs-brand-pill">mds</span>
        </a>

        <nav class="docs-topnav">
            <a href="{$prefix}index.html">Docs</a>
            <a href="{$prefix}demo/demo-en.html">Demo</a>
            <a href="{$prefix}demo/layouts-en.html">Layouts</a>
        </nav>

        <div class="docs-topbar-end">
            <a class="docs-github" href="{$repo}" target="_blank" rel="noreferrer">GitHub</a>
        </div>
    </header>

    <div class="docs-shell">
        <aside class="docs-sidebar" aria-label="Documentation">
            <details class="docs-nav" open data-docs-slug="{$slug}" data-docs-prefix="{$prefix}">
                <summary>Menu</summary>
                <noscript><p class="docs-text">The navigation needs JavaScript — start from the <a href="{$prefix}index.html">overview</a>.</p></noscript>
            </details>
        </aside>

        <main class="docs-main">
            <p class="docs-eyebrow">{$groupLabel}</p>
            <h1>{$title}{$tagline}</h1>
            <p class="docs-lede">{$lede}</p>
            {$body}
        </main>

        <aside class="docs-toc" aria-label="On this page">
            <p class="docs-toc-title">On this page</p>
            <nav>{$tocHtml}</nav>
        </aside>
    </div>

    <script src="{$prefix}assets/livewire.js"></script>
    <script src="{$prefix}assets/flux.js"></script>
    <script src="{$prefix}assets/nav.js"></script>

    <script>
    // Build the sidebar from the nav data. textContent throughout — labels and
    // tags are data, never markup.
    (function () {
        var details = document.querySelector('.docs-nav');
        var slug = details.getAttribute('data-docs-slug');
        var prefix = details.getAttribute('data-docs-prefix');

        (window.__mdsNav || []).forEach(function (group) {
            var wrap = document.createElement('div');
            wrap.className = 'docs-nav-group';

            var title = document.createElement('p');
            title.className = 'docs-nav-title';
            title.textContent = group.title;
            wrap.appendChild(title);

            var items = document.createElement('div');
            items.className = 'docs-nav-items';

            group.items.forEach(function (item) {
                var link = document.createElement('a');
                link.href = prefix + item.path;
                link.textContent = item.label;

                if (item.slug === slug) link.setAttribute('aria-current', 'page');

                if (item.tag) {
                    var tag = document.createElement('span');
                    tag.className = 'docs-nav-tag';
                    tag.textContent = item.tag;
                    link.appendChild(document.createTextNode(' '));
                    link.appendChild(tag);
                }

                items.appendChild(link);
            });

            wrap.appendChild(items);
            details.appendChild(wrap);
        });
    })();

    // On a phone the full nav is ~2000px tall, so it starts collapsed there.
    // Without JS it stays open — the pre-fix behaviour, never worse.
    if (matchMedia('(max-width: 860px)').matches) {
        document.querySelector('.docs-nav').removeAttribute('open');
    }

    // Copy a snippet to the clipboard.
    document.querySelectorAll('.docs-copy').forEach(function (button) {
        button.addEventListener('click', function () {
            var code = button.parentElement.querySelector('code').innerText;
            navigator.clipboard.writeText(code).then(function () {
                button.classList.add('is-copied');
                setTimeout(function () { button.classList.remove('is-copied'); }, 1200);
            });
        });
    });

    // Scroll the sidebar to the page's own link. The sidebar is a fresh
    // element on every page, so without this a reader browsing the second half
    // of the component list re-scrolls it on every navigation.
    var currentLink = document.querySelector('.docs-sidebar a[aria-current="page"]');
    var sidebar = document.querySelector('.docs-sidebar');

    if (currentLink && currentLink.offsetTop > sidebar.clientHeight - 80) {
        sidebar.scrollTop = currentLink.offsetTop - sidebar.clientHeight / 2;
    }

    // Highlight the section the reader is looking at in the right-hand rail.
    var links = [].slice.call(document.querySelectorAll('.docs-toc a'));
    var targets = links.map(function (a) { return document.getElementById(a.hash.slice(1)); }).filter(Boolean);

    if (targets.length) {
        var mark = function () {
            var top = window.scrollY + 120;
            var active = targets[0];
            targets.forEach(function (t) { if (t.offsetTop <= top) active = t; });
            links.forEach(function (a) { a.toggleAttribute('aria-current', a.hash === '#' + active.id); });
        };
        mark();
        window.addEventListener('scroll', mark, { passive: true });
    }
    </script>

    </body>
    </html>
    HTML;
}
