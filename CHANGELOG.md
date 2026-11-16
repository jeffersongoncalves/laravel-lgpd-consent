# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0 - 2026-11-16

First release: append-only LGPD consent ledger.

- Grants/revocations per purpose with terms version, source, metadata and request proof (IP, user agent)
- Terms version bump invalidates older consents; optional expiry per purpose
- `HasConsents` trait for models, `LgpdConsent` facade for anonymous visitors
- Optional `POST /lgpd/consent` endpoint for banners (guest id in encrypted cookie)
- `ConsentRecorded` event; records are append-only, personal data columns anonymizable via laravel-lgpd

## [Unreleased]
