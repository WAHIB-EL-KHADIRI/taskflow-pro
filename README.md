# TaskFlow Pro

Enterprise-grade Task & Project Management System built with PHP 8.1+, MySQL, and a custom MVC framework following Clean Architecture principles.

## Architecture

```
app/
├── Application/        # Use cases (business logic orchestration)
├── Core/               # Shared services (i18n)
├── Domain/             # Entities and repository interfaces
├── Http/               # Controllers, middleware, request/response, routing
└── Infrastructure/     # Database repositories (PDO)
bootstrap/              # Application bootstrap
config/                 # (reads from .env via Dotenv)
database/               # Migration scripts
public/                 # Web root (entry point: index.php)
resources/              # Views, translations
routes/                 # Route definitions
storage/                # Cache, logs, sessions, uploads
tests/                  # PHPUnit tests
```

## Requirements

- PHP >= 8.1
- MySQL >= 5.7
- Apache with mod_rewrite (or PHP built-in server)
- Composer

## Installation

```bash
git clone <repo-url>
cd taskflow-pro
cp .env.example .env
composer install
php database/migrate.php seed
php -S localhost:8000 -t public/
```

## Environment

Copy `.env.example` to `.env` and configure:

| Variable | Description | Default |
|---|---|---|
| `APP_ENV` | `development` or `production` | `production` |
| `APP_DEBUG` | Verbose error output | `false` |
| `DB_HOST` | MySQL host | `127.0.0.1` |
| `DB_DATABASE` | Database name | `taskflow_pro` |
| `DB_USERNAME` | DB user | `root` |
| `DB_PASSWORD` | DB password | (empty) |

## Available Commands

```bash
composer start          # Start dev server
composer test           # Run PHPUnit
composer lint           # Check PSR-12 style
composer analyse        # PHPStan static analysis
php database/migrate.php refresh seed  # Reset DB with seed data
```

## License

MIT
