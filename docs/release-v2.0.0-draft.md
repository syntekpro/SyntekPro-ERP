# Release v2.0.0 (Draft)

**Status:** In progress

## Phase 1 — Design system documentation

- Rewrote `docs/DESIGN_SYSTEM.md` (v3), replacing the v1.0.0-classic-ui
  teal/amber Linear-style spec. Covers header, sidebar, Quick Menu, page background, dashboard,
  tables, forms, settings pages, modals, empty states, permission matrix, org settings, and the
  separate navy POS theme. No app code touched.

## Phase 2 — Shared components

- Added new design tokens to `resources/css/app.css` (`--color-page-bg`,
  `--color-action`, `--color-link`, `--color-danger`, `--color-positive`, pastel stat-card colors,
  chart series colors) alongside the existing v2 tokens.
- `.btn-primary` is now solid black (`--color-action`) instead of brass, matching the "Create
  New" / "Save" spec. Added `.btn-link` / `.btn-link-danger` classes and matching `link` /
  `link-danger` button variants (`resources/views/components/button.blade.php`) for text-link
  actions like Clear / View List / Export / Import / Delete.
- `.ui-badge-success` / `.ui-badge-danger` / `.status-pill-success` / `.status-pill-danger` now use
  the new green/red positive/danger tokens with a soft tinted background.
- `.ui-card` restyled to a flatter white-card look (page background is handled in Phase 3's shell
  work); `.ui-table thead th` confirmed gray/uppercase; `.ui-input` focus ring now blue
  (`--color-link`) and checkboxes/radios get `accent-color: var(--color-link)`.
- `resources/views/components/card.blade.php` default `surface` changed from `panel` to `surface`
  (white), matching "white content cards" in the spec — prop API unchanged.
- `badge.blade.php`, `status-badge.blade.php`, `table.blade.php`, `input.blade.php` unchanged
  (their look comes entirely from the CSS classes above); no prop/API changes anywhere.

