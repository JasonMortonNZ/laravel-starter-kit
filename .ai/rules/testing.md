# Testing Rules

## Testing (Pest)

- Every behaviour change needs a Pest test. Write feature tests by default (`tests/Feature/{Area}`), which run with `RefreshDatabase` on in-memory SQLite. Use `tests/Unit` only for pure logic and `tests/Browser` for Pest browser tests.
- Use the `test('does something', function () { ... })` style, build data with model factories and their states, and look up URLs with `route()`.
- For API endpoints, use `getJson`/`postJson` against `api.*` route names, and assert the status, JSON paths (including `toast.message`) and database state. For SPA pages, assert that the shell route returns `assertOk()`.
- Cover authorization and validation failures (403/422) as well as the happy path.
- `composer test` enforces **100% code coverage** (`pest --parallel --coverage --exactly=100.0`) and **100% type coverage** (`pest --type-coverage --min=100`), plus lint and PHPStan. CI enforces the same gates. New code must not lower either coverage figure.
- While working, run the narrowest set of tests that covers the change, e.g. `php artisan test --compact --filter=...`.

## Factories

We should utilise model factories for all models and apply various states when necessary.

## Seeding

Where it makes sense we should create seed files to ensure quick and consistent data is available within tests.
