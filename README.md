<h1 align="center">
    Hi there, We're neuro App!
    <img src="https://github.com/blackcater/blackcater/raw/main/images/Hi.gif" height="32"/>
</h1>
<h3 align="center"></h3>

## This is a repository dedicated to the Laravel-vue module of the "Neuro App" project

### Look what we have!

- [Installing](#installing)
  

## Installing 

To run laravel-vue on a local machine you need:

- install project locally:
	- git init
	- git clone https://github.com/finesko1/neuro-app-laravel-vue.git
    - you also can download make for system: https://gnuwin32.sourceforge.net/packages/make.htm
- init project environment:
    - create .env file by copying .env.example
- run docker desktop and then (you can use the makefile):
	- docker-compose -p neuro-laravel build *(to display the process use --progress=plain)* or *make build*
    - docker-compose -p neuro-laravel up -d or *make up*
- to stop container:
    docker-compose -p neuro-laravel down --remove-orphans or *make down*
- if you need to restart containers:
    use make command: make restart
- for work into php-fpm container (artisan and composer):
    docker exec -it php-fpm /bin/sh
- for work into ollama container (use windows CMD!):
    docker exec -it ollama /bin/sh (you can install models into this container, example: ollama && ollama pull deepseek-r1:1.5b)
    **IMPORTANT!** *install nomic-embed-text:latest for embeddings*
- check docker logs:
    @docker-compose -p neuro-laravel logs -f or *make docker-logs*
- you can work with project outer folder using a file global_makefile (create Makefile by copying this file outer folder)
     - to build this project: make build-laravel-vue
     - to up this project: make up-laravel-vue
     - to down thiw project: make down-laravel-vue
- you may encounter problems with sql (check postgres container pr .env (DB user, password))
- you may encounter problems with artisan (check php-fpm container, vendor folder. You must example commands: composer i, php artisan migrate:fresh)
- you may encounter problems with vite (re-build project)
