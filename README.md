# Backend coding challenge for Arkade
## Purpose
Laravel application which reads from https://dog.ceo/api/ and pushes them into Shopify as new products.

## Technologies Used
* Docker (optional)
* PHP 7.3 (Laravel 6)

## Dependencies Needed
* PHP 7.3
* Composer
* laravel/framework 6.2
* GuzzleHTTP library 6.5
* Phpunit 8.2

##Getting Started

### Option 1 : Use DOCKER

1. build and run the following containers :
    - arkade_app (php-fpm)
    - arkade_db(Mysql)
    - arkade_nginx(nginx)
    - arkade_scheduler(cron)<br>
  use the following command 
```
docker-compose up -d
```

2. Connect to the docker_app container:
 ```
 docker exec -it arkade_app bash
```

3. Copy .env.example to .env
 ```
 cp .env.example .env 
```

4. Install dependencies with Composer:
 ```
 composer install
 ```
 
5. Access the web app from http://localhost:8080
 
## Run Scheduler
The cron job is running on "arkade_scheduler" docker container, the container is running the following cron job
```
* * * * * su -c '/usr/local/bin/php /var/www/html/artisan schedule:run >> /var/www/html/storage/logs/schedule.log 2>&1'
```
The products sync logic is set as a laravel command located in app/Console/Commands/Sync.php, to manually run it: 
```
php artisan shopify:sync
``` 
##Unit Tests
```
 phpunit tests/Unit
```
