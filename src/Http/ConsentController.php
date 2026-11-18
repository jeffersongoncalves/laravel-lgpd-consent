<?php

namespace JeffersonGoncalves\LgpdConsent\Http;

use JeffersonGoncalves\LgpdConsent\ConsentManager;
use JeffersonGoncalves\Privacy\Consent\ConsentController as BaseConsentController;

/**
 * Endpoint for front-end banners: POST {purpose, granted}.
 */
class ConsentController extends BaseConsentController
{
    protected string $manager = ConsentManager::class;
}
