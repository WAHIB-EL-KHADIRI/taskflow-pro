# Contributing to TaskFlow Pro

Thanks for taking the time to contribute.

## Development setup

```bash
git clone https://github.com/WAHIB-EL-KHADIRI/taskflow-pro
cd taskflow-pro
composer install
cp .env.example .env   # then fill in your database credentials
composer start         # serves on http://localhost:8000
```

Requires PHP 8.1+ and MySQL.

## Before opening a pull request

Run the full check suite — CI runs exactly this:

```bash
composer check    # lint (PSR-12) + static analysis + tests
```

Individually:

| Command | What it does |
|---|---|
| `composer lint` | PSR-12 compliance (phpcs) |
| `composer lint-fix` | Auto-fix fixable style violations |
| `composer analyse` | Static analysis (PHPStan, level 5) |
| `composer test` | Test suite (PHPUnit) |

## Code style

- PSR-12, enforced by phpcs. Run `composer lint-fix` before committing.
- The project follows a layered structure: `app/Domain` (entities and repository
  interfaces), `app/Infrastructure` (implementations), `app/Http` (request/response
  handling). Keep domain code free of framework and database concerns.
- New behavior needs a test. A test that cannot fail is not a test.

## Commit messages

[Conventional Commits](https://www.conventionalcommits.org/):

```
feat(tasks): add recurring task support
fix(auth): reject expired session tokens
docs: clarify database setup
```

## Reporting bugs

Open an issue with: what you did, what you expected, what happened, and your
PHP/MySQL versions. A failing test case is the most useful thing you can attach.
