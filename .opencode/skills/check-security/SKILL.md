---
name: check-security
description: check PHP/JS for WordPress security issues (nonces, capability checks, sanitization, escaping, SQL) and plugin good practices
---

Use this skill when the user asks to check or harden security of the plugin,
or as a review step for handlers that accept user input (AJAX endpoints,
form/GET/POST processing, list-table actions, template rendering).

## 1. Tooling

Two layers — run both:

1. **phpcs with the security sniffs** (part of the installed WordPress
   standards, no extra install needed):
   ```
   phpcs --standard=WordPress --sniffs=WordPress.Security,WordPress.DB,Squiz.PHP.DisallowObfuscation,Generic.PHP.ForbiddenFunctions --extensions=php <files>
   ```
   Key sniffs: `WordPress.Security.NonceVerification` (unprocessed
   `$_POST`/`$_GET`/`$_REQUEST`), `WordPress.Security.ValidatedSanitizedInput`
   (missing input sanitization/capability check), `WordPress.Security
   .EscapeOutput` (unescaped output), `WordPress.Security.PluginActionLinks`,
   `WordPress.DB.PreparedSQL` / `PreparedSQLPlaceholders` (unprepared queries),
   `WordPress.DB.DirectDatabaseQuery` (direct `$wpdb` without caching).
   As always with this repo: findings on vendored code
   (`includes/iworks/options`, `includes/iworks/rate`) are **report-only**.
2. **Manual review** guided by §2 — sniffs catch patterns, not logic (e.g.
   a nonce that is checked but with the wrong action, or a capability check
   on the wrong role).

## 2. Checklist (what to verify by hand)

**Input handling — every request-facing entry point**
- AJAX handlers (`admin-ajax.php` in `class-iworks-aqualog-wp-admin*.php`):
  1. `check_ajax_referer( 'nonce-name', 'nonce-field' )` or
     `wp_verify_nonce` **before touching** `$_POST`/`$_GET`.
  2. Capability check (`current_user_can( 'manage_options' )` or the
     plugin's own capability) — a valid nonce only proves the user saw the
     form, not that they may act.
  3. All input read through `sanitize_text_field` / `absint` /
     `sanitize_key` / (floats) `is_numeric` + `(float)` — including values
     used in `wpdb` queries and `$_REQUEST['orderby']`/`order` in list
     tables (whitelist them, never interpolate).
  4. Nonce field names and actions match what the JS sends (check
     `assets/scripts/src/admin/*.js` against the localized `nonces` object).
- Admin page GET params (`page`, `view`, `date`, `id`, `deleted`, …) are
  cast/whitelisted before use — even inside wp-admin, `admin.php?page=…`
  params come from the user.
- Files: never trust `$_FILES` without `wp_check_filetype_and_ext` +
  capability check (media handling in `media-library.js` flow).

**Output escaping — templates (`assets/templates/**`) and PHP-rendered HTML**
- Escape on output, not on input: `esc_html`, `esc_attr`, `esc_url`,
  `esc_html__*`/`esc_attr__*` for translations; `wp_kses_post` for
  rich content.
- JSON handed to `wp.template`/JS via `data-*` attributes or
  `wp_json_encode` — never raw `json_encode`.
- All dynamic SQL goes through `$wpdb->prepare()` with proper placeholders
  (`%d`/`%f`/`%s`); table/column names can't be placeholders — whitelist
  them against a fixed array.
- `unserialize`, `extract`, `eval`, `create_function`, `base64_decode`,
  `$$var` — should not exist; flag immediately.

**Storage & data flow**
- Options/postmeta written via the settings API or `update_option` with
  sanitized values; nothing user-controlled passed to `unserialize`.
- `WP_List_Table` bulk/row actions process via `check_admin_referer`
  (bulk actions) or nonce + capability (row links).
- Email/reminder sends (`reminder` cron path) don't leak other users' data
  — aquarium ownership respected (`user_id` / aquarium permission checks).

**Client side (`assets/scripts/src/admin/**`)**
- All AJAX POSTs include the nonce from the localized object and pass
  capability-relevant data — JS security is server-enforced, so confirm the
  server never trusts client-sent `aquarium_id` without an ownership check.
- No secrets/keys hardcoded; API endpoints over `https`.

**WordPress good practices (review step)**
- Every hook callback registered for AJAX/REST/save checks auth as above.
- Direct file access guard at the top of every PHP file:
  `if ( ! defined( 'ABSPATH' ) ) { exit; }` (or the project's equivalent).
- Textdomains are the literal placeholders (`'PLUGIN_NAME'`) in sources —
  not a security issue, do not "fix".
- Version bump + `db_version` guards around schema changes (see
  `class-iworks-aqualog-db.php`) so stale installs can't run wrong SQL.
- Data deletion (e.g. uninstall path) limited to the plugin's own tables/
  options only.

## 3. Reporting

Per file/entry point: issue, severity (blocker / should-fix / hardening),
the concrete attack path (who can trigger it and how), and the fix.
Report vendored-code findings separately without fixing them. After fixing,
re-run the phpcs security sniffs on the changed files and re-verify the
handler end-to-end (browser or `wp.eval`-style request) — a security fix
that breaks the happy path is not fixed.
