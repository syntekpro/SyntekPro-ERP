# SyntekPro ERP — Design System (v2)

This file replaces `docs/DesignInstructions.md`, `docs/BrandingEngine.md`, and `docs/CodingStandards.md`.
Those three files described the same intent inconsistently and contradicted each other on icon style.
Delete them once this is merged. `docs/phase-13-design-system.md` stays as historical record of the
navy/brass "ledger" direction — we are moving away from it toward the direction below.

## Direction

The product should read as a 2026 SaaS product, not an admin template. References: Linear, Stripe
Dashboard, Notion, Arc. Not references: ERPNext, Odoo, Bootstrap Admin, AdminLTE, Metronic.

This is a retune, not a rebuild. Keep the existing architecture as-is:
- The `@theme` token block and `--theme-*` / `--app-*` / `--brand-*` variable layers in `resources/css/app.css`
- The `data-theme` attribute mechanism and `data-theme-toggle` control (light / dark / auto — user-selectable,
  defaults to **light** on first visit if no saved preference)
- The `--brand-*` variables as the white-label override surface — a tenant deployment changes only these
- Arabic/RTL font fallback (`:root[lang^='ar']`) — already correct, do not touch
- Existing Blade components in `resources/views/components/*.blade.php` — restyle in place, never duplicate
- `x-lucide-*` icon usage — keep Lucide as the icon source, we are changing how icons are *presented*, not
  replacing the icon set (see Icons section)

Only the token **values** and the **hierarchy** of a handful of components change.

## Token values (`resources/css/app.css`)

Replace the light-mode values in the `@theme` block and `:root` (default/light) section. Keep every
variable *name* the same so no component needs to change.

```css
@theme {
    --color-ink-navy: #12151A;        /* was #102033 — cooler, less "ledger" navy */
    --color-brass: #D97706;           /* was #b8872f — the one primary accent */
    --color-brass-contrast: #7C3D02;
    --color-ledger: #0F9E93;          /* was #24745a — modern teal, not olive-green */
    --color-rust: #DC2626;            /* danger stays a clear red */
    --color-paper: #F6F7F9;           /* was warm ivory #f7f1e4 — neutral cool gray */
    --color-surface: #FFFFFF;         /* was warm cream #fffaf0 — pure white cards */
    --color-panel: #F6F7F9;
    --color-line: #E7E9EE;            /* was warm tan #ded1bb — cool neutral hairline */
    --color-muted: #6B7280;
    --color-subtle: #9CA3AF;
}
```

Dark mode block (`:root[data-theme='dark']`) keeps its existing `color-mix()` derivation logic —
just confirm it still derives sensibly from the new light values above; don't hand-author separate
dark hex values unless contrast testing says otherwise.

Fonts stay as-is: `IBM Plex Sans` / `IBM Plex Mono`. It's a deliberate, distinctive choice already
wired for financial tabular figures (`.figure-mono`) — don't swap it for Inter or a generic default.

## Hierarchy fix (the actual bug in the current screens)

`resources/views/components/shell/header.blade.php` currently styles every control identically:
workspace switcher, quick actions, search, notifications, locale switch, theme toggle, and profile
all use `h-10 rounded-ui border border-line bg-panel px-3`. Nothing leads. Fix:

- **Search** is the one wide, visually prominent control (`bg-surface`, full border, placeholder text) —
  everything else in the header is a compact icon-only button (`w-10 h-10`, no visible border until hover)
- **Notifications, locale, theme toggle, profile** collapse to icon-only with tooltips; only show the
  text label at `lg:` breakpoint and above for profile email
- Only ONE element in the header may use the brass/accent fill: none, by default — brass is reserved for
  primary actions inside page content (buttons like "Save", "Add shop"), not chrome
- Same fix applies to `drawer.blade.php`: nav sections get a quiet uppercase-11px label used ONCE per
  section (already close to correct) — don't add borders around individual nav links, only background
  on hover/active

## Icons — soft-dimensional, not flat, not glossy

Neither flat single-line icons nor literal glossy/skeuomorphic 3D icons (previous docs asked for both,
which is why output was inconsistent). The 2026 reference point is Linear/Notion-style icon *tiles*:
a Lucide icon sitting inside a soft, faintly dimensional colored tile.

Create one new component, `resources/views/components/icon-tile.blade.php`, and wrap existing
`x-lucide-*` icons in it wherever an icon currently sits in a stat card, nav item, or quick action —
do not change the icon library itself.

```blade
@props(['color' => 'brass', 'size' => 'md'])
@php
$sizes = ['sm' => 'h-8 w-8', 'md' => 'h-11 w-11', 'lg' => 'h-14 w-14'];
@endphp
<div {{ $attributes->merge([
    'class' => $sizes[$size].' shrink-0 rounded-xl flex items-center justify-center'
]) }}
style="
    background: linear-gradient(155deg, color-mix(in srgb, var(--color-{{ $color }}) 16%, white), color-mix(in srgb, var(--color-{{ $color }}) 8%, white));
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), inset 0 -1px 2px color-mix(in srgb, var(--color-{{ $color }}) 25%, transparent), 0 1px 2px rgba(16,24,32,.06);
    color: color-mix(in srgb, var(--color-{{ $color }}) 70%, black);
">
    {{ $slot }}
</div>
```

Usage: `<x-icon-tile color="ledger"><x-lucide-store class="h-6 w-6" /></x-icon-tile>`. The gradient +
double inset shadow is what gives the soft dimensional read without looking like a rendered 3D asset.
Icon itself stays a normal Lucide outline icon at 24-28px — the tile carries the depth, not the glyph.

## Charts

Current dashboard chart panel renders an empty dashed placeholder — replace with real Chart.js
(already fine to add via npm/Vite, it's a 40kb dependency). Rules:

- Line/area charts: 2px stroke, filled area at 6-8% opacity of the line color, no visible data points
  except on hover, gridlines only on the Y axis at `var(--color-line)`
- Every chart gets exactly one primary series color (`--color-ledger` teal) and, if comparing two
  series, one secondary (`--color-brass`) — never more than two colors in one chart
- Stat cards get a small inline sparkline (last 7-30 points, no axes, no labels) next to the number,
  not a separate chart panel
- Donut/breakdown charts: thick ring (`cutout: 72%`), color from the same two-color system, numeric
  legend beside it rather than a floating chart legend
- Empty state (no data yet): show the axes and gridlines with a centered one-line message and a
  next-action link — never a bare dashed rectangle

## Layout rules (apply everywhere, not just dashboard)

- Sidebar: icon-only rail at rest, expands on hover to show labels (desktop); becomes a slide-over
  drawer with backdrop on mobile — this exists in `shell-drawer` already, just needs the hover-expand
  behavior and its own internal scroll region (`overflow-y: auto` on the nav list, not the whole aside)
  so it works at any viewport height
- Grids are fluid at all breakpoints: 1 column mobile → 2 tablet → 3+ desktop for card grids; never a
  fixed pixel width on the content area
- One accent color rule holds everywhere: brass for primary actions, teal (ledger) for positive/live
  data and secondary chart series, red (rust) for danger/overdue, gray for everything else

## Rollout order

1. Token value swap in `app.css` + header/drawer hierarchy fix (this doc, immediate)
2. `icon-tile` component + wire into dashboard stat cards and quick actions
3. Dashboard charts (replace placeholder with Chart.js per above)
4. Shops / Warehouses / Products / Stock Transfers list + form screens
5. POS terminal screens (separate pass — touch-first rules, not covered here)
6. Reports screens + final RTL pass on the new visual values (font fallback already correct, just
   verify layout mirrors properly with the new spacing)

## Workflow rule for Copilot

Before touching any screen: check `resources/views/components/` for an existing component first.
Extend or restyle it in place. Never create a second card/button/table component. Never hardcode a
hex color, brand name, or logo path directly in a Blade file — always go through the existing
`--theme-*` / `--brand-*` variables and `BusinessSettingsService`.
