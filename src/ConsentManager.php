<?php

namespace JeffersonGoncalves\LgpdConsent;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JeffersonGoncalves\LgpdConsent\Enums\ConsentAction;
use JeffersonGoncalves\LgpdConsent\Events\ConsentRecorded;
use JeffersonGoncalves\LgpdConsent\Models\ConsentRecord;

/**
 * A subject is an Eloquent model (a user) or a string anonymous id (a visitor).
 */
class ConsentManager
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function grant(Model|string $subject, string $purpose, ?string $version = null, ?string $source = null, array $metadata = []): ConsentRecord
    {
        $expiresAfter = $this->purpose($purpose)['expires_after'] ?? null;

        return $this->record($subject, $purpose, ConsentAction::Granted, $version ?? $this->currentVersion($purpose), $source, $metadata, [
            'expires_at' => is_string($expiresAfter) ? now()->add(CarbonInterval::make($expiresAfter)) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function revoke(Model|string $subject, string $purpose, ?string $source = null, array $metadata = []): ConsentRecord
    {
        return $this->record($subject, $purpose, ConsentAction::Revoked, $this->currentVersion($purpose), $source, $metadata);
    }

    /**
     * Latest decision is a grant, not expired, and for the required terms version
     * (the configured current version when none is given).
     */
    public function has(Model|string $subject, string $purpose, ?string $version = null): bool
    {
        $latest = $this->latest($subject, $purpose);
        $required = $version ?? $this->currentVersion($purpose);

        return $latest !== null
            && $latest->isActive()
            && ($required === null || $latest->version === $required);
    }

    public function latest(Model|string $subject, string $purpose): ?ConsentRecord
    {
        return $this->query($subject)->where('purpose', $purpose)->latest('id')->first();
    }

    /**
     * @return Collection<int, ConsentRecord>
     */
    public function history(Model|string $subject, ?string $purpose = null): Collection
    {
        return $this->query($subject)
            ->when($purpose !== null, fn (Builder $query) => $query->where('purpose', $purpose))
            ->oldest('id')
            ->get();
    }

    /**
     * @return Builder<ConsentRecord>
     */
    public function query(Model|string $subject): Builder
    {
        $query = ConsentRecord::query();

        if (is_string($subject)) {
            return $query->where('anonymous_id', $subject);
        }

        return $query->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', (string) $subject->getKey());
    }

    public function currentVersion(string $purpose): ?string
    {
        $version = $this->purpose($purpose)['version'] ?? null;

        return is_string($version) ? $version : null;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<string, mixed>  $attributes
     */
    protected function record(Model|string $subject, string $purpose, ConsentAction $action, ?string $version, ?string $source, array $metadata, array $attributes = []): ConsentRecord
    {
        $request = config('lgpd-consent.store_request', true) ? request() : null;

        $record = ConsentRecord::create([
            ...(is_string($subject)
                ? ['anonymous_id' => $subject]
                : ['subject_type' => $subject->getMorphClass(), 'subject_id' => (string) $subject->getKey()]),
            'purpose' => $purpose,
            'version' => $version,
            'action' => $action,
            'source' => $source,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
            ...$attributes,
        ]);

        event(new ConsentRecorded($record));

        return $record;
    }

    /**
     * @return array<string, mixed>
     */
    protected function purpose(string $purpose): array
    {
        $purposes = (array) config('lgpd-consent.purposes', []);

        if ($purposes === []) {
            return [];
        }

        if (! array_key_exists($purpose, $purposes)) {
            throw new InvalidArgumentException("Unknown consent purpose [{$purpose}]. Add it to lgpd-consent.purposes.");
        }

        return (array) $purposes[$purpose];
    }
}
