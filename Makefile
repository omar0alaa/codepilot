.PHONY: help setup start stop restart logs shell artisan test migrate fresh

help: ## Show this help message
	@echo "CodePilot AI - Available Commands"
	@echo "-----------------------------------"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

setup: ## Initial project setup (first time)
	@echo "Setting up CodePilot AI..."
	cp .env.example .env
	docker-compose build
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	@echo "Setup complete! Visit http://localhost"

start: ## Start all containers
	docker-compose up -d
	@echo "Containers started"

stop: ## Stop all containers
	docker-compose stop
	@echo "Containers stopped"

restart: ## Restart all containers
	docker-compose restart
	@echo "Containers restarted"

logs: ## View logs (follow)
	docker-compose logs -f

logs-app: ## View app logs
	docker-compose logs -f app

logs-horizon: ## View Horizon logs
	docker-compose logs -f horizon

shell: ## Access app container shell
	docker-compose exec app sh

artisan: ## Run artisan command (make artisan c="migrate")
	docker-compose exec app php artisan $(c)

test: ## Run tests
	docker-compose exec app php artisan test

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

fresh: ## Fresh migration with seed
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Run seeders
	docker-compose exec app php artisan db:seed

horizon: ## Start Horizon dashboard
	docker-compose exec app php artisan horizon

queue: ## Start queue worker
	docker-compose exec app php artisan queue:work

tinker: ## Run tinker
	docker-compose exec app php artisan tinker

npm: ## Run npm command (make npm c="install")
	docker-compose exec app npm $(c)

build: ## Build frontend assets
	docker-compose exec app npm run build

watch: ## Watch frontend assets
	docker-compose exec app npm run watch

clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimize for production
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

down: ## Take application down for maintenance
	docker-compose exec app php artisan down

up: ## Bring application back up
	docker-compose exec app php artisan up

ps: ## View container status
	docker-compose ps

clean: ## Remove all containers and volumes
	docker-compose down -v
	@echo "Cleaned up"

# Git shortcuts
git-push: ## Push to GitHub
	git push origin main

git-pull: ## Pull from GitHub
	git pull origin main

git-status: ## Check git status
	git status

# Docker cleanup
docker-clean: ## Remove unused Docker resources
	docker system prune -f
	docker volume prune -f
	@echo "Docker cleaned"
