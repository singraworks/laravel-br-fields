<?php

declare(strict_types=1);

namespace SingraWorks\LaravelBrFields\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use SingraWorks\LaravelBrFields\BrFieldsServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param Application $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [BrFieldsServiceProvider::class];
    }
}
