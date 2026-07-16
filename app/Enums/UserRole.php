<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case ShopManager = 'shop_manager';
    case Cashier = 'cashier';
}
