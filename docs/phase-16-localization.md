# Phase 16: Arabic and RTL Localization

## Scope and locked defaults

Implemented for this phase:
- Session-level locale resolution with per-user persistence.
- Arabic RTL shell support across Hub, Login, POS, and printable documents.
- Arabic companion typeface support via IBM Plex Sans Arabic.
- Financial values forced to Western numerals and LTR-isolated rendering.
- Gregorian calendar retained (Arabic locale uses Arabic UI language while keeping Gregorian dates).

Locked defaults applied:
1. Financial figures use Western Arabic numerals (0123456789) and are wrapped with LTR isolation (`.ltr-content` / `.figure-mono`).
2. Gregorian calendar remains in use; no Hijri support added in this phase.
3. Single active locale per session/user (no bilingual side-by-side rendering).
4. Applies to Hub, POS, Login, and printable document templates.

## Localization infrastructure

### Persistence model
- `users.locale` (nullable): per-user language preference.
- `business_settings.default_locale` (default `en`): guest/login and default fallback locale.

### Business settings Arabic variants
Added Arabic counterparts for fields that are rendered in documents and legal identity surfaces:
- `business_settings.legal_name_ar`
- `business_settings.address_ar`
- `business_settings.invoice_footer_text_ar`

Reasoning:
- These fields are legal/branding strings that are customer-facing and frequently required in Arabic for Saudi deployments.
- Other settings like numeric formats, currency code, and tax rates are not language-dependent values and do not need language variants.

### Runtime locale resolution
- New middleware: `app/Http/Middleware/ResolveLocale.php`
- Resolution order:
  1. Authenticated user locale (`users.locale`) if set
  2. Session locale
  3. `business_settings.default_locale`
  4. fallback `en`

### Language switchers
- New route/controller:
  - `POST /locale` -> `LocaleController@update`
- Hub and login both provide language selection.
- Authenticated users persist locale to `users.locale`; guests persist locale in session.

### Translation files
- `lang/en.json`
- `lang/ar.json`

## RTL layout and typography

### HTML direction and language
`dir` is now applied at layout-level:
- Hub layout
- Login layout
- POS layout
- Printable document layout

### Typeface stack
- Google Fonts now loads:
  - IBM Plex Sans
  - IBM Plex Mono
  - IBM Plex Sans Arabic
- CSS applies Arabic UI font when `lang="ar"`.

### Logical CSS/property migration
Direction-sensitive utility classes were migrated across Blade surfaces where detected:
- `text-left` -> `text-start`
- `text-right` -> `text-end`
- `ml-*`/`mr-*` -> `ms-*`/`me-*`
- `pl-*`/`pr-*` -> `ps-*`/`pe-*`
- `border-l`/`border-r` -> `border-s`/`border-e`
- `left-*`/`right-*` -> `start-*`/`end-*`

## Printable documents (Phase 14 integration)

Updated shared printable layout:
- `resources/views/documents/print.blade.php`

Behavior:
- Uses `dir="rtl"` when Arabic locale is active.
- Uses Arabic legal name (`legal_name_ar`) and Arabic footer (`invoice_footer_text_ar`) when locale is Arabic, with fallback to default fields.
- Financial totals and line values are LTR-isolated and keep Western numerals.
- 80mm receipt path remains supported by existing print CSS branch.

## Automated tests vs manual verification

### Automated tests added
File: `tests/Feature/Phase16LocalizationRtlTest.php`

- `test_locale_preference_persists_per_user_across_sessions`
- `test_login_screen_respects_business_settings_default_locale_for_guests`
- `test_financial_figures_render_ltr_with_western_numerals_under_arabic_locale`
- `test_printable_document_layout_uses_arabic_business_fields_when_locale_is_arabic`

### Regression tests run
- `tests/Feature/Phase13NavigationDesignSystemTest.php`

### Manual visual verification still required
These checks require browser-level visual inspection and are not fully covered by HTTP feature tests:
- Sidebar and command palette directional icon semantics in RTL.
- Table reading flow and perceived column direction across all report screens.
- Modal/form optical alignment and spacing at multiple breakpoints.
- Dark mode parity with RTL for all pages.
- 80mm receipt visual fit and clipping behavior in browser print preview and physical thermal printer output.

## Screen-by-screen audit list

Status legend:
- `Localized+RTL`: translated shell/title wiring and logical-direction class audit applied.
- `Component baseline`: inherited localization/RTL behavior from parent layout/components.
- `Manual visual required`: still requires final visual verification in browser.

1. `resources/views/dashboard.blade.php` - Localized+RTL, Manual visual required
2. `resources/views/welcome.blade.php` - Localized+RTL, Manual visual required
3. `resources/views/accounts/create.blade.php` - Localized+RTL, Manual visual required
4. `resources/views/accounts/edit.blade.php` - Localized+RTL, Manual visual required
5. `resources/views/accounts/index.blade.php` - Localized+RTL, Manual visual required
6. `resources/views/auth/login.blade.php` - Localized+RTL, Manual visual required
7. `resources/views/cheques/create.blade.php` - Localized+RTL, Manual visual required
8. `resources/views/cheques/index.blade.php` - Localized+RTL, Manual visual required
9. `resources/views/components/button.blade.php` - Component baseline, Manual visual required
10. `resources/views/components/document-actions.blade.php` - Component baseline, Manual visual required
11. `resources/views/components/empty-state.blade.php` - Component baseline, Manual visual required
12. `resources/views/components/status-badge.blade.php` - Component baseline, Manual visual required
13. `resources/views/credit-notes/create.blade.php` - Localized+RTL, Manual visual required
14. `resources/views/credit-notes/index.blade.php` - Localized+RTL, Manual visual required
15. `resources/views/customer-receivables/create-payment.blade.php` - Localized+RTL, Manual visual required
16. `resources/views/customer-receivables/index.blade.php` - Localized+RTL, Manual visual required
17. `resources/views/customers/create.blade.php` - Localized+RTL, Manual visual required
18. `resources/views/customers/edit.blade.php` - Localized+RTL, Manual visual required
19. `resources/views/customers/index.blade.php` - Localized+RTL, Manual visual required
20. `resources/views/debit-notes/create.blade.php` - Localized+RTL, Manual visual required
21. `resources/views/debit-notes/index.blade.php` - Localized+RTL, Manual visual required
22. `resources/views/documents/print.blade.php` - Localized+RTL, Manual visual required
23. `resources/views/documents/shares.blade.php` - Localized+RTL, Manual visual required
24. `resources/views/emails/document-link.blade.php` - Component baseline, Manual visual required
25. `resources/views/journal-entries/create.blade.php` - Localized+RTL, Manual visual required
26. `resources/views/journal-entries/index.blade.php` - Localized+RTL, Manual visual required
27. `resources/views/layouts/hub.blade.php` - Localized+RTL, Manual visual required
28. `resources/views/livewire/accounts/form-page.blade.php` - Component baseline, Manual visual required
29. `resources/views/livewire/accounts/index-page.blade.php` - Localized+RTL, Manual visual required
30. `resources/views/livewire/customers/form-page.blade.php` - Component baseline, Manual visual required
31. `resources/views/livewire/customers/index-page.blade.php` - Localized+RTL, Manual visual required
32. `resources/views/livewire/journal-entries/form-page.blade.php` - Component baseline, Manual visual required
33. `resources/views/livewire/journal-entries/index-page.blade.php` - Localized+RTL, Manual visual required
34. `resources/views/livewire/price-categories/form-page.blade.php` - Component baseline, Manual visual required
35. `resources/views/livewire/price-categories/index-page.blade.php` - Localized+RTL, Manual visual required
36. `resources/views/livewire/products/form-page.blade.php` - Localized+RTL, Manual visual required
37. `resources/views/livewire/products/index-page.blade.php` - Localized+RTL, Manual visual required
38. `resources/views/livewire/purchase-orders/form-page.blade.php` - Component baseline, Manual visual required
39. `resources/views/livewire/purchase-orders/index-page.blade.php` - Localized+RTL, Manual visual required
40. `resources/views/livewire/settings/settings-page.blade.php` - Localized+RTL, Manual visual required
41. `resources/views/livewire/shops/form-page.blade.php` - Component baseline, Manual visual required
42. `resources/views/livewire/shops/index-page.blade.php` - Localized+RTL, Manual visual required
43. `resources/views/livewire/stock-transfers/form-page.blade.php` - Component baseline, Manual visual required
44. `resources/views/livewire/stock-transfers/index-page.blade.php` - Localized+RTL, Manual visual required
45. `resources/views/livewire/supplier-bills/index-page.blade.php` - Localized+RTL, Manual visual required
46. `resources/views/livewire/supplier-bills/payment-form-page.blade.php` - Component baseline, Manual visual required
47. `resources/views/livewire/suppliers/form-page.blade.php` - Component baseline, Manual visual required
48. `resources/views/livewire/suppliers/index-page.blade.php` - Localized+RTL, Manual visual required
49. `resources/views/livewire/units/form-page.blade.php` - Component baseline, Manual visual required
50. `resources/views/livewire/units/index-page.blade.php` - Localized+RTL, Manual visual required
51. `resources/views/livewire/users/form-page.blade.php` - Component baseline, Manual visual required
52. `resources/views/livewire/users/index-page.blade.php` - Localized+RTL, Manual visual required
53. `resources/views/livewire/warehouses/form-page.blade.php` - Component baseline, Manual visual required
54. `resources/views/livewire/warehouses/index-page.blade.php` - Localized+RTL, Manual visual required
55. `resources/views/pos/sales.blade.php` - Localized+RTL, Manual visual required
56. `resources/views/price-categories/create.blade.php` - Localized+RTL, Manual visual required
57. `resources/views/price-categories/edit.blade.php` - Localized+RTL, Manual visual required
58. `resources/views/price-categories/index.blade.php` - Localized+RTL, Manual visual required
59. `resources/views/products/create.blade.php` - Localized+RTL, Manual visual required
60. `resources/views/products/edit.blade.php` - Localized+RTL, Manual visual required
61. `resources/views/products/import.blade.php` - Localized+RTL, Manual visual required
62. `resources/views/products/index.blade.php` - Localized+RTL, Manual visual required
63. `resources/views/purchase-orders/create.blade.php` - Localized+RTL, Manual visual required
64. `resources/views/purchase-orders/edit.blade.php` - Localized+RTL, Manual visual required
65. `resources/views/purchase-orders/index.blade.php` - Localized+RTL, Manual visual required
66. `resources/views/reports/ap-aging.blade.php` - Localized+RTL, Manual visual required
67. `resources/views/reports/ar-aging.blade.php` - Localized+RTL, Manual visual required
68. `resources/views/reports/balance-sheet.blade.php` - Localized+RTL, Manual visual required
69. `resources/views/reports/cash-flow.blade.php` - Localized+RTL, Manual visual required
70. `resources/views/reports/fiscal-periods.blade.php` - Localized+RTL, Manual visual required
71. `resources/views/reports/income-statement.blade.php` - Localized+RTL, Manual visual required
72. `resources/views/reports/index.blade.php` - Localized+RTL, Manual visual required
73. `resources/views/reports/trial-balance.blade.php` - Localized+RTL, Manual visual required
74. `resources/views/settings/index.blade.php` - Localized+RTL, Manual visual required
75. `resources/views/shops/create.blade.php` - Localized+RTL, Manual visual required
76. `resources/views/shops/edit.blade.php` - Localized+RTL, Manual visual required
77. `resources/views/shops/index.blade.php` - Localized+RTL, Manual visual required
78. `resources/views/stock-transfers/create.blade.php` - Localized+RTL, Manual visual required
79. `resources/views/stock-transfers/index.blade.php` - Localized+RTL, Manual visual required
80. `resources/views/supplier-bills/create-payment.blade.php` - Localized+RTL, Manual visual required
81. `resources/views/supplier-bills/index.blade.php` - Localized+RTL, Manual visual required
82. `resources/views/suppliers/create.blade.php` - Localized+RTL, Manual visual required
83. `resources/views/suppliers/edit.blade.php` - Localized+RTL, Manual visual required
84. `resources/views/suppliers/index.blade.php` - Localized+RTL, Manual visual required
85. `resources/views/units/create.blade.php` - Localized+RTL, Manual visual required
86. `resources/views/units/edit.blade.php` - Localized+RTL, Manual visual required
87. `resources/views/units/index.blade.php` - Localized+RTL, Manual visual required
88. `resources/views/users/create.blade.php` - Localized+RTL, Manual visual required
89. `resources/views/users/edit.blade.php` - Localized+RTL, Manual visual required
90. `resources/views/users/index.blade.php` - Localized+RTL, Manual visual required
91. `resources/views/warehouses/create.blade.php` - Localized+RTL, Manual visual required
92. `resources/views/warehouses/edit.blade.php` - Localized+RTL, Manual visual required
93. `resources/views/warehouses/index.blade.php` - Localized+RTL, Manual visual required

## Notes
- `resources/views/emails/document-link.blade.php` and `resources/views/welcome.blade.php` are not part of daily hub workflows but were included in the audit list for completeness.
- Visual verification remains mandatory for RTL icon direction semantics and print-fit nuances.
