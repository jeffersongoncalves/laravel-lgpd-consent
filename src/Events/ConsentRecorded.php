<?php

namespace JeffersonGoncalves\LgpdConsent\Events;

use JeffersonGoncalves\Privacy\Consent\ConsentRecorded as BaseConsentRecorded;

/**
 * Fired after each grant or revoke ($record is a JeffersonGoncalves\LgpdConsent\Models\ConsentRecord).
 */
class ConsentRecorded extends BaseConsentRecorded {}
