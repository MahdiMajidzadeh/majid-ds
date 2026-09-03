<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Tests\TestCase;
use ReflectionClass;

/**
 * llms.txt is the API contract agents generate code against, and the first
 * file CLAUDE.md tells them to read. It fell five commits behind the views
 * once — eleven props missing, one line contradicting the code — because
 * keeping it current relied on remembering step 5 of a checklist. These
 * tests make drift a failing build instead.
 */
class LlmsTxtTest extends TestCase
{
    private string $llms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->llms = (string) file_get_contents(dirname(__DIR__, 2).'/llms.txt');
    }

    public function test_every_component_prop_is_documented(): void
    {
        $checked = 0;

        foreach ($this->views() as $view => $tag) {
            $section = $this->sectionFor($tag);

            $this->assertNotNull($section, "<{$tag}> ({$view}) has no `### <{$tag}>` heading and is not mentioned under its parent's section in llms.txt.");

            foreach ($this->propsOf($view) as $prop) {
                $kebab = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $prop));

                $this->assertTrue(
                    $this->mentions($section, $kebab) || $this->mentions($section, $prop),
                    "Prop `{$kebab}` of <{$tag}> ({$view}) is not documented in its llms.txt section.",
                );

                $checked++;
            }
        }

        $this->assertGreaterThan(100, $checked, 'Expected to check well over a hundred props.');
    }

    public function test_every_documented_component_still_exists(): void
    {
        preg_match_all('/^### <(mds:[a-z0-9.-]+)>/m', $this->llms, $matches);

        $this->assertNotEmpty($matches[1]);

        $real = array_values($this->views());

        foreach ($matches[1] as $tag) {
            $this->assertContains($tag, $real, "llms.txt documents <{$tag}> but no view renders it.");
        }
    }

    public function test_component_sections_sit_under_the_components_heading(): void
    {
        // A "## CSS layer" heading was once left open and swallowed seventeen
        // component sections. Agents read structure; keep it honest.
        $current = null;

        foreach (explode("\n", $this->llms) as $number => $line) {
            if (str_starts_with($line, '## ')) {
                $current = $line;
            } elseif (str_starts_with($line, '### <mds:')) {
                $this->assertSame('## Components', $current, 'Line '.($number + 1).": {$line} sits under [{$current}], not under ## Components.");
            }
        }
    }

    public function test_every_public_helper_is_documented(): void
    {
        foreach (['Persian', 'Jalali', 'Charts', 'Icons', 'Iran'] as $class) {
            $reflection = new ReflectionClass('MajidDs\\Support\\'.$class);

            $this->assertStringContainsString("`MajidDs\\Support\\{$class}`", $this->llms, "The PHP API section does not introduce {$class}.");

            foreach ($reflection->getMethods() as $method) {
                if (! $method->isPublic() || ! $method->isStatic() || $method->getDeclaringClass()->getName() !== $reflection->getName()) {
                    continue;
                }

                $this->assertStringContainsString(
                    '`'.$method->getName(),
                    $this->llms,
                    "{$class}::{$method->getName()}() is public but not documented in llms.txt.",
                );
            }
        }
    }

    public function test_every_config_key_and_directive_is_documented(): void
    {
        $config = require dirname(__DIR__, 2).'/config/mds.php';

        foreach ($this->flatten($config) as $key) {
            $this->assertStringContainsString("`mds.{$key}`", $this->llms, "Config key mds.{$key} is not documented in llms.txt.");
        }

        $provider = (string) file_get_contents(dirname(__DIR__, 2).'/src/MajidDsServiceProvider.php');

        preg_match_all("/Blade::directive\('([a-zA-Z]+)'/", $provider, $directives);

        $this->assertNotEmpty($directives[1]);

        foreach ($directives[1] as $directive) {
            $this->assertStringContainsString("`@{$directive}", $this->llms, "Directive @{$directive} is not documented in llms.txt.");
        }
    }

    /**
     * Every view under resources/views/mds/ mapped to the tag that renders it.
     *
     * @return array<string, string> view path (relative) => tag
     */
    private function views(): array
    {
        $root = dirname(__DIR__, 2).'/resources/views/mds/';
        $views = [];

        foreach (glob($root.'{*.blade.php,*/*.blade.php}', GLOB_BRACE) ?: [] as $path) {
            $relative = substr($path, strlen($root));
            $name = substr($relative, 0, -strlen('.blade.php'));

            $tag = 'mds:'.match (true) {
                str_ends_with($name, '/index') => substr($name, 0, -6),
                str_contains($name, '/') => str_replace('/', '.', $name),
                default => $name,
            };

            $views[$relative] = $tag;
        }

        return $views;
    }

    /**
     * The text documenting a tag: its own `###` section, or — for a part
     * documented as a bullet inside its parent — the parent's section.
     */
    private function sectionFor(string $tag): ?string
    {
        $own = $this->headingSection($tag);

        if ($own !== null) {
            return $own;
        }

        $parent = 'mds:'.explode('.', substr($tag, 4))[0];
        $section = $this->headingSection($parent);

        return $section !== null && str_contains($section, "<{$tag}") ? $section : null;
    }

    private function headingSection(string $tag): ?string
    {
        $start = strpos($this->llms, "### <{$tag}>");

        if ($start === false) {
            return null;
        }

        preg_match('/\n##+ /', $this->llms, $next, PREG_OFFSET_CAPTURE, $start + 4);

        return substr($this->llms, $start, $next ? $next[0][1] - $start : null);
    }

    /**
     * @return list<string>
     */
    private function propsOf(string $view): array
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/'.$view);

        if (! preg_match('/@props\(\[(.*?)\]\)/s', $source, $block)) {
            return [];
        }

        preg_match_all("/^\s*'([a-zA-Z]+)'\s*=>/m", $block[1], $keys);

        return $keys[1];
    }

    private function mentions(string $haystack, string $word): bool
    {
        return (bool) preg_match('/(?<![a-z0-9-])'.preg_quote($word, '/').'(?![a-z0-9-])/i', $haystack);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function flatten(array $config, string $prefix = ''): array
    {
        $keys = [];

        foreach ($config as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $value !== [] && ! array_is_list($value)) {
                $keys = [...$keys, ...$this->flatten($value, $path)];
            } else {
                $keys[] = $path;
            }
        }

        return $keys;
    }
}
