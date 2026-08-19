# SyntekPro ERP — Design System (v3, ViknBooks direction)

This replaces the v2 teal/amber "Linear-style" spec. That direction is preserved historically via the
`v1.0.0-classic-ui` git tag — do not resurrect it. This document is the single source of truth for the
v2.0.0 visual redesign; it targets parity with ViknBooks' admin UI conventions while keeping SyntekPro's
existing architecture (Blade components, `@theme` tokens, Livewire, RTL/Arabic) untouched underneath.

## Non-negotiables carried over from v2

- Keep `resources/css/app.css` `@theme` token block and `--theme-*` / `--app-*` / `--brand-*` variable
  layers as the mechanism — only token *values* change, never variable names or the white-label override
  surface (`--brand-*`).
- Keep the `data-theme` attribute + `data-theme-toggle` mechanism (light / dark / auto).
- Keep Arabic/RTL font fallback (`:root[lang^='ar']`) as-is.
- Typography stays **IBM Plex Sans** / **IBM Plex Sans Arabic** (and `IBM Plex Mono` for figures).
- **RTL is required everywhere.** Use logical CSS properties (`inset-inline-start/end`, `margin-inline`,
  `padding-inline`, `text-align: start/end`, `ms-*`/`me-*`/`ps-*`/`pe-*` Tailwind utilities) — never
  hardcode `left`/`right` in new or restyled markup.
- Restyle existing Blade components in place (`resources/views/components/*.blade.php`); never fork a
  second card/button/table component.
- Never hardcode hex colors, brand name, or logo paths directly in Blade — always go through
  `--theme-*` / `--brand-*` variables and `BusinessSettingsService`.

## Color system

Two distinct palettes: **Admin/back-office** (white + black + gray, pastel accents for data) and
**POS terminal** (navy blue), kept visually separate on purpose (Phase 6).

```css
@theme {
    /* Admin surfaces */
    --color-page-bg: #F0F1F3;      /* light neutral gray page background */
    --color-surface: #FFFFFF;      /* white content/table/form cards */
    --color-ink: #14161A;          /* near-black text, bold headings */
    --color-line: #E7E9EE;         /* hairline borders */
    --color-muted: #6B7280;
    --color-subtle: #9CA3AF;

    /* Primary actions */
    --color-action: #14161A;       /* solid black — primary buttons (Create New, Save) */
    --color-link: #2563EB;         /* blue text-links — Export/Import, Clear, View List, column-customize */
    --color-danger: #DC2626;       /* red text-link/badge — Delete, negative balances, unpaid */
    --color-positive: #16A34A;     /* green — paid/positive balances, "New" badge, positive trend */

    /* Pastel stat-card backgrounds (dashboard) */
    --color-pastel-blue: #EAF2FF;
    --color-pastel-green: #EAFBEF;
    --color-pastel-pink: #FDEDF3;

    /* Chart series */
    --color-chart-sales: #16A34A;          /* Sales */
    --color-chart-sales-return: #EC4899;   /* Sales Return */
    --color-chart-purchase: #2563EB;       /* Purchase */
    --color-chart-purchase-return: #F97316;/* Purchase Return */
    --color-chart-expense: #DC2626;        /* Expense trend line */
    --color-chart-income: #16A34A;         /* Income vs Expense bars */

    /* POS terminal only (Phase 6) */
    --color-pos-navy: #0D3B66;
    --color-pos-navy-deep: #092A4A;
}
```

Dark mode keeps its existing `color-mix()` derivation approach from the previous token set — re-derive
from these new values rather than hand-authoring separate dark hex codes.

## [Header] ~64px, white background

- Left: logo + two-line bilingual company name (EN on one line, AR on the next) + workspace/company
  dropdown chevron + a small plan/subscription badge pill.
- Right, in order: grid-icon **Quick Menu** button, search icon, **EN/AR** language pill toggle, settings
  gear icon, theme (light/dark) toggle, a dark pill showing live clock + date, user avatar + name +
  dropdown chevron.
- All icon buttons are icon-only (no borders at rest); the language pill and clock/date pill are the
  only two elements with a filled background.
- File: `resources/views/components/shell/header.blade.php`.

## [Sidebar] icon rail

- Narrow, ~56–60px wide, full viewport height, dark charcoal background (near-black, not navy).
- Icon-only, no text labels, no hover-expand — each module gets a distinct color for its icon (not
  monochrome) so modules are visually distinguishable at a glance.
- File: `resources/views/components/shell/drawer.blade.php`.

## [Quick Menu]

- Floating rounded card, blue-tinted background, drops directly below the header's Quick Menu button.
- Sized to its content (not full-screen/full-width), positioned as a dropdown/popover.
- Pencil-edit icon in the top-right corner of the card (for customizing pinned shortcuts, if wired up
  later — Phase 3 just needs the visual slot).
- Body: a grid of icon tiles, each with a colored icon + label underneath/beside it; some tiles carry a
  small green "New" badge.
- Tiles are grouped into categories with a small header per group.
- **Source of truth for entries/routes/permissions: the existing `navSections` array in
  `resources/views/layouts/hub.blade.php`.** The Quick Menu must render from that same data — never
  hand-duplicate a second route list.

## [Page background]

Light neutral gray (`--color-page-bg`, ~`#F0F1F3`) behind all white content cards, across every
authenticated screen.

## [Dashboard]

- Stat cards: pastel background (soft blue / green / pink from the palette above), a circular icon
  badge, a small info (ⓘ) icon, a bold large amount, and a trend line of text below (e.g. "+12% vs last
  month").
- List rows for customers/suppliers/accounts: colorful circular avatar showing the entity's initial;
  balance colored green when positive, red when negative.
- Charts: Sales / Sales Return use green / pink; Purchase / Purchase Return use blue / orange; Expense
  trend is a red line; Income vs Expense is green/red bars. Reuse the existing Chart.js setup, only
  change the series colors and card chrome.

## [Tables] (list/index pages)

- White card container.
- Bold black title top-left.
- Search icon, a "Filter" pill button, blue text-link **Export** / **Import**, red text-link **Delete**.
- Solid **black** "+ Create New" button (top-right area).
- Blue pencil icon for column customization near the header row.
- Column headers: gray, uppercase, small.
- Status pills: green for paid/positive states, red for unpaid/negative states.
- Pagination control top-right (not bottom).

## [Forms] (create/edit pages)

- White background, bold page title.
- Top-right action row: **Clear** (blue text-link), **View List** (blue text-link), **Save** (solid
  black button).
- Inputs: minimal borders, labels positioned above the field (not floating/inline).
- Checkboxes: blue when checked.
- Section dividers: bold header text, no heavy box/border around the section.

## [Settings pages]

- Left vertical list of categories (white pill buttons; active item has a blue background).
- Horizontal sub-tabs under the page title for the selected category.
- Toggle-switch settings rendered as cards in a responsive grid.

## [Modals]

- White rounded card centered over a dark overlay.
- Bold title, an optional contextual link, and an X close button in the header row.
- Body rows can include an icon or status indicator per row.
- Destructive actions use red warning text.

## [Empty states]

- Centered simple line-art illustration.
- Bold heading, gray subtext beneath it.

## [Permission matrix pages]

- Same left settings category list as other settings screens.
- Role dropdown + a "Create User Type" link at the top.
- Sub-tabs: General / Dashboard / Settings.
- Matrix table: module names as the left column; toggle columns for View / Save / Edit / Delete /
  Print-Export, plus contextual extra columns where relevant (Unit Price / Discount / Limit / Purchase
  Price).
- Section header rows (grouping modules) are visually distinct from data rows (bold, shaded background).

## [Organization / company settings]

- Single scrolling page (not multiple tabs), with bold section headers in this order:
  1. **Organization Details**
  2. **Financial Details** (VAT / CR / Financial Year)
  3. **Address Details** (bilingual EN/AR toggle)
  4. **Contact Details** (dynamic add/remove rows for phones, emails, websites, social links)

## [POS terminal] — separate theme

`resources/views/pos/sales.blade.php` uses its own **navy blue** theme (`--color-pos-navy` range), not
the admin white/black theme described above. Full detail specified in Phase 6; not built in this phase.

## Icons

Keep Lucide (`x-lucide-*`) as the icon source. Icon tiles (colored background + icon) are used in stat
cards, Quick Menu tiles, and sidebar — color varies per module/context rather than a single accent.

## Rollout order (matches session phases)

1. This document (Phase 1).
2. Shared components — card, button, badge, status-badge, table, input (Phase 2).
3. Header, sidebar, Quick Menu (Phase 3).
4. Dashboard (Phase 4).
5. Floating quick-action FAB (Phase 5).
6. POS terminal navy theme (Phase 6).
7. Functional gap audit, then scoped functional phases (Phase 7+).

## Workflow rule for Copilot

Before touching any screen: check `resources/views/components/` for an existing component first. Extend
or restyle it in place. Never create a second card/button/table component. Never hardcode a hex color,
brand name, or logo path directly in a Blade file — always go through the existing `--theme-*` /
`--brand-*` variables and `BusinessSettingsService`. Visual-phase commits touch Blade markup and CSS
only — no PHP logic, Livewire methods/properties, routes, or database changes.
