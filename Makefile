CURRENT_UID=$(shell id -u)

.PHONY: docker-up vendors

install: vendors

docker-up: .docker-build data docker-compose.override.yml
	CURRENT_UID=$(CURRENT_UID) docker-compose up

.docker-build: docker-compose.yml docker-compose.override.yml $(shell find docker/dockerfiles -type f)
	CURRENT_UID=$(CURRENT_UID) docker-compose build
	touch .docker-build

docker-compose.override.yml:
	cp docker-compose.override.yml-dist docker-compose.override.yml

vendors: vendor

vendor: composer.phar composer.lock
	php composer.phar install

composer.phar:
	$(eval EXPECTED_SIGNATURE = "$(shell wget -q -O - https://composer.github.io/installer.sig)")
	$(eval ACTUAL_SIGNATURE = "$(shell php -r "copy('https://getcomposer.org/installer', 'composer-setup.php'); echo hash_file('SHA384', 'composer-setup.php');")")
	@if [ "$(EXPECTED_SIGNATURE)" != "$(ACTUAL_SIGNATURE)" ]; then echo "Invalid signature"; exit 1; fi
	php composer-setup.php
	rm composer-setup.php

init: .docker-build
	CURRENT_UID=$(CURRENT_UID) docker-compose run --rm cli make vendors

data:
	mkdir -p docker/data
	mkdir -p docker/data/composer
