# Workflow Rules

## End of every phase

- Before a phase is called done, and before opening any phase PR, run the `phase-review` skill. Every gate must pass: Rector, Pint, PHPStan, JS lint and types, tests with full coverage, accessibility, security and knowledge upkeep.
- Put the review summary (each gate with pass/fail, plus any follow-ups) in the PR description.

## Keeping agent knowledge current

- When a change alters a pattern documented by a rule in `.ai/rules` or a domain skill in `.ai/skills`, update that rule or skill in the same PR.
- Create and edit custom skills in `.ai/skills/<name>/SKILL.md`, never in `.claude/skills`, which is gitignored and generated. Run `php artisan boost:update` to sync a new skill.
- `phase-review` runs `maintain-agent-knowledge` to catch drift, add skills for newly recurring patterns, and remove anything stale.
- Never edit Boost-managed skills, because `php artisan boost:update` overwrites them. Put project-specific overrides in `.ai/rules` instead.
