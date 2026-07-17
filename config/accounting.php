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
];
