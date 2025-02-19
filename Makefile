# Подключение окружения проекта
include .env

# Создание образов, контейнеров и их запуск
install:
	@$(MAKE) -s down
	@$(MAKE) -s docker-build
	@$(MAKE) -s up

# Сборка контейнеров
build: docker-build
# Перезапуск контейнеров
restart: down up
# Запуск контейнеров
up: docker-up
# Удаление контейнеров
down: docker-down

# Просмотр запущенных контейнеров
ps:
	@docker ps

# Запуск контейнеров
docker-up:
	@docker-compose -p ${INDEX} up -d
# Удаление контейнеров
docker-down:
	@docker-compose -p ${INDEX} down --remove-orphans
# Создание образов для контейнеров
docker-build: \
	docker-build-php-cli \
	docker-build-php-fpm \
	docker-build-nginx \
	docker-build-nodejs

# Образ для nginx
docker-build-nginx:
	@docker-compose -p ${INDEX} build nginx --no-cache
# Образ для php-fpm
docker-build-php-fpm:
	@docker-compose -p ${INDEX} build php-fpm --no-cache
# Образ для php-cli
docker-build-php-cli:
	@docker-compose -p ${INDEX} build php-cli --no-cache
# Образ для nodejs (запуск vite)
docker-build-nodejs:
	@docker-compose -p ${INDEX} build nginx --no-cache

# Просмотр логов контейнеров
docker-logs:
	@docker-compose -p ${INDEX} logs -f

# Работа с php-fpm (atrisan, composer)
shell:
	@docker exec -it php-fpm /bin/sh
# Работа с ollama контейнером
ollama:
	@docker exec -it ollama /bin/sh

chown:
	@$(MAKE) app-php-cli-exec cmd="chown 1000:1000 -R ./"

