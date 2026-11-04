# Project mechanics — iWorks Aquarium Log (aqualog)

> Loading flow, assets, translations, options framework, dependencies, general conventions.
> Read only when the task involves these areas.

## Overview

An iWorks-style WordPress plugin: tracks aquariums, water chemistry, maintenance
tasks and notes. Main file `aqualog.php` (textdomain `aqualog`); the plugin
slug/package name is `aqualog` (single source of truth: `package.json` `"name"`).

Source files contain **literal placeholders** (`PLUGIN_VERSION`, `PLUGIN_NAME`,
`BUILDTIMESTAMP`, `PLUGIN_TILL_YEAR`, …) — they are replaced by the Bun
bundler at build time (see `build.md`), never in the source. Notably the
textdomain is written as `'PLUGIN_NAME'` in sources.

The version lives only in `package.json` (`"version"`); the main file header
and the version constant both use the `PLUGIN_VERSION` placeholder.

## Directory structure

```
/
├── aqualog.php                   # Plugin bootstrap (header, constants, options, main class)
├── etc/
│   └── options.php               # Options pages configuration (iworks_options format)
├── includes/
│   ├── template-tags.php         # Procedural template helper functions
│   └── iworks/
│       ├── class-iworks-aqualog-base.php       # Base class
│       ├── class-iworks-aqualog.php            # Main plugin class
│       └── aqualog/                            # Feature classes
│           ├── class-iworks-aqualog-posttypes.php    # Post type loader
│           ├── class-iworks-aqualog-taxonomies.php   # Taxonomy loader
│           ├── class-iworks-aqualog-wp-admin.php     # Admin layer (menu, pages, assets)
│           ├── class-iworks-aqualog-db.php           # DB layer
│           ├── class-iworks-aqualog-logger.php       # Logger
│           ├── class-iworks-aqualog-github.php       # GitHub self-updater
│           ├── posttypes/      # Abstract post type + aquarium, note
│           ├── taxonomies/     # Abstract taxonomy + maintenance-task-type
│           └── wp-admin/       # Admin feature classes (activity, aquarium,
│                               #   chemistry, dashboard, maintenance, statistics)
├── assets/
│   ├── sass/admin/               # SCSS sources (settings, commons, dashboard, chemistry, events)
│   ├── scripts/src/{admin,frontend}/  # JS sources
│   ├── blocks/                   # Gutenberg blocks (one dir per block, each with *.php)
│   ├── templates/                # Rendered templates (loaded via load_template())
│   └── styles/                   # Compiled CSS (gitignored)
├── languages/                    # aqualog.pot (+ .po/.mo built)
├── tests/php/                    # PHPUnit (phpunit.xml, bootstrap.php)
├── bundler/                      # Bun build system — see build.md
├── package.json                  # Version + release metadata (single source of truth)
└── readme.txt                    # WordPress.org readme
```

## Loading flow

1. WordPress loads `aqualog.php`:
   - defines `IWORKS_AQUALOG_VERSION` (`'PLUGIN_VERSION'`) and
     `IWORKS_AQUALOG_PREFIX` (`'iworks_aqualog_'`);
   - requires the main class from `includes/iworks/` (guarded by `class_exists`),
     `etc/options.php`, the vendored `iworks_options` class (guarded), and
     `includes/template-tags.php`;
   - defines `iworks_aqualog_get_options()` and instantiates
     `iworks_aqualog`, registering activation/deactivation hooks.
2. `iworks_aqualog::__construct()`:
   - registers `init` → `action_init_settings()` (frontend CSS enqueue,
     non-admin only) and `query_vars` (adds `aquarium_id`);
   - **enables the post types** `aquarium` and `note` via filters
     `iworks/aqualog/load/posttype/{name}` → `__return_true`;
   - loads feature classes from `includes/iworks/aqualog/` with the
     `is_file()` + `include_once` + `new` pattern, storing instances in
     `$this->objects[]`: `posttypes`, `taxonomies`, `github`, `db`, `logger`;
     `wp-admin` only when `is_admin()`;
   - includes every `assets/blocks/*/*.php` when `register_block_type()` exists;
   - registers the action `iworks/aqualog/register_objects` (lets other
     objects register themselves into `$this->objects`) and the filter
     `iworks/aqualog/is_active` (`__return_true`).
3. `iworks_aqualog_posttypes` / `iworks_aqualog_taxonomies` glob their
   directories, extract the name from the filename and load each class **only
   when the filter `iworks/aqualog/load/posttype/{name}` /
   `.../load/taxonomy/{name}` returns `true`** (name comes from the
   *filename*, see Known pitfalls).

## Constants

- `IWORKS_AQUALOG_VERSION` — plugin version (`'PLUGIN_VERSION'` placeholder).
- `IWORKS_AQUALOG_PREFIX` — prefix for options (`'iworks_aqualog_'`).
- `IWORKS_DEV_MODE` (optional, `wp-config.php`) — dev mode: non-minified asset
  handling in `get_version()` (md5 of file / `time()`), see below.
- Global: `$iworks_aqualog_options` (options object), `$iworks_aqualog`
  (main instance), `$iworks_aqualog_base` / `$iworks_aqualog_vendor`
  (paths, bootstrap only).

## Post types and taxonomies

- Post types are **disabled by default**; enabled via filters
  `iworks/aqualog/load/posttype/{name}` (`__return_true`). The main class
  enables `aquarium` and `note` out of the box.
- Registered post type slugs use the `iw_` prefix: `iw_aquarium`, `iw_note`
  (admin menu links use `edit.php?post_type=iw_...`).
- Post type classes extend the abstract `iworks_aqualog_posttype`
  (`posttypes/class-iworks-aqualog-posttype.php`) and must implement
  `action_init_register_post_type()` / `action_init_register_taxonomy()`,
  both hooked on `init` (priority 1) in the abstract constructor.
- Taxonomies live in `taxonomies/` and extend `iworks_aqualog_taxonomy`;
  loaded via `iworks/aqualog/load/taxonomy/{name}` filters
  (e.g. `maintenance-task-type` → maintenance task types).
- Templates live in `assets/templates/` and are loaded through the base class
  `load_template()` / `get_template_file()` helpers.

## Admin layer (wp-admin)

- `iworks_aqualog_wp_admin` (loaded only on `is_admin()`) registers:
  `admin_enqueue_scripts` (priority 0) → admin assets, `wp_loaded` → options
  init (`check_option_object()`), `admin_menu` → the Aqualog menu, `init` →
  current-aquarium handling, `plugin_row_meta` → meta links, and the
  `iworks/aqualog/etc/config/metaboxes` filter ("love" / "assistance" metaboxes,
  `loved_this_plugin()` / `need_assistance()`).
- Menu: main page slug `aqualog-dashboard` (SVG base64 icon, position from the
  `menu_position` option); submenus (Current Aquarium, Dosings, Chemistry,
  Maintenance, Calendar, Notes, Equipment, Plants, Aquariums, Help) are gated by
  **modules**: `is_module_enabled( $module )` reads the `module_{name}` option
  and `module_load_check => 'check'` entries additionally add the filter
  `iworks/aqualog/load/module/{module}` → `__return_true` when the module is on.
  (Calendar and Help use `module_load_check => 'skip'` — always visible.)
- Admin sub-classes live in `wp-admin/` (activity, aquarium, chemistry,
  dashboard, maintenance, statistics); each page renders via actions like
  `iworks/aqualog/wp-admin/page/dashboard` and can extend the JS
  `wp_localize_script` data via the `iworks/aqualog/wp-admin/wp_localize_script`
  filter.
- A "current aquarium" selector bar is rendered via the
  `iworks/aqualog/wp-admin/current-aquarium-bar` action; the selected aquarium
  ID is resolved by the base class `set_current_aquarium_id()` (nonce-checked
  GET/POST → user meta → `default_aquarium_id` option, final
  `iworks/aqualog/set/current_aquarium_id` filter) and persisted in the
  `aqualog_current_aquarium_id` user meta.

## Options layer (etc/options.php + iworks_options)

- `etc/options.php` returns the config array from `iworks_aqualog_options()`.
- Main page entry `$options['index']`: `page_title`, `menu` (`submenu`),
  `parent` (`aqualog-dashboard`), `use_tabs`, `version`.
- Field arrays: `options` (filter `iworks/aqualog/etc/config/options`),
  `metaboxes` (filter `iworks/aqualog/etc/config/metaboxes`).
- Field shape: `name`, `type` (e.g. `heading`, `text`, `textarea`), `th` (label),
  `description`, `classes`, `sanitize_callback`, `since`.
- The option object is created by `iworks_aqualog_get_options()`
  (option function name + `IWORKS_AQUALOG_PREFIX` prefix); classes access it via
  `$this->check_option_object()` (base class).

## Assets layer

- **CSS**: `assets/sass/admin/*.scss` → compiled to `assets/styles/admin/*.css`
  → concatenated to `assets/styles/aqualog-admin.css` (+ `.min.css`).
- **JS**: `assets/scripts/src/{admin,frontend}/*.js` → concatenated to
  `assets/scripts/aqualog-{admin,frontend}.js` → uglified to `.min.js`.
- **Enqueue**: `action_init_settings()` enqueues
  `assets/styles/aqualog-frontend{dev}.css` on non-admin requests; admin pages
  enqueue the `aqualog-admin` style/script handle via
  `admin_enqueue_assets()` (base class), hooked on each page's `load-{slug}`.
- **Debug/dev mode**: `WP_DEBUG` or `IWORKS_DEV_MODE` → `$this->dev = ''`
  (unminified files) and non-empty `$this->eol`; with `IWORKS_DEV_MODE`,
  `get_version( $file )` returns the file's md5 (or `time()`) for cache busting,
  otherwise the plugin version.
- **Blocks**: `assets/blocks/{block-name}/*.php` are `include_once`d by the main
  class when block editor functions exist (`register_block_type`).
- Compiled files are **gitignored** (`assets/styles/`,
  `assets/scripts/aqualog-*.js`, `languages/*.mo`, `release/`,
  `.sass-cache/`, `package-lock.json`) — never edit them, edit the sources and
  rebuild.

## Translations (i18n)

- Textdomain: `aqualog`; `languages/aqualog.pot`.
- In sources the textdomain is the **literal `'PLUGIN_NAME'`** (plus
  `IWORKS_RATE_TEXTDOMAIN` / `IWORKS_OPTIONS_TEXTDOMAIN` in vendored code) —
  replaced in the release copies only. (The old `grunt checktextdomain` task
  was dropped with Grunt.)
- `bun run make-pot` runs wp-cli `i18n make-pot` (creates the `.pot` and
  updates every existing `.po`).
- `.mo` files are compiled (`make-mo`) only in `bun run build:github`.

## GitHub self-updater

`class-iworks-aqualog-github.php` — standalone class, updates the plugin from
the GitHub repository (`iworks/aqualog`) via the GitHub releases API:
`plugins_api` → plugin info popup, update-transient checks, install fix-ups.
This class is **excluded from the WordPress.org ZIP** (wp.org releases must not
self-update from GitHub).

## PHP dependencies

- No Composer. Requires (from `package.json`, replaced into headers at build):
  WordPress ≥ 6.0, PHP ≥ 8.1, tested up to 7.0.
- Vendored frameworks live in `includes/iworks/`: `options/` (settings pages)
  and `rate/` (rate-it / "love" admin notices + anniversary templates). Both
  are separate git repos — treat them as vendor code: do not refactor their
  internals.

## Tests

- PHPUnit in `tests/php/` (`phpunit.xml`, `bootstrap.php`),
  run via `bun run test` (see `build.md`).

## General conventions

- Formatting: tabs for PHP/JS/SCSS, LF, final newline (`.editorconfig`);
  WordPress-style docblocks everywhere (package iWorks, subpackage Aqualog,
  `@since 1.0.0`).
- Every PHP file: `defined( 'ABSPATH' ) || exit;` plus a
  `if ( class_exists( ... ) ) { return; }` guard.
- File naming: kebab-case, `class-iworks-aqualog-{name}.php`;
  class names: `iworks_aqualog_{name}` (snake_case, lowercase).
- Hooks and options: `iworks/aqualog/...` or `iworks_aqualog_...`;
  post meta keys get the `_iw` prefix (base class `get_meta_name()`).
- Never read the vendored frameworks' internals from feature classes — use the
  base-class helpers (`check_option_object()`, `get_meta_name()`,
  `get_post_meta()`).
- Scratch files (test harnesses, debug scripts) live in `.opencode/tmp/` — never
  outside the project. Delete them when the task is done.

## Known pitfalls (do not fix without approval)

- Source placeholders (`PLUGIN_VERSION`, `PLUGIN_NAME`, `BUILDTIMESTAMP`, …)
  are intentional — do not replace them with real values in the repo.
- The base class computes `$plugin_file_path` as `iworks-aqualog.php`, but the
  actual main file is `aqualog.php` — `plugin_basename()` based logic
  referencing it may be off.
- The taxonomy loader regex is `class-iworks-aqualog-taxonomy-([a-z]+).php`,
  which cannot match the existing `maintenance-task-type` file (hyphens are
  outside `[a-z]+`) — taxonomy autoload for that file silently never fires.
- Post type / taxonomy registration argument filters use legacy
  `iworks/theme/register_post_type/...` names, not `iworks/aqualog/...`.
- The `aquarium` post type class also registers a taxonomy of its own inside
  `action_init_register_taxonomy()`.
