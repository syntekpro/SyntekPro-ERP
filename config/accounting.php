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
    ],

    'purchasing' => [
        'inventory_account_code' => env('GL_PURCHASING_INVENTORY_ACCOUNT_CODE', '1200'),
        'input_vat_receivable_account_code' => env('GL_PURCHASING_INPUT_VAT_ACCOUNT_CODE', '1300'),
        'accounts_payable_account_code' => env('GL_PURCHASING_AP_ACCOUNT_CODE', '2100'),
        'payment_cash_or_bank_account_code' => env('GL_PURCHASING_PAYMENT_CASH_ACCOUNT_CODE', '1020'),
        'posting_shop_id' => env('GL_PURCHASING_POSTING_SHOP_ID'),
    ],
];
