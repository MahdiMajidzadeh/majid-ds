<?php

namespace MajidDs\Tests;

use Afatmustafa\HugeIcons\BladeHugeIconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Flux\FluxServiceProvider;
use Livewire\LivewireServiceProvider;
use MajidDs\MajidDsServiceProvider;
use MajidDs\Mds;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Runner\ErrorHandler;

abstract class TestCase extends Orchestra
{
    /**
     * Hand deprecations back to PHPUnit. Booting the Laravel app installs
     * Laravel's own error handler in front of PHPUnit's, and its
     * shouldIgnoreDeprecationErrors() swallows every deprecation while unit
     * tests run — so phpunit.xml.dist's failOnDeprecation never fired, and a
     * deprecation raised in src/ passed green.
     *
     * PHPUnit's handler is registered directly, not wrapped: PHPUnit strips
     * its own frame from the trace it categorises, but a wrapping closure adds
     * one it cannot strip, and an engine-invoked handler frame carries no file
     * — so the trigger's callee came back null and nothing could ever be
     * classified "indirect". Registered this way, PHPUnit's <source> scoping
     * holds: a deprecation triggered in third-party code and called from
     * third-party code is indirect and ignored, so framework noise across the
     * Laravel matrix cannot fail the build; one triggered in src/, or by src/
     * or a test calling a deprecated API directly, fails as it should. The
     * level mask leaves everything else to Laravel. (__invoke returns false,
     * which is why phpunit.xml.dist keeps display_errors off.)
     */
    protected function setUp(): void
    {
        parent::setUp();

        set_error_handler(ErrorHandler::instance(), E_DEPRECATED | E_USER_DEPRECATED);
    }

    protected function tearDown(): void
    {
        // Pop ours before Testbench tears the app down, so its own
        // restore_error_handler() removes Laravel's handler, not this one.
        restore_error_handler();

        parent::tearDown();
    }

    /**
     * Decode the `\uXXXX` escapes Blade's `@js()` / `Js::from()` emits, so an
     * assertion about a built-in Persian string reads the same on every
     * supported Laravel.
     *
     * Laravel 12 escapes non-ASCII into a JSON string (ق.ظ becomes
     * \u0642.\u0638) and Laravel 13 emits it literally. Both are the same
     * JavaScript string and both components behave identically; only the bytes
     * in the page differ. Eight assertions matched the literal form, so they
     * passed on 13 and failed on 12 while nothing was actually wrong — the kind
     * of version-specific test that makes a matrix look like a product bug.
     */
    protected function jsDecoded(string $html): string
    {
        // Two passes, the longer form first. Laravel 12 renders a Js::from()
        // payload as JSON.parse('…') — a JSON string inside a JavaScript string
        // literal — so a Persian letter arrives double-escaped as \\u0627,
        // while a plain @js() attribute escapes an ellipsis once as \u2026.
        // Decoding the single form first would consume half of the double one
        // and leave a stray backslash in front of every letter.
        $html = (string) preg_replace_callback(
            '/\\\\\\\\u([0-9a-fA-F]{4})/',
            fn (array $m): string => mb_chr((int) hexdec($m[1]), 'UTF-8'),
            $html,
        );

        return (string) preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            fn (array $m): string => mb_chr((int) hexdec($m[1]), 'UTF-8'),
            $html,
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FluxServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeHugeIconsServiceProvider::class,
            MajidDsServiceProvider::class,
        ];
    }

    /**
     * Testbench does not read composer.json's extra.laravel block, so the
     * facade alias it declares has to be mirrored here to match a real app.
     *
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['Mds' => Mds::class];
    }
}
