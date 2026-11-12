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
