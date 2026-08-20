<?php

namespace MajidDs\Tests;

use Afatmustafa\HugeIcons\BladeHugeIconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Flux\FluxServiceProvider;
use Livewire\LivewireServiceProvider;
use MajidDs\MajidDsServiceProvider;
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
}
