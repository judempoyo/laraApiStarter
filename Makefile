.DEFAULT_GOAL := help

.PHONY: help up down restart stop ps logs shell \
        setup install dev test test-coverage format analyze check \
        migrate migrate-fresh seed cache-clear \
        modules module-add module-remove doctor scaffold

help: ## Show this help message
	@echo "\033[36mUsage:\033[0m make <command> [ARG=value]"
	@echo ""
	@echo "\033[36mCommands:\033[0m"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}'
	@echo ""

# ── Docker Compose ────────────────────────────────────────────────────────────

up: ## Start all Docker Compose services in background
	docker compose up -d

down: ## Stop and remove Docker Compose containers
	docker compose down

stop: ## Stop Docker Compose containers without removing them
	docker compose stop

restart: ## Restart all Docker Compose services
	docker compose restart

ps: ## List running Docker Compose services
	docker compose ps

logs: ## View live logs from Docker Compose services
	docker compose logs -f

shell: ## Open an interactive bash shell in the app container
	docker compose exec app bash

# ── Application Setup & Lifecycle ─────────────────────────────────────────────

setup: ## Interactive installation: dependencies, env, key, and presets
	composer setup
	php artisan api:install

install: ## Install Composer dependencies
	composer install

dev: ## Start local PHP development server
	composer dev

migrate: ## Run pending database migrations
	php artisan migrate

migrate-fresh: ## Wipe database and run fresh migrations
	php artisan migrate:fresh

seed: ## Run database seeders (including default admin)
	php artisan db:seed

cache-clear: ## Clear all application, route, config, and view caches
	php artisan optimize:clear

# ── Code Quality & Tests ──────────────────────────────────────────────────────

test: ## Run Pest test suite
	./vendor/bin/pest

test-coverage: ## Run Pest test suite with code coverage report
	./vendor/bin/pest --coverage

format: ## Fix code style issues with Laravel Pint
	./vendor/bin/pint

analyze: ## Run PHPStan / Larastan static analysis
	./vendor/bin/phpstan analyse

check: ## Run Pint (dry-run), PHPStan analysis, and Pest tests
	composer check-all

# ── LaraApiStarter Modules ────────────────────────────────────────────────────

modules: ## List all available, installed, and optional API modules
	php artisan api:list

module-add: ## Install a module (Usage: make module-add M=webhooks)
	@if [ -z "$(M)" ]; then echo "\033[31mError:\033[0m Module name required. Example: make module-add M=webhooks"; exit 1; fi
	php artisan api:add $(M)

module-remove: ## Remove an installed module (Usage: make module-remove M=webhooks)
	@if [ -z "$(M)" ]; then echo "\033[31mError:\033[0m Module name required. Example: make module-remove M=webhooks"; exit 1; fi
	php artisan api:remove $(M)

doctor: ## Diagnose starter configuration, modules, and database health
	php artisan api:doctor --database

scaffold: ## Scaffold an Action-DTO CRUD resource (Usage: make scaffold NAME=Product)
	@if [ -z "$(NAME)" ]; then echo "\033[31mError:\033[0m Entity name required. Example: make scaffold NAME=Product"; exit 1; fi
	php artisan make:api-scaffold $(NAME)

