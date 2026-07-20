# Phase 15: Cheque Management (Post-Dated Cheques)

## Scope

Phase 15 adds post-dated cheque support as an additional settlement mechanism for receivables and payables. Existing cash/bank payment flows remain active and unchanged.

Implemented capabilities:

- Record incoming cheque against open customer receivables (credit sales).
- Record outgoing cheque against open supplier bills.
- Cheque register with filters:
  - Direction (incoming/outgoing)
  - Status (pending/cleared/bounced/cancelled)
  - Cheque date range
  - Sort by cheque date (earliest/latest)
- Mark pending cheque as cleared.
- Mark pending cheque as bounced.
- Full journal posting via `JournalEntryService` for all cheque transitions.
- Closed fiscal period enforcement on clear/bounce posting dates via `JournalEntryService`.

## Accounting Model

A post-dated cheque is a holding instrument, not immediate cash.

Configured holding accounts:

- `accounting.cheques.pdc_receivable_account_code` (default `1160`) -> Post-Dated Cheques Receivable (Asset)
- `accounting.cheques.pdc_payable_account_code` (default `2150`) -> Post-Dated Cheques Payable (Liability)

### Incoming Cheque Recording (AR)

- Debit PDC Receivable
- Credit Accounts Receivable

Result: sale outstanding balance is reduced immediately.

### Outgoing Cheque Recording (AP)

- Debit Accounts Payable
- Credit PDC Payable

Result: supplier bill outstanding balance is reduced immediately.

### Cheque Cleared

Incoming cheque clear:

- Debit Cash/Bank
- Credit PDC Receivable

Outgoing cheque clear:

- Debit PDC Payable
- Credit Cash/Bank

### Cheque Bounced

Incoming cheque bounce:

- Debit Accounts Receivable
- Credit PDC Receivable

Result: sale outstanding balance is restored.

Outgoing cheque bounce:

- Debit PDC Payable
- Credit Accounts Payable

Result: supplier bill outstanding balance is restored.

## Worked Example 1: Incoming Cheque Recorded then Cleared

Assume invoice open balance SAR 1,200.00.

### Step A: Record cheque (pending) for SAR 500.00

- Dr 1160 Post-Dated Cheques Receivable: SAR 500.00
- Cr 1100 Accounts Receivable: SAR 500.00

Invoice outstanding moves from SAR 1,200.00 to SAR 700.00.

### Step B: Mark cheque cleared

- Dr 1020 Bank Account: SAR 500.00
- Cr 1160 Post-Dated Cheques Receivable: SAR 500.00

Invoice outstanding remains SAR 700.00 (already settled at recording time).

## Worked Example 2: Outgoing Cheque Recorded then Bounced

Assume supplier bill outstanding SAR 900.00.

### Step A: Record outgoing cheque (pending) for SAR 300.00

- Dr 2100 Accounts Payable: SAR 300.00
- Cr 2150 Post-Dated Cheques Payable: SAR 300.00

Supplier bill outstanding moves from SAR 900.00 to SAR 600.00.

### Step B: Mark cheque bounced

- Dr 2150 Post-Dated Cheques Payable: SAR 300.00
- Cr 2100 Accounts Payable: SAR 300.00

Supplier bill outstanding is restored to SAR 900.00.

## State Transition Rules

Valid transitions:

- `pending -> cleared`
- `pending -> bounced`

Invalid transitions:

- `cleared -> pending`
- `bounced -> pending`
- `pending -> cleared` more than once
- `pending -> bounced` more than once
- `cleared -> bounced`
- `bounced -> cleared`

Implementation enforces state preconditions explicitly in service methods and policy checks.

## AP Aging / AR Aging Impact

No report query change was required.

Reason:

- AP Aging and AR Aging already aggregate from `supplier_bills.outstanding_balance` and `sales.outstanding_balance`.
- Cheque recording updates those outstanding balances immediately (same settlement behavior as existing payments).
- Cheque clear/bounce only changes holding/cash account classification and, for bounce, restores outstanding balances.

Therefore aging reports remain correct automatically through the existing outstanding-balance mechanism.
