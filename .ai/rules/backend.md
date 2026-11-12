# Backend Rules

The backend is a JSON API serving several clients: first the Vue SPA, and later a native mobile app. Design every endpoint so that any client can use it.

* Never use Inertia or Livewire. Don't install `inertiajs/inertia-laravel`, `@inertiajs/*` or `livewire/livewire`. Don't add Blade views other than the app SPA shell and mail templates.
* Endpoints return JSON data through API Resources. They must not return HTML, redirects or Blade output. Validation errors come back as standard 422 JSON, and authorization failures as 403 JSON. 
* Don't put web-client concerns into responses or business logic. Mobile won't use client-side route paths, flash/session state or UI-only fields, so leave those out. The client decides where to navigate, so never return `redirect` paths. Guests hitting a guarded SPA page are sent to `/login?redirect=<path>` and the client resumes from there. 
* Keep business logic in Actions, not controllers, so that other entry points can reuse it: future API versions, mobile-specific controllers, console commands and jobs. 
* Actions and models must not depend on the session or cookies. Always take the user (or team) as an explicit argument. The SPA currently uses Fortify session auth. Mobile will need token auth (e.g. Sanctum API tokens), so keep authentication confined to middleware and controllers. Adding Sanctum or any other package requires approval. 
* Make API contracts stable and explicit. Every JSON key is snake_case: top-level response keys, Resources, nested objects and DTOs (app/Data classes implement JsonSerializable to map their camelCase properties). Use ISO-8601 dates via `->toISOString()` and never leak raw model attributes. Frontend-internal names (store state, props, locals) stay camelCase, and only the API types in resources/js/types mirror the snake_case keys.

## Exception: Laravel Nova (platform admin)

* Laravel Nova is the one approved exception to the Inertia/Blade rules above. Nova is an Inertia app and pulls in `inertiajs/inertia-laravel` as its own dependency. That is allowed only for Nova's internal platform-admin area at `/nova`.
* Never use Inertia, Nova components or Blade views anywhere else, and never build tenant-facing features in Nova. Nova resources stay read-only by default (extend `App\Nova\ReadOnlyResource`), and any change they make goes through the same Actions the API uses, so it is audited.
* Nova has no login of its own. Platform admins sign in through the SPA (with 2FA), and access needs `is_platform_admin` plus confirmed two-factor authentication (`php artisan platform:admin {email}`).

## Exception: the marketing site

* The public marketing site is the second scoped exception to the Blade rule. `resources/views/marketing.blade.php` wraps static HTML prerendered at build time from `resources/js/marketing` (served by `MarketingPageController`, with page metadata in `config/marketing.php`). It must never read tenant data.
* The signed-in app lives under `/app`. Build every link to it with `App\Support\SpaUrl::to()`, never a hard-coded path.
