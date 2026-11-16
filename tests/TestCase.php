<?php

namespace JeffersonGoncalves\LgpdConsent\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JeffersonGoncalves\Lgpd\LgpdServiceProvider;
use JeffersonGoncalves\LgpdConsent\LgpdConsentServiceProvider;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            LgpdServiceProvider::class,
            LgpdConsentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('lgpd-consent.route.enabled', true);
        $app['config']->set('lgpd-consent.route.middleware', ['web']);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(LgpdConsentServiceProvider::class);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }
}
