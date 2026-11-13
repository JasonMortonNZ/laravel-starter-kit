# Rules Index

Read every rule file whose globs match the paths you are about to create or edit. `general.md` and `git.md` always apply. Then run `grep -rin '<keyword>' .ai/rules` to catch anything a glob match misses.

| Rule file | Applies to (globs) |
|---|---|
| [general.md](general.md) | `**/*` |
| [git.md](git.md) | Commits, branches, pull requests, PR comments |
| [stack.md](stack.md) | `composer.json`, `package.json`, `pnpm-workspace.yaml`, any dependency change |
| [php.md](php.md) | `**/*.php` |
| [backend.md](backend.md) | `app/**`, `routes/**`, `bootstrap/app.php`, `config/**`, `resources/views/**` |
| [database.md](database.md) | `database/**`, `app/Models/**`, any query or `Schema::` usage |
| [testing.md](testing.md) | `tests/**`, `database/factories/**`, `database/seeders/**`, `phpunit.xml`, `.github/workflows/**` |
| [javascript.md](javascript.md) | `resources/js/**`, `resources/css/**`, `*.ts`, `*.vue`, `vite.config.ts`, `package.json` |
| [domain.md](domain.md) | `app/**`, `routes/**`, `database/**`, `tests/**`, `resources/js/**` |
| [workflow.md](workflow.md) | End of a phase, phase PRs, any change to a pattern a rule or skill documents |

## Domain skills

Step-by-step recipes for recurring work live in `.ai/skills/`. `php artisan boost:update` symlinks them into `.claude/skills/`. Load the matching skill before starting the task:

| Skill | Use when |
|---|---|
| `phase-review` | Finishing a phase or opening a phase PR |
| `maintain-agent-knowledge` | Reviewing and updating these rules and skills (run by `phase-review`) |

When present, project decisions that shape the code live in `.scopes/scope.md` and `.scopes/questions.md`, and terminology lives in `docs/internal/glossary.md`.
