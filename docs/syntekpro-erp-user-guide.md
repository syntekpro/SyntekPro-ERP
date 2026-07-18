# SyntekPro ERP — Getting Started Guide

This guide walks through using SyntekPro ERP for the first time, assuming
no prior experience with the system. Follow it roughly in order — later
sections depend on things set up in earlier ones.

## 1. Logging in

Go to `https://erp.syntekpro.com` and sign in with your super admin
account. You'll land on the **Dashboard**, which shows top-level numbers
(shops, warehouses, products, recent activity).

The left-hand navigation is your home base for everything in this guide.

## 2. Roles — who can do what

There are four roles:

| Role | Can do |
|---|---|
| **super_admin** | Everything — the only role that can close/reopen fiscal periods |
| **accountant** | Chart of Accounts, journal entries, financial reports, AP/AR |
| **shop_manager** | Manage their own shop's stock, receive transfers, view their shop's sales |
| **cashier** | Ring up POS sales at their assigned shop only |

Set each staff member's role when you create their user account (Section 3).

## 3. Initial setup — do this first

Before you can sell anything, set up the following, in this order:

1. **Warehouses** (left nav → Warehouses) — add your central warehouse(s)
2. **Shops** (left nav → Shops) — add each shop/branch location
3. **Products** (left nav → Products) — add your catalog. Set the selling
   price and VAT rate per product; cost price will update automatically
   once you start purchasing stock (see Section 5)
4. **Users** (left nav → Users) — create an account for each staff member,
   assign their role, and for shop staff, assign which shop they belong to

## 4. Getting stock into the system

Stock has to physically flow: **Supplier → Warehouse → Shop**. There's no
way to sell a product until it has stock in a specific shop.

### Step A: Record a Purchase Order
1. Go to **Purchase Orders** → Create
2. Select a supplier (add one under **Suppliers** first if needed) and
   the warehouse it's going to
3. Add line items: product, quantity, unit cost
4. Submit the PO

### Step B: Receive the PO
1. Open the PO and receive it — you can receive the full quantity or
   less (a partial receipt), and receive again later against the same
   PO for the remainder
2. Receiving automatically: increases warehouse stock, updates the
   product's average cost, and generates a **Supplier Bill** for you —
   you don't create the bill manually

### Step C: Pay the supplier (whenever you're ready)
1. Go to **Supplier Bills**, find the bill, record a payment
2. Payments can be partial — the bill tracks its remaining balance

### Step D: Transfer stock from warehouse to a shop
1. Go to **Stock Transfers** → Create
2. Select the source warehouse, destination shop, and quantities
3. Dispatch the transfer, then **receive** it at the shop end — this is
   the step that actually adds stock to that shop, ready to sell

Only after Step D does a shop have stock to sell.

## 5. Making a sale (POS)

1. Log in as a cashier (or navigate to **POS → Sales** as any user
   assigned to a shop)
2. Add items to the sale, same as any point-of-sale screen
3. Choose a payment method:
   - **Cash** or **Card** — settles immediately
   - **Credit Account** — requires selecting a customer (add one under
     **Customers** first if needed); this creates an outstanding balance
     the customer owes, due by their payment terms
4. Complete the sale

**Offline note:** the POS works even without an internet connection — it
queues the sale locally and syncs automatically once you're back online.
If a queued sale can't be synced (e.g. the item went out of stock while
you were offline), the whole sale is held for manual review rather than
partially applied — check with your admin if a sale doesn't appear to
have synced.

## 6. Getting paid by customers

1. Go to **Receivables** (Customer Receivables) to see all outstanding
   credit-account sales
2. Click into one to record a payment — partial payments are supported,
   and you can't record more than what's actually still owed
3. Check **AR Aging** periodically to see who owes you money and how
   overdue it is

## 7. Understanding the accounting side

You don't need to do manual bookkeeping for day-to-day operations — the
system posts to the ledger automatically:

- Every sale posts revenue, VAT, and either a cash or receivable entry
- Every sale also automatically records the cost of what was sold
  (Cost of Goods Sold) against your inventory value
- Every supplier bill and payment posts itself automatically
- Every customer payment posts itself automatically

**You only need the accounting screens to check things, not to enter
them by hand** (unless you have a specific manual adjustment to make).

### The Chart of Accounts

Under **Accounts**, you'll find the default set already configured:

| Code | Account | Type |
|---|---|---|
| 1010 | Cash on Hand | Asset |
| 1020 | Bank Account | Asset |
| 1100 | Accounts Receivable | Asset |
| 1200 | Inventory | Asset |
| 1300 | VAT Receivable | Asset |
| 2100 | Accounts Payable | Liability |
| 2200 | VAT Payable | Liability |
| 3100 | Owner Capital | Equity |
| 3200 | Retained Earnings | Equity |
| 4100 | Sales Revenue | Revenue |
| 5100 | Cost of Goods Sold | Expense |
| 5200 | Rent Expense | Expense |
| 5300 | Utilities Expense | Expense |
| 5400 | Salaries Expense | Expense |

You can edit or add to this list under **Accounts**, but don't delete an
account that already has activity posted against it — deactivate it
instead.

### Manual journal entries

If you need to record something the system doesn't do automatically
(e.g. rent paid by bank transfer), go to **Ledger (GL) → Journal Entries
→ Create**. Every entry must balance — your debit lines must add up to
exactly the same total as your credit lines, or it will be rejected.

## 8. Checking your numbers

- **Trial Balance** — confirms every account's debits/credits are
  internally consistent. Check this first if something looks off.
- **Balance Sheet** — your Assets, Liabilities, and Equity as of any
  date. Always company-wide (not per-shop).
- **Income Statement (P&L)** — Revenue, COGS, Gross Profit, and Net
  Income for any date range. Can be filtered to a single shop, or left
  unfiltered for the whole company.
- **Cash Flow Statement** — how cash moved in and out over a date range.
- **AP Aging** / **AR Aging** — who you owe, and who owes you, bucketed
  by how overdue.

## 9. Closing a period (month-end)

Once you're confident a month's books are correct and finalized:

1. Go to **Fiscal Periods**
2. Close the relevant month (super_admin only)

Once closed, nothing can be posted with a date inside that period — not
even automatically (e.g. a backdated sale). If you find a mistake in a
closed period later, don't try to reopen and edit it — record a
**reversing entry** in the current open period instead. This keeps your
historical books trustworthy.

You can reopen a closed period if genuinely necessary (super_admin only),
but treat that as an exception, not routine.

## 10. Quick troubleshooting

| Problem | Likely cause |
|---|---|
| Cashier can't sell a product | No stock at that specific shop — check Stock Transfers, not just Products |
| A journal entry won't save | Debits and credits don't match exactly, or it's dated inside a closed period |
| Can't record a payment | The amount exceeds what's actually still owed on that bill/sale |
| Credit sale rejected at POS | Would push the customer over their credit limit |
| Numbers look wrong | Check Trial Balance first — if it doesn't balance, something's wrong upstream; if it does balance, the issue is likely a data-entry mistake, not a system bug |

---

*This guide reflects SyntekPro ERP as of Phase 9 (version 1.0.0). Some
features mentioned in the feature list (returns/refunds, Arabic
localization, ZATCA Phase 2) are not yet built and won't appear in the
system.*
