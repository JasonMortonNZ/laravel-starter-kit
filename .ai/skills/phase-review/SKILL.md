---
name: phase-review
description: "End-of-phase quality gate for this app. Runs Rector, Pint, PHPStan, JS lint and type checks, the full test suite with 100% code and type coverage, then an accessibility review, a security review and agent-knowledge upkeep, and produces a summary for the PR. Use when a phase (or a phase branch) is finished, before opening a phase PR, or when asked to check, review or sign off a phase."
---

# Phase review

Run every gate in order. Fix failures and rerun until each one passes. Don't skip a gate or mark it as passed without running it. Scope the manual reviews to the phase's changes:

```bash
git diff master...HEAD --name-only   # the default branch here is master
```

## 1. PHP refactoring and style

```bash
vendor/bin/rector
vendor/bin/pint --format agent
```

## 2. Static analysis

```bash
vendor/bin/phpstan analyse --memory-limit=1G
```

## 3. Frontend lint and types

```bash
php artisan wayfinder:generate --no-interaction
corepack pnpm exec vp check
corepack pnpm exec vp run types:check
```

## 4. Full test suite

```bash
composer test
```

This runs the lint dry-run, PHPStan, 100% type coverage and 100% code coverage. If coverage tooling isn't available locally, use `composer test:local` (Herd).

## 5. Accessibility (WCAG 2.2 AA, manual)

Review every changed `.vue` file and the components it uses. Check that:

- Every input has a visible `<label>` (or `aria-label`), and errors are linked with `aria-describedby` and announced.
- Everything works by keyboard alone, with a logical tab order, a visible focus style and no keyboard traps.
- Focus moves sensibly on route change, after form submits, and in and out of dialogs (focus is trapped while a dialog is open and restored when it closes).
- Toasts, loading states and async results are announced through an `aria-live` region.
- Text and controls meet contrast ratios (4.5:1 for text, 3:1 for UI and focus), and meaning is never carried by colour alone.
- Headings follow a logical order, landmarks are used, tables have header cells, and icon-only buttons have accessible names.
- Targets are at least 24×24px, and content reflows at 320px width and 200% zoom.
- Public-facing pages (e.g. consent forms opened by guardians) use plain language.

Fix any issues, then list what was checked.

## 6. Security

Run the built-in `/security-review` against the branch. It needs a git remote (`origin/HEAD`). Until the repo has one, have an independent subagent review `git diff master...HEAD` instead, and brief it with the invariants in `.ai/rules/domain.md` but not your own conclusions. Then check the domain-specific points:

- [ ] Every new org route is inside the `orgs/{organisation}` group and has a cross-organisation 404 test.
- [ ] Every mutating endpoint calls `Gate::authorize` with the right permission, and has 403 tests.
- [ ] Public or token routes are throttled. Tokens are random, single-purpose, expiring and revocable, and are compared in constant time.
- [ ] No people or guardian personal data appears in audit properties, logs, exceptions or URLs.
- [ ] FormRequests validate every input. No mass assignment of `org_id` or role fields from request data.
- [ ] Destructive actions require `password.confirm`.
- [ ] File uploads validate type and size, and are stored on private disks and served through authorized routes.
- [ ] No secrets in code or committed config. New env keys are added to `.env.example`.

## 7. Knowledge upkeep

Run the `maintain-agent-knowledge` skill.

## 8. Summary

Output a checklist for the PR description: each gate with pass/fail, what was fixed, and any follow-ups deferred with a reason.
