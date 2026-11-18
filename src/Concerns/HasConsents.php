<?php

namespace JeffersonGoncalves\LgpdConsent\Concerns;

use Illuminate\Database\Eloquent\Model;
use JeffersonGoncalves\LgpdConsent\ConsentManager;
use JeffersonGoncalves\LgpdConsent\Models\ConsentRecord;
use JeffersonGoncalves\Privacy\Consent\Concerns\HasConsents as BaseHasConsents;
use JeffersonGoncalves\Privacy\Consent\ConsentManager as BaseConsentManager;

/**
 * @mixin Model
 */
trait HasConsents
{
    use BaseHasConsents;

    protected static function consentManager(): BaseConsentManager
    {
        return app(ConsentManager::class);
    }

    protected static function consentRecordModel(): string
    {
        return ConsentRecord::class;
    }
}
