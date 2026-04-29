# ServiceAI Cloud v3

Laravel 12 + Vue 3 starter for a ServiceAI SaaS product.

## Requirements
- PHP 8.2+
- Composer 2.7+
- Node.js 20+
- MySQL 8+

## Quick start
```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
```

## Development
Open two terminals.

Terminal 1:
```bash
php artisan serve
```

Terminal 2:
```bash
npm run dev
```

Then open the Laravel URL shown by `php artisan serve`.

## Docker Development
Start Docker Desktop first, then run:

```bash
docker compose up --build
```

Open:
- App: http://localhost:8080
- Vite dev server: http://localhost:5173
- MySQL: localhost:3307
- Redis: localhost:6380

Run migrations inside the app container:

```bash
docker compose exec app php artisan migrate --seed
```

Useful commands:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan queue:work
docker compose down
```

## Production asset build
```bash
npm run build
```

## Demo login
- Email: owner@example.com
- Password: password

## Notes
- This starter does not include `vendor` or `node_modules`.
- If you see a cache path error, create the missing folders under `storage/framework` and `bootstrap/cache`.
