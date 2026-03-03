# 🛒 Shop Admin - Laravel 8
# � Shop Admin (Laravel)

This repository contains a Laravel-based admin panel and simple store management app.

Requirements (if running locally)
- Docker & Docker Compose (recommended)
- PHP 8.3.6 (if running without Docker)
- Composer

2) Development (use `docker-compose.dev.yml` for DB + phpMyAdmin and run PHP locally)

This flow is useful if you prefer running PHP on your host machine but want a containerized database.

- Start only the DB + phpMyAdmin services:

```bash
docker compose -f docker-compose.dev.yml up -d
```

- Copy `.env` and update DB settings to connect from your host to the MySQL container (note the forwarded port 3307):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=ShopAdminDB
DB_USERNAME=root
DB_PASSWORD=root
```

- Install dependencies and run the app on your machine:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

- Open the app at:

http://127.0.0.1:8000

- phpMyAdmin is available at:

http://127.0.0.1:8081

Helpful artisan commands

```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

Notes
- If you run the full Docker Compose setup, be sure your `.env` DB_HOST is `db` (the service name). If you run the DB-only dev compose and use `php artisan serve` from the host, point DB_HOST to `127.0.0.1` and DB_PORT to `3307` (the port mapped in `docker-compose.dev.yml`).
- Default seeded users (see DatabaseSeeder):
	- admin@example.com / password (admin)
	- user1@example.com / password (user)
	- user2@example.com / password (user)

## 📧 Queue & Email setup

This project now uses Laravel's queue system to send order confirmation emails asynchronously.

1. Set the connection in `.env`:
   ```dotenv
   QUEUE_CONNECTION=database
   MAIL_MAILER=log   # or smtp/other depending on your environment
   ```
2. Publish the queue tables and run migrations:
   ```bash
   php artisan migrate
   # if you haven't already created the jobs/failed_jobs tables:
   # php artisan queue:table && php artisan queue:failed-table && php artisan migrate
   ```
3. Start a worker in development (in another terminal):
   ```bash
   php artisan queue:work --tries=3
   ```
   The worker will pick up `SendOrderCreatedEmail` jobs and dispatch them to the mailer.

4. You can test the queue behaviour in automated tests (see `tests/Feature/OrderTest.php`).

> When QUEUE_CONNECTION is `sync` (the default), jobs run immediately, which is fine for simple local testing but defeats the purpose of background processing.
