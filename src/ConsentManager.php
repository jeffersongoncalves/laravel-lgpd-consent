<?php

namespace JeffersonGoncalves\LgpdConsent;

use JeffersonGoncalves\LgpdConsent\Events\ConsentRecorded;
use JeffersonGoncalves\LgpdConsent\Models\ConsentRecord;
use JeffersonGoncalves\Privacy\Consent\ConsentManager as BaseConsentManager;

/**
 * Append-only consent ledger. A subject is an Eloquent model (a user) or a string anonymous id (a visitor).
 */
class ConsentManager extends BaseConsentManager
{
    protected string $config = 'lgpd-consent';

    protected string $model = ConsentRecord::class;

    protected string $event = ConsentRecorded::class;
}
