<?php

namespace App\Enums;

enum SupplierBillStatus: string
{
    case Open = 'open';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
}
