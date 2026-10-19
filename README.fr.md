# LaraApiStarter

Starter API Laravel 12 : Sanctum, Actions, DTO typés, Resources, traductions en/fr et modules optionnels. [English](README.md).

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

Santé : `/api/v1/health`. Interface Scalar : `/docs/api`. Spécification Scramble : `/docs/api.json`, exportable avec `php artisan scramble:export`. Hors environnement local, les docs sont fermées tant qu’une `DOCS_ACCESS_KEY` non vide n’est pas configurée et présentée dans le cookie `docs_access_key`. Le gate peut être adapté à votre authentification.

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
