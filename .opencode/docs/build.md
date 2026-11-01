# Build — Grunt

> Toolchain, tasks, pipeline, placeholders, release ZIPs, translations, tests.
> Read only when working on the build.

## Toolchain

- npm + Grunt 1.x (`Gruntfile.js`); tasks loaded via `load-grunt-tasks` from
  `package.json` `devDependencies` (grunt-contrib-concat/uglify/sass/cssmin/
  compress/copy/clean/watch, grunt-replace, grunt-wp-i18n, grunt-checktextdomain,
  grunt-potomo, grunt-phpunit). If grunt misbehaves: delete `node_modules`,
  run `npm install`.
- Ruby sass (grunt-contrib-sass, `style: expanded`, no source maps,
  unix newlines). PHP ≥ 8.1; PHPUnit for tests.
- **No bun/bundler here** — unlike newer iWorks plugins, this repo is
  Grunt-based. There is no `bundler/` directory.

## Tasks (`grunt ...`)

| Command              | Description                                                                 |
| -------------------- | --------------------------------------------------------------------------- |
| `grunt` (default)    | `clean:temp` → concat → uglify → sass → concat_css → cssmin (all assets)    |
| `grunt js`           | concat + uglify only                                                        |
| `grunt css`          | sass + concat_css + cssmin only                                             |
| `grunt watch`        | watcher: sass scripts targets (500 ms debounce)                              |
| `grunt i18n`         | checktextdomain + makepot (`.pot` + `.po` update)                            |
| `grunt build`        | **wp.org flavor**: default → i18n → copy:wporg → replace → compress:wporg    |
| `grunt build:github` | **GitHub flavor**: default → i18n → potomo → copy:github → replace → compress |
| `grunt release`      | `build:wporg`-equivalent + `build:github` + release notes                    |
| `grunt test`         | phpunit + jshint + release notes                                             |

## Asset pipeline (`grunt` default)

1. **clean:temp** — remove `**/*.tmp`, `.afpDeleted*`, `.DS_Store`.
2. **concat** — JS sources → `assets/scripts/aqualog-{frontend,admin}.js`
   (sources: `assets/scripts/src/{frontend,admin}/*.js`), grunt banner
   `/*! {title} - {version}\n * {homepage}\n * Copyright (c) {year}\n * Licensed {license} */`.
3. **uglify** — every non-`.min.js` in `assets/scripts/` → `.min.js`
   (mangle except `jQuery`, banner with `Copyright (c) {year};` — note the `;`).
4. **sass** — fixed list in `css_files_compile`: `assets/sass/admin/{settings,commons,dashboard,chemistry,events}.scss`
   → `assets/styles/admin/*.css` (adding a SCSS file requires editing the Gruntfile).
5. **concat_css** — `assets/styles/admin/*.css` → `assets/styles/aqualog-admin.css`
   (+ a frontend bundle target from `assets/styles/frontend/`).
6. **cssmin** — every non-`.min.css` → `.min.css`.

Compiled outputs (`assets/styles/`, `assets/scripts/aqualog-*.js`) are
gitignored — never edit them.

## Release ZIPs

- **wp.org** (`grunt build`) → `release/wporg/{version}/{name}/` →
  `release/wporg/aqualog.zip`. Excludes (ordered, first match wins): repo meta
  (`.git*`, `.editorconfig`, `Gruntfile.js`, `package.json`, lock files,
  `README.md`, `LICENSE`, `contributing.md`, tests, `release/`,
  `.sass-cache/`, source maps, legacy `**/img/src`, `**/js/src`,
  `**/css/{less,sass,src}`), asset sources (`assets/sass`,
  `assets/scripts/src`, `assets/scss`, `assets/styles/frontend`), the GitHub
  self-updater class (`includes/iworks/aqualog/class-iworks-aqualog-github.php`),
  `includes/pro/`, `languages/*.po|*.mo`. `readme.txt` **is** included
  (required by wp.org).
- **GitHub** (`grunt build:github`) → `release/github/{version}/{name}/`.
  Base excludes plus `readme.txt` and `includes/pro/`; `.po` **are** included
  and `.mo` are compiled from them (`potomo`). Pairs with the GitHub
  self-updater class.
- The exclude lists are plain arrays in `Gruntfile.js`
  (`excludeCopyFiles`, `excludeCopyFilesGIT`, `excludeCopyFilesWPorg`) built
  with `**` include first + `!` negations — the catch-all `**` must stay first.
- After copy, **replace** applies the placeholder patterns to every staged text
  file (images and `.mo` skipped), then **compress** zips the staged directory.

## Placeholders (release copies only)

`AUTHOR_NAME/URI/URL`, `BUILDTIMESTAMP`, `BUILDTIME`, `IWORKS_RATE_TEXTDOMAIN`,
`IWORKS_OPTIONS_TEXTDOMAIN`, `PLUGIN_DESCRIPTION`, `PLUGIN_GITHUB_WEBSITE`,
`PLUGIN_NAME` (also used as the textdomain!), `PLUGIN_REQUIRES_PHP`,
`PLUGIN_REQUIRES_WORDPRESS`, `PLUGIN_TESTED_WORDPRESS`, `PLUGIN_TAGLINE`,
`PLUGIN_TAGS`, `PLUGIN_TILL_YEAR`, `PLUGIN_TITLE`, `PLUGIN_URI`,
`PLUGIN_VERSION`, and the `^Version: .*$` header line — all sourced from
`package.json` + build time (`Gruntfile.js` `replace_patterns`).

`CURRENT_YEAR` is **not** replaced at build (copyright years are baked in
manually, e.g. `2026-PLUGIN_TILL_YEAR`).

## Translations

- `grunt i18n`: `checktextdomain` (domains: `aqualog`, `PLUGIN_NAME`,
  `IWORKS_RATE_TEXTDOMAIN`, `IWORKS_OPTIONS_TEXTDOMAIN` over
  `aqualog.php` + `includes/**/*.php`) then `makepot` (grunt-wp-i18n) →
  `languages/aqualog.pot` + update-po.
- `grunt build:github` additionally runs `potomo` (`.po` → `.mo`).

## Tests

- `grunt test` → `phpunit --configuration tests/php/phpunit.xml`
  (bootstrap: `tests/php/bootstrap.php`) + jshint on the Gruntfile and
  `assets/scripts/src/**`. Needs a WordPress test environment.

## Runtime quirks / pitfalls (do not fix without approval)

- `grunt build` is the **wp.org** build; the GitHub flavor is `build:github`
  (there is no `build:wporg` alias).
- New SCSS entries must be added to the `css_files_compile` map in the
  Gruntfile — sass does not glob.
- The `concat_css` frontend target reads `assets/styles/frontend/` which only
  exists after a sass step produces it; the map is grunt-concat-css config, not
  sass output.
- jshint + checktextdomain still run here (unlike the bun-era stub which
  dropped them).
- Sass compile uses Ruby sass via grunt-contrib-sass — requires a working
  `sass` binary in the environment.
