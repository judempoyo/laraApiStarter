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
| `VALIDATION_FAILED` | 422 | Input validation failed |
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
