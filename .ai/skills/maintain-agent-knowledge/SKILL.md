---
name: maintain-agent-knowledge
description: "Self-maintenance for this project's agent knowledge. Reviews .ai/rules and the custom skills in .ai/skills (including this one) against the code, fixes stale references, adds skills for newly recurring patterns, adds rules for new invariants, removes dead ones, and keeps .ai/rules/index.md in sync. Use at the end of every phase (step 7 of phase-review), after a significant refactor, or when asked to update, audit or tidy the rules and skills."
---

# Maintain agent knowledge

This keeps the codebase's rules and skills true as the project grows. Rules (`.ai/rules/*.md`) hold short invariants that must never break. Skills hold step-by-step recipes for recurring multi-file work.

**Custom skills** live in `.ai/skills/*/SKILL.md` (committed) and are listed under "Domain skills" in `.ai/rules/index.md`. Edit them there, never through the `.claude/skills` symlinks, and run `php artisan boost:update` after adding one. All the other skills in `.claude/skills` are Boost-managed and get overwritten by `php artisan boost:update`, so never edit them. Record project-specific overrides in `.ai/rules` instead.

## Steps

1. **Detect drift.** For every rule file and custom skill, extract each class, method, file path, artisan command and route name it mentions, and check that each one still exists (`grep -rn`, `ls`, `php artisan route:list --name=...`). Fix references that moved, and remove guidance whose subject is gone.
2. **Find new patterns.** Look at what the phase changed:
   ```bash
   git log --oneline master..HEAD    # default branch is master; or since the last phase merge
   git diff master...HEAD --stat
   ```
   Look for: new `app/` folders, new contracts in `app/Contracts`, new enums, registries or config-driven class lists, new middleware, new helpers in `tests/Pest.php`, new Actions/Resources that follow an existing slice, and new frontend conventions (stores, composables, layouts, shared components).
3. **Decide what each finding needs:**
   - **Update an existing skill or rule** if it changes a documented pattern.
   - **Add a skill** if a multi-file recipe has now appeared at least twice, or the scope says it will certainly recur.
   - **Add a rule** if it's an invariant that must never be broken (tenancy, privacy, audit, security, persisted keys). Prefer adding to an existing rule file. Create a new file only for a distinct area, and map it in `index.md`.
   - **Remove** a skill or rule whose pattern no longer exists.
   - **Do nothing** if the change is a one-off or is enforced by tooling (Pint, Rector, PHPStan).
4. **Look ahead.** If `.scopes/scope.md` and `.scopes/questions.md` exist, read the next phase in them. List the recipes it will need (e.g. a third-party provider adapter, a public token-link flow, a queued processing job). **Propose** these in the report rather than writing them speculatively. Write a skill once the first real implementation exists to point at.
5. **Write well.** Keep rules to one or two imperative lines each. Keep each skill to a short numbered checklist that names real reference files instead of pasting code. Use frontmatter with `name` matching the folder and a trigger-rich `description`. Give custom skill names that won't clash with Boost's.
6. **Keep the index in sync.** Update the rule table and the "Domain skills" table in `.ai/rules/index.md`.
7. **Review this skill.** Script reference checks in Python or with plain variable names. In zsh, a shell loop variable called `path` overwrites `$PATH`, which breaks the loop.
   Were any steps missing, unhelpful or noisy this time? Amend this file.
8. **Report** what was updated, added, removed and proposed, for the phase PR summary.
