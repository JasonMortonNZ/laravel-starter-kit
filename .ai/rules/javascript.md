# JavaScript Rules

## JS tooling: use `vp`, never `npm`

- Install with `vp install`. Run the dev server with `vp dev` and build with `vp build`.
- Lint and format with `vp check` (or `vp check --fix`). Type-check with `vp run types:check`.
- pnpm manages the packages underneath (see `pnpm-workspace.yaml`). Don't run `npm`, `npx` or `bun`. Where the Boost guidelines below say `npm run build`/`npm run dev`, use `vp build`/`vp dev` instead.

## Running `vp` locally

- Vite+ is installed per project (no global CLI). Run it through pnpm via corepack: `corepack pnpm exec vp <command>` (e.g. `corepack pnpm exec vp check`, `corepack pnpm exec vp run types:check`). If `vp` is installed globally later, the bare `vp <command>` form works the same.
- Wayfinder output (`resources/js/actions`, `resources/js/routes`, `resources/js/wayfinder`) is generated and gitignored. Run `php artisan wayfinder:generate --no-interaction` before type-checking outside the dev server.

## Live updates

- Pages that change in the background use `useLiveUpdates` (`@/composables/useLiveUpdates`) with the event's `broadcastAs()` name, never a bare `setInterval`. It refetches on each event and after every (re)subscribe, and polls only while the websocket is disconnected or Reverb isn't configured.
- The Echo connection lives in `@/lib/realtime`. `AppLayout` starts it after sign-in and stops it when the signed-in area closes.
