include .env

install:
	@$(MAKE) -s down
	@$(MAKE) -s docker-build
	@$(MAKE) -s up
	@$(MAKE) -s composer-install

restart: down up
up: docker-up
down: docker-down

ps:
	@docker-compose ps

get-pwd:
	echo $(PWD)

docker-up:
	@docker-compose -p ${INDEX} up -d

docker-down:
	@docker-compose -p ${INDEX} down --remove-orphans

docker-build: \
	docker-build-app-php-cli \
	docker-build-app-php-fpm \
	docker-build-app-nginx \
	docker-build-app-nodejs \

docker-build-app-nginx:
	@docker build --target=nginx \
	-t ${REGISTRY}/${INDEX}-nginx:${IMAGE_TAG} -f ./docker/Dockerfile .

docker-build-app-php-fpm:
	@docker build --target=fpm \
	-t ${REGISTRY}/${INDEX}-php-fpm:${IMAGE_TAG} -f ./docker/Dockerfile .

docker-build-app-php-cli:
	@docker build --target=cli \
	-t ${REGISTRY}/${INDEX}-php-cli:${IMAGE_TAG} -f ./docker/Dockerfile .

docker-build-app-nodejs:
	@docker build --target=nodejs \
	-t ${REGISTRY}/${INDEX}-nodejs:${IMAGE_TAG} -f ./docker/Dockerfile .

docker-logs:
	@docker-compose -p ${INDEX} logs -f

app-php-cli-exec:
	@docker-compose -p ${INDEX} run --rm php-cli $(cmd)

composer-install:
	$(MAKE) app-php-cli-exec cmd="composer install"

composer-update:
	$(MAKE) app-php-cli-exec cmd="composer update"

shell:
	@docker-compose -p ${INDEX} run --rm php-cli /bin/sh

chown:
	@$(MAKE) app-php-cli-exec cmd="chown 1000:1000 -R ./"

build:
	@docker-compose run --rm app-nodejs

watch:
	@docker-compose run --rm app-nodejs npm run dev

node:
	@docker-compose exec app-nodejs /bin/sh

ollama:
	@docker exec -it ollama /bin/bash
