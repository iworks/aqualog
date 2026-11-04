# AGENTS.md — iWorks Aquarium Log (aqualog)

> Working rules only. Technical knowledge lives in topic docs — read on demand, never preload.

## What the project is

A WordPress plugin for aquarium tracking (water parameters, maintenance, notes):
main file (`aqualog.php`) + PHP classes (`/includes/iworks`) + options config
(`/etc/options.php`) + assets (`/assets`), built with Bun (bundler/) → `release/` ZIPs.
Scaffolded from the iWorks WordPress Plugin Stub; sources contain build-time
placeholders (`PLUGIN_VERSION`, `PLUGIN_NAME`, `CURRENT_YEAR`, …).

## Docs map — read only what the task needs

| Task | Read first |
| --- | --- |
| PHP classes, admin layer, post types, taxonomies, options, new class | `.opencode/docs/classes.md` |
| Bun bundler build, release ZIPs, placeholders, translations (.pot/.po) | `.opencode/docs/build.md` |
| Loading flow, assets, enqueue, i18n, dependencies, project conventions | `.opencode/docs/project.md` |
| After any task that changed code | skill `verify` (`.opencode/skills/verify/SKILL.md`) |

- Read ONLY the docs relevant to the current task — do not load everything upfront.
- When a change alters documented behavior, update the corresponding doc in the same task.

## Evergreen rules (every task)

- All documentation and README in English.
- `CHANGELOG` is edited only by the user — never touch it unless the user explicitly asks
  (desired format when asked: `## X.Y.Z - RRRR.MM`, categories `Added` / `Improved` / `Fixed` /
  `Removed` (tabs), English, concise).
- Never commit, push, amend or create PRs without an explicit request.
- Naming: kebab-case files (`class-iworks-aqualog-{name}.php`);
  PHP classes `iworks_aqualog_` prefix, snake_case, lowercase.
- Version lives only in `package.json` — placeholders (`PLUGIN_VERSION`,
  `BUILDTIMESTAMP`, and the literal `'PLUGIN_NAME'` textdomain) are replaced by
  the Bun bundler during build; never hardcode real values in sources.
- Do not fix known typos/pitfalls without approval (see topic docs).

## Commands

`bun run compile` (all assets, with maps) · `bun run dev` (watcher) ·
`bun run build:wporg` (wp.org ZIP) · `bun run build:github` (GitHub ZIP) ·
`bun run build` (both ZIPs) · `bun run make-pot` (i18n) ·
`bun run update-libs` · `bun run test` (phpunit)
