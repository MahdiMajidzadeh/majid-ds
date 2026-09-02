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
