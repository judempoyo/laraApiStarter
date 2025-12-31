# 📖 API Documentation

This document lists the main authentication and profile management endpoints of the **LaraApiStarter**.

All routes are prefixed by `/api/v1`.

## 🔐 Authentication

### Registration
`POST /auth/register`

| Field | Type | Description |
| :--- | :--- | :--- |
| `name` | string | Full name |
| `email` | string | Unique email address |
| `password` | string | Min 6 chars |

**Response (201):**
```json
{
    "code": 201,
    "success": true,
    "data": {
        "user": { "id": 1, "name": "...", "email": "..." },
        "token": "...",
        "token_type": "Bearer",
        "expires_at": "..."
    }
}
```

### Login
`POST /auth/login`

**Response (200):**
```json
{
    "code": 200,
    "success": true,
    "data": {
        "token": "...",
        "expires_at": "..."
    }
}
```

### Refresh Token
`POST /auth/refresh`
*Requires Authentication*

Revokes current token and issues a new one.

---

## 👤 Profile Management

### Update Profile
`PUT /auth/profile`
*Requires Authentication*

| Field | Type | Description |
| :--- | :--- | :--- |
| `name` | string | |
| `email` | string | |

### Update Password
`PUT /auth/profile/password`
*Requires Authentication*

| Field | Type | Description |
| :--- | :--- | :--- |
| `current_password` | string | |
| `password` | string | New password |

---

## 🔑 Password Reset

### Forgot Password
`POST /auth/password/email`

Sends a reset link to the email provided after validation.

### Reset Password
`POST /auth/password/reset`

| Field | Type | Description |
| :--- | :--- | :--- |
| `token` | string | From email |
| `email` | string | |
| `password` | string | |

---

## 📊 Error Response Format

In case of error (e.g., 422, 401, 429), the API returns:

```json
{
    "code": 429,
    "success": false,
    "error": {
        "code": "RATE_LIMIT_EXCEEDED",
        "message": "Too many requests."
    },
    "message": "You have exceeded your request limit. Please try again later.",
    "data": null
}
```
