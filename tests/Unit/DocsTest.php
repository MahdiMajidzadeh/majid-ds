<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Tests\TestCase;

/**
 * The kit's API used to be written out three times — llms.txt, the docs pages,
 * and a 660-line hand-kept Components section in README.md — and only llms.txt
 * had a test behind it. The other two drifted, which is exactly what an
 * unenforced copy does.
 *
 * There are two copies left and each is now pinned from the side it can rot on:
 *
 * - llms.txt is checked for COMPLETENESS by LlmsTxtTest: every `@props` key of
 *   every view has to appear in that component's section.
 * - the docs' `reference` tables are checked here for ACCURACY, against
 *   llms.txt rather than against `@props` directly. A table legitimately
 *   documents things that are not props — `wire:model`, Flux's colon
 *   attributes like `label:sr-only`, and attributes a wrapped component
 *   forwards — so comparing with `@props` alone reports two dozen false
 *   positives. Tying the tables to llms.txt instead is transitive and exact:
 *   llms.txt cannot omit a real prop (LlmsTxtTest) and the tables cannot
 *   describe something llms.txt has never heard of, so neither can drift from
 *   the code without a test going red. Completeness is deliberately NOT asked
 *   of the tables: `fa` and the slot names are explained once in the guides
 *   rather than repeated into thirty tables.
 * - README.md does not document components at all. It points at the docs site
 *   and llms.txt and stops there, and the test below fails the build if a
 *   per-component section grows back — that copy is the one that rotted last
 *   time, 664 lines of it, describing props that had moved on.
 */
class DocsTest extends TestCase
{
    /** @var array<string, array<string, mixed>> */
    private array $pages;

    private string $readme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pages = require dirname(__DIR__, 2).'/bin/docs/mds.php';
        $this->readme = (string) file_get_contents(dirname(__DIR__, 2).'/README.md');
    }

    public function test_every_prop_named_in_a_docs_reference_table_is_in_llms_txt(): void
    {
        $llms = (string) file_get_contents(dirname(__DIR__, 2).'/llms.txt');
        $checked = 0;
        $unknown = [];

        foreach ($this->pages as $slug => $page) {
            foreach ($page['reference'] ?? [] as $block) {
                $name = $block['name'] ?? null;

                if (! is_string($name) || ! str_contains($name, 'mds:')) {
                    continue;
                }

                // One table may cover several tags that share a shape — the
                // four mds:input presets do.
                $tags = preg_split('/\s*·\s*/', $name) ?: [];
                $section = '';

                foreach ($tags as $tag) {
                    $this->assertNotNull(
                        $this->viewFor($tag),
                        "The reference table names <{$tag}> on the {$slug} page, but no view renders it.",
                    );

                    $section .= $this->llmsSectionFor($llms, $tag);
                }

                $this->assertNotSame('', $section, "llms.txt has no section covering <{$name}>.");

                foreach ($block['props'] ?? [] as $row) {
                    $prop = $row[0] ?? '';

                    // The "…" catch-all for a wrapped component's passthrough,
                    // and slot names written in angle brackets.
                    if ($prop === '' || $prop === '…' || str_contains($prop, '<')) {
                        continue;
                    }

                    $checked++;

                    if (! preg_match('/(?<![a-z0-9:-])'.preg_quote($prop, '/').'(?![a-z0-9-])/i', $section)) {
                        $unknown[] = "{$slug}: <{$name}> documents `{$prop}`, which its llms.txt section never mentions";
                    }
                }
            }
        }

        $this->assertGreaterThan(150, $checked, 'Expected the docs to document well over 150 props.');
        $this->assertSame([], $unknown, "The docs and llms.txt disagree:\n".implode("\n", $unknown));
    }

    public function test_the_readme_does_not_document_components(): void
    {
        // The README points at the docs site and llms.txt and stops there. A
        // per-component section here is a third copy of an API that two other
        // places already carry, and it is the copy that rotted last time —
        // 664 lines of it, describing props that had moved on.
        $this->assertStringNotContainsString(
            '### `<mds:',
            $this->readme,
            'README.md is documenting components again — the reference belongs in bin/docs/mds.php and llms.txt.',
        );

        $this->assertStringNotContainsString(
            "\nProps:",
            $this->readme,
            'README.md is listing props again — llms.txt and the component pages own that.',
        );

        // And it still sends the reader somewhere that is kept current.
        $this->assertStringContainsString('llms.txt', $this->readme);
        $this->assertStringContainsString('docs/index.html', $this->readme);
    }

    /** The view that renders a tag, or null when nothing does. */
    private function viewFor(string $tag): ?string
    {
        $name = str_replace('.', '/', substr($tag, 4));
        $root = dirname(__DIR__, 2).'/resources/views/mds/';

        return match (true) {
            file_exists($root.$name.'.blade.php') => $root.$name.'.blade.php',
            file_exists($root.$name.'/index.blade.php') => $root.$name.'/index.blade.php',
            default => null,
        };
    }

    /**
     * A tag's own `###` section in llms.txt, or its parent's when it is
     * documented as a bullet there — the same rule LlmsTxtTest applies.
     */
    private function llmsSectionFor(string $llms, string $tag): string
    {
        foreach ([$tag, 'mds:'.explode('.', substr($tag, 4))[0]] as $heading) {
            $start = strpos($llms, "### <{$heading}>");

            if ($start === false) {
                continue;
            }

            preg_match('/\n##+ /', $llms, $next, PREG_OFFSET_CAPTURE, $start + 4);

            return substr($llms, $start, $next ? $next[0][1] - $start : null);
        }

        return '';
    }

}
