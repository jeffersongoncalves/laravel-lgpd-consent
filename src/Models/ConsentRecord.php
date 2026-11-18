<?php

namespace JeffersonGoncalves\LgpdConsent\Models;

use JeffersonGoncalves\Lgpd\Attributes\PersonalData;
use JeffersonGoncalves\Lgpd\Concerns\InteractsWithPersonalData;
use JeffersonGoncalves\Lgpd\Contracts\HasPersonalData;
use JeffersonGoncalves\Lgpd\Enums\LegalBasis;
use JeffersonGoncalves\LgpdConsent\Enums\ConsentAction;
use JeffersonGoncalves\Privacy\Consent\ConsentRecord as BaseConsentRecord;

/**
 * One immutable consent event. The ledger is append-only: a new decision is a new row.
 * Only the personal data columns may change, so the person can still be anonymized.
 *
 * @property ConsentAction $action
 */
#[PersonalData('ip_address', purpose: 'Proof of consent (art. 8, §2)', legalBasis: LegalBasis::ExerciseOfRights)]
#[PersonalData('user_agent', purpose: 'Proof of consent (art. 8, §2)', legalBasis: LegalBasis::ExerciseOfRights)]
class ConsentRecord extends BaseConsentRecord implements HasPersonalData
{
    use InteractsWithPersonalData;

    protected string $actionEnum = ConsentAction::class;

    public function getTable(): string
    {
        return config('lgpd-consent.table', 'lgpd_consents');
    }
}
