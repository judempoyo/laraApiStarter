# LaraApiStarter — Starter Kit API Laravel 12 Professionnel

[![Laravel 12+](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Licence: MIT](https://img.shields.io/badge/Licence-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**LaraApiStarter** est un point de départ production-ready pour construire des API REST évolutives et sécurisées avec Laravel 12. Il associe une architecture **Action-DTO** propre à une authentification flexible (Sanctum ou Passport), un RBAC, la 2FA, des clés API, l'impersonation et un riche ensemble d'outils pour développeurs — tout câblé et prêt dès le premier jour.

[English](./README.md) | [Documentation API](./API_DOCUMENTATION.md)

---

## Fonctionnalités principales

- **Architecture propre** : Actions pour la logique métier, DTOs pour les données typées, Resources pour la sérialisation.
- **Authentification flexible** : Choisissez entre **Laravel Sanctum** ou **Laravel Passport** via une seule variable d'environnement (`AUTH_DRIVER`). Le reste du code est driver-agnostique grâce à `TokenServiceInterface`.
- **Authentification à deux facteurs (2FA)** : 2FA TOTP via `pragmarx/google2fa`. Compatible Google Authenticator, Authy, et autres applications TOTP.
- **Clés API** : Authentification machine-to-machine via l'en-tête `X-API-Key` avec portée et expiration configurables.
- **Impersonation admin** : Les administrateurs peuvent prendre l'identité d'un utilisateur pour le support ou le débogage, avec journalisation d'audit complète.
- **RBAC** : Contrôle d'accès par rôle via `spatie/laravel-permission` avec fichiers de routes séparés par rôle.
- **Sécurité renforcée** : En-têtes de sécurité, détection de requêtes suspectes, limitation de la taille des requêtes, rate limiting, validation de mot de passe durcie.
- **API Préférences utilisateur** : Stockage clé-valeur JSON par utilisateur.
- **API Notifications in-app** : Lecture, marquage et suppression de notifications via des endpoints REST.
- **Générateur de scaffold** : `php artisan make:api-scaffold Product` génère la pile complète en une commande.
- **Réponses standardisées** : JSON cohérent via `ApiResponse` et enums `ErrorCode`.
- **Installateur interactif** : `php artisan api:install` guide la configuration du driver, des migrations et de la clé d'application.

---

## Démarrage rapide

### Installation via Composer

```bash
composer create-project judempoyo/lara-api-starter mon-api
cd mon-api
php artisan api:install
```

La commande `api:install` demandera quel driver d'authentification utiliser, génèrera la clé applicative, exécutera les migrations et fournira les prochaines étapes.

### Installation manuelle

```bash
git clone https://github.com/judempoyo/lara-api-starter.git mon-api
cd mon-api
composer install
cp .env.example .env
php artisan api:install
```

---

## Driver d'authentification

Définissez `AUTH_DRIVER` dans votre `.env` :

```env
# Par défaut — aucune étape supplémentaire requise
AUTH_DRIVER=sanctum
AUTH_GUARD=sanctum

# Ou pour utiliser Passport :
AUTH_DRIVER=passport
AUTH_GUARD=api
```

### Passer à Passport

1. Installez le package : `composer require laravel/passport`
2. Dans `app/Models/User.php`, remplacez :
   ```php
   use Laravel\Sanctum\HasApiTokens;
   ```
   par :
   ```php
   use Laravel\Passport\HasApiTokens;
   ```
3. Exécutez : `php artisan passport:install`
4. Dans `config/auth.php`, définissez le driver du guard `api` sur `passport`.

Toutes les actions, controllers et middlewares utilisent `TokenServiceInterface` — aucun autre changement n'est nécessaire.

---

## Structure du projet

```
app/
├── Actions/                    # Logique métier — une classe par opération
│   ├── Auth/                   # Login, Register, 2FA, Password...
│   │   └── TwoFactor/          # Enable, Confirm, Verify, Disable
│   ├── Admin/                  # Impersonation
│   └── Security/               # Journalisation des événements de sécurité
├── Console/Commands/
│   ├── InstallApiCommand.php   # php artisan api:install
│   └── MakeApiScaffoldCommand.php # php artisan make:api-scaffold
├── Contracts/Auth/
│   └── TokenServiceInterface.php  # Abstraction du driver d'auth
├── DTOs/                       # Objets de transfert de données typés
├── Enums/                      # ErrorCode, SecurityEvent, enums Result
├── Exceptions/
│   └── ApiException.php        # Exception sémantique (notFound, forbidden...)
├── Http/
│   ├── Controllers/Api/
│   │   ├── Auth/               # Auth, Profile, Session, 2FA, Socialite
│   │   ├── Admin/              # Impersonation
│   │   ├── User/               # Préférences, Notifications
│   │   └── ApiKeyController    # Clés API machine-to-machine
│   ├── Middleware/             # OptionalAuth, Sécurité, RequestSizeLimit...
│   ├── Requests/               # Form Requests héritant de ApiRequest
│   ├── Resources/              # Eloquent JSON Resources
│   └── Responses/
│       └── ApiResponse.php     # success/created/accepted/noContent/error/paginated
├── Models/
│   ├── User.php                # + relations apiKeys/preferences/securityLogs
│   ├── ApiKey.php
│   └── UserPreference.php
├── Services/Auth/
│   ├── SanctumTokenService.php
│   └── PassportTokenService.php
routes/
├── api.php                     # Auth, Profile, 2FA, routes publiques
└── api/
    ├── admin.php               # Routes role:admin (Impersonation, stats...)
    └── user.php                # Routes utilisateur authentifié (Préférences, Notifications, Clés API)
```

---

## Routes

Toutes les routes sont préfixées par `/api/v1/`.

### Auth (public)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/auth/register` | Inscription |
| POST | `/auth/login` | Connexion |
| POST | `/auth/password/email` | Envoi du lien de réinitialisation |
| POST | `/auth/password/reset` | Réinitialisation du mot de passe |
| POST | `/auth/check-email` | Vérifier si un email existe |
| GET | `/auth/email/verify/{id}/{hash}` | Vérifier l'email |

### Auth (authentifié)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/auth/logout` | Déconnexion de la session courante |
| POST | `/auth/logout-all` | Déconnexion de tous les appareils |
| POST | `/auth/refresh` | Rotation du token |
| GET | `/auth/user` | Utilisateur connecté |
| GET | `/auth/sessions` | Liste des sessions |

### Authentification à deux facteurs
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/auth/two-factor/enable` | Générer le secret et l'URI du QR code |
| POST | `/auth/two-factor/confirm` | Activer la 2FA avec le premier code |
| POST | `/auth/two-factor/verify` | Vérifier un code (flux de connexion) |
| DELETE | `/auth/two-factor` | Désactiver la 2FA |

### Routes utilisateur (`/user/*`)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/user/preferences` | Lister les préférences |
| PUT | `/user/preferences/{key}` | Définir une préférence |
| DELETE | `/user/preferences/{key}` | Supprimer une préférence |
| GET | `/user/notifications` | Lister les notifications |
| POST | `/user/notifications/{id}/read` | Marquer comme lu |
| POST | `/user/notifications/read-all` | Tout marquer comme lu |
| DELETE | `/user/notifications/{id}` | Supprimer une notification |
| GET | `/user/api-keys` | Lister les clés API |
| POST | `/user/api-keys` | Créer une clé API |
| DELETE | `/user/api-keys/{id}` | Révoquer une clé API |

### Routes admin (`/admin/*`)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/admin/impersonate/{userId}` | Démarrer l'impersonation |
| DELETE | `/admin/impersonate` | Arrêter l'impersonation |

---

## Sécurité

- **`SecurityHeadersMiddleware`** : CSP, HSTS, X-Frame-Options, etc. (configurable dans `config/api.php`)
- **`SuspiciousRequestMiddleware`** : Bloque les patterns SQLi, traversée de chemin, et XSS
- **`RequestSizeLimitMiddleware`** : Rejette les requêtes dépassant la taille configurée (défaut 10 Mo)
- **Rate limiting** (configurable dans `.env`) :
  - `RATE_LIMIT_API=60` — API générale
  - `RATE_LIMIT_AUTH=10` — endpoints d'authentification
  - `RATE_LIMIT_LOGIN=5` — connexion (par email+IP)
  - `RATE_LIMIT_REGISTER=10` — inscription (par IP, par heure)
  - `RATE_LIMIT_PASSWORD_RESET=3` — réinitialisation (par email, par heure)

---

## Générateur de scaffold

Générez une ressource API complète en une commande :

```bash
php artisan make:api-scaffold Product
```

Génère :
- `app/Models/Product.php`
- `app/Http/Controllers/Api/v1/ProductController.php`
- `app/Actions/Product/{Create,Update,Delete}ProductAction.php`
- `app/DTOs/Product/{Create,Update}ProductDTO.php`
- `app/Http/Requests/Product/{Store,Update}ProductRequest.php`
- `app/Http/Resources/ProductResource.php`
- Une migration (optionnelle via `--no-migration`)
- Un indice de route prêt à copier

---

## Outils développeur

| Outil | Commande |
|-------|---------|
| Installation interactive | `php artisan api:install` |
| Générateur de scaffold | `php artisan make:api-scaffold NomDuModele` |
| Formatage du code | `composer format` |
| Analyse statique | `composer analyze` |
| Tests | `composer test` |
| Tous les contrôles | `composer check-all` |
| Documentation API | Visitez `/docs/api` |

---

## Licence

Licence MIT. Voir [LICENSE.md](LICENSE.md) pour plus d'informations.
