<?php

return [
    // Phase 6 assumption: fiscal year is fixed to Jan-Dec (calendar year).
    'fiscal_year' => [
        'type' => 'calendar_year',
        'starts_month' => 1,
    ],

    'pos' => [
        'cash_account_code' => env('GL_POS_CASH_ACCOUNT_CODE', '1010'),
        'sales_revenue_account_code' => env('GL_POS_SALES_REVENUE_ACCOUNT_CODE', '4100'),
        'vat_payable_account_code' => env('GL_POS_VAT_PAYABLE_ACCOUNT_CODE', '2200'),
        'cogs_account_code' => env('GL_POS_COGS_ACCOUNT_CODE', '5100'),
    ],

    'purchasing' => [
        'inventory_account_code' => env('GL_PURCHASING_INVENTORY_ACCOUNT_CODE', '1200'),
        'input_vat_receivable_account_code' => env('GL_PURCHASING_INPUT_VAT_ACCOUNT_CODE', '1300'),
        'accounts_payable_account_code' => env('GL_PURCHASING_AP_ACCOUNT_CODE', '2100'),
        'payment_cash_or_bank_account_code' => env('GL_PURCHASING_PAYMENT_CASH_ACCOUNT_CODE', '1020'),
    ],

    'receivables' => [
        'accounts_receivable_account_code' => env('GL_RECEIVABLES_AR_ACCOUNT_CODE', '1100'),
        'payment_cash_or_bank_account_code' => env('GL_RECEIVABLES_PAYMENT_CASH_ACCOUNT_CODE', '1020'),
    ],

    'returns' => [
        'damaged_goods_account_code' => env('GL_RETURNS_DAMAGED_GOODS_ACCOUNT_CODE', '5500'),
        'due_from_supplier_account_code' => env('GL_RETURNS_DUE_FROM_SUPPLIER_ACCOUNT_CODE', '1150'),
    ],
];
