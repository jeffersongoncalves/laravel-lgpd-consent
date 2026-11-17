<?php

namespace JeffersonGoncalves\LgpdConsent;

use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
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

        if (config('lgpd-consent.route.claim_on_login', true)) {
            Event::listen(Login::class, function (Login $event): void {
                $id = request()->cookie((string) config('lgpd-consent.route.cookie', 'lgpd_consent_id'));

                if (is_string($id) && $id !== '' && $event->user instanceof Model) {
                    $this->app->make(ConsentManager::class)->claim($id, $event->user);
                }
            });
        }

        Route::middleware((array) config('lgpd-consent.route.middleware', ['web']))
            ->post((string) config('lgpd-consent.route.path', 'lgpd/consent'), ConsentController::class)
            ->name('lgpd-consent.store');
    }
}
