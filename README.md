# Starlfinx Demo

This repository contains a Laravel demo application with time-logging and leave management features.

This Dockerfile is configured to install PHP, Composer, Node/npm, and will attempt to scaffold Laravel Breeze and build frontend assets during image build. See notes below.

## Build the Docker image

From the project root run:

```bash
docker build -t starlfinx-demo1-app .
```

Or, using docker-compose:

```bash
docker-compose build
docker-compose up -d
```

## What the Dockerfile does

- Installs PHP 8.4 and required PHP extensions
- Installs Node.js (npm) so front-end tooling is available
- Runs `composer install` to install PHP dependencies

Recommended workflow (safe):

1. Build the image without relying on artisan scaffolding:

```bash
docker build -t starlfinx-demo1-app .
docker run --rm -it -v $(pwd):/var/www starlfinx-demo1-app bash
# inside container
composer install
php artisan key:generate
php artisan migrate --seed
composer require laravel/breeze --dev
php artisan breeze:install
npm ci
npm run build
```

2. Or run the Breeze install and npm steps on your host machine before building the production image.

## Running migrations & seeds

Once the container is running and you have DB connectivity configured, run:

```bash
docker exec -it <container> php artisan migrate --seed
```

## Permissions

Ensure `storage` and `bootstrap/cache` are writable by the web server user. The Dockerfile attempts to set permissions, but on some host setups you may need to adjust ownership.

## Rebuilding assets

If you change front-end code (resources/js or resources/css):

```bash
npm ci
npm run build
```

Or inside the container:

```bash
docker exec -it <container> npm run build
```

## Troubleshooting

- If Breeze scaffolding did not appear, run `composer require laravel/breeze --dev` and `php artisan breeze:install` inside the container or locally and rebuild assets.
- If artisan commands fail due to missing `.env`, copy `.env.example` to `.env` and run `php artisan key:generate`.

## Accessing services (URLs)

By default the compose setup exposes the application and helper services to localhost. Confirm actual ports in `docker-compose.yml` if you changed them.

- Application (Nginx): http://localhost/ (port 80)
- MailHog (email testing UI): http://localhost:8025/
- phpMyAdmin (database GUI): http://localhost:8080/  
	- Default DB connection values from `docker-compose.yml`: host=`mysql`, user=`user`, password=`user123`, database=`laravel_db`.

If ports in your `docker-compose.yml` are different, adjust the host URLs accordingly. You can list running containers and their ports with:

```bash
docker-compose ps
# or
docker ps
```
