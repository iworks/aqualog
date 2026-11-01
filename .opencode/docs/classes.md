# Classes — iWorks Aquarium Log (aqualog)

> Reference for work on PHP classes: base class, main class, feature classes,
> post types, taxonomies, admin classes, and the vendored iworks frameworks.
> Read only when the task involves these areas.

## Class hierarchy

```
iworks_aqualog_base                         (foundation, includes/iworks/)
├── iworks_aqualog                          (main class, instantiated in bootstrap)
├── iworks_aqualog_posttype                 (abstract, aqualog/posttypes/)
│   └── iworks_aqualog_posttype_{name}      (aquarium, note)
├── iworks_aqualog_taxonomy                 (abstract, aqualog/taxonomies/)
│   └── iworks_aqualog_taxonomy_{name}      (maintenance-task-type)
├── iworks_aqualog_wp_admin                 (admin only, is_admin())
│   └── iworks_aqualog_wp_admin_{name}      (wp-admin/ feature classes)
├── iworks_aqualog_db
└── iworks_aqualog_logger
iworks_aqualog_posttypes                    (loader, extends base)
iworks_aqualog_taxonomies                   (loader, extends base)
iworks_aqualog_github                       (standalone self-updater)
```

## Base class (class-iworks-aqualog-base.php)

`iworks_aqualog_base` — shared properties and helpers; all plugin classes
extend it (except the `github` updater).

Properties (constructor-filled):

- `$debug` — `WP_DEBUG` or `IWORKS_DEV_MODE`; `$dev` — `''` in debug, otherwise `'.min'`.
- `$eol` — `PHP_EOL` in debug, otherwise `''` (for building inline output).
- `$base` (`__DIR__` of includes/iworks), `$dir` (plugin directory name),
  `$url` (plugin URL), `$plugin_file`, `$plugin_file_path` (⚠ points to
  `iworks-aqualog.php`, see pitfalls in `project.md`).
- `$includes_directory` — `includes/iworks/aqualog/` (feature classes dir).
- `$version` — literal `'PLUGIN_VERSION.BUILDTIMESTAMP'` (replaced at build;
  the main class overrides it to `'PLUGIN_VERSION'`).
- `$meta_prefix` — `'_iw'`; `$capability` — `manage_options`; `$options`
  (lazy `iworks_options`); `$wp_admin_slug` — `aqualog-dashboard`.
- `$current_aquarium_id` (?int) + nonce action `set_current_aquarium_id`;
  `$posttypes_names` / `$taxonomies_names` (via
  `iworks/aqualog/{posttypes,taxonomies}_names/array` filters);
  `$option_name_media` — `_iworks_aqualog_media`.

Methods:

- `get_version( $file = null )` — plugin version; with `IWORKS_DEV_MODE` returns
  `md5_file()` of `$file` (or `time()`) for cache busting.
- `get_meta_name( $name )` / `get_post_meta( $post_id, $meta_key )` — prefixed
  meta access (`_iw_{name}`).
- `check_option_object()` — lazily creates `$this->options` via
  `iworks_aqualog_get_options()`.
- `print_table_body( $post_id, $fields )` — renders admin meta-field table rows
  (`number`, `date` types; extra attrs: `placeholder`, `style`, `class`, `id`).
- `get_module_file( $filename, $vendor = 'iworks' )` — resolves
  `includes/iworks/{vendor}/{dir}/{filename}.php`.
- `get_template_file( $file, $group )` / `load_template( $file, $group,
  $load_once, $args )` — resolve and load `assets/templates/{group}/{file}.php`
  via WP `load_template()`; default template args: `messages` (filter
  `iworks/aqualog/wp-admin/messages/files`), `counters`, `nonces`
  (`set_current_aquarium`); logs via Simple History when a template is missing.
- `html_title( $text )` — `<h1 class="wp-heading-inline">` for admin pages.
- `admin_enqueue_assets()` — enqueues the `aqualog-admin` style/script handles.
- `set_current_aquarium_id()` — resolves the current aquarium (nonce-checked
  `aquarium_id` GET/POST → `aqualog_current_aquarium_id` user meta →
  `default_aquarium_id` option; each candidate validated through the
  `iworks/aqualog/post_type/aquarium/check/id` filter) and persists it to user
  meta; final value passes the `iworks/aqualog/set/current_aquarium_id` filter.
- `is_module_enabled( $module )` — reads the `module_{name}` option.
- `get_nonce_value_and_sanitize_it( $nonce_name = '_wpnonce' )`.
- `get_time_elapsed_text( $date )` / `get_time_elapsed_text_seconds( $datetime )`
  — human-readable "N days ago" helpers.
- `get_stub_data()` — plugin metadata (published date, version, GitHub URL).
- `simple_history_logger_helper( $message, $data, $level )` — logs via the
  Simple History plugin when active (adds current user data; levels:
  `debug` / `warning` / `notice`).

## Main class (class-iworks-aqualog.php)

`iworks_aqualog extends iworks_aqualog_base` — instantiated once in
`aqualog.php`.

- `__construct()` — parent setup, sets `$version = 'PLUGIN_VERSION'`,
  registers `init` → `action_init_settings()` and `query_vars` →
  `add_query_vars()` (adds `aquarium_id`), enables post types
  `aquarium` + `note`, then loads feature classes with the **canonical loading
  pattern** into `$this->objects[]`:

  ```php
  $filename = $this->includes_directory . '/class-iworks-aqualog-{name}.php';
  if ( is_file( $filename ) ) {
      include_once $filename;
      $this->objects['{name}'] = new iworks_aqualog_{name}();
  }
  ```

  Loads: `posttypes`, `taxonomies`, `github`, `db`, `logger`; `wp-admin`
  only inside `is_admin()`. Then includes every `assets/blocks/*/*.php`
  when `register_block_type()` exists. Finally registers the action
  `iworks/aqualog/register_objects` and the filter
  `iworks/aqualog/is_active` → `__return_true`.
- `register_objects( $name, $group, $object )` — callback for the
  `iworks/aqualog/register_objects` action; stores foreign objects in
  `$this->objects[ $group ][ $name ]`.
- `action_init_settings()` — on `init`: non-admin only, enqueues
  `assets/styles/aqualog-frontend{dev}.css` (version via `get_version()`).
- `register_activation_hook()` — `$this->objects['db']->db_install()` +
  `check_option_object()` + `$this->options->activate()` +
  `do_action( 'iworks/aqualog/register_activation_hook' )`.
- `register_deactivation_hook()` — options `deactivate()` +
  `do_action( 'iworks/aqualog/register_deactivation_hook' )`.

## Feature classes (includes/iworks/aqualog/)

Every file: `defined( 'ABSPATH' ) || exit;` + `class_exists` guard + own
`require_once` of the base class (they are included directly, not autoloaded).

- **`class-iworks-aqualog-posttypes.php`** — post-type loader. Globs
  `posttypes/class*.php`, extracts the type from
  `class-iworks-aqualog-posttype-([a-z]+).php`, and instantiates the class only
  when the filter `iworks/aqualog/load/posttype/{name}` is `true`. Instances
  are kept in `$posttype_objects`.
- **`class-iworks-aqualog-taxonomies.php`** — taxonomy loader (same pattern):
  globs `taxonomies/class*.php`, pattern
  `class-iworks-aqualog-taxonomy-([a-z]+).php`, filter
  `iworks/aqualog/load/taxonomy/{name}`, instances in `$taxonomy_objects`.
- **`class-iworks-aqualog-wp-admin.php`** — admin layer (see the Admin layer
  section in `project.md` for the menu/module gating): `admin_enqueue_scripts`
  (priority 0), `wp_loaded` → options init, `plugin_row_meta`, `admin_menu`
  (main page `aqualog-dashboard` + module-gated submenus), metabox filters,
  "love" / "need assistance" callbacks (`loved_this_plugin()` /
  `need_assistance()`), page renderers that fire
  `do_action( 'iworks/aqualog/wp-admin/page/{page}' )`, and the
  current-aquarium bar (`iworks/aqualog/wp-admin/current-aquarium-bar` action).
  It also loads the admin sub-classes from `wp-admin/` matching
  `class-iworks-aqualog-wp-admin-([a-z]+).php`.
- **`wp-admin/` sub-classes** — activity, aquarium, chemistry, dashboard,
  maintenance, statistics; e.g. the dashboard class hooks
  `iworks/aqualog/wp-admin/page/dashboard` (render) and
  `iworks/aqualog/wp-admin/wp_localize_script` (extra JS data).
- **`class-iworks-aqualog-db.php`** — DB layer: option
  `aquarium_log_db_version`, `$table_names` array (names get the `$wpdb`
  prefix and are registered with `$wpdb` so they are reachable as
  `$wpdb->{name}`), schema versioning, `db_install()`, and
  `drop_tables()` on the `iworks/aqualog/register_uninstall_hook` action.
- **`class-iworks-aqualog-logger.php`** — activity logger: `log_action( $type,
  $aquarium_id, $message, $details, $user_id, $log_date )` plus typed helpers
  (`log_aquarium_created/updated/deleted`,
  `log_chemistry_measurement_added/updated`), queries via
  `get_log_entries( $aquarium_id, $type, $limit, $offset )` and
  `get_last_events()`; shows latest events on the dashboard
  (`action_show_aquarium_latest_events`). Complement: the base class
  `simple_history_logger_helper()` for the Simple History plugin.
- **`class-iworks-aqualog-github.php`** — GitHub self-updater
  (standalone): repo `iworks/aqualog`, GitHub releases API,
  `plugins_api` (plugin popup), update-transient check, install fix-ups.
  Excluded from the WordPress.org release ZIP.

## Post types (aqualog/posttypes/)

- **`class-iworks-aqualog-posttype.php`** — abstract
  `iworks_aqualog_posttype extends …_base`; constructor hooks
  `action_init_register_post_type()` / `action_init_register_taxonomy()` /
  `action_init_settings()` on `init` (priority 1). Both register methods are
  abstract. Provides metabox rendering/saving machinery
  (`add_meta_boxes()`, `render_meta_box_content()`, `save_meta()`,
  field renderers: image, checkbox, select, radio), `get_select_array()`,
  `get_taxonomy()`, `get_post_meta_name()`, and asset enqueuing on
  admin screens.
- Concrete classes: `class-iworks-aqualog-posttype-{name}.php` —
  `aquarium` (`iw_aquarium`, also registers its own taxonomy in
  `action_init_register_taxonomy()`) and `note` (`iw_note`). Class name:
  `iworks_aqualog_posttype_{name}`.
- **Enabling**: `add_filter( 'iworks/aqualog/load/posttype/{name}',
  '__return_true' )` — the main class enables `aquarium` and `note`
  by default; `{name}` is derived from the filename (see pitfalls in
  `project.md`).

## Taxonomies (aqualog/taxonomies/)

- **`class-iworks-aqualog-taxonomy.php`** — abstract
  `iworks_aqualog_taxonomy extends …_base`; hooks
  `action_init_register_taxonomy()` on `init` (priority 1).
- Concrete: `class-iworks-aqualog-taxonomy-maintenance-task-type.php`
  (maintenance task types; ⚠ its filename does not match the loader's
  `[a-z]+` regex — see pitfalls in `project.md`).

## Vendored frameworks (includes/iworks/)

- `options/` — `iworks_options`: settings pages, option storage and upgrade
  routines. Configured from `etc/options.php`; option names get the
  `IWORKS_AQUALOG_PREFIX` prefix. Separate git repo with its own
  README — treat as vendor, do not refactor.
- `rate/` — rate-it / "enjoying this plugin?" admin notices and dashboard
  widgets, plus anniversary templates (`templates/happy-anniversary/`),
  wired via `IWORKS_RATE_TEXTDOMAIN` (placeholder). Separate git repo — vendor.

## Adding a new class

- **Feature class**: create
  `includes/iworks/aqualog/class-iworks-aqualog-{name}.php`
  with the ABSPATH guard, `class_exists` guard, base-class `require_once`, and
  the class extending `iworks_aqualog_base`; instantiate it in the
  main constructor following the `is_file()` + `$this->objects[]` pattern
  (admin-only classes go inside the `is_admin()` branch).
- **Post type**: copy an existing `posttypes/class-iworks-aqualog-posttype-{name}.php`,
  rename class/file (kebab-case filename → `{name}` must be `[a-z]+` to match
  the loader regex), implement the abstract register methods, and enable it
  with `add_filter( 'iworks/aqualog/load/posttype/{name}', '__return_true' )`.
- **Taxonomy**: same in `taxonomies/` — beware the loader regex only accepts
  `[a-z]+` filenames.
- **Admin sub-class**: create
  `wp-admin/class-iworks-aqualog-wp-admin-{name}.php` (`[a-z]+` name) —
  it is picked up by the wp-admin loader.
- **Options field**: add the field array in `etc/options.php`
  (`name`, `type`, `th`, `sanitize_callback`, `since`) or extend via the
  `iworks/aqualog/etc/config/options` filter.
