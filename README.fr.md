# 🚀 LaraApiStarter - Architecture API Laravel 12 Professionnelle

[![Laravel 12+](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**LaraApiStarter** est une base solide et prête pour la production pour construire des APIs REST scalables et sécurisées avec Laravel 12. Ce projet s'éloigne des contrôleurs encombrés en implémentant une architecture propre basée sur les **Actions & DTOs**, axée sur la sécurité, la performance et l'expérience développeur.

[English 🇺🇸](./README.md) | [Documentation API](./API_DOCUMENTATION.md)

---

## 🔥 Fonctionnalités Clés

- **🏗️ Architecture Propre** : Utilisation d'**Actions** pour la logique métier et de **DTOs** (Data Transfer Objects) pour la manipulation de données typées.
- **🔐 Authentification Sécurisée** : Propulsé par **Laravel Sanctum**. Inclus :
    - Login / Inscription / Déconnexion (Simple ou Multi-appareils).
    - Logique de **Refresh Token** avec métadonnées d'expiration.
    - Vérification d'email et réinitialisation de mot de passe **asynchrones** (Files d'attente/Queues) pour des réponses ultra-rapides.
- **🛡️ Sécurité Maximale** :
    - **Headers de Sécurité** personnalisés (CSP, XSS, Frame-options, etc.).
    - **Rate Limiting** robuste (configuré pour l'Auth et l'API générale).
    - Validation des mots de passe renforcée.
- **📑 Journalisation d'Activité** : Migration automatique des **Audit Logs** pour suivre toutes les actions sensibles (mises à jour de profil, changements de mot de passe, connexions).
- **🚀 Optimisation des Performances** :
    - Notifications asynchrones (Jobs).
    - Index de base de données pour les logs d'audit et les requêtes courantes.
    - Nettoyage automatique des tokens Sanctum.
- **💎 Standardisation des Réponses** : Structure JSON cohérente utilisant une classe `ApiResponse` dédiée et des enums `ErrorCode`.

---

## 🛠️ Stack Technique

- **Framework** : Laravel 12
- **Authentification** : Laravel Sanctum
- **Architecture** : Pattern Action-DTO
- **Logs** : Service d'Audit Natif en BD
- **Optimisation** : Laravel Boost

---

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/votreusername/lara-api-starter.git
   cd lara-api-starter
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Lancer les migrations**
   ```bash
   php artisan migrate
   ```

5. **Démarrer le serveur**
   ```bash
   php artisan serve
   ```

---

## 📁 Structure du Projet

```text
app/
├── Actions/        # Logique métier (Actions atomiques)
├── DTOs/           # Objets de transfert de données typés
├── Enums/          # Codes d'erreurs et constantes
├── Http/
│   ├── Requests/   # Form Requests (Validation)
│   ├── Responses/  # Gestionnaire de réponses ApiResponse standardisé
│   └── Resources/  # Ressources Eloquent (Sérialisation JSON)
├── Traits/         # LogsActivity et autres traits réutilisables
└── Notifications/  # Emails et alertes asynchrones
```

---

## 🧪 Tests

Lancez les tests pour vérifier que tout fonctionne correctement :
```bash
php artisan test
```

---

## 📄 Licence

Licence MIT. Voir le fichier [LICENSE.md](LICENSE.md) pour plus d'informations.
