# Deployment Linux dan Nginx

## Requirement Server

PHP 8.3+ beserta ekstensi bcmath, ctype, curl, dom, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml; Composer 2; MySQL 8+/MariaDB; Node.js 20+ untuk build; Nginx; HTTPS.

## Proses

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Atur owner web server dan permission write hanya untuk `storage/` dan `bootstrap/cache/`. Jangan memberi write permission ke seluruh project.

## Environment

Gunakan `APP_ENV=production`, `APP_DEBUG=false`, URL HTTPS, credential MySQL terpisah dengan hak minimum, `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL=warning`, timezone `Asia/Jayapura`, dan locale `id`. Jangan commit `.env`.

## Nginx

Document root harus menunjuk ke direktori `public/`. Gunakan `try_files $uri $uri/ /index.php?$query_string`, batasi akses file dot, dan teruskan PHP hanya ke PHP-FPM. Aktifkan TLS, HSTS setelah HTTPS tervalidasi, serta backup database dan `storage/app/public`.

Saat rilis berikutnya: aktifkan maintenance mode bila diperlukan, pull artifact, `composer install`, `npm run build`, `php artisan migrate --force`, `php artisan optimize`, lalu nonaktifkan maintenance mode. Queue dan scheduler tidak wajib pada versi ini.
