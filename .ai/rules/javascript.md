# JavaScript Rules

## JS tooling: use `vp`, never `npm`

- Install with `vp install`. Run the dev server with `vp dev` and build with `vp build`.
- Lint and format with `vp check` (or `vp check --fix`). Type-check with `vp run types:check`.
- pnpm manages the packages underneath (see `pnpm-workspace.yaml`). Don't run `npm`, `npx` or `bun`. Where the Boost guidelines below say `npm run build`/`npm run dev`, use `vp build`/`vp dev` instead.

## Running `vp` locally

- Vite+ is installed per project (no global CLI). Run it through pnpm via corepack: `corepack pnpm exec vp <command>` (e.g. `corepack pnpm exec vp check`, `corepack pnpm exec vp run types:check`). If `vp` is installed globally later, the bare `vp <command>` form works the same.
- Wayfinder output (`resources/js/actions`, `resources/js/routes`, `resources/js/wayfinder`) is generated and gitignored. Run `php artisan wayfinder:generate --no-interaction` before type-checking outside the dev server.

## Marketing site

- The marketing site is a separate Vite entry (`resources/js/marketing.ts`, styles in `resources/css/marketing.css`) with an SSR entry (`resources/js/marketing-ssr.ts`). The full build is the `build` package script (`corepack pnpm run build`): client build, SSR build, then `scripts/prerender-marketing.mjs`. Deploys must run that script, not just `vp build`.
- All marketing copy lives in `resources/js/marketing/content.ts`. Use `brandName` from `@/lib/brand` rather than writing the product name.
- Add a page in three places: the route in `createMarketingApp.ts`, its path in `paths`, and its metadata in `config/marketing.php`.

## Live updates

- Pages that change in the background use `useLiveUpdates` (`@/composables/useLiveUpdates`) with the event's `broadcastAs()` name, never a bare `setInterval`. It refetches on each event and after every (re)subscribe, and polls only while the websocket is disconnected or Reverb isn't configured.
- The Echo connection lives in `@/lib/realtime`. `AppLayout` starts it after sign-in and stops it when the signed-in area closes.
