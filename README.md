# Notification System

A multi-channel notification system built with **Laravel 13** for the LoanPro challenge.

When a message is published in a category, the system fans it out to every user subscribed to that category, through every channel that user has enabled (Email, SMS, Push). Each delivery is processed by a queued job and recorded in the database.

---

## Requirements

- **PHP** 8.3 or higher
- **Composer** 2.x
- **Node.js** 18+ and **npm**
- **SQLite** (default, no setup needed) or any database supported by Laravel

---

## Quick start

```bash
# 1. Clone and enter the project
git clone <repo-url> notification-system
cd notification-system

# 2. One-shot install (env, deps, key, migrations, assets)
composer setup

# 3. Seed catalogs and 10 demo users (with random subscriptions/channels)
php artisan db:seed

# 4. Run everything in dev mode (server + queue + logs + vite)
composer dev
```

Open [http://localhost:8000](http://localhost:8000).

> `composer dev` starts `php artisan serve`, `queue:listen`, `pail` (logs), and `vite` in parallel.

## Using the app

The home page (`/`) has two sections:

1. **Send message form** — pick a category, type a message (max 1000 chars), click _Send_.
2. **Notifications log** — table that auto-refreshes after each send, showing every delivery attempt with its status (`pending`, `delivered`, `failed`).

Behind the scenes, sending a message:

1. `POST /api/v1/messages` creates the message row.
2. The dispatcher finds every user subscribed to that category.
3. One queued job is fired per `(user × enabled channel)`.
4. Each job calls the right channel (Email / SMS / Push) and persists a `notification` row with the outcome.

---

## API reference

Base URL: `http://localhost:8000/api/v1`

### List categories

```http
GET /api/v1/categories
```

```json
{
    "data": [
        { "id": 1, "name": "Sports", "slug": "sports" },
        { "id": 2, "name": "Finance", "slug": "finance" },
        { "id": 3, "name": "Movies", "slug": "movies" }
    ]
}
```

### Publish a message

```http
POST /api/v1/messages
Content-Type: application/json

{
  "category_slug": "sports",
  "body": "Lakers won the game!"
}
```

Returns `201 Created` and the dispatch summary. Invalid input returns `422` with field-level errors.

### List notifications

```http
GET /api/v1/notifications
```

```json
{
    "data": [
        {
            "id": 42,
            "user_id": 3,
            "user_name": "Jane Doe",
            "message_id": 7,
            "message_body": "Lakers won the game!",
            "channel_slug": "email",
            "category_slug": "sports",
            "status": "delivered",
            "error_message": null,
            "created_at": "2026-05-26T03:51:12+00:00"
        }
    ]
}
```

---

## Project layout

```
app/
├── Channels/          Strategy implementations (Email, Sms, Push)
├── DTOs/              Immutable readonly transport objects
├── Enums/             NotificationStatus, CategorySlug, ChannelSlug
├── Factories/         ChannelFactory resolves a slug to a channel
├── Http/
│   ├── Controllers/Api/v1/    Versioned API controllers
│   ├── Requests/              FormRequests with validation
│   └── Resources/             JSON shape for responses
├── Interfaces/        NotificationChannelInterface contract
├── Jobs/              SendNotificationJob (queued, retried)
├── Models/            Eloquent models
├── Repositories/      Data access layer
└── Services/          MessageService, NotificationDispatcher
```

### Adding a new channel

1. Create `app/Channels/MyChannel.php` implementing `NotificationChannelInterface`.
2. Add a case to `app/Enums/ChannelSlug.php` (e.g. `case MyChannel = 'my-channel';`).
3. Register it inside `app/Factories/ChannelFactory.php`.

No other code needs to change.

---

## Configuration

Useful `.env` variables:

| Variable           | Default                 | What it does                                     |
| ------------------ | ----------------------- | ------------------------------------------------ |
| `DB_CONNECTION`    | `sqlite`                | Database driver. SQLite works out of the box.    |
| `QUEUE_CONNECTION` | `sync`                  | Use `database` to exercise real async + retries. |
| `APP_URL`          | `http://localhost:8000` | Public URL for the app.                          |

To switch to the database queue:

```bash
php artisan queue:table
php artisan migrate
# set QUEUE_CONNECTION=database in .env
php artisan queue:work
```

---

## Tests

```bash
composer test
```

This runs `php artisan test` against an in-memory SQLite (configured in `phpunit.xml`).

---

## Common commands

| Command                            | Purpose                          |
| ---------------------------------- | -------------------------------- |
| `composer dev`                     | Run server + queue + logs + vite |
| `composer test`                    | Run the test suite               |
| `php artisan migrate:fresh --seed` | Reset DB and re-seed demo data   |
| `php artisan queue:listen`         | Process queued notification jobs |
| `php artisan pail`                 | Tail application logs            |
| `./vendor/bin/pint`                | Format code (PSR-12)             |

---

## Troubleshooting

- **Notifications never appear in the log.** Make sure the queue worker is running (`composer dev` does this for you, otherwise run `php artisan queue:listen`).
- **`422 The selected category is invalid`.** The `category_slug` must match one of `sports`, `finance`, `movies` (run `php artisan db:seed` if missing).
- **Empty log on first load.** Send a message first — there are no notifications until a message is published.
- **`SQLSTATE[HY000] no such table`.** Run `php artisan migrate`.
