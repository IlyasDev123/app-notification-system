# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```sh
# Install dependencies and bootstrap
composer setup

# Run dev server (starts artisan dev)
composer dev

# Run all tests (clears config first, uses in-memory SQLite)
composer test

# Run a single test file
php artisan test tests/Unit/Service/NotificationEngineServiceTest.php

# Run tests filtered by name
php artisan test --filter=NotificationEngineServiceTest

# Lint / auto-fix code style (Laravel Pint)
./vendor/bin/pint

# Run migrations
php artisan migrate

# Retry failed notifications (artisan command)
php artisan notifications:retry-failed --tenant=<uuid> --channel=email --limit=50
```

Tests run against an in-memory SQLite database with `QUEUE_CONNECTION=sync`, so jobs execute inline rather than being deferred.

## Frontend (Admin Dashboard)

Web routes at `/` and `/tenants` serve a Blade + Tailwind v4 admin panel using Alpine.js (via CDN) for interactivity.

- `GET /` — dashboard overview (tenant counts, quick-start guide)
- `GET /tenants` — paginated tenant list
- `GET /tenants/create` — create tenant form
- `GET /tenants/{id}` — tenant detail page with 4 tabs: Overview, API Keys, Providers, Logs
- `POST /tenants/{id}/keys` — generate API key (plain text returned via flash session, shown in one-time modal)
- `DELETE /tenants/{id}/keys/{keyId}` — revoke API key
- `POST /tenants/{id}/providers` — configure provider (channel + provider + credentials form)
- `DELETE /tenants/{id}/providers/{providerId}` — remove provider

Web controllers are in `app/Http/Controllers/Web/`. Views are in `resources/views/tenants/`.

Alpine.js handles: tab switching on tenant detail page, modals for creating keys/providers, copy-to-clipboard for generated API keys.

## Architecture

This is a **multi-tenant notification dispatch API** built on Laravel 13. Tenants authenticate via API keys and submit notifications that are queued and dispatched through pluggable provider drivers.

### Request lifecycle

```
POST /api/v1/notifications/send
  → AuthenticateTenantApiKey middleware (resolves tenant from bearer token)
  → NotificationController::send()
  → SendNotificationJob dispatched to queue
  → Job calls NotificationEngineService::dispatch()
  → Provider sends the message, result logged to notification_logs
```

### Key layers

| Layer | Location | Purpose |
|-------|----------|---------|
| Contracts | `app/Contracts/` | Interfaces for all major services and repositories |
| Repositories | `app/Repositories/Eloquent*.php` | Eloquent implementations of repository interfaces |
| Services | `app/Services/` | Business logic (engine, tenant, API key, provider factory) |
| Providers (drivers) | `app/Services/Provider/` | Per-channel delivery: `MailgunProvider`, `TwilioProvider`, `SmtpProvider` |
| Jobs | `app/Jobs/SendNotificationJob.php` | Queued worker — retries 3×, backoff 10/30/90s |
| Middleware | `app/Http/Middleware/AuthenticateTenantApiKey.php` | Validates `identifier.secret` bearer tokens via SHA-256 hash comparison |

### Dependency injection

All interface-to-implementation bindings are registered in `AppServiceProvider`. When adding a new service or repository, bind its interface there. The notification engine (`NotificationEngineService`) depends on `NotificationLogRepositoryInterface`, injected via the container.

### Provider pattern

`NotificationProviderFactory::make(string $providerName)` resolves the correct driver class via a `match` expression. New providers must implement `NotificationProviderInterface::send()`. Register them in the factory's `match` block.

### API key format

Keys use the format `identifier.secret`. The middleware splits on `.`, looks up by `key_identifier`, then verifies with `hash_equals($stored_hash, hash('sha256', $fullToken))` — timing-safe comparison against the stored SHA-256 hash.

### Data model

- `Tenant` → has many `TenantApiKey`, `TenantProvider`, `NotificationLog`
- `TenantApiKey` — stores `key_identifier` + `key_hash` (SHA-256), supports expiry and active flag
- `TenantProvider` — per-tenant provider credentials (encrypted at rest via `EncryptionServiceInterface`)
- `NotificationLog` — audit trail with `status` (`queued` | `sent` | `failed`), `channel`, `provider`, `recipient`, `response` (JSON)
