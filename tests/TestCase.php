<?php

namespace JeffersonGoncalves\LgpdConsent\Tests;

use JeffersonGoncalves\LgpdConsent\LgpdConsentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LgpdConsentServiceProvider::class,
        ];
    }
}
