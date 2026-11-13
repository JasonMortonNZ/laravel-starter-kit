# Laravel Starter Kit

My opinionated Laravel + Vue starter kit, built on `laravel/vue-starter-kit`. Private use only.

## Create a new app

```bash
composer create-project jasonmortonnz/laravel-starter-kit my-app \
  --repository='{"type":"vcs","url":"git@github.com:JasonMortonNZ/laravel-starter-kit.git"}' \
  --stability=dev
```

Composer needs access to the private repo, either through an SSH key loaded for GitHub or a token:

```bash
composer config -g github-oauth.github.com <token>
```

Creating the project copies `.env`, generates the app key, generates Reverb credentials and switches broadcasting to Reverb, creates and migrates the SQLite database, and syncs the agent skills with `boost:update`.

## After install

1. Set `APP_NAME` and `APP_URL` in `.env`.
2. Run `composer setup` to install frontend dependencies and build.
3. Fill in `.ai/rules/domain.md` with the project's own invariants.
4. Run `composer dev`.

## What's included

- Laravel 13, PHP 8.5, Fortify, Wayfinder, Reverb
- Vue 3, Vue Router, Pinia, Axios, Laravel Echo (pusher-js), TypeScript, Tailwind CSS 4, Vite+ (`vp`)
- Pest 5 with 100% code and type coverage, PHPStan (level max), Rector, Pint
- Opinionated defaults in `app/Configurables`, toggled in `config/configurables.php`
- Agent rules in `.ai/rules` and custom skills in `.ai/skills`, wired up through Laravel Boost
- GitHub Actions CI in `.github/workflows/tests.yml`

## Keeping up with upstream

```bash
git remote add upstream https://github.com/laravel/vue-starter-kit.git
git fetch upstream
git merge upstream/main
```
