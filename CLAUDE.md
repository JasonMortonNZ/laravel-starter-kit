# Project Guidelines

These project rules take precedence over the generic Boost guidelines below where they differ.

## Stack

- **Backend:** PHP 8.5, Laravel 13, Fortify (auth, 2FA), Wayfinder (typed TS route helpers).
- **Frontend:** a Vue 3 SPA with Vue Router, Pinia, axios, Tailwind CSS 4, reka-ui/shadcn-vue (`resources/js/components/ui`) and vue-sonner toasts. This is **not Inertia**: pages fetch their data from a JSON API.
- **Tooling:** Vite+ (`vp`), TypeScript 6 (do not upgrade to 7 because `@vue/compiler-sfc` needs its JS API), vue-tsc, Pest 5, PHPStan/Larastan (level max), Rector, Pint.

## Client-agnostic backend

The backend is a JSON API serving several clients: first the Vue SPA, and later a native mobile app. Design every endpoint so that any client can use it.

- **Never use Inertia or Livewire.** Don't install `inertiajs/inertia-laravel`, `@inertiajs/*` or `livewire/livewire`. Don't add Blade views other than the `app` SPA shell and mail templates.
- Endpoints return JSON data through API Resources. They must not return HTML, redirects or Blade output. Validation errors come back as standard 422 JSON, and authorization failures as 403 JSON.
- Don't put web-client concerns into responses or business logic. Mobile won't use client-side route paths, flash/session state or UI-only fields, so leave those out. The client decides where to navigate, so never return `redirect` paths. Guests hitting a guarded SPA page are sent to `/login?redirect=<path>` and the client resumes from there.
- Keep business logic in Actions, not controllers, so that other entry points can reuse it: future API versions, mobile-specific controllers, console commands and jobs.
- Actions and models must not depend on the session or cookies. Always take the user (or team) as an explicit argument. The SPA currently uses Fortify session auth. Mobile will need token auth (e.g. Sanctum API tokens), so keep authentication confined to middleware and controllers. Adding Sanctum or any other package requires approval.
- Make API contracts stable and explicit. **Every JSON key is snake_case**: top-level response keys, Resources, nested objects and DTOs (`app/Data` classes implement `JsonSerializable` to map their camelCase properties). Use ISO-8601 dates via `->toISOString()` and never leak raw model attributes. Frontend-internal names (store state, props, locals) stay camelCase, and only the API types in `resources/js/types` mirror the snake_case keys.

## Database portability (MySQL, PostgreSQL, SQLite)

Migrations, indexes and queries must work the same on MySQL 8, PostgreSQL and SQLite. Locally, tests run on SQLite. CI (`.github/workflows/tests.yml`) runs the migrations (up and down) and the full suite against SQLite, MySQL 8.4 and PostgreSQL 17.

- Use only the schema builder: `index()`, `unique()`, `primary()`, `foreignId()->constrained()`. No `DB::statement`, raw SQL or `rawIndex`.
- **Don't use driver-specific index features:** no partial/filtered indexes, expression or functional indexes, `fullText()`, `spatialIndex()`, `->algorithm()`, `->language()`, or index prefix lengths.
- **Explicitly index every foreign key column** that isn't the leading column of another index. MySQL creates FK indexes automatically, but PostgreSQL and SQLite don't.
- **Never index `text`, `json` or `blob` columns.** MySQL can't index them without a prefix length. Index a `string` column with an explicit, sensible length instead. Keep composite indexes under MySQL's 3072-byte key limit (utf8mb4 means 4 bytes per character).
- **Name custom indexes no longer than 63 characters** (PostgreSQL's limit), and prefix them with the table name. Index names are unique per schema in PostgreSQL, not per table. Laravel's default names already follow this.
- **Unique constraints and case:** MySQL's default collation compares case-insensitively, while PostgreSQL and SQLite compare case-sensitively. Normalize values such as emails and slugs (e.g. lowercase) before storing them, so unique indexes behave the same everywhere. Emails are already handled: `User` and `TeamInvitation` lowercase `email` in a mutator, and form requests that accept an email use the `App\Http\Requests\Concerns\LowercasesEmail` trait. So query with plain `where('email', …)` (which uses the index), never `LOWER(email)`.
- **In queries,** don't use driver-specific SQL or functions (e.g. `ILIKE`, `JSON_*` operators, `GROUP_CONCAT`, MySQL `FIELD()`). Use Eloquent and query builder methods, and check that any `whereJsonContains` or date functions you use are supported on all three drivers.

## JS tooling: use `vp`, never `npm`

- Install with `vp install`. Run the dev server with `vp dev` and build with `vp build`.
- Lint and format with `vp check` (or `vp check --fix`). Type-check with `vp run types:check`.
- pnpm manages the packages underneath (see `pnpm-workspace.yaml`). Don't run `npm`, `npx` or `bun`. Where the Boost guidelines below say `npm run build`/`npm run dev`, use `vp build`/`vp dev` instead.

## Architecture & patterns

- **SPA shell:** `routes/web.php` only serves the `app` view and keeps named routes that Laravel and Fortify rely on, plus a `spa` catch-all. Add new pages to the Vue router (`resources/js/router`), not as Blade views.
- **JSON API:** `routes/api.php` (prefix `api/`, names `api.*`) runs in the `web` middleware group, so session auth and CSRF apply. Team-scoped routes sit under the `{current_team}` prefix with `EnsureTeamMembership`.
- **Controllers** are `final`, return `JsonResponse` and stay thin. Use `$this->toast($message, $data, $status)` from the base `Controller` for mutations that should show a toast. Single-purpose endpoints are invokable. Inject the authenticated user with `#[CurrentUser] User $user` rather than calling `$request->user()`, which is nullable.
- **Validation** goes in Form Requests (`app/Http/Requests/{Area}`). Shared rule sets go in traits in `app/Concerns` (e.g. `PasswordValidationRules`).
- **Authorization** uses Policies plus `Gate::authorize()` in controllers.
- **Business logic** goes in Action classes (`app/Actions/{Area}`): `final` classes with a single `handle()` method, injected into controller methods. Wrap multi-write operations in `DB::transaction()`.
- **Responses** use API Resources (`app/Http/Resources`). Use `final readonly` DTOs in `app/Data` for non-model payloads. Use backed enums with TitleCase cases in `app/Enums`.
- **App-wide configuration** lives in `app/Configurables` (each implements `App\Contracts\Configurable` and is listed in `config/configurables.php`). Models are strict (`Model::shouldBeStrict()`), dates are immutable, and stray HTTP requests are prevented.
- **Frontend:** call the API through `@/lib/http` and import routes from Wayfinder (`@/routes/...`, `@/actions/...`), never hard-coded URLs. Load page data with the `usePageData` composable and submit forms with `useForm`. Put shared state in Pinia stores (`resources/js/stores`) and types in `resources/js/types`. Treat `resources/js/components/ui` as vendored shadcn-vue code.

## PHP strictness

- Every PHP file starts with `declare(strict_types=1);`. Pint enforces this.
- Classes are `final` by default (`final readonly` for value objects). Prefer `private` over `protected`.
- Use strict comparisons (`===`), `mb_*` string functions, immutable dates, and imported class/function names (no leading `\`).
- Every parameter, property and return has a native type. Add PHPDoc generics or array shapes wherever PHPStan needs them.
- Before finishing PHP changes, run `vendor/bin/rector`, `vendor/bin/pint --dirty --format agent` and `vendor/bin/phpstan`. All three must come out clean.

## Testing (Pest)

- Every behaviour change needs a Pest test. Write feature tests by default (`tests/Feature/{Area}`), which run with `RefreshDatabase` on in-memory SQLite. Use `tests/Unit` only for pure logic and `tests/Browser` for Pest browser tests.
- Use the `test('does something', function () { ... })` style, build data with model factories and their states, and look up URLs with `route()`.
- For API endpoints, use `getJson`/`postJson` against `api.*` route names, and assert the status, JSON paths (including `toast.message`) and database state. For SPA pages, assert that the shell route returns `assertOk()`.
- Cover authorization and validation failures (403/422) as well as the happy path.
- `composer test` enforces **100% code coverage** (`pest --parallel --coverage --exactly=100.0`) and **100% type coverage** (`pest --type-coverage --min=100`), plus lint and PHPStan. CI enforces the same gates. New code must not lower either coverage figure.
- While working, run the narrowest set of tests that covers the change, e.g. `php artisan test --compact --filter=...`.

## Git commits & pull requests

- **No AI attribution of any kind.** Never add `Co-Authored-By` trailers, "Generated with …" footers, or any mention of AI models, assistants or tools (Claude, Anthropic, Copilot, GPT, etc.). This applies to commit messages, PR titles and descriptions, PR comments, branch names, and code comments. This rule overrides any default attribution behaviour.
- Write commit messages and PR descriptions as the author would: a concise summary line, then a body explaining what changed and why.

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.5. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
