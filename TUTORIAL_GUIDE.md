# 🎓 SyntekPro ERP - Complete Tutorial Guide

> **Step-by-step interactive tutorials for every module and common workflows**

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Initial Setup & Configuration](#initial-setup--configuration)
3. [Tutorial 1: Basic Inventory Setup](#tutorial-1-basic-inventory-setup)
4. [Tutorial 2: Creating Your First Sale](#tutorial-2-creating-your-first-sale)
5. [Tutorial 3: Purchase Order Workflow](#tutorial-3-purchase-order-workflow)
6. [Tutorial 4: Customer Payments & AR Aging](#tutorial-4-customer-payments--ar-aging)
7. [Tutorial 5: Supplier Payments & AP Aging](#tutorial-5-supplier-payments--ap-aging)
8. [Tutorial 6: Journal Entries & GL](#tutorial-6-journal-entries--gl)
9. [Tutorial 7: Month-End Close Process](#tutorial-7-month-end-close-process)
10. [Tutorial 8: ZATCA e-Invoice Setup](#tutorial-8-zatca-e-invoice-setup)
11. [Tutorial 9: Bank Reconciliation](#tutorial-9-bank-reconciliation)
12. [Tutorial 10: POS Operations](#tutorial-10-pos-operations)

---

## Getting Started

### Prerequisites Check

Before starting, ensure you have:

```bash
# Check Docker installation
docker --version
# Expected: Docker version 20.10+

# Check Docker Compose installation
docker compose version
# Expected: Docker Compose version v2.0+

# Check available ports (8080, 3306, 6379 should be free)
netstat -an | grep LISTEN  # macOS/Linux
```

### First Login

1. **Open browser:** `http://localhost:8080`
2. **Login with:**
   - Email: `development@syntekpro.com`
   - Password: `password`
3. **You should see:** Dashboard with key metrics
4. **First action:** Change your password!
   - Click profile → Settings → Change Password

---

## Initial Setup & Configuration

### Configure Business Settings

**Time Required:** 10 minutes

#### Step 1: Enter Company Information

1. Navigate: **Settings** → **Business Settings**
2. Enter the following:

   | Field | Example |
   |-------|---------|
   | **Legal Company Name** | Saudi Trading Company Ltd |
   | **Short Name** | STC |
   | **Registration Number** | 1010123456 |
   | **Tax Number (VAT)** | 311111111111111 |
   | **Address** | PO Box 1234, Riyadh |
   | **City** | Riyadh |
   | **Country** | Saudi Arabia |
   | **Phone** | +966501234567 |
   | **Email** | info@stc.com |

3. Click **Save**

#### Step 2: Set Business Defaults

1. Same page, scroll to "Business Defaults"
2. Configure:

   | Setting | Value |
   |---------|-------|
   | **Default Currency** | SAR (Saudi Riyal) |
   | **VAT Rate (%)** | 15 |
   | **Financial Year Starts** | January 1 |
   | **Demo Mode** | Off (if production) |

3. Click **Save**

#### Step 3: Configure Email

1. Navigate: **Settings** → **Email Configuration**
2. Enter your SMTP provider details:

   ```
   Mail Provider: SMTP
   Host: smtp.mailtrap.io
   Port: 587
   Username: your-username
   Password: your-password
   Encryption: TLS
   From Address: noreply@company.com
   ```

3. Click **Test Email** to verify
4. Click **Save**

---

## Tutorial 1: Basic Inventory Setup

**Estimated Time:** 20 minutes | **Difficulty:** Beginner

### Objective
Set up your warehouse and product catalog with initial stock levels.

### Step 1: Create Warehouse

1. Navigate: **Settings** → **Warehouses** → **Add Warehouse**

   ```
   Name:           Central Warehouse Riyadh
   Type:           Main Warehouse
   Location:       King Abdullah Road, Riyadh
   Manager Name:   Ahmed Al-Sudairi
   Phone:          +966501234567
   Email:          warehouse@company.com
   City:           Riyadh
   ```

2. Click **Save**
3. Expected: Green success message "Warehouse created successfully"

### Step 2: Create Product Categories

1. Navigate: **Settings** → **Product Categories** → **Add Category**

   **Category 1:**
   ```
   Name:        Electronics
   Description: Electronic devices and gadgets
   Icon:        📱
   ```

   **Category 2:**
   ```
   Name:        Supplies
   Description: Office and shop supplies
   Icon:        📦
   ```

2. Save each category

### Step 3: Create Brands

1. Navigate: **Settings** → **Brands** → **Add Brand**

   ```
   Name:        Samsung
   Description: Samsung Electronics
   Logo:        (upload logo if available)
   ```

2. Create 2-3 more brands for variety
3. Save each

### Step 4: Create Units

1. Navigate: **Settings** → **Units** → **Add Unit**

   ```
   Name:       Piece
   Abbreviation: pc
   Conversion Factor: 1
   
   Name:       Carton
   Abbreviation: ctn
   Conversion Factor: 12 (1 carton = 12 pieces)
   
   Name:       Kilogram
   Abbreviation: kg
   Conversion Factor: 1
   ```

2. Save each unit

### Step 5: Create Products

1. Navigate: **Products** → **Add Product**

   **Product 1: Smartphone**
   ```
   SKU:             PHONE-001
   Name:            Samsung Galaxy A13
   Category:        Electronics
   Brand:           Samsung
   Unit:            Piece
   Description:     5000mAh battery, 128GB storage
   Reorder Point:   10 units
   Base Price:      1,500 SAR
   ```

   **Product 2: USB Cable**
   ```
   SKU:             USB-001
   Name:            USB-C Cable 1m
   Category:        Supplies
   Brand:           Generic
   Unit:            Piece
   Description:     Charging cable for phones
   Reorder Point:   50 units
   Base Price:      50 SAR
   ```

   **Product 3: Screen Protector**
   ```
   SKU:             SCREEN-001
   Name:            Tempered Glass Screen Protector
   Category:        Electronics
   Brand:           Generic
   Unit:            Piece
   Description:     Anti-glare, 9H hardness
   Reorder Point:   20 units
   Base Price:      100 SAR
   ```

2. For each product, click **Save**

### Step 6: Add Initial Stock to Warehouse

1. Navigate: **Products**
2. For each product, click **Edit**
3. Scroll to "Warehouse Stock"
4. Enter opening balance:

   ```
   PHONE-001:      200 units
   USB-001:        1,000 units
   SCREEN-001:     500 units
   ```

5. Click **Save**

### Step 7: Verify Setup

1. Navigate: **Dashboard**
2. You should see:
   - ✅ Warehouse count: 1
   - ✅ Product count: 3
   - ✅ Total inventory value
3. Navigate: **Reports** → **Inventory Summary**
4. Verify all products show correct stock levels

✅ **Checkpoint:** Your inventory system is ready!

---

## Tutorial 2: Creating Your First Sale

**Estimated Time:** 15 minutes | **Difficulty:** Beginner

### Objective
Process a sale and record payment from a customer.

### Step 1: Create a Customer

1. Navigate: **Customers** → **Add Customer**

   ```
   Company Name:        ABC Trading LLC
   Contact Person:      Mohammed Al-Rashid
   Email:               mohammed@abctrading.com
   Phone:               +966501111111
   Address:             123 Commerce St, Riyadh
   City:                Riyadh
   Country:             Saudi Arabia
   Credit Limit:        50,000 SAR
   Payment Terms:       Net 30 (Pay within 30 days)
   ```

2. Click **Save**
3. Note the Customer ID (e.g., CUST-001)

### Step 2: Create a Sale (Credit Sale)

1. Navigate: **Sales** → **Create Sale**

2. Fill in header information:
   ```
   Sale Date:      Today's date
   Customer:       ABC Trading LLC (select from dropdown)
   Shop:           (Select your main shop)
   Status:         Draft
   ```

3. Add line items by clicking "Add Item":

   **Line 1:**
   ```
   Product:        Samsung Galaxy A13
   Quantity:       5
   Unit Price:     1,500 SAR (auto-filled)
   Line Total:     7,500 SAR (auto-calculated)
   Description:    Delivery to Riyadh branch
   ```

   **Line 2:**
   ```
   Product:        USB-C Cable 1m
   Quantity:       50
   Unit Price:     50 SAR (auto-filled)
   Line Total:     2,500 SAR (auto-calculated)
   Description:    Bulk order
   ```

4. Review totals:
   ```
   Subtotal:       10,000 SAR
   VAT (15%):      1,500 SAR
   Total:          11,500 SAR
   ```

5. Notes: "First order from new customer"

6. Click **Save as Draft**

### Step 3: Review & Finalize Sale

1. Navigate: **Sales** → View the sale you just created
2. Review all details
3. Click **Confirm** (status changes to "Confirmed")
4. Click **Print** to preview invoice
5. Click **Send** (sends email to customer with invoice)

### Step 4: Record Payment

1. From the sales record, click **Record Payment**

   ```
   Amount Received:    11,500 SAR
   Payment Method:     Bank Transfer
   Reference:         Bank transfer receipt #123456
   Date Received:      Today
   Notes:              Payment received from customer
   ```

2. Click **Save**
3. Sale status changes to "Paid"

### Step 5: Verify Stock Deduction

1. Navigate: **Products**
2. View inventory levels:
   - Samsung Galaxy A13: Should be 195 (was 200, sold 5)
   - USB-C Cable: Should be 950 (was 1,000, sold 50)

✅ **Checkpoint:** First sale completed! Stock automatically decremented.

---

## Tutorial 3: Purchase Order Workflow

**Estimated Time:** 25 minutes | **Difficulty:** Intermediate

### Objective
Complete a full purchase order cycle: Create → Send → Receive → Bill → Pay

### Step 1: Create Supplier

1. Navigate: **Suppliers** → **Add Supplier**

   ```
   Company Name:        Electronics Wholesale Inc
   Contact Person:      Ahmed Al-Khateeb
   Email:               purchasing@elecwholesale.com
   Phone:               +966502222222
   Address:             Industrial Zone, Jeddah
   Payment Terms:       Net 45 (Pay within 45 days)
   VAT Number:         311222222222222
   ```

2. Click **Save**

### Step 2: Create Purchase Order

1. Navigate: **Purchasing** → **Purchase Orders** → **Create PO**

2. Fill header:
   ```
   PO Date:        Today
   Supplier:       Electronics Wholesale Inc
   Delivery Date:  14 days from today
   Status:         Draft
   ```

3. Add line items:

   **Line 1:**
   ```
   Product:        Samsung Galaxy A13
   Quantity:       100
   Unit Cost:      800 SAR (wholesale price)
   Line Total:     80,000 SAR
   Description:    Bulk purchase - monthly stock
   ```

   **Line 2:**
   ```
   Product:        Screen Protector
   Quantity:       200
   Unit Cost:      40 SAR (wholesale price)
   Line Total:     8,000 SAR
   Description:    Bulk purchase
   ```

4. Review totals:
   ```
   Subtotal:       88,000 SAR
   VAT (15%):      13,200 SAR
   Total:          101,200 SAR
   ```

5. Terms: "Net 45, FOB Destination"

6. Click **Save**

### Step 3: Send PO to Supplier

1. From PO, click **Send to Supplier**
2. Email preview shows all order details
3. Click **Confirm Send**
4. Status changes to "Sent"
5. Supplier receives email with PO details

### Step 4: Receive Goods

*Simulating: Supplier delivers goods after 5 days*

1. Navigate: **Purchasing** → **Purchase Orders** → Select your PO

2. Click **Receive Goods**

3. Verify receipt:
   ```
   Item 1: Samsung Galaxy A13
           Ordered: 100    Received: 100    ✓
   
   Item 2: Screen Protector
           Ordered: 200    Received: 200    ✓
   ```

4. Receiving notes: "All items in perfect condition. Delivered on 15-Jan-2024"

5. Click **Confirm Receipt**

6. System automatically updates warehouse stock:
   - Samsung Galaxy A13: +100
   - Screen Protector: +200

### Step 5: Process Supplier Invoice

1. Navigate: **Purchasing** → **Supplier Bills** → **Create Bill**

2. Link to Purchase Order:
   ```
   Select PO:      Your PO (auto-populates items and amounts)
   Status:         Draft
   ```

3. Verify amounts match PO:
   ```
   Subtotal:       88,000 SAR
   VAT:            13,200 SAR
   Total:          101,200 SAR
   ```

4. Additional charges (if any):
   ```
   Shipping:       0 SAR (FOB - no additional charge)
   ```

5. Bill date: Invoice date from supplier
6. Due date: Auto-calculated as 45 days from bill date

7. Click **Save**

8. Status changes to "Awaiting Payment"

### Step 6: Record Payment to Supplier

*Simulating: Payment due on specific date*

1. Navigate: **Payables** → **Supplier Bills**
2. Find your bill (status "Awaiting Payment")
3. Click **Record Payment**

   ```
   Amount:         101,200 SAR
   Method:         Bank Transfer
   Reference:      Bank transfer reference #789123
   Date:           Today
   Notes:          Payment for PO #001
   ```

4. Click **Save**
5. Bill status changes to "Paid"

### Step 7: View AP Aging Report

1. Navigate: **Reports** → **AP Aging**
2. If you create multiple POs/bills with different due dates, you'll see:

   ```
   Supplier Name        Total Due    Current    30-60 Days    60-90 Days    >90 Days
   ─────────────────────────────────────────────────────────────────────────────────
   Electronics Inc      101,200      Paid       -             -             -
   (Shows aging by date due)
   ```

✅ **Checkpoint:** Full PO-to-payment cycle complete!

---

## Tutorial 4: Customer Payments & AR Aging

**Estimated Time:** 20 minutes | **Difficulty:** Intermediate

### Objective
Track customer receivables and manage collection activities.

### Step 1: Create Multiple Sales (Various Dates)

1. Navigate: **Sales** → Create sales with different due dates:

   **Sale A: Due Today**
   ```
   Customer:   ABC Trading LLC
   Amount:     11,500 SAR
   Due Date:   Today
   Status:     Confirmed (unpaid)
   ```

   **Sale B: Due in 35 days**
   ```
   Customer:   New Customer "XYZ Retail"
   Amount:     25,000 SAR
   Due Date:   Today + 35 days
   Status:     Confirmed (unpaid)
   ```

   **Sale C: Due in 75 days**
   ```
   Customer:   New Customer "Retail Corp"
   Amount:     15,000 SAR
   Due Date:   Today + 75 days
   Status:     Confirmed (unpaid)
   ```

2. Leave all unpaid for this tutorial

### Step 2: View Customer Receivables Summary

1. Navigate: **Sales** → **Customer Receivables**

2. You should see table:
   ```
   Customer          Total Due   Current   30-60 Days   60-90 Days   >90 Days
   ─────────────────────────────────────────────────────────────────────────
   ABC Trading       11,500      11,500    -            -            -
   XYZ Retail        25,000      -         25,000       -            -
   Retail Corp       15,000      -         -            15,000       -
   ─────────────────────────────────────────────────────────────────────────
   TOTAL             51,500      11,500    25,000       15,000       -
   ```

### Step 3: View AR Aging Report

1. Navigate: **Reports** → **AR Aging**

2. Detailed aging view:

   ```
   ╔════════════════════════════════════════════════════════╗
   ║ ACCOUNTS RECEIVABLE AGING REPORT                       ║
   ║ As of: January 15, 2024                                ║
   ╠════════════════════════════════════════════════════════╣
   ║ Customer      │ Total Due │ Current │ 30-60 │ 60-90 │ >90 ║
   ╠════════════════════════════════════════════════════════╣
   ║ ABC Trading   │ 11,500    │ 11,500  │ -     │ -     │ -   ║
   ║ XYZ Retail    │ 25,000    │ -       │ 25,000│ -     │ -   ║
   ║ Retail Corp   │ 15,000    │ -       │ -     │ 15,000│ -   ║
   ╠════════════════════════════════════════════════════════╣
   ║ TOTAL         │ 51,500    │ 11,500  │ 25,000│ 15,000│ -   ║
   ║ Percentage    │ 100%      │ 22.3%   │ 48.5% │ 29.1% │ -   ║
   ╚════════════════════════════════════════════════════════╝
   ```

3. **Key Insight:** 
   - 11,500 SAR is current (due today) - needs immediate collection
   - 25,000 SAR is 30-60 days overdue - send reminder
   - 15,000 SAR is 60-90 days overdue - follow up urgently

### Step 4: Record Partial Payment from ABC Trading

1. Navigate: **Sales** → **Customer Receivables**
2. Find "ABC Trading LLC" sale
3. Click **Record Payment**

   ```
   Invoice Amount:     11,500 SAR
   Amount Received:    5,000 SAR (partial payment)
   Payment Method:     Cheque
   Cheque #:          12345
   Date Received:     Today
   ```

4. Click **Save**

5. ABC Trading balance now shows:
   ```
   Total Due:         6,500 SAR (11,500 - 5,000)
   Status:            Partially Paid
   ```

### Step 5: Record Payment from XYZ Retail

1. Find "XYZ Retail" sale
2. Click **Record Payment**

   ```
   Invoice Amount:     25,000 SAR
   Amount Received:    25,000 SAR (full payment)
   Payment Method:     Bank Transfer
   Reference:         Bank transfer TX-98765
   Date Received:     Today
   ```

3. Click **Save**

4. XYZ Retail shows:
   ```
   Total Due:         0 SAR
   Status:            Fully Paid
   ```

### Step 6: View Updated AR Aging

1. Navigate: **Reports** → **AR Aging**

2. New report shows:

   ```
   Customer      │ Total Due │ Current │ 30-60 │ 60-90 │ >90
   ──────────────┼───────────┼─────────┼───────┼───────┼────
   ABC Trading   │ 6,500     │ 6,500   │ -     │ -     │ -
   Retail Corp   │ 15,000    │ -       │ -     │ 15,000│ -
   ──────────────┼───────────┼─────────┼───────┼───────┼────
   TOTAL         │ 21,500    │ 6,500   │ -     │ 15,000│ -
   ```

### Step 7: Collections Actions

1. For overdue invoices (>30 days):
   - Click **Send Reminder**
   - System emails customer
   - Tracked in communication log

2. For >60 days overdue:
   - Click **Create Collection Note**
   - System records escalation
   - Flag for manager review

✅ **Checkpoint:** AR management complete!

---

## Tutorial 5: Supplier Payments & AP Aging

**Estimated Time:** 20 minutes | **Difficulty:** Intermediate

### Objective
Track supplier payables and manage payment schedules.

### Step 1: Create Multiple Bills (Various Due Dates)

1. Navigate: **Purchasing** → **Supplier Bills** → Create 3 bills:

   **Bill A: Due Today**
   ```
   Supplier:       Electronics Wholesale Inc
   Amount:         50,000 SAR
   Due Date:       Today
   ```

   **Bill B: Due in 30 days**
   ```
   Supplier:       New Supplier "Components Ltd"
   Amount:         30,000 SAR
   Due Date:       Today + 30 days
   ```

   **Bill C: Due in 60 days**
   ```
   Supplier:       New Supplier "Parts Distribution"
   Amount:         20,000 SAR
   Due Date:       Today + 60 days
   ```

2. All remain "Awaiting Payment"

### Step 2: View Payables Summary

1. Navigate: **Purchasing** → **Supplier Bills**

2. Summary shows all unpaid bills with due dates

### Step 3: View AP Aging Report

1. Navigate: **Reports** → **AP Aging**

2. Detailed view:

   ```
   ╔════════════════════════════════════════════════════════╗
   ║ ACCOUNTS PAYABLE AGING REPORT                          ║
   ║ As of: January 15, 2024                                ║
   ╠════════════════════════════════════════════════════════╣
   ║ Supplier         │ Total Due │ Current │ 30-60 │ 60-90 │
   ╠════════════════════════════════════════════════════════╣
   ║ Electronics Inc  │ 50,000    │ 50,000  │ -     │ -     ║
   ║ Components Ltd   │ 30,000    │ -       │ 30,000│ -     ║
   ║ Parts Dist.      │ 20,000    │ -       │ -     │ 20,000║
   ╠════════════════════════════════════════════════════════╣
   ║ TOTAL            │ 100,000   │ 50,000  │ 30,000│ 20,000║
   ║ Percentage       │ 100%      │ 50%     │ 30%   │ 20%   ║
   ╚════════════════════════════════════════════════════════╝
   ```

### Step 4: Schedule Payment Plan

1. Click "Payment Plan" from AP Aging report

2. System recommends payment sequence:
   ```
   Priority 1 (Today):     50,000 SAR  [Electronics Inc - OVERDUE]
   Priority 2 (in 15d):    30,000 SAR  [Components Ltd - DUE SOON]
   Priority 3 (in 45d):    20,000 SAR  [Parts Distribution - OK]
   ```

### Step 5: Process Payment to Overdue Supplier

1. Navigate: **Payables** → **Supplier Bills**
2. Find Electronics bill (status "Awaiting Payment")
3. Click **Record Payment**

   ```
   Bill Amount:        50,000 SAR
   Payment Amount:     50,000 SAR
   Method:            Bank Transfer
   Reference:         TRF-2024-001
   Date Paid:         Today
   ```

4. Click **Save**

5. Bill status → "Paid"

6. Payment recorded in **Banking** → **Bank Transactions**

### Step 6: View Cheque Register (Alternative Payment Method)

1. Navigate: **Banking** → **Cheques** → **Issue Cheque**

   ```
   Cheque #:          1001
   Bank Account:      Company Bank Account
   Payee:             Components Ltd
   Amount:            30,000 SAR
   Issue Date:        Today
   Note:              Payment for Bill #xx
   ```

2. Click **Save** (status: "Issued")

3. When cheque clears bank:
   - Navigate: **Cheques**
   - Click **Clear**
   - Status: "Cleared"

### Step 7: View Updated AP Aging

1. Navigate: **Reports** → **AP Aging**

2. Updated report shows:
   ```
   Supplier         │ Total Due │ Current │ 30-60 │ 60-90
   ──────────────────┼───────────┼─────────┼───────┼────────
   Components Ltd    │ 30,000    │ -       │ 30,000│ -
   Parts Dist.       │ 20,000    │ -       │ -     │ 20,000
   ──────────────────┼───────────┼─────────┼───────┼────────
   TOTAL             │ 50,000    │ -       │ 30,000│ 20,000
   ```

✅ **Checkpoint:** AP management complete!

---

## Tutorial 6: Journal Entries & GL

**Estimated Time:** 25 minutes | **Difficulty:** Advanced

### Objective
Create manual journal entries and understand the GL posting process.

### Step 1: Understand Chart of Accounts

1. Navigate: **Accounting** → **Chart of Accounts**

2. View hierarchical account structure:

   ```
   1000 - ASSETS
   ├─ 1100 - Current Assets
   │  ├─ 1110 - Cash on Hand
   │  ├─ 1120 - Bank Accounts
   │  ├─ 1130 - Accounts Receivable
   │  └─ 1140 - Prepaid Expenses
   ├─ 1200 - Fixed Assets
   │  ├─ 1210 - Equipment
   │  └─ 1220 - Accumulated Depreciation
   
   2000 - LIABILITIES
   ├─ 2100 - Current Liabilities
   │  ├─ 2110 - Accounts Payable
   │  ├─ 2120 - Short-term Loans
   │  └─ 2130 - Accrued Expenses
   
   3000 - EQUITY
   ├─ 3100 - Capital Stock
   ├─ 3200 - Retained Earnings
   
   4000 - REVENUE
   ├─ 4100 - Sales Revenue
   └─ 4200 - Service Revenue
   
   5000 - EXPENSES
   ├─ 5100 - Cost of Goods Sold
   ├─ 5200 - Salaries & Wages
   ├─ 5300 - Rent Expense
   ├─ 5400 - Utilities
   └─ 5500 - Depreciation
   ```

### Step 2: Create Manual Journal Entry (Rent Payment)

1. Navigate: **Accounting** → **Journal Entries** → **Create Entry**

2. Fill in header:
   ```
   Entry Date:      Today
   Reference:       CHK-5001
   Description:     Rent payment for January 2024
   Source:          Manual
   ```

3. Add journal lines:

   **Line 1 (Debit):**
   ```
   Account:         5300 - Rent Expense
   Type:           Debit
   Amount:         5,000 SAR
   Description:    Monthly rent for office space
   ```

   **Line 2 (Credit):**
   ```
   Account:         1120 - Bank Accounts
   Type:           Credit
   Amount:         5,000 SAR
   Description:    Cheque #5001
   ```

4. System validates: 
   ✅ Total Debits (5,000) = Total Credits (5,000)

5. Click **Save & Post**

6. Verification message:
   ```
   ✓ Journal Entry #JE-001 posted successfully
   - 5300 - Rent Expense debited 5,000 SAR
   - 1120 - Bank Account credited 5,000 SAR
   ```

### Step 3: Record Accrual (Utilities Estimated)

*Example: Utilities used but not yet billed*

1. Navigate: **Accounting** → **Journal Entries** → **Create Entry**

2. Entry details:
   ```
   Entry Date:      Last day of month
   Reference:      ADJ-001
   Description:    Monthly utilities accrual (estimated)
   ```

3. Add lines:

   **Line 1:**
   ```
   Account:         5400 - Utilities Expense
   Type:           Debit
   Amount:         2,500 SAR
   Description:    Estimated utilities for January
   ```

   **Line 2:**
   ```
   Account:         2130 - Accrued Utilities
   Type:           Credit
   Amount:         2,500 SAR
   Description:    Liability for utilities not yet billed
   ```

4. Click **Save & Post**

### Step 4: Record Depreciation

*Example: Monthly depreciation on equipment*

1. Navigate: **Accounting** → **Journal Entries** → **Create Entry**

2. Entry:
   ```
   Entry Date:      Last day of month
   Reference:       DEP-001
   Description:     Monthly depreciation on equipment
   ```

3. Lines:

   **Line 1:**
   ```
   Account:         5500 - Depreciation Expense
   Type:           Debit
   Amount:         500 SAR
   Description:    Equipment depreciation (straight-line)
   ```

   **Line 2:**
   ```
   Account:         1220 - Accumulated Depreciation
   Type:           Credit
   Amount:         500 SAR
   Description:    Monthly depreciation accumulation
   ```

4. Click **Save & Post**

### Step 5: View Trial Balance

1. Navigate: **Reports** → **Trial Balance**

2. View shows all GL accounts:

   ```
   Account #  Account Name                  Debit         Credit
   ─────────────────────────────────────────────────────────────
   1110       Cash on Hand                 20,000
   1120       Bank Accounts                80,000
   1130       Accounts Receivable          51,500
   1210       Equipment                   100,000
   1220       Accumulated Depreciation                     500
   2110       Accounts Payable                           50,000
   2130       Accrued Utilities                           2,500
   3100       Capital Stock                            100,000
   4100       Sales Revenue                            151,500
   5100       COGS                         70,000
   5200       Salaries & Wages             15,000
   5300       Rent Expense                  5,000
   5400       Utilities Expense             2,500
   5500       Depreciation Expense            500
   ─────────────────────────────────────────────────────────────
   TOTALS                                 344,500      344,500
                                          ═════════════════════
   ```

3. Key validation: **Total Debits = Total Credits** ✓

### Step 6: View GL Account Details

1. Click on any account to drill down:
   - Account: **5300 - Rent Expense**
   - Shows all journal entries affecting this account
   - Running balance by date

✅ **Checkpoint:** GL and journal entry basics complete!

---

## Tutorial 7: Month-End Close Process

**Estimated Time:** 45 minutes | **Difficulty:** Advanced

### Objective
Complete a full month-end accounting close following best practices.

### Pre-Close Checklist

- [ ] All sales recorded and invoiced
- [ ] All purchase invoices received and entered
- [ ] Bank reconciliation completed
- [ ] AR aging reviewed
- [ ] AP aging reviewed
- [ ] No pending transactions

### Step 1: Review Trial Balance

1. Navigate: **Reports** → **Trial Balance**
2. Filter by date range: 1st to last day of month
3. Verify: Total Debits = Total Credits
4. Print/export for records

### Step 2: Reconcile Bank Account

1. Navigate: **Banking** → **Bank Reconciliation** → **Reconcile Account**

2. Import bank statement:
   ```
   File: January bank statement from bank
   Account: Main bank account
   Start balance: X
   End balance: Y
   ```

3. Match transactions:
   - Deposits ✓
   - Cheques ✓
   - Fees ✓
   - Interest ✓

4. Identify items:
   - Outstanding cheques
   - Deposits in transit
   - Bank errors

5. **Bank balance per statement** should equal **GL bank account balance**

6. Print reconciliation report

### Step 3: Review Receivables Aging

1. Navigate: **Reports** → **AR Aging**

2. Actions based on aging:
   - **Current:** Monitor
   - **30-60 days:** Send reminder email
   - **60-90 days:** Contact customer
   - **>90 days:** Escalate for collection

3. Document any expected bad debts (for provision)

### Step 4: Review Payables Aging

1. Navigate: **Reports** → **AP Aging**

2. Payment schedule:
   - Current/due: Schedule payment
   - 30+ days due: May negotiate extension
   - Upcoming due: Plan cash for payment

### Step 5: Record Month-End Accruals

Create journal entries for:

**A. Utilities (if not yet billed)**
```
Debit:   5400 - Utilities Expense      2,500 SAR
Credit:  2130 - Accrued Utilities                 2,500 SAR
```

**B. Salaries (if not yet paid)**
```
Debit:   5200 - Salaries & Wages       25,000 SAR
Credit:  2140 - Accrued Salaries                 25,000 SAR
```

**C. Insurance (monthly allocation)**
```
Debit:   5600 - Insurance Expense      1,000 SAR
Credit:  2150 - Prepaid Insurance                 1,000 SAR
```

**D. Depreciation**
```
Debit:   5500 - Depreciation Expense   500 SAR
Credit:  1220 - Accumulated Depr.                500 SAR
```

### Step 6: Verify Trial Balance After Accruals

1. Navigate: **Reports** → **Trial Balance**
2. Confirm: Still balanced after adjustments
3. Print updated trial balance

### Step 7: Generate Financial Statements

**1. Balance Sheet**
```
Navigate: Reports → Balance Sheet
Date: January 31, 2024

ASSETS                                  SAR
Current Assets
- Cash                              20,000
- Bank                              80,000
- Accounts Receivable               51,500
- Prepaid Insurance                  9,000
Total Current Assets               160,500

Fixed Assets
- Equipment                        100,000
- Less: Accumulated Depr.            (500)
Net Fixed Assets                    99,500
────────────────────────
TOTAL ASSETS                       260,000

LIABILITIES
Current Liabilities
- Accounts Payable                 50,000
- Accrued Utilities                 2,500
- Accrued Salaries                 25,000
Total Current Liabilities           77,500

EQUITY
- Capital Stock                   100,000
- Retained Earnings                82,500
Total Equity                       182,500
────────────────────────
TOTAL LIABILITIES + EQUITY         260,000
```

**2. Income Statement**
```
Navigate: Reports → Income Statement
Period: January 1-31, 2024

REVENUE
- Sales Revenue                   151,500
- Service Revenue                       -
Total Revenue                      151,500

EXPENSES
- Cost of Goods Sold              (70,000)
- Salaries & Wages               (40,000)
- Rent Expense                     (5,000)
- Utilities Expense                (2,500)
- Depreciation Expense               (500)
- Insurance Expense               (1,000)
Total Expenses                     (119,000)
────────────────────────
NET INCOME                          32,500
```

**3. Cash Flow Statement**
```
Navigate: Reports → Cash Flow
Period: January 2024

OPERATING ACTIVITIES
- Net Income                       32,500
- Add: Depreciation                  500
- Add: Increase in AP              50,000
- Less: Increase in AR            (51,500)
Net Cash from Operations            31,500

INVESTING ACTIVITIES
- Equipment Purchased            (100,000)
Net Cash from Investing           (100,000)

FINANCING ACTIVITIES
- Capital Invested                100,000
Net Cash from Financing            100,000
────────────────────────
NET CASH MOVEMENT                   31,500

CASH BALANCE
- Beginning Cash                        -
- Net Change                      31,500
- Ending Cash                     31,500
```

### Step 8: Create Fiscal Period & Close

1. Navigate: **Accounting** → **Fiscal Periods**

2. Create January period:
   ```
   Period Name:     January 2024
   Start Date:      2024-01-01
   End Date:        2024-01-31
   Status:         Open
   ```

3. Once verified, click **Close Period**

4. Confirmation:
   ```
   ✓ Period closed successfully
   - No further entries allowed
   - System archived trial balance
   - Closing entries scheduled
   ```

### Step 9: Archive Month-End Package

1. Export and save:
   - Trial Balance (PDF)
   - Balance Sheet (PDF)
   - Income Statement (PDF)
   - Cash Flow Statement (PDF)
   - Bank Reconciliation (PDF)
   - AR Aging (PDF)
   - AP Aging (PDF)

2. Save in shared folder with date: `2024-01 Month End Close`

3. Share with accountant/manager for review

✅ **Checkpoint:** Month-end close complete!

---

## Tutorial 8: ZATCA e-Invoice Setup

**Estimated Time:** 30 minutes | **Difficulty:** Advanced

### Objective (Saudi Arabia Specific)
Configure system for ZATCA compliance and e-invoice generation.

### Step 1: Gather Required Information

Before starting, collect:
- [ ] Legal entity name (exactly as registered)
- [ ] 15-digit VAT number (SAR number)
- [ ] Organization ID (from SATRCK)
- [ ] Certificate (if already approved by ZATCA)

### Step 2: Configure ZATCA Settings

1. Navigate: **Settings** → **ZATCA Configuration**

2. Enter seller information:

   ```
   Legal Entity Name:       Saudi Trading Company Ltd
   VAT Number:             311111111111111 (15-digit)
   Organization ID:        SATRCK number (if available)
   Seller Name:            Saudi Trading Company
   Street Address:         123 Commerce Street
   City:                  Riyadh
   Postal Code:           12345
   Building Number:       123 (optional)
   District:              Business District (optional)
   Country:               SA (auto-filled)
   ```

3. Click **Save**

### Step 3: Generate Certificate Signing Request (CSR)

*Skip if you already have a ZATCA certificate*

1. On ZATCA Configuration page, click **Generate CSR**

2. System generates:
   ```
   CSR File created: ZATCA-CSR-[DATE].pem
   Private Key created: ZATCA-PRIVATE-[DATE].key
   ```

3. Download both files and save securely:
   ```
   Location: Secure folder on your server
   Backup: Store backup copy offline
   ```

4. Next steps:
   - Log in to [ZATCA Portal](https://zakat.zatca.gov.sa)
   - Submit CSR file
   - Receive certificate
   - Upload certificate back (see Step 5)

### Step 4: Upload ZATCA Certificate (if received from ZATCA)

1. Navigate: **Settings** → **ZATCA Configuration**

2. Section: "Upload Certificate"

3. Select certificate file (PEM format) and upload

4. System validates:
   ```
   ✓ Certificate valid
   ✓ Matches registered VAT number
   ✓ Certificate expires: [DATE]
   ```

5. Click **Activate Certificate**

### Step 5: Configure Invoice Format

1. Navigate: **Settings** → **ZATCA Configuration** → **Invoice Settings**

2. Configure:

   ```
   Invoice Type:           Tax Invoice
   VAT Rate (%):          15
   Include QR Code:       Yes (required)
   Use e-Signature:       Yes (if cert active)
   Transmission Status:   Manual / Automatic
   ```

3. Click **Save**

### Step 6: Verify ZATCA Compliance on First Invoice

1. Create a test sale:
   ```
   Customer:    ABC Trading LLC
   Items:       2x Product A @ 100 SAR each
   Subtotal:    200 SAR
   VAT (15%):   30 SAR
   Total:       230 SAR
   ```

2. Save and view invoice

3. Check invoice for:
   - ✅ ZATCA compliance banner
   - ✅ QR code displayed
   - ✅ VAT details shown
   - ✅ Invoice hash chain

4. Click **Print** or **Email** to customer

### Step 7: Enable Automatic Transmission (Optional)

1. Navigate: **Settings** → **ZATCA Configuration** → **Transmission Settings**

2. Options:
   ```
   Manual:     You submit invoices to ZATCA portal (recommended for testing)
   Automatic:  System auto-submits via ZATCA API (requires API key)
   ```

3. If automatic:
   - Enter ZATCA API credentials
   - Click **Test Connection**
   - System shows: `✓ Connected to ZATCA` or error

4. Save

### Step 8: Generate ZATCA Reports

1. Navigate: **Reports** → **ZATCA Compliance**

2. View monthly summary:

   ```
   ZATCA COMPLIANCE REPORT - January 2024
   
   Total Invoices Issued:           45
   Total Sales Amount:         450,000 SAR
   Total VAT Collected:         67,500 SAR
   
   Invoice Hash Chain Validation:    ✓ Valid
   Transmission Status:
   - Transmitted to ZATCA:     45 / 45 (100%)
   - Failed/Pending:           0 / 45
   
   Credit Notes Issued:              2
   Credit VAT:                   3,000 SAR
   
   ZATCA Compliance:                ✓ Compliant
   Next Review:                 2024-02-15
   ```

3. Download ZATCA submission file for audit

✅ **Checkpoint:** ZATCA e-invoicing configured!

---

## Tutorial 9: Bank Reconciliation

**Estimated Time:** 20 minutes | **Difficulty:** Intermediate

### Objective
Reconcile bank statement to GL for accurate cash position.

### Step 1: Obtain Bank Statement

1. Download from your bank:
   - Format: CSV or Excel
   - Period: Full month (1st to last day)
   - Columns: Date, Description, Debit, Credit, Balance

   Example:
   ```
   Date         Description                 Debit    Credit   Balance
   2024-01-01   Opening Balance                                100,000
   2024-01-03   Sale Receipt - Invoice #1          2,000    102,000
   2024-01-05   Cheque #1001                 5,000           97,000
   2024-01-08   Supplier Payment             30,000          67,000
   2024-01-10   Deposit - Customer XYZ             15,000     82,000
   2024-01-15   Bank Fee                        100          81,900
   2024-01-30   Interest Income                       200     82,100
   ```

### Step 2: Import Bank Statement

1. Navigate: **Banking** → **Bank Reconciliation** → **Import Statement**

2. Select bank account:
   ```
   Bank Account:    Main Business Bank Account
   Currency:        SAR
   ```

3. Upload CSV file or enter details manually

4. Map columns:
   ```
   Date Column:          Date
   Description Column:   Description
   Amount Column:        Debit/Credit (select "Single Amount" or "Separate D/C")
   Balance Column:       Balance
   Has Header Row:       Yes
   ```

5. Click **Import**

6. System shows:
   ```
   ✓ 10 transactions imported
   Starting Balance: 100,000 SAR
   Ending Balance: 82,100 SAR
   ```

### Step 3: Reconcile Transactions

1. View reconciliation screen:

   ```
   Bank Statement Balance: 82,100 SAR
   GL Account Balance:     80,000 SAR
   Difference:             2,100 SAR
   ```

2. Match transactions by clicking each statement line:

   **Transaction 1: Sale Receipt (Jan 3, 2,000 SAR)**
   - Statement shows: ✓ Deposit 2,000
   - GL shows: ✓ Sale #1 recorded 2,000
   - Status: ✓ Matched

   **Transaction 2: Cheque #1001 (Jan 5, 5,000 SAR)**
   - Statement shows: ✓ Cheque cleared 5,000
   - GL shows: ✓ Rent payment posted 5,000
   - Status: ✓ Matched (Click to match)

   **Transaction 3: Supplier Payment (Jan 8, 30,000 SAR)**
   - Status: ✓ Matched

   **Transaction 4: Customer Deposit (Jan 10, 15,000 SAR)**
   - Status: ✓ Matched

   **Transaction 5: Bank Fee (Jan 15, 100 SAR)**
   - Statement shows: ✓ Fee charged 100
   - GL shows: ✗ Not yet recorded
   - Status: ⚠ Unmatched - Need to post

   **Transaction 6: Interest Income (Jan 30, 200 SAR)**
   - Statement shows: ✓ Interest 200
   - GL shows: ✗ Not yet recorded
   - Status: ⚠ Unmatched - Need to post

### Step 4: Record Bank Fees & Interest

1. Click on "Bank Fee" transaction → **Create Journal Entry**

   ```
   Entry Date:     Jan 15
   Description:    Bank service fee
   
   Debit:   5700 - Bank Charges         100 SAR
   Credit:  1120 - Bank Account                100 SAR
   ```

2. Click **Post** → Automatically marks as matched

3. Repeat for interest income:

   ```
   Entry Date:     Jan 30
   Description:    Interest income on deposits
   
   Debit:   1120 - Bank Account          200 SAR
   Credit:  4300 - Interest Income            200 SAR
   ```

### Step 5: Identify Outstanding Items

1. Items in GL but not yet on bank statement:

   ```
   Outstanding Cheques:
   - Cheque #1005 to Supplier ABC:  10,000 SAR (dated Jan 28 - not cleared)
   - Cheque #1006 to Supplier XYZ:   5,000 SAR (dated Jan 29 - not cleared)
   
   Deposits in Transit:
   - Customer deposit (Jan 31):      7,500 SAR (received but not deposited yet)
   ```

2. These explain the remaining difference

### Step 6: Final Reconciliation

1. After all matching:

   ```
   Bank Statement Balance:           82,100 SAR
   
   Add: Deposits in Transit           7,500 SAR
                                     ─────────
   Total:                            89,600 SAR
   
   Less: Outstanding Cheques
   - Cheque #1005                   (10,000) SAR
   - Cheque #1006                    (5,000) SAR
                                    ─────────
   Adjusted Bank Balance:            74,600 SAR
   
   GL Account Balance:               74,600 SAR ✓
   
   RECONCILED!
   ```

### Step 7: Print Reconciliation Report

1. Click **Generate Report**

2. Report shows:

   ```
   ═════════════════════════════════════════════════════════
   BANK RECONCILIATION REPORT
   Account: Main Business Bank
   Period: January 1-31, 2024
   ═════════════════════════════════════════════════════════
   
   Bank Balance per Statement (Jan 31):        82,100 SAR
   
   Add:
     Deposits in Transit                        7,500
                                              ─────────
   Total                                      89,600
   
   Less:
     Outstanding Cheque #1005                (10,000)
     Outstanding Cheque #1006                 (5,000)
                                              ─────────
   Adjusted Bank Balance                      74,600
   
   GL Balance per Account 1120                74,600 ✓
   
   DIFFERENCE                                      0 ✓
   
   Status: RECONCILED
   Reconciled by: Admin User
   Date: January 31, 2024
   ═════════════════════════════════════════════════════════
   ```

3. Save/print for records

✅ **Checkpoint:** Bank reconciliation complete!

---

## Tutorial 10: POS Operations

**Estimated Time:** 30 minutes | **Difficulty:** Beginner

### Objective
Process sales through the POS interface with offline-first capability.

### Step 1: Set Up POS Device

1. Navigate: **Settings** → **POS Devices** → **Add Device**

   ```
   Device Name:        POS Terminal 1 - Checkout
   Location:           Riyadh Shop
   Device ID:          (Auto-generated)
   Status:             Active
   ```

2. Generate API Token:
   - Click **Generate Token**
   - Copy token (save securely)
   - Token expires: 1 year

### Step 2: Start POS Cashier Session

1. Navigate: **POS** → **Checkout**

2. System displays:
   ```
   ┌─────────────────────────────────────┐
   │    SYNTEKPRO POS - CHECKOUT         │
   │  Riyadh Shop | Till 1               │
   ├─────────────────────────────────────┤
   │                                     │
   │  ITEMS                              │
   │  ─────────────────────────────────  │
   │  (empty - ready for scanning)       │
   │                                     │
   │  TOTAL:              0.00 SAR       │
   │  SUBTOTAL:           0.00 SAR       │
   │  VAT (15%):          0.00 SAR       │
   │                                     │
   │  [Scan/Search]  [Manual Add]        │
   └─────────────────────────────────────┘
   ```

### Step 3: Create a Sale

1. **Option A: Scan Product Barcode**
   - If product has barcode, scan it
   - Product auto-adds to cart

2. **Option B: Search for Product**
   - Click **Product Search**
   - Type product name or SKU: "Samsung"
   - Select from dropdown
   - Enter quantity

   ```
   Product: Samsung Galaxy A13
   Unit Price: 1,500 SAR
   Qty: [1]
   Line Total: 1,500 SAR
   [Add]
   ```

3. **Add Second Item:**

   ```
   Product: USB-C Cable
   Unit Price: 50 SAR
   Qty: [3]
   Line Total: 150 SAR
   [Add]
   ```

4. **Cart shows:**

   ```
   ITEMS                            QTY    PRICE    TOTAL
   ──────────────────────────────────────────────────────
   Samsung Galaxy A13                1    1,500    1,500
   USB-C Cable                       3       50      150
   ──────────────────────────────────────────────────────
   SUBTOTAL:                                       1,650 SAR
   VAT (15%):                                        247.50 SAR
   TOTAL:                                          1,897.50 SAR
   ```

### Step 4: Select Customer

1. Click **Customer**

   ```
   ☐ Walk-in Customer (anonymous)
   ☑ Registered Customer (select below)
   
   [Search Customers...]
   ```

2. Options:
   - **Walk-in:** No customer record, no AR tracking
   - **Registered:** Select from customer list
   - **New Customer:** Create on-the-fly

3. Example: Select "ABC Trading LLC"

### Step 5: Apply Discount (if authorized)

1. Click **Discount**

   ```
   Discount Type:
   ☑ Percentage    ☐ Fixed Amount
   
   Amount: [5] %
   
   Applied Discount: 82.50 SAR
   New Total: 1,815 SAR
   
   [Apply] [Cancel]
   ```

2. Click **Apply**

3. System deducts from total

### Step 6: Select Payment Method

1. Click **Payment Method**

   ```
   Payment Type:
   ☑ Cash           ☐ Card           ☐ Cheque
   ☐ Bank Transfer  ☐ Promissory
   ```

2. Select **Cash**

3. Enter tendered amount:

   ```
   Sale Total:       1,815 SAR
   Tendered:         2,000 SAR
   Change:             185 SAR
   
   [Confirm Payment]
   ```

4. Click **Confirm Payment**

### Step 7: Complete Sale

1. System processes:

   ```
   ✓ Sale saved
   ✓ Stock decremented (3x USB cables, 1x Phone)
   ✓ Journal entries posted to GL
   ✓ Invoice generated
   ✓ ZATCA QR code included
   
   Sale #: INV-20240115-001
   Time: 14:35:22
   Cashier: Admin User
   ```

2. Options displayed:

   ```
   [Print Receipt]  [Email Receipt]  [New Sale]
   ```

3. Click **Print Receipt** (receipt prints at POS terminal)

4. Click **New Sale** (clears cart for next customer)

### Step 8: Offline Mode

*If internet connection is lost:*

1. System detects offline:

   ```
   ⚠️ OFFLINE MODE ACTIVE
   Internet connection unavailable.
   Transactions cached locally.
   
   Cached Transactions: 5
   Last Sync: 14:35:22
   ```

2. POS continues working:
   - Create sales normally
   - All data stored locally
   - No network calls

3. When internet restored:

   ```
   ✓ Connection restored
   Syncing 5 cached transactions...
   ✓ Sync complete
   ```

   - All local sales uploaded to server
   - Stock updates propagated
   - Idempotent API prevents duplicates

### Step 9: View Shift Summary

1. End of day, click **Shift Summary**

   ```
   ╔═══════════════════════════════════╗
   ║ SHIFT SUMMARY - Jan 15, 2024      ║
   ╠═══════════════════════════════════╣
   ║ Shift Start Time:    08:00:00     ║
   ║ Shift End Time:      16:30:15     ║
   ║                                   ║
   ║ TRANSACTIONS:                     ║
   ║ Total Sales:              23      ║
   ║ Total Revenue:       45,250 SAR   ║
   ║ Total VAT:            6,787.50   ║
   ║                                   ║
   ║ PAYMENT BREAKDOWN:                ║
   ║ Cash:               35,000 SAR    ║
   ║ Card:                8,000 SAR    ║
   ║ Cheque:              2,250 SAR    ║
   ║                                   ║
   ║ INVENTORY IMPACT:                 ║
   ║ Items Sold:              127      ║
   ║ Stock Decremented:        ✓       ║
   ║                                   ║
   ║ [Print Summary]  [Close Shift]    ║
   ╚═══════════════════════════════════╝
   ```

2. Click **Close Shift** to end day

3. System prepares for next shift

### Step 10: Daily POS Reconciliation

1. Navigate: **POS** → **Sales Summary** → Today's Date

2. Compare:
   ```
   POS Register Total:       45,250 SAR
   Cash Counted:             45,250 SAR
   Difference:                    0 SAR ✓
   ```

3. If difference:
   - Investigate discrepancies
   - Record cash over/short adjustment
   - Post to GL

✅ **Checkpoint:** POS operations complete!

---

## 🎓 Learning Paths

### Path 1: Sales Manager
1. Tutorial 1: Inventory Setup
2. Tutorial 2: Create First Sale
3. Tutorial 4: Customer Payments & AR Aging
4. Tutorial 10: POS Operations

### Path 2: Accounting Manager
1. Tutorial 6: Journal Entries & GL
2. Tutorial 7: Month-End Close
3. Tutorial 9: Bank Reconciliation
4. Tutorial 8: ZATCA Setup (if in Saudi Arabia)

### Path 3: Purchasing Manager
1. Tutorial 1: Inventory Setup
2. Tutorial 3: Purchase Order Workflow
3. Tutorial 5: Supplier Payments & AP Aging
4. Tutorial 9: Bank Reconciliation

### Path 4: Store Manager
1. Tutorial 1: Inventory Setup
2. Tutorial 10: POS Operations
3. Tutorial 2: Create First Sale

---

## 📚 Additional Resources

- **Video Tutorials**: Available on support portal
- **API Documentation**: See `/docs/api`
- **Community Forum**: forum.syntekpro.com
- **Support Email**: support@syntekpro.com

---

**Last Updated:** January 2024
