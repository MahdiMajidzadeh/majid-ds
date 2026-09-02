<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use MajidDs\Mds;
use MajidDs\Tests\TestCase;

/**
 * Every interactive component registers its Alpine behaviour in an inline
 * <script>. Under a Content-Security-Policy with `script-src 'nonce-…'` a
 * bare inline script is dropped and the whole kit goes inert. So each tag
 * carries @mdsNonce, which echoes the nonce the app registered with Laravel
 * (Vite::useCspNonce() — the registry Livewire's own tags read) or nothing.
 */
class CspNonceTest extends TestCase
{
    public function test_every_inline_script_in_the_kit_takes_the_nonce_directive(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views/';
        $offenders = [];
        $tags = 0;

        foreach (glob($root.'{*,*/*,*/*/*}.blade.php', GLOB_BRACE) ?: [] as $path) {
            $source = (string) file_get_contents($path);

            preg_match_all('/<script\b[^>]*>/', $source, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as [$tag, $offset]) {
                $tags++;

                if (! str_contains($tag, '@mdsNonce')) {
                    $offenders[] = substr($path, strlen($root)).':'.(substr_count($source, "\n", 0, $offset) + 1);
                }
            }
        }

        $this->assertGreaterThanOrEqual(10, $tags, 'Expected to find the kit\'s inline scripts.');
        $this->assertSame([], $offenders, "Inline <script> tags without @mdsNonce — blocked under a CSP:\n".implode("\n", $offenders));
    }

    public function test_scripts_carry_no_nonce_until_the_app_registers_one(): void
    {
        $this->assertNull(Mds::cspNonce());

        $html = Blade::render('<mds:quantity />');

        $this->assertStringContainsString('<script', $html);
        $this->assertStringNotContainsString('nonce', $html);
    }

    public function test_every_script_carries_the_nonce_registered_with_laravel(): void
    {
        Vite::useCspNonce('r4nd0m-n0nc3');

        $this->assertSame('r4nd0m-n0nc3', Mds::cspNonce());

        $html = Blade::render('<mds:quantity /><mds:countdown until="2030-01-01" />');

        preg_match_all('/<script[^>]*>/', $html, $tags);

        $this->assertGreaterThanOrEqual(2, count($tags[0]), 'Expected the components\' scripts and the shared digits partial.');
        $this->assertSame(array_fill(0, count($tags[0]), '<script nonce="r4nd0m-n0nc3">'), $tags[0], 'A script tag slipped through without the nonce.');
    }

    public function test_the_directive_serves_app_views_too_and_escapes(): void
    {
        $this->assertSame('<script >x</script>', trim(Blade::render('<script @mdsNonce>x</script>')));

        Vite::useCspNonce('a"b<c');

        $this->assertSame('<script nonce="a&quot;b&lt;c">x</script>', trim(Blade::render('<script @mdsNonce>x</script>')));
    }
}
