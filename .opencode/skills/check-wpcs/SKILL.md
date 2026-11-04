---
name: check-wpcs
description: check PHP sources against the WordPress coding standards (PHP_CodeSniffer + WordPressCS)
---

Use this skill when the user asks to check PHP code against the WordPress
coding guidelines/standards (WPCS), or before a wp.org release of changed
PHP files.

## 1. Tooling

`phpcs` (and `phpcbf` for auto-fixes) are installed at
`~/.config/composer/vendor/bin/` with the **WordPress** standards
(`WordPress-Core`, `WordPress-Docs`, `WordPress-Extra`). Verify with
`phpcs -i` if in doubt. There is **no `phpcs.xml.dist` in this repo** — the
standard and options are passed on the command line.

## 2. Scope — check what changed

- Changed/new PHP only (matched to the file list actually edited in the task):
  ```
  phpcs --standard=WordPress --extensions=php --parallel=8 <files-or-dirs>
  ```
- Whole plugin (slow, noisy on vendored code):
  ```
  phpcs --standard=WordPress --extensions=php \
    --ignore=release,node_modules,.opencode,.sass-cache,languages,tests,o \
    --parallel=8 .
  ```
- The main file (`aqualog.php`) and `includes/iworks/aqualog/**` matter most;
  vendored libraries (`includes/iworks/options`, `includes/iworks/rate`) are
  third-party — report their findings but do not fix them.

## 3. Reading the report

- Sniffs are grouped as errors (must fix) vs warnings (judgement call).
- Common false-positive / ignore-worthy findings in this codebase:
  - `Squiz.PHP.DisallowMultipleAssignments`, line-length `Generic.Files.LineLength`
    warnings — match the surrounding style instead.
  - `WordPress.Files.FileName.InvalidClassFileName` — this repo's naming
    (`class-iworks-aqualog-*.php`) is intentional; do not rename without
    approval (AGENTS.md "evergreen rules").
  - Placeholders: `'PLUGIN_NAME'`, `PLUGIN_VERSION` etc. are **build-time
    placeholders**, not bugs.
- Never claim full WPCS compliance from `php -l` — only from a clean
  `phpcs` run.

## 4. Fixing

- Prefer targeted fixes in the files changed by the task; keep the diff
  minimal and consistent with the surrounding code (tabs, `iworks_aqualog_`
  naming — see AGENTS.md).
- Safe mechanical fixes can be applied with:
  ```
  phpcbf --standard=WordPress <file>
  ```
  **Review the `phpcbf` diff afterwards** (`git diff <file>`) — it may
  reformat unrelated lines; revert unwanted hunks.
- Re-run `phpcs` on the same scope after fixing; report remaining
  warnings/errors with a one-line verdict per file. Do not "fix" the known
  typos/pitfalls listed in the topic docs without user approval.
