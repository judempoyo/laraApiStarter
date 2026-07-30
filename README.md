# LaraApiStarter — Professional Laravel 12 API Starter Kit

[![Laravel 12+](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**LaraApiStarter** is a production-ready starting point for building scalable and secure REST APIs with Laravel 12. It combines a clean **Action-DTO** architecture with flexible authentication (Sanctum or Passport), RBAC, 2FA, API keys, impersonation, and a rich set of developer tools — all wired and ready from day one.

[Français](./README.fr.md) | [API Documentation](./API_DOCUMENTATION.md)

---

## Key Features

- **Clean Architecture**: Actions for business logic, DTOs for typed data, Resources for serialization.
- **Flexible Authentication**: Choose between **Laravel Sanctum** or **Laravel Passport** via a single env variable (`AUTH_DRIVER`). The rest of the codebase is driver-agnostic through `TokenServiceInterface`.
- **Two-Factor Authentication (2FA)**: TOTP-based 2FA powered by `pragmarx/google2fa`. Works with Google Authenticator, Authy, and compatible apps.
- **API Key Authentication**: Machine-to-machine authentication via `X-API-Key` header with ability scoping and expiry.
- **Admin Impersonation**: Admins can take the identity of any non-admin user for support/debug purposes, with full audit logging.
- **RBAC**: Role-based access control via `spatie/laravel-permission` with role-separated route files.
- **Security First**: Security headers, suspicious request detection, request size limiting, rate limiting, and hardened password validation.
- **User Preferences API**: Key-value store for arbitrary per-user settings.
- **In-App Notifications API**: Read, mark as read, and delete database notifications via REST endpoints.
- **Outbound Webhooks**: Register HTTP listener endpoints for system events (`api_key.*`, `export.*`). Async delivery with HMAC-SHA256 signing, exponential backoff, delivery history, and manual redeliver.
- **File & Media Manager**: Generic upload system for `local`, `S3`, and `Cloudflare R2` disks. Collection system (`avatars`, `product_images`, `store_images`, ...), automatic image thumbnails, and signed URL generation for private files.
- **Async Data Export**: Export any model to CSV or JSON in the background. Admin exports (users, API keys) and user-scoped exports. Dynamic filters: ID range, specific IDs, date range, role, status. Extensible via `ExportableInterface`.
- **Async Data Import**: Bulk import data from CSV or JSON files. Supports dry-runs (validation preview), row-by-row structural validation, detailed progress tracking, partial success logging, user notifications, and webhooks. Extensible via `ImportableInterface`.
- **Internationalization (i18n)**: `Accept-Language` header support (with quality-value parsing). All API responses — including exceptions — are translated. Ships with English and French. Adding new locales requires only a new lang file.
- **Scaffold Generator**: `php artisan make:api-scaffold Product` generates the full stack in one command.
- **Response Standardization**: Consistent JSON via `ApiResponse` and `ErrorCode` enums.
- **Interactive Installer**: `php artisan api:install` guides through driver selection, migrations, and setup.

---

## Quick Start

### Install via Composer

```bash
composer create-project judempoyo/lara-api-starter my-api
cd my-api
php artisan api:install
```

The `api:install` command will ask which auth driver to use, generate your app key, run migrations, and provide next steps.

### Manual Installation

```bash
git clone https://github.com/judempoyo/lara-api-starter.git my-api
cd my-api
composer install
cp .env.example .env
php artisan api:install
```

---

## Authentication Driver

Set `AUTH_DRIVER` in your `.env` to choose your driver:

```env
# Default — no extra steps required
AUTH_DRIVER=sanctum
AUTH_GUARD=sanctum

# Or to use Passport:
AUTH_DRIVER=passport
AUTH_GUARD=api
```

### Switching to Passport

1. Install the package: `composer require laravel/passport`
2. In `app/Models/User.php`, replace:
   ```php
   use Laravel\Sanctum\HasApiTokens;
   ```
   with:
   ```php
   use Laravel\Passport\HasApiTokens;
   ```
3. Run: `php artisan passport:install`
4. In `config/auth.php`, set the `api` guard driver to `passport`.

All actions, controllers, and middleware use `TokenServiceInterface` — no other changes needed.

---

## Project Structure

```
app/
├── Actions/                   # Business logic — one class per operation
│   ├── Auth/                  # Login, Register, 2FA, Password...
│   │   └── TwoFactor/         # Enable, Confirm, Verify, Disable
│   ├── Admin/                 # Impersonation
│   ├── Webhook/               # Create, Update, Delete webhook
│   ├── Media/                 # Upload, Delete
│   ├── Export/                # CreateExport
│   └── Security/              # Security event logging
├── Console/Commands/
│   ├── InstallApiCommand.php  # php artisan api:install
│   └── MakeApiScaffoldCommand.php # php artisan make:api-scaffold
├── Contracts/
│   ├── Auth/TokenServiceInterface.php  # Auth driver abstraction
│   └── ExportableInterface.php         # Export model contract
├── DTOs/                      # Typed data transfer objects
├── Enums/                     # ErrorCode, SecurityEvent, WebhookEvent, ExportFormat, ...
├── Exceptions/
│   └── ApiException.php       # Semantic exception factory — fully multilingual
├── Exports/                   # ExportableInterface implementations
│   ├── Concerns/AppliesExportFilters.php
│   ├── UsersExport.php        # Admin-only
│   ├── ApiKeysExport.php      # Admin-only
│   ├── UserPreferenceExport.php
│   └── NotificationExport.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── Auth/              # Auth, Profile, Session, 2FA, Socialite
│   │   ├── Admin/             # Impersonation
│   │   ├── User/              # Preferences, Notifications
│   │   └── V1/                # ApiKey, Webhook, Media, Export
│   ├── Middleware/            # OptionalAuth, Security, SetLocale, RequestSizeLimit...
│   ├── Requests/              # FormRequests per feature (no inline validation)
│   ├── Resources/             # Eloquent JSON Resources
│   └── Responses/ApiResponse.php
├── Jobs/
│   ├── WebhookDeliveryJob.php # HMAC-signed delivery + backoff
│   └── ProcessExportJob.php   # Async CSV/JSON export
├── Models/
│   ├── User.php               # + apiKeys/preferences/webhooks/media/exports
│   ├── ApiKey.php
│   ├── UserPreference.php
│   ├── Webhook.php
│   ├── WebhookDelivery.php
│   ├── Media.php
│   └── Export.php
├── Services/
│   ├── Auth/SanctumTokenService.php
│   ├── Auth/PassportTokenService.php
│   ├── WebhookDispatcher.php  # Event → subscribers → async jobs
│   └── MediaService.php       # Upload/thumbnail/signed-URL/delete
config/
├── api.php                    # Version, auth, rate limits, security
└── export.php                 # Exportable resource registry
lang/
├── en/api.php                 # Complete English translations
└── fr/api.php                 # Complete French translations
routes/
├── api.php                    # Auth, Profile, 2FA, public routes
└── api/
    ├── admin.php              # role:admin routes
    └── user.php               # Auth user: Preferences, Notifications, API Keys, Webhooks, Media, Exports
```

---

## Routes

All routes are prefixed with `/api/v1/`.

### Auth (public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register |
| POST | `/auth/login` | Login |
| POST | `/auth/password/email` | Send password reset link |
| POST | `/auth/password/reset` | Reset password |
| POST | `/auth/check-email` | Check if email exists |
| GET | `/auth/email/verify/{id}/{hash}` | Verify email |
| GET | `/auth/google/redirect` | Google OAuth redirect |
| GET | `/auth/google/callback` | Google OAuth callback |

### Auth (authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/logout` | Logout current session |
| POST | `/auth/logout-all` | Logout all devices |
| POST | `/auth/refresh` | Refresh token |
| GET | `/auth/user` | Get current user |
| GET | `/auth/sessions` | List sessions |
| DELETE | `/auth/sessions/{id}` | Revoke session |
| PATCH | `/auth/profile` | Update profile |
| PUT | `/auth/profile/password` | Update password |

### Two-Factor Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/two-factor/enable` | Generate secret and QR code URI |
| POST | `/auth/two-factor/confirm` | Activate 2FA with first code |
| POST | `/auth/two-factor/verify` | Verify code (login flow) |
| DELETE | `/auth/two-factor` | Disable 2FA |

### User Routes (`/user/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/user/preferences` | List all preferences |
| PUT | `/user/preferences/{key}` | Set a preference |
| DELETE | `/user/preferences/{key}` | Delete a preference |
| GET | `/user/notifications` | List notifications |
| POST | `/user/notifications/{id}/read` | Mark as read |
| POST | `/user/notifications/read-all` | Mark all as read |
| DELETE | `/user/notifications/{id}` | Delete notification |
| GET | `/user/api-keys` | List API keys |
| POST | `/user/api-keys` | Create API key |
| DELETE | `/user/api-keys/{id}` | Revoke API key |
| GET | `/user/webhooks/events` | List available events |
| GET | `/user/webhooks` | List registered webhooks |
| POST | `/user/webhooks` | Register a webhook |
| PATCH | `/user/webhooks/{id}` | Update a webhook |
| DELETE | `/user/webhooks/{id}` | Delete a webhook |
| GET | `/user/webhooks/{id}/deliveries` | Delivery history |
| POST | `/user/webhooks/{id}/deliveries/{dId}/redeliver` | Retry a delivery |
| GET | `/user/media` | List media files |
| POST | `/user/media` | Upload a file |
| GET | `/user/media/{id}/url` | Get signed download URL |
| DELETE | `/user/media/{id}` | Delete a file |
| GET | `/user/exports/resources` | List exportable resources |
| GET | `/user/exports` | List exports |
| POST | `/user/exports` | Trigger an export (202 Accepted) |
| GET | `/user/exports/{id}` | Check export status / get download URL |
| GET | `/user/imports/resources` | List importable resources |
| GET | `/user/imports` | List imports |
| POST | `/user/imports` | Trigger an import (202 Accepted) |
| GET | `/user/imports/{id}` | Check import status, progress, and errors |

### Admin Routes (`/admin/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/stats` | Example admin route |
| POST | `/admin/impersonate/{userId}` | Start impersonating a user |
| DELETE | `/admin/impersonate` | Stop impersonation |

---

## Security

- **`SecurityHeadersMiddleware`**: CSP, HSTS, X-Frame-Options, etc. (configurable in `config/api.php`)
- **`SuspiciousRequestMiddleware`**: Blocks SQLi, path traversal, and XSS patterns
- **`RequestSizeLimitMiddleware`**: Rejects requests over the configured size (default 10MB)
- **Rate Limiting** (all configurable in `.env`):
  - `RATE_LIMIT_API=60` — general API
  - `RATE_LIMIT_AUTH=10` — auth endpoints
  - `RATE_LIMIT_LOGIN=5` — login (by email+IP)
  - `RATE_LIMIT_REGISTER=10` — registration (by IP, per hour)
  - `RATE_LIMIT_PASSWORD_RESET=3` — password reset (by email, per hour)

---

## Scaffold Generator

Generate a complete API resource scaffold in one command:

```bash
php artisan make:api-scaffold Product
```

Generates:
- `app/Models/Product.php`
- `app/Http/Controllers/Api/v1/ProductController.php`
- `app/Actions/Product/CreateProductAction.php`
- `app/Actions/Product/UpdateProductAction.php`
- `app/Actions/Product/DeleteProductAction.php`
- `app/DTOs/Product/CreateProductDTO.php`
- `app/DTOs/Product/UpdateProductDTO.php`
- `app/Http/Requests/Product/StoreProductRequest.php`
- `app/Http/Requests/Product/UpdateProductRequest.php`
- `app/Http/Resources/ProductResource.php`
- A migration (optional via `--no-migration`)
- A ready-to-copy route hint

---

## Roles and Permissions

Add role-separated routes to `routes/api/admin.php` (admin) or `routes/api/user.php` (any auth user).

```php
// routes/api/admin.php
Route::apiResource('users', UserController::class);

// routes/api/user.php
Route::apiResource('orders', OrderController::class);
```

The route files already include the correct middleware group (`auth:sanctum` / `auth:api` based on your driver, `throttle:api`, and `role:admin` for admin).

---

## Developer Tools

| Tool | Command |
|------|---------|
| Interactive setup | `php artisan api:install` |
| Scaffold generator | `php artisan make:api-scaffold ModelName` |
| Code formatting | `composer format` |
| Static analysis | `composer analyze` |
| Tests | `composer test` |
| All checks | `composer check-all` |
| API docs | Visit `/docs/api` |

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
