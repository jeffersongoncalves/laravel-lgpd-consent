# Changelog

All notable changes to this project will be documented in this file.

## 2.0.0 - 2026-11-18

### Breaking

- The engine moves to [laravel-privacy](https://github.com/jeffersongoncalves/laravel-privacy), the base shared by the LGPD, GDPR, CCPA, US privacy and Quebec suites. Every class of this package keeps its name and now extends its `JeffersonGoncalves\Privacy` base. If you extend one of them, check your overrides.

### Unchanged

- Config keys, tables, migrations, commands, events and the `#[PersonalData]` declarations: apps need no change.

### Added

- Suites installed side by side (e.g. LGPD and GDPR) only see their own declarations and events.

## 1.1.0 - 2026-11-16

- `LgpdConsent::claim($anonymousId, $user)`: carries a visitor's latest decision per purpose over to the account as new records (source `claimed`, original IP, user agent and expiry kept as proof). The ledger stays append-only; decisions are ordered by when they were originally made, so the latest one wins across devices.
- Runs automatically on Laravel's `Login` event when the consent endpoint is enabled (reads the `lgpd_consent_id` cookie). Turn off with `route.claim_on_login => false`.

## 1.0.0 - 2026-11-16

First release: append-only LGPD consent ledger.

- Grants/revocations per purpose with terms version, source, metadata and request proof (IP, user agent)
- Terms version bump invalidates older consents; optional expiry per purpose
- `HasConsents` trait for models, `LgpdConsent` facade for anonymous visitors
- Optional `POST /lgpd/consent` endpoint for banners (guest id in encrypted cookie)
- `ConsentRecorded` event; records are append-only, personal data columns anonymizable via laravel-lgpd

## [Unreleased]
