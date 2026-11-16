<?php

namespace JeffersonGoncalves\LgpdConsent\Facades;

use Illuminate\Support\Facades\Facade;
use JeffersonGoncalves\LgpdConsent\ConsentManager;

/**
 * @method static \JeffersonGoncalves\LgpdConsent\Models\ConsentRecord grant(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $version = null, ?string $source = null, array $metadata = [])
 * @method static \JeffersonGoncalves\LgpdConsent\Models\ConsentRecord revoke(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $source = null, array $metadata = [])
 * @method static bool has(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose, ?string $version = null)
 * @method static \JeffersonGoncalves\LgpdConsent\Models\ConsentRecord|null latest(\Illuminate\Database\Eloquent\Model|string $subject, string $purpose)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \JeffersonGoncalves\LgpdConsent\Models\ConsentRecord> history(\Illuminate\Database\Eloquent\Model|string $subject, ?string $purpose = null)
 *
 * @see ConsentManager
 */
class LgpdConsent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConsentManager::class;
    }
}
