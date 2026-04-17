# Internal Academy

[![tests](https://github.com/serena-peschiaroli/internal-academy/actions/workflows/tests.yml/badge.svg)](https://github.com/serena-peschiaroli/internal-academy/actions/workflows/tests.yml)
[![linter](https://github.com/serena-peschiaroli/internal-academy/actions/workflows/lint.yml/badge.svg)](https://github.com/serena-peschiaroli/internal-academy/actions/workflows/lint.yml)

Internal Academy is a Laravel + Vue + Inertia application to manage internal company workshops.

## Stack

- Backend: Laravel 13, PHP 8.4
- Frontend: Vue 3 + TypeScript + Vite + Inertia
- Auth: Laravel Fortify
- Database: MySQL (primary), SQLite (test environment)
- Queues: Laravel Queues (sync for development)
- Tests: Pest (Feature + Unit)

## Features

- Role-based app areas (`admin`, `employee`)
- Admin workshop CRUD
- Employee workshop catalog (future workshops only)
- Workshop registration/cancellation
- Waiting list (FIFO)
- No-ubiquity rule (cannot be confirmed in overlapping workshops, including waitlist promotion)
- Admin stats dashboard with real-time updates via Laravel Reverb (WebSocket), fallback polling on disconnect
- Reminder command: `academy:remind`
- Profile and security settings

## Prerequisites

- PHP 8.4+
- Composer
- Node.js 20+
- npm

## Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Create environment file and app key:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure and prepare database (MySQL):

```bash
# set DB_CONNECTION=mysql and your local DB credentials in .env
php artisan migrate --seed
```

4. Build frontend assets (production build):

```bash
npm run build
```

## Run in development

Use the integrated dev command (app server + queue worker + Reverb + Vite):

```bash
composer run dev
```

Or run services separately:

```bash
php artisan serve
php artisan queue:listen --tries=1
php artisan reverb:start
npm run dev
```

## Seed data

`php artisan migrate --seed` loads:

- roles (`admin`, `employee`)
- test employee: `test@example.com` / `password`
- test admin: `test-admin@internal-academy.com` / `Password1`
- sample workshops

## Test suite

Run full suite:

```bash
php artisan test
```

Run targeted suites:

```bash
php artisan test tests/Feature
php artisan test tests/Unit
```

## Linting and formatting

```bash
npm run lint:check
npm run format:check
npm run types:check
composer run lint:check
```

## Reminder command

Send reminders for tomorrow workshops:

```bash
php artisan academy:remind
```

Target a specific date:

```bash
php artisan academy:remind --date=2026-04-16
```

## Challenge compliance

See [docs/challenge-compliance.md](docs/challenge-compliance.md) for requirement-by-requirement mapping.
