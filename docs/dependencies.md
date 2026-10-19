# Dependency decisions

- **Larastan 3 / PHPStan 2**: requested to make `composer analyze` functional. Analysis starts at level 1 without a suppression baseline; raise it as types are improved.
- **Laravel Boost 2.1**: required for custom agent skills. The installed package supports PHP 8.2 and Laravel 12.41.1+. Its `boost:install` has guidelines, skills and MCP options, but no agent-selection CLI option. Agent selection therefore remains interactive. See https://laravel.com/docs/12.x/boost.
- Optional runtime dependencies belong to manifests: Spatie Permission (rbac), Google2FA (two-factor), Socialite (social-login). Sanctum, Scramble and Scalar remain core dependencies.
- No JavaScript build tool is required. The development runner uses PHP processes.
- Packagist metadata checked through `https://repo.packagist.org/p2/jump/lara-api-starter.json` (200). The equivalent judempoyo package returned 404. Keep the published Composer name `jump/lara-api-starter`; this does not imply that the GitHub repository was renamed.

- **Pest 3 / Laravel plugin 3** replace Pest 4 because the existing Pest 4 dependency requires PHP 8.3. Composer resolves against PHP 8.2.0 so the starter and its developer tooling match the declared PHP 8.2+ support.
