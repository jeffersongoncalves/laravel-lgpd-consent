<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use JeffersonGoncalves\LgpdConsent\Enums\ConsentAction;
use JeffersonGoncalves\LgpdConsent\Events\ConsentRecorded;
use JeffersonGoncalves\LgpdConsent\Facades\LgpdConsent;
use JeffersonGoncalves\LgpdConsent\Models\ConsentRecord;
use JeffersonGoncalves\LgpdConsent\Tests\Fixtures\User;

function user(): User
{
    return User::create(['name' => 'Maria']);
}

it('grants and revokes, keeping the full history', function () {
    Event::fake([ConsentRecorded::class]);
    $user = user();

    expect($user->hasConsent('marketing'))->toBeFalse();

    $user->grantConsent('marketing', source: 'signup');
    expect($user->hasConsent('marketing'))->toBeTrue();

    $user->revokeConsent('marketing');
    expect($user->hasConsent('marketing'))->toBeFalse()
        ->and(LgpdConsent::history($user, 'marketing')->pluck('action')->all())->toBe([ConsentAction::Granted, ConsentAction::Revoked])
        ->and($user->consents)->toHaveCount(2);

    Event::assertDispatchedTimes(ConsentRecorded::class, 2);
});

it('invalidates consent when the terms version is bumped', function () {
    config()->set('lgpd-consent.purposes', ['marketing' => ['version' => 'v1']]);
    $user = user();
    $user->grantConsent('marketing');

    expect($user->hasConsent('marketing'))->toBeTrue()
        ->and($user->consents()->first()->version)->toBe('v1');

    config()->set('lgpd-consent.purposes.marketing.version', 'v2');

    expect($user->hasConsent('marketing'))->toBeFalse();
});

it('expires consent after the configured period', function () {
    config()->set('lgpd-consent.purposes', ['marketing' => ['expires_after' => '1 year']]);
    $user = user();
    $user->grantConsent('marketing');

    $this->travel(13)->months();

    expect($user->hasConsent('marketing'))->toBeFalse();
});

it('rejects unknown purposes once purposes are configured', function () {
    config()->set('lgpd-consent.purposes', ['marketing' => []]);

    user()->grantConsent('newsletter');
})->throws(InvalidArgumentException::class);

it('records anonymous visitors by id', function () {
    LgpdConsent::grant('visitor-1', 'analytics');

    expect(LgpdConsent::has('visitor-1', 'analytics'))->toBeTrue()
        ->and(LgpdConsent::has('visitor-2', 'analytics'))->toBeFalse();
});

it('is append-only but can be anonymized', function () {
    $record = user()->grantConsent('marketing');

    expect(fn () => $record->update(['action' => ConsentAction::Revoked]))->toThrow(LogicException::class)
        ->and(fn () => $record->delete())->toThrow(LogicException::class);

    $record->refresh()->anonymizePersonalData();

    expect($record->fresh()->ip_address)->toBeNull()
        ->and($record->fresh()->action)->toBe(ConsentAction::Granted);
});

it('stores guest choices from the endpoint with a cookie id', function () {
    $response = $this->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/lgpd/consent', ['purpose' => 'analytics', 'granted' => true])
        ->assertNoContent()
        ->assertCookie('lgpd_consent_id');

    $id = $response->getCookie('lgpd_consent_id')->getValue();
    $record = ConsentRecord::sole();

    expect($record->anonymous_id)->toBe($id)
        ->and($record->ip_address)->toBe('127.0.0.1')
        ->and($record->source)->toBe('route');

    // Returning visitor keeps the same id.
    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->withCookie('lgpd_consent_id', $id)
        ->post('/lgpd/consent', ['purpose' => 'analytics', 'granted' => false])
        ->assertNoContent();

    expect(LgpdConsent::has($id, 'analytics'))->toBeFalse()
        ->and(LgpdConsent::history($id))->toHaveCount(2);
});

it('claims a visitor\'s latest decisions for the user, once', function () {
    LgpdConsent::grant('visitor-1', 'analytics');
    LgpdConsent::revoke('visitor-1', 'analytics');
    LgpdConsent::grant('visitor-1', 'marketing');
    $user = user();
    $user->grantConsent('newsletter');

    $claimed = LgpdConsent::claim('visitor-1', $user);

    expect($claimed)->toHaveCount(2)
        ->and($user->hasConsent('analytics'))->toBeFalse()
        ->and($user->hasConsent('marketing'))->toBeTrue()
        ->and(LgpdConsent::latest($user, 'marketing'))
        ->source->toBe('claimed')
        ->metadata->toMatchArray(['anonymous_id' => 'visitor-1'])
        ->ip_address->toBe('127.0.0.1')
        ->and(LgpdConsent::history('visitor-1'))->toHaveCount(3)
        ->and(LgpdConsent::claim('visitor-1', $user))->toHaveCount(0);
});

it('keeps the user\'s own later decision over the visitor\'s', function () {
    LgpdConsent::grant('visitor-1', 'marketing');
    $user = user();
    $user->revokeConsent('marketing');

    expect(LgpdConsent::claim('visitor-1', $user))->toHaveCount(0)
        ->and($user->hasConsent('marketing'))->toBeFalse();
});

it('claims the cookie id on login', function () {
    LgpdConsent::grant('visitor-1', 'analytics');
    $user = user();

    Route::middleware('web')->post('/test-login', function () use ($user) {
        Auth::login($user);

        return response()->noContent();
    });

    $this->withoutMiddleware(VerifyCsrfToken::class)
        ->withCookie('lgpd_consent_id', 'visitor-1')
        ->post('/test-login')
        ->assertNoContent();

    expect($user->hasConsent('analytics'))->toBeTrue();
});

it('stores logged-in choices on the user', function () {
    $user = user();

    $this->actingAs($user)
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->post('/lgpd/consent', ['purpose' => 'analytics', 'granted' => false])
        ->assertNoContent();

    expect($user->consents()->sole()->action)->toBe(ConsentAction::Revoked);
});
