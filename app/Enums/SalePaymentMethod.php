<?php

namespace App\Enums;

enum SalePaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case CreditAccount = 'credit_account';
}
