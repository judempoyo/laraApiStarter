# Modular architecture

The repository ships the minimal core. `stubs/modules/<name>/module.json` owns a module's files, migrations, packages, environment keys, provider, User trait and AI instructions. `.api-starter.json` records exact installed paths and SHA-256 checksums.

The core loads `routes/api/modules/*.php` within its versioned API group. Each module registers its guards, middleware and listeners in its own provider. User traits merge fillable, hidden and cast attributes through Eloquent initializers. Module removal strips only its tracked trait and provider entries.

Core authentication publishes `UserRegistered`, `UserAuthenticated`, `LoginChallenged`, `UserLoggedIn`, `ActivityRecorded` and `SecurityEventOccurred`. RBAC enriches registration, two-factor can respond to the challenge, and security-audit persists audit records. API keys and import/export own their integration events; webhooks subscribes to IntegrationEvent instances without requiring emitters to be installed.

Export/import require media. Optional resource adapters are available only when their feature exists. Admin-only operations use the `api.admin` gate and remain denied without an authorization provider. Their cross-module integration tests run in the full preset; standalone module tests do not assume optional integrations exist.

The file transaction resolves dependencies, rejects cycles and unsafe paths, copies files, patches anchors and registers providers. It journals original file contents and restores them on failure. A lock prevents concurrent file transactions. Files changed by the developer cannot be silently removed. Database migration execution is a separate explicit operation; schema rollback is never guessed from installation state.

Composer dependencies are installed before module files. Composer and Boost regeneration are external processes, distinct from the file transaction. A failure in regeneration is reported and the installed module remains inspectable with api:doctor. Do not treat module removal as a database rollback or automatic package cleanup.
