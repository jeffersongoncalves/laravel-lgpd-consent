<?php

namespace JeffersonGoncalves\LgpdConsent;

use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LgpdConsent\Http\ConsentController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LgpdConsentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lgpd-consent')
            ->hasConfigFile()
            ->hasMigrations(['create_lgpd_consents_table']);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ConsentManager::class);
    }

    public function packageBooted(): void
    {
        if (! config('lgpd-consent.route.enabled', false)) {
            return;
        }

        Route::middleware((array) config('lgpd-consent.route.middleware', ['web']))
            ->post((string) config('lgpd-consent.route.path', 'lgpd/consent'), ConsentController::class)
            ->name('lgpd-consent.store');
    }
}
