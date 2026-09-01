<?php

namespace MajidDs\Tests;

use Afatmustafa\HugeIcons\BladeHugeIconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Flux\FluxServiceProvider;
use Livewire\LivewireServiceProvider;
use MajidDs\MajidDsServiceProvider;
use MajidDs\Mds;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
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
