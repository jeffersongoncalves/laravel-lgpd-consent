<?php

namespace JeffersonGoncalves\LgpdConsent\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\LgpdConsent\LgpdConsent
 */
class LgpdConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-lgpd-consent';
    }
}
