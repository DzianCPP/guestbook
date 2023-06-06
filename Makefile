SHELL := /bin/bash

tests:
	symfony console doctrine:database:drop --force --env=test || true
	symfony console doctrine:database:create --env=test
	symfony console doctrine:migrations:migrate -n --env=test
	symfony console doctrine:fixtures:load -n --env=test
	php bin/phpunit $@

.PHONY: tests

start:
	docker compose start
	symfony server:start -d

.PHONY: start

stop:
	docker compose stop
	symfony server:stop

.PHONY: stop

up:
	docker compose up -d
	symfony server:start -d

.PHONY: up

down:
	docker compose down
	symfony server:stop

.PHONY: down

up-build:
	docker compose up -d --build
	symfony server:start -d

.PHONY: up-build

migrate:
	symfony console doctrine:migrations:migrate

.PHONY: migrate