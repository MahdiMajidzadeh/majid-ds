<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Tests\TestCase;

class StylesheetTest extends TestCase
{
    /**
     * Unlayered CSS outranks every Tailwind utility, so a component rule
     * written outside @layer cannot be overridden by a class — the caller has
     * no way back. [x-cloak] is the deliberate exception: it has to win over
     * whatever the component sets until Alpine boots.
     */
    public function test_every_component_rule_lives_in_a_layer(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/mds.css');

        // Strip comments, then walk the top level collecting block headers.
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);

        $headers = [];
        $depth = 0;
        $header = '';

        foreach (str_split($css) as $char) {
            if ($char === '{') {
                if ($depth === 0) {
                    $headers[] = trim(preg_replace('/\s+/', ' ', $header) ?? '');
                }

                $depth++;
                $header = '';
            } elseif ($char === '}') {
                $depth--;
            } elseif ($depth === 0) {
                $header .= $char;
            }
        }

        $this->assertNotEmpty($headers, 'Could not parse mds.css.');

        $allowed = ['@theme', '[x-cloak]'];

        foreach ($headers as $found) {
            $this->assertTrue(
                str_starts_with($found, '@layer ') || in_array($found, $allowed, true),
                "[{$found}] sits outside @layer, so no utility class can override it. Move it into @layer components.",
            );
        }
    }
}
