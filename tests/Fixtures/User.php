<?php

namespace JeffersonGoncalves\LgpdConsent\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use JeffersonGoncalves\LgpdConsent\Concerns\HasConsents;

class User extends Authenticatable
{
    use HasConsents;

    protected $guarded = [];

    public $timestamps = false;
}
