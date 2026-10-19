# Upgrading to the modular starter

This is a breaking structural upgrade from 1.x. Back up your code and database, and rehearse it on a copy. Do not run `migrate:fresh` on an existing database.

## New projects

Run `api:install --preset=full` to install all former features, or choose minimal/standard. The repository itself is the minimal core. `api:install` is additive: selecting minimal on a full installation does not remove anything.

## Existing projects

1. Keep your application branch and its customizations. Start a separate checkout of this version, install full, and compare it with your current application. The installer deliberately refuses to overwrite existing files; it is not an in-place merge tool for 1.x.
2. Carry over your `.env` values and application key without replacing them with example defaults. Keep `API_VERSION=v1` and Sanctum authentication. Rotate any documentation key equal to the old public default. Docs are denied outside local with an empty key.
3. Carry over the generated `.api-starter.json`, module providers and User trait anchors, then reapply your customizations. Keep all existing migration-history rows. Core status/password_updated_at columns now belong to the initial users migration; existing databases must already contain them from their old migration.
4. The two 2FA migrations are consolidated into `2026_07_24_000001_add_two_factor_to_users_table.php`. If both old migrations ran, keep their history unchanged. If only the first ran, add the nullable `two_factor_recovery_codes` text column in an application migration before enabling 2FA; the already-recorded first migration will not rerun.
5. Avatar has its own media migration. On an existing database where `users.avatar` already exists, mark that migration as applied after verifying its schema, or adapt a project migration to skip the existing column. Never drop avatar data to satisfy a migration. Social columns retain google_id/provider/provider_id for compatibility.
6. Run pending migrations, then `api:doctor --database`, the full test suite and a route comparison in staging. Check workers, OAuth redirects, webhook endpoints, storage and mail configuration before release.

## Commands and routes

- `api:install`, `api:add`, `api:remove`, `api:list`, `api:doctor` manage installation. `make:api-*` generates code; `las:all` becomes `make:api-stack`. All `las:*` aliases are removed immediately. Update scripts and CI: `las:install` → `api:install`; `las:dto`, `las:action`, `las:request`, `las:resource`, `las:controller` → their corresponding `make:api-*` commands. There is no compatibility alias.
- Generated controllers move from `Api/v1` to `Api/V1`. Update old namespaces and file paths together on case-sensitive filesystems.
- Passport support is removed. Migrate any Passport deployment to Sanctum before this upgrade; this starter does not convert existing OAuth tokens.
- Removed routes: `GET /api/v1/test-api-key`, `GET /api/v1/admin/stats`, `POST /api/v1/auth/check-email`, and `POST /api/v1/auth/two-factor/reset-with-recovery-code`. Use the existing public password/recovery-code-validated `POST /api/v1/auth/two-factor/reset` flow for recovery reset.
- Scalar moves from `/doc` to `/docs/api`, replacing the alternate docs UI. The JSON specification remains `/docs/api.json`. The handwritten API_DOCUMENTATION.md and vendored Scalar JavaScript are removed.
- Suspicious keyword rejection is removed: legitimate text containing SQL-like words is accepted. Request size limits should be configured at the web server; the optional middleware remains available. Security headers belong to security-audit; documentation CSP belongs to the docs middleware.
- `.env.example` uses SQLite and array sessions, a neutral app name, debug false and no documentation secret. These defaults do not rewrite an existing `.env` unless an installer option explicitly selects a database.

## Module removal and customization

Removal deletes exactly tracked module files, its provider, User trait, environment block and AI sources. It rejects modules still required by another module and files changed locally. Back up modifications and restore the distributed version before removing; there is no force-delete flag.

Database data and migration history are intentionally retained. Reinstalling the same module restores its code without rerunning migrations already recorded. Use reviewed application migrations for permanent schema cleanup. Composer packages are retained after removal because they may be used by custom application code; inspect usage before manually removing them.

Module file transactions roll back copied files and patches if an operation fails. Composer package installation and Boost generation are external steps: investigate their reported failures and run api:doctor before continuing. An installed module is not automatically undone when subsequent cache or agent regeneration fails.

Use the module template in `stubs/modules/<name>` as the distributable source. `.api-starter.json` stores checksums to detect local edits; do not manually change hashes to hide drift. The Pint configuration preserves fully qualified trait anchors so formatting does not invalidate removal.

## Tooling and AI

PHP 8.2+ remains supported; Pest 3 replaces Pest 4 because Pest 4 requires PHP 8.3. Laravel must be at least 12.41.1 for Boost 2. Composer resolves dependencies against PHP 8.2.0. Larastan/PHPStan is configured at level 1; `composer check-all` checks formatting without modifying files, then analysis and tests.

Module-specific translation keys move to `modules/<module>.<key>` in matching en/fr files. Update custom callers of moved keys. Core and shared translations remain under `api.*`.

Run `boost:install` interactively to select AI agents; Boost generates AGENTS.md/CLAUDE.md and MCP configuration. The installer does not invent unsupported noninteractive agent options. Module changes call `boost:update` when `boost.json` exists. Core `.ai` sources do not contain instructions for absent modules.
