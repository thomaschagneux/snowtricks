# Nom des commandes
PHP = php
COMPOSER = composer
CONSOLE = $(PHP) bin/console
SYMFONY_SERVER = symfony serve

# Commandes
.PHONY: help install start stop clean reset

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Installe les dépendances du projet
	$(COMPOSER) install
	$(CONSOLE) d:d:c --if-not-exists
	$(CONSOLE) d:m:m --no-interaction
	$(CONSOLE) d:f:l --no-interaction

reset:
	$(CONSOLE) d:d:d --force
	$(CONSOLE) d:d:c --if-not-exists
	$(CONSOLE) d:m:m --no-interaction
	$(CONSOLE) d:f:l --no-interaction

start: ## Démarre le serveur Symfony
	$(SYMFONY_SERVER) -d

stop: ## Stoppe le serveur Symfony
	$(SYMFONY_SERVER) stop

clean: ## Nettoie les fichiers de cache et logs
	$(CONSOLE) c:c
	$(CONSOLE) c:w

quality: clean
	vendor/bin/php-cs-fixer fix
	vendor/bin/phpstan analyse

add: quality
	git add .
