# Phase 13: Navigation Restructuring, Design System Baseline, and Dark Mode

## Scope

Phase 13 establishes the shared navigation and visual baseline for existing Phase 0-12 screens. It intentionally does not redesign individual page layouts or workflows. Deeper screen-by-screen composition work is deferred until Phases 13-16 settle the final feature surface.

## Navigation Structure

The former flat sidebar is grouped into collapsible sections:

- Operations: Shops, Warehouses, Products, Stock Transfers
- Purchasing: Suppliers, Purchase Orders, Supplier Bills, Debit Notes
- Sales: Customers, Customer Receivables, Credit Notes, POS
- Accounting: Accounts, Journal Entries, Fiscal Periods
- Reports: Reports Overview, Trial Balance, Balance Sheet, Income Statement, Cash Flow Statement, AP Aging, AR Aging
- Administration: Users, Units, Price Categories, Settings / Roles / Branding

Collapsed section state is persisted per user in `users.navigation_state.collapsed_sections`. The hub shell reads that state during render and posts updates to `/user-interface-preferences`.

A keyboard command palette is available from the sidebar and with `Ctrl+K` / `Cmd+K`. It is populated from the same visible navigation model as the sidebar, so it only offers screens the current user can reach.

## Dark Mode

Dark mode is a per-user preference stored in `users.theme_mode` with `light` or `dark`. If a user has no saved preference, the first authenticated shell load resolves the browser/OS preference and persists it.

The app uses a `data-theme` attribute on `<html>`. The hub, login, and POS surfaces all resolve that attribute before CSS renders to avoid a visible flash. The existing deployment branding stylesheet remains in use and is deliberately loaded after compiled Vite CSS:

1. `@vite(['resources/css/app.css', 'resources/js/app.js'])`
2. `@livewireStyles` where applicable
3. `<link rel="stylesheet" href="{{ route('theme.css') }}">`

That order preserves the Phase 11/12 fix: dynamic deployment theme values have final say over compiled Tailwind theme variables. Dark mode changes background, surface, panel, line, text, muted, and subtle tokens. Deployment brand colors remain recognizable through `--color-brass` and `--color-ledger` overrides emitted by `theme.css`.

## Tokens

### Typography

- UI font: `IBM Plex Sans`
- Figure font: `IBM Plex Mono`
- Financial figures: `.figure-mono` applies `font-variant-numeric: tabular-nums lining-nums`
- Summary totals: `.ledger-total` applies IBM Plex Mono, tabular numbers, and the restrained double underline

### Radius and Spacing

- `--radius-ui: 0.625rem`
- Buttons: `0.625rem 1rem`
- Sidebar rows: `0.625rem 0.75rem`
- Empty states: `1.5rem 2.5rem`

### Light Theme Colors

- Ink navy: `--color-ink-navy: #102033`
- Brass/gold accent: `--color-brass: #b8872f`
- Brass contrast: `--color-brass-contrast: #2b1b05`
- Ledger green: `--color-ledger: #24745a`
- Rust danger/debit: `--color-rust: #a8482f`
- Paper background: `--color-paper: #f7f1e4`
- Surface: `--color-surface: #fffaf0`
- Panel: `--color-panel: #fff7e8`
- Line: `--color-line: #ded1bb`
- Muted text: `--color-muted: #5f6b77`
- Subtle text: `--color-subtle: #83909b`

### Runtime Theme Aliases

- `--app-ink`
- `--app-paper`
- `--app-surface`
- `--app-panel`
- `--app-line`
- `--app-muted`
- `--app-subtle`

### Dark Theme Overrides

- Ink: `#e9edf2`
- Paper: `#07111f`
- Surface: `#0d1b2c`
- Panel: `#13243a`
- Line: `#28415e`
- Muted: `#b4c0cb`
- Subtle: `#7f91a3`

## Icon Package

Chosen package: `mallardduck/blade-lucide-icons`.

Reason: it exposes Lucide as Blade components using the familiar `<x-lucide-...>` syntax, fits the existing Blade/Livewire stack, and avoids hand-maintained inline SVGs. Icons are outline-style and monochrome by default because they inherit current text color. Brass is reserved for active/selected states and empty-state emphasis.

Composer note: the first attempted package name, `codeat3/blade-lucide-icons`, was unavailable under this project's Composer stability constraints. `mallardduck/blade-lucide-icons` was installed from source because this Windows PHP CLI lacks zip/unzip support.

## Components

- `resources/views/components/button.blade.php`: `primary`, `secondary`, and `danger/destructive` variants
- `resources/views/components/status-badge.blade.php`: neutral, success/paid/credit, and danger/overdue/debit tones
- `resources/views/components/empty-state.blade.php`: icon, message, and optional primary action

The shared CSS also applies sticky table headers, hover-revealed row actions, status pill normalization, mono figure styling, and total underline styling to existing markup so Phase 13 can establish a baseline without redesigning each page.

## Screen Audit

| Screen | Audit result |
| --- | --- |
| Login | Updated for IBM Plex, paper/surface tokens, dark/light OS resolution, dynamic `theme.css` loaded after Vite. |
| Dashboard | Covered by hub shell, grouped nav, command palette, dark mode, table/figure baseline. KPI totals use the shared total styling where numbers are detected. |
| Shops | Covered by grouped Operations nav, table baseline, status pill normalization, empty row message retained. |
| Warehouses | Covered by grouped Operations nav, table baseline, status pill normalization, empty row message retained. |
| Products | Covered by grouped Operations nav, table baseline, mono financial figures for price/cost values, status normalization. |
| Units | Covered by grouped Administration nav, table baseline, status normalization, empty row message retained. |
| Price Categories | Covered by grouped Administration nav, table baseline, status normalization, empty row message retained. |
| Stock Transfers | Moved into Operations, transfer status normalized, item quantities receive mono figure styling, empty state retained. |
| Suppliers | Moved into Purchasing, table baseline, status normalization, empty row message retained. |
| Purchase Orders | Moved into Purchasing, status normalization, sticky item tables, quantity figures receive mono styling. |
| Supplier Bills | Moved into Purchasing, total/outstanding values receive mono styling, status normalization. |
| Supplier Bill Payment | Covered by Purchasing context, amount input and outstanding figure receive the shared finance baseline. |
| Debit Notes | Moved into Purchasing, totals receive mono styling, empty row messages retained. |
| Debit Note Create | Covered by Purchasing context, selected bill outstanding and item quantities receive finance baseline. |
| Customers | Moved into Sales, credit limit receives mono styling, status normalization, empty row retained. |
| Customer Receivables | Moved into Sales, outstanding values receive mono styling, payment action remains hover-revealed in row actions. |
| Customer Payment | Covered by Sales context, outstanding and amount fields receive the finance baseline. |
| Credit Notes | Moved into Sales, total/refund/applied values receive mono styling. |
| Credit Note Create | Covered by Sales context, sale totals and item quantities receive finance baseline. |
| POS | Updated for `data-theme`, IBM Plex, semantic surfaces, theme toggle, mono cart figures, and double-underlined cart total. |
| Accounts | Moved into Accounting, table baseline, status normalization. |
| Journal Entries | Moved into Accounting, table baseline, line amount figures receive mono styling. |
| Journal Entry Create | Covered by Accounting context; manual amount entry remains workflow-specific but inherits token baseline. |
| Fiscal Periods | Moved into Accounting, table baseline, status messages and actions inherit tokens. |
| Reports Overview | Moved into Reports, all report tables receive sticky headers and mono financial values. |
| Trial Balance | Moved into Reports, debit/credit figures receive mono styling; balance badge normalizes to semantic status. |
| Balance Sheet | Moved into Reports, account balances receive mono styling; totals receive the double underline. |
| Income Statement | Moved into Reports, revenue/COGS/expense lines receive mono styling; gross/net totals receive double underline. |
| Cash Flow Statement | Moved into Reports, cash flow figures receive mono styling; net/operating totals receive double underline. |
| AP Aging | Moved into Reports, aging bucket values receive mono styling, overdue semantics use rust via status conventions. |
| AR Aging | Moved into Reports, aging bucket values receive mono styling, overdue semantics use rust via status conventions. |
| Users | Moved into Administration, table baseline, status normalization. |
| Settings / Roles / Branding | Moved into Administration, permission tables receive sticky headers, branding continues to use the existing business settings theme system. |

No primary screen was intentionally skipped. Deep per-screen layout redesign, card restructuring, and workflow-specific visual composition were intentionally deferred because they are out of scope for Phase 13.

## Verification Requirements

Focused tests added:

- `test_user_interface_preferences_persist_per_user`
- `test_hub_layout_renders_grouped_navigation_command_palette_and_theme_state`
- `test_login_and_pos_load_dynamic_theme_after_compiled_assets`

Manual/browser checks required before Phase 13 completion:

- Toggle dark/light mode and confirm it persists across a new session for the same user.
- Open command palette with `Ctrl+K` / `Cmd+K` and navigate to at least five sample screens.
- Collapse and expand nav sections, reload, and confirm the same user's state persists.
- Confirm hub, login, and POS all load `theme.css` after compiled app CSS.
