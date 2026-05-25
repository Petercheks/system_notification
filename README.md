# Notification System

Notification system built for the LoanPro challenge.

## Requirements

- PHP 8.3+
- Composer
- Node.js

## Installation

```bash
composer setup
```

Or manually:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Development

```bash
composer dev
```

The app will be available at [http://localhost:8000](http://localhost:8000).

## Tests

```bash
composer test
```
