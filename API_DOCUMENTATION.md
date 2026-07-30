# API Documentation

Complete reference for the **LaraApiStarter** REST API.

All routes are prefixed with `/api/v1`. Authentication uses a `Bearer` token in the `Authorization` header unless noted otherwise.

> Interactive documentation (Swagger UI) is available at `/docs/api` when the application is running.

---

## Response Format

### Success

```json
{
    "code": 200,
    "success": true,
    "message": "Operation successful.",
    "data": { ... },
    "error": null
}
```

### Paginated Success

```json
{
    "code": 200,
    "success": true,
    "data": [ ... ],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total_items": 42,
        "total_pages": 3,
        "has_more_pages": true,
        "count": 15,
        "is_empty": false
    }
}
```

### Error

```json
{
    "code": 422,
    "success": false,
    "error": {
        "code": "VALIDATION_FAILED",
        "message": "Validation failed"
    },
    "message": "The email field is required.",
    "errors": {
        "email": "The email field is required."
    },
    "data": null
}
```

---

## Authentication

### Register

`POST /auth/register`

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | required, max:255 |
| `email` | string | required, email, unique |
| `password` | string | required, min:8, confirmed |

**Response 201:**
```json
{
    "code": 201,
    "success": true,
    "message": "User registered successfully. Please verify your email.",
    "data": {
        "user": {
            "id": 1,
            "name": "Jane Doe",
            "email": "jane@example.com",
            "roles": ["user"],
            "two_factor_enabled": false,
            "is_email_verified": false,
            "created_at": "2026-07-24T00:00:00+00:00"
        },
        "token": "1|AbcDef...",
        "token_type": "Bearer",
        "expires_at": null
    }
}
```

---

### Login

`POST /auth/login`

| Field | Type | Rules |
|-------|------|-------|
| `email` | string | required, email |
| `password` | string | required |

**Response 200:**
```json
{
    "code": 200,
    "success": true,
    "message": "Login successful.",
    "data": {
        "user": { ... },
        "token": "2|XyzAbc...",
        "token_type": "Bearer",
        "expires_at": null
    }
}
```

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 401 | `INVALID_CREDENTIALS` | Wrong email or password |
| 422 | `ACCOUNT_DISABLED` | Account has been disabled |

---

### Logout

`POST /auth/logout` — *Auth required*

Revokes the current token.

**Response 204** (No Content)

---

### Logout All Devices

`POST /auth/logout-all` — *Auth required*

Revokes all tokens for the user.

**Response 204** (No Content)

---

### Refresh Token

`POST /auth/refresh` — *Auth required*

Revokes the current token and issues a new one.

**Response 200:**
```json
{
    "data": {
        "token": "3|NewToken...",
        "token_type": "Bearer",
        "expires_at": null
    }
}
```

---

### Sessions

`GET /auth/sessions` — *Auth required*

Returns all active tokens (sessions) for the user.

**Response 200:**
```json
{
    "data": [
        { "id": 1, "name": "iPhone", "is_current": true, "created_at": "..." },
        { "id": 2, "name": "Chrome", "is_current": false, "created_at": "..." }
    ]
}
```

`DELETE /auth/sessions/{tokenId}` — Revoke a specific session.

`DELETE /auth/sessions/others` — Revoke all sessions except the current one.

---

### Password Reset

`POST /auth/password/email` — Send reset link

| Field | Type |
|-------|------|
| `email` | string, required |

`POST /auth/password/reset` — Apply reset

| Field | Type |
|-------|------|
| `token` | string, required |
| `email` | string, required |
| `password` | string, required, confirmed |

---

### Email Verification

`GET /auth/email/verify/{id}/{hash}` — Verify email via link from notification.

`POST /auth/email/verification-notification` — *Auth required* — Resend verification email.

---

## Profile

`GET /auth/user` — *Auth required* — Get the authenticated user.

`PATCH /auth/profile` — *Auth required* — Update name and/or email.

| Field | Type |
|-------|------|
| `name` | string, optional |

`PATCH /auth/profile/email` — *Auth required* — Change email (triggers re-verification).

| Field | Type |
|-------|------|
| `email` | string, required, unique |
| `password` | string, required (current password confirmation) |

`PUT /auth/profile/password` — *Auth required*

| Field | Type |
|-------|------|
| `current_password` | string, required |
| `password` | string, required, confirmed |

`POST /auth/profile/avatar` — *Auth required* — Upload avatar image (multipart/form-data).

`DELETE /auth/profile/avatar` — *Auth required* — Delete current avatar.

---

## Two-Factor Authentication (2FA)

All 2FA routes require authentication.

### Enable 2FA

`POST /auth/two-factor/enable`

Returns a TOTP secret and a QR code URI to scan with an authenticator app.

**Response 200:**
```json
{
    "data": {
        "secret": "JBSWY3DPEHPK3PXP...",
        "qr_code_uri": "otpauth://totp/AppName:user@example.com?secret=...&issuer=AppName"
    }
}
```

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 409 | `TWO_FACTOR_ALREADY_ENABLED` | 2FA already active |

### Confirm 2FA

`POST /auth/two-factor/confirm`

Submit the first code from the authenticator app to activate 2FA.

| Field | Type |
|-------|------|
| `code` | string, required, 6 digits |

**Response 200** on success.

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 422 | `TWO_FACTOR_INVALID_CODE` | Wrong or expired code |

### Verify 2FA

`POST /auth/two-factor/verify`

Verify a TOTP code during the login flow.

| Field | Type |
|-------|------|
| `code` | string, required, 6 digits |

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 422 | `TWO_FACTOR_NOT_ENABLED` | 2FA not set up |
| 422 | `TWO_FACTOR_NOT_CONFIRMED` | 2FA enabled but not yet confirmed |
| 422 | `TWO_FACTOR_INVALID_CODE` | Wrong code |

### Disable 2FA

`DELETE /auth/two-factor`

| Field | Type |
|-------|------|
| `password` | string, required (current password) |

**Response 204** on success.

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 422 | `TWO_FACTOR_NOT_ENABLED` | 2FA not active |
| 422 | `PASSWORD_MISMATCH` | Wrong password |

---

## API Keys (Machine-to-Machine)

API keys allow non-interactive clients to authenticate using the `X-API-Key` header instead of a Bearer token.

### List API Keys

`GET /user/api-keys` — *Auth required*

Returns all keys for the user (secrets are never returned after creation).

### Create an API Key

`POST /user/api-keys` — *Auth required*

| Field | Type | Description |
|-------|------|-------------|
| `name` | string, required | Descriptive name |
| `abilities` | array, optional | Default: `["*"]` |
| `expires_in_days` | integer, optional | 1–365 |

**Response 201:**
```json
{
    "data": {
        "id": 1,
        "name": "CI Pipeline",
        "key": "aB3x...64chars",
        "abilities": ["*"],
        "expires_at": null
    },
    "message": "API key created. Save the key now — it will not be shown again."
}
```

> The plain-text `key` is returned **only once**. Store it immediately.

### Revoke an API Key

`DELETE /user/api-keys/{id}` — *Auth required*

**Response 204** on success.

### Using an API Key

Pass the key in the `X-API-Key` header:

```http
GET /api/v1/auth/user
X-API-Key: aB3x...64chars
```

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 401 | `INVALID_CREDENTIALS` | Header missing |
| 401 | `API_KEY_INVALID` | Key not found |
| 401 | `API_KEY_EXPIRED` | Key has expired |

---

## User Preferences

Key-value store for arbitrary per-user settings. Values can be any JSON-serializable type.

### Get All Preferences

`GET /user/preferences` — *Auth required*

**Response 200:**
```json
{
    "data": {
        "theme": "dark",
        "language": "fr",
        "notifications": { "email": true, "sms": false }
    }
}
```

### Set a Preference

`PUT /user/preferences/{key}` — *Auth required*

| Field | Type |
|-------|------|
| `value` | any, required |

Creates or updates the preference at `{key}`.

### Delete a Preference

`DELETE /user/preferences/{key}` — *Auth required*

**Response 204** on success. Returns 404 if the key does not exist.

---

## Notifications

Manages database notifications stored via Laravel's `Notifiable` trait.

### List Notifications

`GET /user/notifications` — *Auth required* — Paginated, newest first.

### Mark as Read

`POST /user/notifications/{id}/read` — *Auth required*

**Response 204** on success.

### Mark All as Read

`POST /user/notifications/read-all` — *Auth required*

**Response 204** on success.

### Delete a Notification

`DELETE /user/notifications/{id}` — *Auth required*

**Response 204** on success. Returns 404 if not found.

---

## Admin

All admin routes require the `role:admin` middleware.

### Impersonation

`POST /admin/impersonate/{userId}` — Start impersonating a user.

Returns a new Bearer token scoped to the target user. Use this token for subsequent requests to act as that user.

**Response 200:**
```json
{
    "data": {
        "user": { "id": 5, "email": "target@example.com", ... },
        "token": "10|ImpersonationToken...",
        "token_type": "Bearer",
        "expires_at": null
    }
}
```

**Errors:**
| HTTP | `error.code` | Reason |
|------|-------------|--------|
| 403 | `FORBIDDEN` | Trying to impersonate another admin |
| 404 | `RESOURCE_NOT_FOUND` | Target user not found |
| 422 | `IMPERSONATION_SELF` | Cannot impersonate yourself |

`DELETE /admin/impersonate` — Stop the impersonation session (revokes the impersonation token).

**Response 204** on success.

---

## Health Check

`GET /health` — Public endpoint, no authentication required.

**Response 200 (healthy):**
```json
{
    "data": {
        "status": "healthy",
        "timestamp": "2026-07-24T04:00:00+00:00",
        "version": "v1",
        "environment": "production",
        "services": {
            "database": { "status": "up", "latency_ms": 1.23 },
            "cache": { "status": "up" },
            "queue": { "status": "up", "driver": "redis" },
            "storage": { "status": "up" }
        }
    }
}
```

**Response 503 (degraded):** Same structure with `"status": "degraded"` and a `Retry-After: 30` header.

---

## Webhooks

Register HTTP endpoints to receive real-time notifications when system events occur.
All webhook routes require authentication.

### List Available Events

`GET /user/webhooks/events`

**Response 200:**
```json
{
    "data": [
        { "value": "api_key.created", "label": "API Key Created" },
        { "value": "api_key.deleted", "label": "API Key Deleted" },
        { "value": "export.completed", "label": "Export Completed" },
        { "value": "export.failed",    "label": "Export Failed" }
    ]
}
```

### List Webhooks

`GET /user/webhooks`

### Register a Webhook

`POST /user/webhooks`

| Field | Type | Rules |
|-------|------|-------|
| `url` | string | required, valid URL, max:2048 |
| `events` | array | required, min 1 item, each must be a valid event value |
| `description` | string | optional, max:255 |

**Response 201:**
```json
{
    "data": {
        "id": 1,
        "url": "https://example.com/hooks",
        "events": ["api_key.created"],
        "is_active": true,
        "description": "CI hook"
    }
}
```

> The signing `secret` is never returned after creation. Store it when you create the webhook.

### Update a Webhook

`PATCH /user/webhooks/{id}`

| Field | Type |
|-------|------|
| `url` | string, optional |
| `events` | array, optional |
| `is_active` | boolean, optional |
| `description` | string, optional |

### Delete a Webhook

`DELETE /user/webhooks/{id}` — **Response 204**

### Delivery History

`GET /user/webhooks/{id}/deliveries` — Paginated, newest first.

### Redeliver a Failed Delivery

`POST /user/webhooks/{webhookId}/deliveries/{deliveryId}/redeliver` — **Response 202**

### Payload Signature

Every delivery includes an `X-Signature` header:
```
X-Signature: sha256=<hmac-sha256-hex-digest>
```

Verify it server-side:
```php
hash_equals(
    'sha256=' . hash_hmac('sha256', $rawBody, $secret),
    $request->header('X-Signature')
);
```

---

## Media

Generic file upload and management system. Supports `local`, `S3`, and `Cloudflare R2` disks.
Use the `collection` field to organize files by purpose.
All routes require authentication.

### List Media

`GET /user/media` — Paginated. Filter by collection: `?collection=avatars`

**Response 200:**
```json
{
    "data": [
        {
            "id": 1,
            "original_name": "photo.jpg",
            "mime_type": "image/jpeg",
            "size": 204800,
            "human_size": "200 KB",
            "collection": "avatars",
            "disk": "local",
            "is_image": true,
            "created_at": "2026-07-30T00:00:00+00:00"
        }
    ]
}
```

### Upload a File

`POST /user/media` — `multipart/form-data`

| Field | Type | Rules |
|-------|------|-------|
| `file` | file | required, max 100 MB |
| `collection` | string | optional, lowercase/numbers/underscores only |
| `disk` | string | optional, must be a configured filesystem disk |

**Response 201** with `MediaResource`.

Image uploads automatically generate a 300×300 thumbnail (`intervention/image-laravel` required).

**Common collections:** `avatars`, `product_images`, `store_images`, `documents`, `exports`

### Get Signed URL

`GET /user/media/{id}/url`

**Response 200:**
```json
{
    "data": {
        "url": "https://...",
        "thumbnail": "https://...",
        "expires_in": "60 minutes"
    }
}
```

### Delete a File

`DELETE /user/media/{id}` — **Response 204** — Also deletes the physical file and thumbnail from storage.

---

## Data Export

Asynchronous export system. Requests return `202 Accepted` immediately while the file is generated in the background.
All routes require authentication.

### List Available Resources

`GET /user/exports/resources`

**Response 200:**
```json
{
    "data": {
        "resources": [
            { "key": "user_preferences", "label": "User Preferences", "admin_only": false },
            { "key": "notifications",    "label": "Notifications",     "admin_only": false },
            { "key": "users",            "label": "Users",             "admin_only": true  },
            { "key": "api_keys",         "label": "API Keys",          "admin_only": true  }
        ],
        "formats": ["csv", "json", "xlsx"]
    }
}
```

### Trigger an Export

`POST /user/exports`

| Field | Type | Description |
|-------|------|-------------|
| `resource` | string | required, must match a key in `config/export.php` |
| `format` | string | required: `csv`, `json`, or `xlsx` |
| `filters` | object | optional — see filter options below |
| `filters.ids` | array\<int\> | Specific record IDs to include |
| `filters.id_from` | integer | Minimum ID (inclusive) |
| `filters.id_to` | integer | Maximum ID (inclusive) |
| `filters.date_from` | date | Lower bound on `created_at` |
| `filters.date_to` | date | Upper bound on `created_at` |
| `filters.status` | string | Filter by status field |
| `filters.role` | string | Filter by role name (admin exports) |
| `filters.user_id` | integer | Scope to a specific user (admin exports) |

**Response 202:**
```json
{
    "message": "Export queued. You will be notified when it is ready.",
    "data": {
        "id": 12,
        "resource": "users",
        "format": "csv",
        "status": "pending",
        "filters": { "role": "admin" },
        "download_url": null
    }
}
```

> Admin-only resources (`users`, `api_keys`) require the `admin` role. Regular users will receive `403 Forbidden`.

### Check Export Status

`GET /user/exports/{id}`

Returns the export status. When `status` is `completed`, `download_url` contains a signed, time-limited URL.

```json
{
    "data": {
        "id": 12,
        "status": "completed",
        "download_url": "https://storage.example.com/exports/users_20260730_142512.csv?X-Amz-Expires=3600",
        "created_at": "2026-07-30T14:25:00+00:00"
    }
}
```

| Status | Meaning |
|--------|---------|
| `pending` | Job has been dispatched, not yet started |
| `processing` | File is being generated |
| `completed` | File ready — `download_url` is populated |
| `failed` | Generation failed — `error_message` is set |

### List Exports

`GET /user/exports` — Paginated, newest first.

### Adding a Custom Exportable Resource

1. Create a class implementing `App\Contracts\ExportableInterface`
2. Use the `AppliesExportFilters` trait for generic filter support
3. Register it in `config/export.php` under `resources`

```php
// config/export.php
'resources' => [
    'my_orders' => \App\Exports\OrdersExport::class,
],
```

---

## Data Import

Asynchronous import system. Triggered imports return `202 Accepted` immediately, uploading the file and processing it in the background.

### List Available Resources

`GET /user/imports/resources`

**Response 200:**
```json
{
    "data": {
        "resources": [
            { "key": "user_preferences", "label": "User Preferences", "admin_only": false },
            { "key": "users",            "label": "Users",             "admin_only": true  }
        ]
    }
}
```

### Trigger an Import

`POST /user/imports` — `multipart/form-data`

| Field | Type | Description |
|-------|------|-------------|
| `file` | file | required, max 100 MB, CSV or JSON format |
| `resource` | string | required, must match a key in `config/import.php` |
| `dry_run` | boolean | optional, default `false`. If `true`, only validates data without writing to database |

**Response 202:**
```json
{
    "message": "Import queued. You will be notified when it is ready.",
    "data": {
        "id": 5,
        "resource": "user_preferences",
        "status": "pending",
        "dry_run": false,
        "total_rows": 0,
        "processed_rows": 0,
        "successful_rows": 0,
        "failed_rows": 0,
        "errors": null,
        "error_message": null,
        "media": {
            "id": 10,
            "original_name": "my_preferences.csv",
            "mime_type": "text/csv",
            "size": 1024,
            "human_size": "1 KB",
            "collection": "imports"
        }
    }
}
```

### Check Import Status & Errors

`GET /user/imports/{id}`

Returns the import progress. When `status` is `completed`, row validation errors are detailed in the `errors` object.

```json
{
    "data": {
        "id": 5,
        "status": "completed",
        "dry_run": false,
        "total_rows": 10,
        "processed_rows": 10,
        "successful_rows": 8,
        "failed_rows": 2,
        "errors": [
            {
                "row": 3,
                "errors": {
                    "key": ["The key field is required."]
                }
            },
            {
                "row": 7,
                "errors": {
                    "value": ["The value field is required."]
                }
            }
        ],
        "error_message": null
    }
}
```

| Status | Meaning |
|--------|---------|
| `pending` | Job has been dispatched, not yet started |
| `processing` | File is being parsed and validated |
| `completed` | Import complete (errors array will detail any failed rows) |
| `failed` | System/structural file error occurred (e.g. invalid columns/file structure) |

### List Imports

`GET /user/imports` — Paginated list of import history, newest first.

### Adding a Custom Importable Resource

1. Create a class implementing `App\Contracts\ImportableInterface`
2. Register it in `config/import.php` under `resources`

```php
// config/import.php
'resources' => [
    'my_items' => \App\Imports\MyItemsImport::class,
],
```

---

## Error Codes Reference

| Code | HTTP | Description |
|------|------|-------------|
| `VALIDATION_FAILED` | 422 | Form validation error |
| `INVALID_CREDENTIALS` | 401 | Wrong email/password or missing token |
| `UNAUTHENTICATED` | 401 | No valid authentication |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `RESOURCE_NOT_FOUND` | 404 | Requested resource does not exist |
| `METHOD_NOT_ALLOWED` | 405 | HTTP verb not allowed on this route |
| `CONFLICT` | 409 | Resource already exists |
| `GONE` | 410 | Resource permanently removed |
| `UNPROCESSABLE` | 422 | Business logic error |
| `TOO_MANY_REQUESTS` | 429 | Rate limit exceeded |
| `SERVER_ERROR` | 500 | Unexpected server error |
| `SERVICE_UNAVAILABLE` | 503 | Service temporarily down |
| `TWO_FACTOR_ALREADY_ENABLED` | 409 | 2FA already active |
| `TWO_FACTOR_NOT_ENABLED` | 422 | 2FA not set up |
| `TWO_FACTOR_NOT_CONFIRMED` | 422 | 2FA pending confirmation |
| `TWO_FACTOR_INVALID_CODE` | 422 | Wrong TOTP code |
| `API_KEY_INVALID` | 401 | API key not found |
| `API_KEY_EXPIRED` | 401 | API key has expired |
| `IMPERSONATION_SELF` | 422 | Cannot impersonate yourself |
| `PASSWORD_MISMATCH` | 422 | Current password is incorrect |
| `ACCOUNT_DISABLED` | 422 | Account is deactivated |
| `EMAIL_ALREADY_VERIFIED` | 422 | Email already confirmed |
