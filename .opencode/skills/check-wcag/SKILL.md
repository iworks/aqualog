---
name: check-wcag
description: check admin UI templates, CSS and JS against WCAG 2.1 level A/AA accessibility rules
---

Use this skill when the user asks to check or improve accessibility (a11y,
WCAG 2.1 AA) of the plugin's admin UI — templates (`assets/templates/**`),
styles (`assets/sass/**`) and scripts (`assets/scripts/src/**`), plus any
new markup added by a task.

## 1. Scope

Aqualog ships **admin screens only** (WP admin pages, modals, list tables),
so the relevant success criteria are WCAG 2.1 **level A and AA**. Prioritize
the files touched by the current task; a full audit should walk
`assets/templates/pages/` + `elements/` (and their generated HTML via the
browser, see §4).

## 2. Static checks (source review)

Go through the markup and report violations against at least:

**Perceivable**
- Every `img`/`svg` used as an icon conveys its meaning: decorative →
  `aria-hidden="true"` + `focusable="false"`; meaningful → `alt` /
  `role="img"` + `aria-label`. Inline FA SVGs in the event list and the
  plugin SVG menu icon must be decorative (the adjacent text carries the
  meaning).
- Text contrast ≥ 4.5:1 (≥ 3:1 for ≥ 18.66 px bold / 24 px). Check the
  colors in `assets/sass/admin/*.scss` (event chips, status badges, muted
  out-of-range day cells) — compute contrast ratios, don't eyeball.
- Color is never the only means of conveying status: the task-status chips
  (`status-pending`/`in_progress`/`completed`) and calendar chips must keep
  their text labels.

**Operable**
- All interactive elements are real buttons/links (`<button>`, `<a href>`) —
  never click handlers on `<div>`/`<span>`.
- Icon-only buttons (calendar day "+", event chips, modal close) need an
  accessible name (`aria-label` / `title` + text).
- Modals (`.aqualog-modal`): focus moved into the modal on open, Escape
  closes it, focus returns to the trigger on close, overlay click-to-close
  must not be the only close method.
- Keyboard: everything reachable/operable with Tab/Enter/Space in DOM
  order; no positive `tabindex`; visible focus styles (don't remove
  `outline` without a replacement).
- Drag/motion only if alternatives exist (not used today — flag if added).

**Understandable**
- `lang` inherited from WP admin (no hardcoded language attributes).
- Form fields: every input/select/textarea has a programmatically associated
  `<label for>` (check the maintenance/reminder form templates) or
  `aria-label`.
- Errors are announced (`wp.a11y.speak()` / `aria-live="polite"`) —
  `iworks_aqualog.showNotice()` in `aqualog.js` is the single notice path;
  verify it uses `wp.a11y.speak`.
- Consistent identification: same action = same label everywhere.

**Robust**
- Valid, unique `id`s; `aria-*` used only with real ARIA roles/patterns;
  status changes in list views (`deleted=N` notices) announced via
  `wp.a11y.speak()`.
- `WP_List_Table` actions rely on core a11y — don't break its markup.

## 3. Known conventions here (do not "fix" without approval)

- Admin pages live inside `wp-admin`, so skip page-level criteria WP core
  already handles (`skip-link`, page `lang`, main landmark).
- `dashicons` are decorative when followed by text.
- Modals are hand-rolled (no `thickbox`); a11y of the modal lifecycle is our
  responsibility (see Operable above).

## 4. Runtime checks (when a WP test site is available)

Use the Playwright browser tools on `wordpress.test`:
1. Log in, open the affected admin page.
2. Tab through: verify focus visibility, focus trap/return for modals,
   Enter/Space activation.
3. Check computed contrast via `browser_evaluate` (`getComputedStyle` color
   vs background) for flagged elements.
4. Optional: inject axe-core from CDN and read the `wcag2a`/`wcag2aa`
   violations:
   ```js
   await tools.playwright.browser_evaluate({ function: `async () => {
     if (!window.axe) { await import('https://cdnjs.cloudflare.com/ajax/libs/axe-core/4.10.2/axe.min.js'); }
     const r = await window.axe.run(document, { runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa'] } });
     return r.violations.map(v => v.id + ' (' + v.impact + '): ' + v.nodes.length + ' node(s) — ' + v.help);
   }` });
   ```
   Treat axe output as a hint list, verify each finding by hand (axe has
   false positives).

## 5. Reporting

Report per file/element: criterion (e.g. "WCAG 2.1 AA 1.4.3 Contrast"),
severity (blocker / should-fix / nice-to-have), and a concrete fix. Fix only
what the task's scope covers unless the user asked for a full audit; keep
fixes consistent with existing styles/patterns and re-verify after fixing
(contrast math + tab-through).
