.PHONY: up down migrate sh test

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

# composer-assets - install composer dependencies inside the PHP app container
composer-install:
	$(DC) exec app composer install

# install-assets - install assets inside the PHP app container
install-assets:
	$(DC) exec app php bin/console importmap:install
	$(DC) exec app php bin/console asset-map:compile

# test - run phpunit test suite
test:
	./vendor/bin/phpunit

# setup
setup:
	make up
	make composer-install
	make migrate
	make install-assets
