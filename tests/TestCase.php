<?php

namespace MajidDs\Tests;

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
            MajidDsServiceProvider::class,
        ];
    }
}
