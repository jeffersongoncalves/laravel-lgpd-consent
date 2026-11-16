<?php

namespace JeffersonGoncalves\LgpdConsent\Events;

use JeffersonGoncalves\LgpdConsent\Models\ConsentRecord;

class ConsentRecorded
{
    public function __construct(public ConsentRecord $record) {}
}
