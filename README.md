# LaraApiStarter

Laravel  API starter with Sanctum, Actions, typed DTOs, Resources, English/French translations and optional modules. [Français](README.fr.md).

```sh
composer create-project jump/lara-api-starter my-api
cd my-api
# The Composer hook starts the interactive installer.
# For a cloned checkout:
composer install
cp .env.example .env
php artisan api:install
```

For CI or unattended installation:

```sh
php artisan api:install --preset=minimal --database=sqlite --migrate --no-interaction
php artisan api:install --preset=standard --modules=webhooks --no-interaction
php artisan serve
```

Health: `/api/v1/health` (live probe: `/api/v1/health/live`). Routes are available under both `/api/v1/...` and direct `/v1/...` (subdomain-ready via `API_PREFIX`). Scalar: `/docs/api`. RFC 7807 Error Catalogue: `/docs/errors`. Scramble specification: `/docs/api.json`; export with `php artisan scramble:export`. Documentation is accessible locally; elsewhere it is denied unless a nonempty `DOCS_ACCESS_KEY` is configured and presented in the `docs_access_key` cookie. Replace the gate with your organization's authentication policy when appropriate.

Quick developer commands (`make help`):
```sh
make setup        # Interactive installation
make up           # Start PostgreSQL, Redis, Mailpit and App with Docker Compose
make test         # Run static analysis and tests (Pint, PHPStan, Pest)
make seed         # Fresh database reset and seeding (includes default admin)
make modules      # List available starter modules
```

| Preset | Modules |
| --- | --- |
| minimal | Core only |
| standard | rbac, two-factor, api-keys, preferences, notifications |
| full | All modules |

Available modules: [preferences](docs/modules/preferences.md), [notifications](docs/modules/notifications.md), [api-keys](docs/modules/api-keys.md), [media](docs/modules/media.md), [export](docs/modules/export.md), [import](docs/modules/import.md), [webhooks](docs/modules/webhooks.md), [two-factor](docs/modules/two-factor.md), [social-login](docs/modules/social-login.md), [rbac](docs/modules/rbac.md), [impersonation](docs/modules/impersonation.md), [security-audit](docs/modules/security-audit.md).

```sh
php artisan api:list
php artisan api:add webhooks
php artisan migrate
php artisan api:remove webhooks
php artisan api:doctor --database
php artisan make:api-scaffold Product
composer dev
composer check-all
```

Other generators: `make:api-action`, `make:api-dto`, `make:api-request`, `make:api-resource`, `make:api-controller`, `make:api-stack`.

The installer adds dependencies automatically and never removes existing modules when changing presets. Remove dependents before their dependencies. Removal refuses locally modified module files and retains database data and Composer packages. See [UPGRADING.md](UPGRADING.md).

Core AI guidelines and skills live in `.ai/`. Module instructions are installed and removed with the module. Run `php artisan boost:install` to select your AI agents; Boost generates their configuration. Subsequent module changes run `boost:update` when Boost has been configured.

See [architecture](docs/architecture.md), [dependency decisions](docs/dependencies.md), and [changelog](CHANGELOG.md).

## Docker

Production-ready container image based on `jkaninda/nginx-php-fpm:8.4`, designed as a deployment base for this Laravel API.

- Docker Hub image: [judempoyo/lara-api-starter](https://hub.docker.com/r/judempoyo/lara-api-starter)

```sh
docker pull judempoyo/lara-api-starter:latest
docker run -d -p 8080:80 --env-file .env judempoyo/lara-api-starter:latest
```

### Version tags

- `latest`: Latest stable release published from a Git release tag (`v*`).
- `<version>` (e.g. `1.2.0`): Immutable version published via Git tag or manual workflow dispatch.
- `sha-<hash>`: Snapshot tied to a specific commit SHA.

