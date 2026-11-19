# LaraApiStarter

Starter API Laravel  : Sanctum, Actions, DTO typés, Resources, traductions en/fr et modules optionnels. [English](README.md).

```sh
composer create-project jump/lara-api-starter mon-api
cd mon-api
# Le hook Composer lance l’installation interactive.
# Depuis un clone :
composer install
cp .env.example .env
php artisan api:install
```

Installation automatisée :

```sh
php artisan api:install --preset=minimal --database=sqlite --migrate --no-interaction
php artisan api:install --preset=standard --modules=webhooks --no-interaction
php artisan serve
```

Santé : `/api/v1/health` (sonde liveness : `/api/v1/health/live`). Les routes sont accessibles à la fois sous `/api/v1/...` et directement sous `/v1/...` (adapté aux sous-domaines via `API_PREFIX`). Interface Scalar : `/docs/api`. Catalogue d'erreurs RFC 7807 : `/docs/errors`. Spécification Scramble : `/docs/api.json`, exportable avec `php artisan scramble:export`. Hors environnement local, les docs sont fermées tant qu’une `DOCS_ACCESS_KEY` non vide n’est pas configurée et présentée dans le cookie `docs_access_key`. Le gate peut être adapté à votre authentification.

Commandes développeur rapides (`make help`) :
```sh
make setup        # Installation interactive complète
make up           # Démarrer PostgreSQL, Redis, Mailpit et l'App via Docker Compose
make test         # Analyse statique et tests (Pint, PHPStan, Pest)
make seed         # Réinitialisation de la base et seed (admin par défaut inclus)
make modules      # Lister les modules disponibles
```

| Preset | Modules |
| --- | --- |
| minimal | Noyau seul |
| standard | rbac, two-factor, api-keys, preferences, notifications |
| full | Tous les modules |

Modules : [preferences](docs/modules/preferences.md), [notifications](docs/modules/notifications.md), [api-keys](docs/modules/api-keys.md), [media](docs/modules/media.md), [export](docs/modules/export.md), [import](docs/modules/import.md), [webhooks](docs/modules/webhooks.md), [two-factor](docs/modules/two-factor.md), [social-login](docs/modules/social-login.md), [rbac](docs/modules/rbac.md), [impersonation](docs/modules/impersonation.md), [security-audit](docs/modules/security-audit.md).

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

Générateurs complémentaires : `make:api-action`, `make:api-dto`, `make:api-request`, `make:api-resource`, `make:api-controller`, `make:api-stack`.

Les dépendances sont ajoutées automatiquement. Changer de preset ne retire aucun module installé. Le retrait refuse les fichiers modifiés et conserve les données en base et les packages Composer. Retirez d’abord les modules dépendants. Consultez [UPGRADING.md](UPGRADING.md).

Les consignes IA du noyau se trouvent dans `.ai/`. Celles des modules suivent leur installation et leur retrait. `php artisan boost:install` permet de choisir les agents ; Boost génère leurs fichiers. Les modifications de modules lancent `boost:update` lorsque Boost est configuré.

Consultez [architecture](docs/architecture.md), [décisions de dépendances](docs/dependencies.md) et le [changelog](CHANGELOG.md).

## Docker

Image de conteneur prête pour la production basée sur `jkaninda/nginx-php-fpm:8.4`, conçue comme socle de déploiement pour cette API Laravel.

- Image Docker Hub : [judempoyo/lara-api-starter](https://hub.docker.com/r/judempoyo/lara-api-starter)

```sh
docker pull judempoyo/lara-api-starter:latest
docker run -d -p 8080:80 --env-file .env judempoyo/lara-api-starter:latest
```

### Tags de version

- `latest` : Dernière version stable publiée depuis un tag Git (`v*`).
- `<version>` (ex. `1.2.0`) : Version immuable publiée via tag Git ou workflow manuel.
- `sha-<hash>` : Snapshot associé au SHA d'un commit spécifique.

