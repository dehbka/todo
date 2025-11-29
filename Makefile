.PHONY: up down migrate

# Detect docker-compose (v1) or docker compose (v2)
DC := $(shell command -v docker-compose >/dev/null 2>&1 && echo docker-compose || echo docker compose)

# up - docker-compose up -d
up:
	$(DC) up -d

# down - docker-compose down
down:
	$(DC) down

# sh - docker-compose down
sh:
	$(DC) exec app sh

# migrate - run doctrine migrations inside the PHP app container
migrate:
	$(DC) exec app php bin/console doctrine:migrations:migrate -n
