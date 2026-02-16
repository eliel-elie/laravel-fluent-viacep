<?php

namespace ViaCep\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ViaCep\ViaCepServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ViaCepServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Perform any environment setup here
    }
}
