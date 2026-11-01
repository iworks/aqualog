---
name: verify
description: post-work verification — full project build and a check that everything works
---

When a task that changed code (PHP, JS, SCSS) is finished, **before reporting success** run:

## 1. Sanity-check diffs

- `git diff --stat` — compare the changes against the list of files you actually edited. **Any changed file you did not edit is an alarm signal** (a stray build/formatter step overwriting sources).
- Compiled files (`assets/styles/`, `assets/scripts/aqualog-*.js`, `languages/*.mo`, `.sass-cache/`) are gitignored — they should never appear in a diff at all.

## 2. Verification with a full build

Do not declare success based on a single quick command — run the **full pipeline** for the changed asset type (each must pass end-to-end):

- **JS (`assets/scripts/src/**`)**: `grunt js` — must pass (jshint runs inside `grunt test`; a plain `grunt`/`grunt js` run validates concat + uglify). For a release-affecting change run the full `grunt build` (wp.org ZIP) or `grunt build:github` when the GitHub release path is affected.
- **SCSS (`assets/sass/**`)**: `grunt css` — must pass (sass → concat_css → cssmin). Remember: new SCSS entry files must be registered in the `css_files_compile` map in `Gruntfile.js`.
- **PHP (`includes/**`, `etc/`, main file)**: `php -l` on every changed file (new files included) — there is no phpcs setup in this repo, so do not claim WPCS compliance, just match the surrounding style. If a WordPress test env is available, `grunt test` (PHPUnit) is a bonus.
- **Gruntfile changes**: full `grunt build` + `grunt build:github` and check the staged ZIP contents (the exclude lists in `Gruntfile.js` are order-sensitive — see `build.md`).

## 3. Report

Summarize the build result and the list of verified files. If the build does not pass — fix it before reporting task completion (or clearly list the blockers for the user to decide). Never write "done" without a successful completion of step 2.
