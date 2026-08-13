# Bank Reconciliation - Status & Plan

## What this adds

| Piece | File | Status |
|---|---|---|
| Schema | `bank_accounts`, `bank_statement_imports`, `bank_statement_lines` | Ready |
| Models | `BankAccount`, `BankStatementImport`, `BankStatementLine` | Ready |
| CSV importer | `app/Services/Banking/BankStatementCsvImporter.php` | Ready, tested against comma-formatted amounts and blank rows |
| Matcher | `app/Services/Banking/BankReconciliationMatcher.php` | Ready - exact-amount + date-window auto-suggest, ambiguous cases left for manual review on purpose |
| CLI test commands | `bank:import-statement`, `bank:reconcile-preview` | Usable today, no UI yet |

## Design decisions

- **Generic CSV with a configurable column map**, not one hardcoded bank
  format. Saudi banks (Al Rajhi, SNB, Riyad Bank, etc.) all export CSV but
  with different column orders and date formats - the `--date-col`,
  `--desc-col`, `--amount-col` (or `--debit-col`/`--credit-col`) options
  handle any of them without new code per bank.
- **Signed single amount internally** (positive = in, negative = out), even
  though many bank CSVs use separate debit/credit columns - simpler to
  match against GL net amount (`debit - credit`) this way.
- **Auto-matching is conservative on purpose**: exact amount match required,
  date is only a tiebreaker within a 10-day window, and if more than one GL
  line matches the same amount/date, nothing is auto-suggested - left for a
  human to pick. Money is the one place where "probably right" isn't good
  enough.

## How to test it today (CLI, no UI yet)

```bash
# 1. Create a bank account record linked to a GL asset account
php artisan tinker
>>> $glAccount = \App\Models\Account::where('account_type', 'asset')->first();
>>> \App\Models\BankAccount::create(['account_id' => $glAccount->id, 'bank_name' => 'Al Rajhi Bank', 'currency_code' => 'SAR']);

# 2. Import a statement CSV
php artisan bank:import-statement 1 /path/to/statement.csv \
  --date-col=0 --desc-col=1 --debit-col=2 --credit-col=3 --balance-col=4 \
  --date-format="d/m/Y"

# 3. Preview suggested matches (read-only)
php artisan bank:reconcile-preview 1
```

## Not yet built (next increment)

- Livewire UI: bank account CRUD, CSV upload with a visual column-mapping
  step, and a side-by-side reconciliation workspace (statement lines left,
  suggested/manual GL matches right) matching the existing index-page/
  form-page pattern used elsewhere in the app.
- "Create adjustment journal entry" flow for statement lines that have no
  GL counterpart at all (bank fees, interest earned) - right now those can
  only be matched, not turned into a new journal entry, from this module.
- Reconciliation summary/sign-off (mark a statement period as fully
  reconciled, lock it from further edits).
