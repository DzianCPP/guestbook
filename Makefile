SHELL := /bin/bash

tests:
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	php bin/phpunit $@

.PHONY: tests

launch:
	docker compose unpause
	symfony server:start -d

.PHONY: launch

stop:
	docker compose pause
	symfony server:stop

.PHONY: stop

up:
	docker compose up -d

.PHONY: up