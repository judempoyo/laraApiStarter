# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v1.0.0.html).

## [1.0.0] - 2026-07-24

### Added

**Auth driver abstraction**
- `app/Contracts/Auth/TokenServiceInterface.php` — driver-agnostic token management contract
- `app/Services/Auth/SanctumTokenService.php` — Sanctum implementation
- `app/Services/Auth/PassportTokenService.php` — Passport stub with full setup instructions
- `AUTH_DRIVER` and `AUTH_GUARD` env variables — switch between Sanctum and Passport with a single config change

**Centralized configuration**
- `config/api.php` — single source of truth for version, auth driver, rate limits, pagination, security headers, and suspicious patterns

**ApiException factory methods**
- `ApiException::unauthorized()` — 401
- `ApiException::conflict()` — 409
- `ApiException::gone()` — 410
- `ApiException::unprocessable()` — 422 (business logic)
- `ApiException::tooManyRequests()` — 429
- `ApiException::serviceUnavailable()` — 503

**ApiResponse semantic helpers**
- `ApiResponse::created()` — 201
- `ApiResponse::accepted()` — 202
- `ApiResponse::noContent()` — 204

**Security middlewares**
- `app/Http/Middleware/RequestSizeLimitMiddleware.php` — rejects oversized requests (configurable via `REQUEST_MAX_SIZE_KB`)
- `app/Http/Middleware/SuspiciousRequestMiddleware.php` — blocks SQLi, path traversal, and XSS patterns
- `app/Http/Middleware/OptionalAuth.php` — driver-agnostic replacement for `OptionalSanctumAuth`
- `app/Http/Middleware/AuthenticateWithApiKey.php` — authenticates via `X-API-Key` header

**Role-separated route files**
- `routes/api/admin.php` — `role:admin` protected routes
- `routes/api/user.php` — authenticated user routes (loaded from `routes/api.php`)

**Two-Factor Authentication (2FA)**
- `app/Actions/Auth/TwoFactor/EnableTwoFactorAction.php`
- `app/Actions/Auth/TwoFactor/ConfirmTwoFactorAction.php`
- `app/Actions/Auth/TwoFactor/VerifyTwoFactorAction.php`
- `app/Actions/Auth/TwoFactor/DisableTwoFactorAction.php`
- `app/Http/Controllers/Api/Auth/TwoFactorController.php`
- `app/Enums/Result/Auth/TwoFactorResult.php`
- `database/migrations/2026_07_24_000001_add_two_factor_to_users_table.php`
- Routes: `POST /auth/two-factor/enable|confirm|verify`, `DELETE /auth/two-factor`

**API Keys (machine-to-machine)**
- `app/Models/ApiKey.php`
- `database/migrations/2026_07_24_000002_create_api_keys_table.php`
- `app/Http/Controllers/Api/ApiKeyController.php`
- Routes: `GET|POST|DELETE /user/api-keys`

**Admin Impersonation**
- `app/Actions/Admin/StartImpersonationAction.php`
- `app/Http/Controllers/Api/Admin/ImpersonationController.php`
- Routes: `POST /admin/impersonate/{userId}`, `DELETE /admin/impersonate`

**Scaffold generator**
- `app/Console/Commands/MakeApiScaffoldCommand.php` — `php artisan make:api-scaffold {entity}` generates Model, Controller, 3 Actions, 2 DTOs, 2 Requests, 1 Resource

**Artisan install command**
- `app/Console/Commands/InstallApiCommand.php` — `php artisan api:install` — interactive driver selection, key generation, migration, seeder, storage link

**User Preferences API**
- `app/Models/UserPreference.php`
- `database/migrations/2026_07_24_000003_create_user_preferences_table.php`
- `app/Http/Controllers/Api/User/PreferenceController.php`
- Routes: `GET|PUT|DELETE /user/preferences/{key}`

**In-App Notifications API**
- `app/Http/Controllers/Api/User/NotificationController.php`
- Routes: `GET /user/notifications`, `POST /user/notifications/{id}/read`, `POST /user/notifications/read-all`, `DELETE /user/notifications/{id}`

**Tests**
- `tests/Feature/Auth/TwoFactorTest.php`
- `tests/Feature/ApiKeyTest.php`
- `tests/Feature/UserPreferenceTest.php`
- `tests/Feature/NotificationTest.php`
- `tests/Feature/HealthAndSecurityTest.php`
- `tests/Feature/Admin/ImpersonationTest.php`
- `tests/Unit/ApiResponseTest.php`
- `tests/Unit/ApiExceptionTest.php`

**ErrorCode enum additions**
- `CONFLICT`, `TOO_MANY_REQUESTS`, `GONE`, `UNPROCESSABLE`, `SERVICE_UNAVAILABLE`
- `TWO_FACTOR_ALREADY_ENABLED`, `TWO_FACTOR_NOT_ENABLED`, `TWO_FACTOR_INVALID_CODE`, `TWO_FACTOR_NOT_CONFIRMED`
- `API_KEY_INVALID`, `API_KEY_EXPIRED`, `API_KEY_REVOKED`
- `IMPERSONATION_NOT_ALLOWED`, `IMPERSONATION_SELF`

**SecurityEvent enum additions**
- `IMPERSONATION_STARTED`, `IMPERSONATION_STOPPED`
- `TWO_FACTOR_ENABLED`, `TWO_FACTOR_DISABLED`
- `API_KEY_CREATED`, `API_KEY_REVOKED`

### Changed

- `app/Providers/AppServiceProvider.php` — binds `TokenServiceInterface`, consolidates all rate limiters (removed duplicate from `bootstrap/app.php`)
- `app/Http/Controllers/Api/Auth/AuthController.php` — injected `TokenServiceInterface`, all error paths now `throw ApiException::xxx()`
- `app/Http/Controllers/Api/Auth/ProfileController.php` — `throw ApiException` replaces `ApiResponse::error()`
- `app/Actions/Auth/LoginUserAction.php` — uses `TokenServiceInterface`
- `app/Actions/Auth/RegisterUserAction.php` — uses `TokenServiceInterface`
- `app/Http/Middleware/AdminMiddleware.php` — throws `ApiException::forbidden()` instead of returning `ApiResponse::error()`
- `app/Http/Middleware/SecurityHeadersMiddleware.php` — CSP directives now driven by `config/api.php`
- `app/Http/Controllers/Api/HealthController.php` — added queue/storage health checks, `Retry-After` header on 503
- `app/Http/Responses/ApiResponse.php` — added `created()`, `accepted()`, `noContent()`, fixed `config('api.version')`
- `app/Models/User.php` — added `apiKeys()`, `preferences()`, 2FA columns to fillable/casts, `hasTwoFactorEnabled()` helper
- `app/Http/Resources/UserResource.php` — added `two_factor_enabled` field
- `bootstrap/app.php` — removed duplicate rate limiters, added `optional.auth` and `api.key` middleware aliases, registered new security middlewares
- `routes/api.php` — added 2FA routes, session sub-resource routes, loads `routes/api/admin.php` and `routes/api/user.php`
- `composer.json` — package renamed to `jump/lara-api-starter`, `post-create-project-cmd` now calls `api:install`, `pragmarx/google2fa` declared
- `.env.example` — added `AUTH_DRIVER`, `AUTH_GUARD`, `RATE_LIMIT_*`, `REQUEST_MAX_SIZE_KB`, `PAGINATION_*`

---

## [Unreleased]

### Added
- **CLI Code Generation System** — Full Artisan command suite for scaffolding API layers:
  - `php artisan las:dto` — Generate DTO classes with model introspection
  - `php artisan las:action` — Generate Action classes with DTO wiring
  - `php artisan las:request` — Generate API Form Requests (extends `ApiRequest`)
  - `php artisan las:resource` — Generate API Resources with model field detection
  - `php artisan las:controller` — Generate skinny CRUD controllers under `Api\v1\`
  - `php artisan las:all {Entity} --crud` — Orchestrate full stack generation (9 files)
- **Model Introspection** — Commands auto-detect model `$fillable`, `$casts`, `$hidden`
- **`ActionInterface`** contract (`App\Contracts\ActionInterface`) for testable, swappable actions
- **`RequestIdMiddleware`** — Attaches `X-Request-Id` UUID to every request/response
- **`HealthController`** — `GET /api/v1/health` endpoint returning DB and cache status
- **`QueryFilter`** base class + `Filterable` trait — Declarative model filtering
- **Composer scripts** — `composer format`, `composer analyze`, `composer check-all`

### Fixed
- Duplicate route — Two `PATCH /profile` routes now correctly separated

### Changed
- `declare(strict_types=1)` added to all PHP files across the codebase
- `ApiException` handler registered in `bootstrap/app.php`
- `RequestIdMiddleware` appended to global middleware stack

---

### Added
- Action-DTO architecture for clean business logic separation
- Laravel Sanctum authentication (login, register, logout, token refresh)
- Queued email verification and password reset notifications
- RBAC via `spatie/laravel-permission` with `api` guard
- Security headers middleware (CSP, X-Frame-Options, HSTS)
- Rate limiting for auth and API routes
- Audit logging via `LogsActivity` trait
- Standardized `ApiResponse` class with `ErrorCode` enums
- Interactive API documentation via Scramble + Scalar
