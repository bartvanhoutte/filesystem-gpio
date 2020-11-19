#!make
include .env
include .env.local
export $(shell sed 's/=.*//' .env)
export $(shell sed 's/=.*//' .env.local)

# Provides a bash in PHP container (user www-data)
bash-php: up
	docker-compose exec php bash

composer-install: up
	# Install PHP dependencies
	docker-compose exec -u www-data php composer install

cache-clear: up
	docker-compose exec -u www-data php php bin/console cac:c

# Up containers
up:
	docker-compose up -d

# Up containers, with build forced
build:
	docker-compose up -d --build

# Down containers
down:
	docker-compose down
