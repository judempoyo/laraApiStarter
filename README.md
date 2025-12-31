# 🚀 LaraApiStarter - Professional Laravel 12 API Architecture

[![Laravel 12+](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**LaraApiStarter** is a robust, production-ready starting point for building scalable and secure REST APIs with Laravel 12. It moves away from bloated controllers by implementing a clean **Action & DTO** architecture, focused on security, performance, and developer experience.

[Français 🇫🇷](./README.fr.md) | [Documentation API](./API_DOCUMENTATION.md)

---

## 🔥 Key Features

- **🏗️ Clean Architecture**: Uses **Actions** for business logic and **DTOs** (Data Transfer Objects) for typed data handling.
- **🔐 Secure Authentication**: Powered by **Laravel Sanctum**. Includes:
    - Login / Register / Logout (Single & Multi-device).
    - **Refresh Token** logic with expiration metadata.
    - **Queued** Email Verification & Password Reset (Ultra-fast responses).
- **🛡️ Security First**:
    - Custom **Security Headers** (CSP, XSS, Frame-options, etc.).
    - Robust **Rate Limiting** (configured for Auth and General API).
    - Hardened Password validation.
- **📑 Activity Logging**: Automated **Audit Logs** to track all security-sensitive actions (profile updates, password changes, logins).
- **🚀 Performance Optimized**:
    - Asynchronous notifications (Queued).
    - Database indices for audit logs and common queries.
    - Automated Sanctum token pruning.
- **💎 Response Standardization**: Consistent JSON structure using a dedicated `ApiResponse` class and `ErrorCode` Enums.

---

## 🛠️ Tech Stack

- **Framework**: Laravel 12
- **Auth**: Laravel Sanctum
- **Architecture**: Action-DTO Pattern
- **Logs**: Native Database Audit Service
- **Optimization**: Laravel Boost

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/lara-api-starter.git
   cd lara-api-starter
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Start the server**
   ```bash
   php artisan serve
   ```

---

## 📁 Project Structure

```text
app/
├── Actions/        # Business logic (Atomic actions)
├── DTOs/           # Typed data transfer objects
├── Enums/          # ErrorCodes and other Constants
├── Http/
│   ├── Requests/   # Form Requests (Validation)
│   ├── Responses/  # Standardized ApiResponse handler
│   └── Resources/  # Eloquent Resources (JSON serialization)
├── Traits/         # LogsActivity and other reusable traits
└── Notifications/  # Queued emails and alerts
```

---

## 🔒 Security Best Practices

This starter kit includes a `SecurityHeadersMiddleware` that automatically injects:
- `Content-Security-Policy`
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Strict-Transport-Security`

Rate limiting is applied in `AppServiceProvider`:
- **Auth**: 5 attempts / minute per IP.
- **Global API**: 60 requests / minute.

---

## 🧪 Testing

Run the feature tests to ensure everything is working:
```bash
php artisan test
```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
