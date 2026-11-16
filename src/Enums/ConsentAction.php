<?php

namespace JeffersonGoncalves\LgpdConsent\Enums;

enum ConsentAction: string
{
    case Granted = 'granted';
    case Revoked = 'revoked';
}
