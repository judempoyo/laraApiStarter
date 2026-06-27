# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **CLI Code Generation System** — Full Artisan command suite for scaffolding API layers:
  - `php artisan las:dto` — Generate DTO classes with model introspection
  - `php artisan las:action` — Generate Action classes with DTO wiring
  - `php artisan las:request` — Generate API Form Requests (extends `ApiRequest`)
  - `php artisan las:resource` — Generate API Resources with model field detection
  - `php artisan las:controller` — Generate skinny CRUD controllers under `Api\v1\`
  - `php artisan las:all {Entity} --crud` — Orchestrate full stack generation (9 files)
- **Model Introspection** — Commands auto-detect model `$fillable`, `$casts`, `$hidden` to pre-fill:
  - DTO properties with correct PHP types
  - Request validation rules with human-readable messages
  - Resource fields (excluding hidden attributes)
- **Configuration** — `config/api-generator.php` for customizable namespaces and paths
- **`ActionInterface`** contract (`App\Contracts\ActionInterface`) for testable, swappable actions
- **`ApiException`** (`App\Exceptions\ApiException`) — Custom exception with `ErrorCode`, static factories (`notFound`, `forbidden`, `validation`, `serverError`)
- **`RequestIdMiddleware`** — Attaches `X-Request-Id` UUID to every request/response for distributed tracing
- **`HealthController`** — `GET /api/v1/health` endpoint returning DB and cache status with latency metrics
- **`QueryFilter`** base class + `Filterable` trait — Declarative model filtering with `?sort=-created_at,name` syntax
- **Composer scripts** — `composer format`, `composer analyze`, `composer check-all`

### Fixed
- **Duplicate route** — Two `PATCH /profile` routes (update + changeEmail) now correctly separated (`/profile` and `/profile/email`)

### Changed
- **`declare(strict_types=1)`** added to all PHP files across the codebase
- **ApiException handler** registered in `bootstrap/app.php` for consistent error responses
- **RequestIdMiddleware** appended to global middleware stack

## [1.0.0] - Initial Release

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
