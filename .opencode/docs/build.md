# Build — Bun bundler

> Toolchain, tasks, pipeline, placeholders, release ZIPs, translations, tests.
> Read only when working on the build.

## Toolchain

- [Bun](https://bun.sh) + the `bundler/` scripts (ported from
  `../wordpress-plugin-stub/bundler/`). Entry point `bundler/index.js`,
  config in `bundler/config.mjs`, pipelines in `bundler/pipelines.mjs`,
  tool wrappers (sass, terser, clean-css, prettier) in `bundler/tools.mjs`,
  ZIP staging in `makeZip.mjs`, i18n via wp-cli in `makePot.mjs`.
- Dependencies (in `package.json` `devDependencies`): `archiver`, `clean-css`,
  `prettier`, `sass`, `terser`. Install with `bun install` (its `postinstall`
  downloads `wp-cli.phar` for `make-pot`; a `wp` binary phar copy also works).
- PHP ≥ 8.1; wp-cli for i18n; PHPUnit for tests. There is **no Gruntfile**
  anymore (removed; grunt deps dropped from `package.json`).
- Prettier reads `.editorconfig` (tabs for JS) + `.prettierrc`
  (`singleQuote`) — `bundler/tools.mjs` resolves the config explicitly
  (`prettier.resolveConfig(..., { editorconfig: true })`) and passes it to
  `prettier.format()` (the bare `editorconfig: true` format option is not
  honored by prettier's API).

## Tasks (`bun run ...`)

| Command            | Description                                                                |
| ------------------ | -------------------------------------------------------------------------- |
| `bun run dev`      | watcher: prettier + recompile assets on change (no ZIP, no i18n)           |
| `bun run compile`  | one-shot asset compile (CSS + JS, **with** source maps, no prettier pass)  |
| `bun run build`    | both flavors: `build:wporg` + `build:github`                               |
| `bun run build:wporg`  | production build → `release/wporg/aqualog.zip`                         |
| `bun run build:github` | production build + `.mo` → `release/github/aqualog.zip`                |
| `bun run make-pot` | translations only (`.pot` + `.po` update via wp-cli i18n)                  |
| `bun run update-libs` | git pull on the vendored iWorks libraries (`includes/iworks/{options,rate}`) |
| `bun run test`     | PHPUnit (`tests/php/phpunit.xml`)                                          |

## Asset pipeline

1. **prettier** (build modes only) — formats `assets/scripts/src/**/*.js`
   and the bundler sources.
2. **CSS** — every non-partial `.scss` under `assets/sass/` is compiled
   (sass API, no fixed list — new SCSS files are picked up automatically),
   then `assets/styles/admin/*.css` → `assets/styles/aqualog-admin.css`
   (+ frontend bundle from `assets/styles/frontend/`, settings.css first),
   then both bundles are minified (clean-css) to `.min.css`. Build modes
   strip `sourceMappingURL` comments; `compile`/`dev` keep maps.
3. **JS** — `assets/scripts/src/{admin,frontend}/*.js` →
   `assets/scripts/aqualog-{admin,frontend}.js` (sorted, banner prepended)
   → terser minify (mangle reserves `jQuery`) to `.min.js`.
4. **i18n** — wp-cli `i18n make-pot` → `languages/aqualog.pot`,
   `update-po` for every `.po`; `build:github` also runs `make-mo`.

Compiled outputs (`assets/styles/`, `assets/scripts/aqualog-*.js`) are
gitignored — never edit them.

## Release ZIPs

- **wp.org** (`bun run build:wporg`) → `release/wporg/{version}/{name}/` →
  `release/wporg/aqualog.zip`. Copy rules live in `bundler/config.mjs`
  `copyRules(flavor)` — ordered, **first match wins** (grunt parity), the
  catch-all `{ glob: "**/*", include: true }` must stay last. Base excludes:
  repo meta (`.git*`, `.github/`, `.editorconfig`, `.prettierrc`,
  `AGENTS.md`, `Gruntfile.js`, `bundler/`, `bun.lock*`, `wp-cli.phar`,
  `package.json`, lock files, `README.md`, `LICENSE`, `contributing.md`,
  tests, `release/`, `.sass-cache/`, `.opencode/`, `.playwright-mcp/`,
  source maps, `o/`, legacy `**/img/src`, `**/js/src`, `**/css/{less,sass,src}`),
  asset sources (`assets/sass`, `assets/scripts/src`, `assets/scss`,
  `assets/styles/frontend`), the GitHub self-updater class
  (`includes/iworks/aqualog/class-iworks-aqualog-github.php`),
  `includes/pro/`, `languages/*.po|*.mo`. `readme.txt` **is** included
  (required by wp.org).
- **GitHub** (`bun run build:github`) → `release/github/{version}/{name}/`.
  Base excludes plus `readme.txt`; `.po` **are** included and `.mo` are
  compiled. Pairs with the GitHub self-updater class.
- Placeholder **replace** runs on every staged text file (binary extensions
  and `.mo` skipped, see `BINARY_EXTENSIONS`), then `archiver` zips the
  staging directory (level 9, `{name}/` wrapper folder).

## Placeholders (release copies only)

`AUTHOR_NAME/URI/URL`, `BUILDTIMESTAMP`, `BUILDTIME`, `IWORKS_RATE_TEXTDOMAIN`,
`IWORKS_OPTIONS_TEXTDOMAIN`, `PLUGIN_DESCRIPTION`, `PLUGIN_GITHUB_WEBSITE`,
`PLUGIN_NAME` (also used as the textdomain!), `PLUGIN_REQUIRES_PHP`,
`PLUGIN_REQUIRES_WORDPRESS`, `PLUGIN_TESTED_WORDPRESS`, `PLUGIN_TAGLINE`,
`PLUGIN_TAGS`, `PLUGIN_TILL_YEAR`, `PLUGIN_TITLE`, `PLUGIN_URI`/`PLUGIN_URL`
(from `pkg.homepage`), `PLUGIN_VERSION`, and the `^Version: .*$` header line —
all sourced from `package.json` + build time (`bundler/config.mjs`
`replacePatterns()`).

`CURRENT_YEAR` is **not** replaced at build (copyright years are baked in
manually, e.g. `2026-PLUGIN_TILL_YEAR`).

## Tests

- `bun run test` → `phpunit --configuration tests/php/phpunit.xml`
  (bootstrap: `tests/php/bootstrap.php`). Needs a WordPress test
  environment. jshint was dropped with Grunt; the bundler runs
  prettier instead and `node --check` on the bundle works as a
  smoke test.

## Runtime quirks / pitfalls (do not fix without approval)

- `bun run build:wporg` is the **wp.org** build; `bun run build` does both
  flavors (there is no plain `build` that only compiles assets — use
  `compile` for that).
- `compile`/`dev` keep source-map comments in the CSS bundles — grunt never
  wrote them; production builds strip them.
- The bundler concatenates full source files (prettier-formatted), so the
  built bundles keep file docblocks; grunt's `stripBanners` removed them.
- `build` runs the **prettier pass over the JS sources in place** — commit
  any formatting churn it produces.
- wp-cli.phar is expected in the project root (`postinstall` downloads it;
  `makePot.mjs` retries the download when missing).
